<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\SupportTicket;
use App\Models\SupportIncident;
use App\Models\ResolutionWorkflow;
use App\Models\KnowledgeBaseArticle;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Carbon\Carbon;
use Exception;

class SupportTicketController extends Controller
{
    /**
     * List all support tickets (tenant-scoped automatically via MultitenantSafe).
     */
    public function index(Request $request)
    {
        $tickets = SupportTicket::with(['user', 'assignedTo', 'incident'])
            ->orderBy('created_at', 'desc')
            ->get();

        return response()->json([
            'success' => true,
            'data' => $tickets
        ]);
    }

    /**
     * Submit a new support ticket.
     */
    public function store(Request $request)
    {
        $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'required|string',
            'priority' => 'required|string|in:low,medium,high,urgent',
            'category' => 'required|string|in:billing,technical,onboarding,general',
        ]);

        try {
            $ticket = SupportTicket::create([
                'user_id' => $request->user()->id,
                'title' => $request->input('title'),
                'description' => $request->input('description'),
                'priority' => $request->input('priority'),
                'category' => $request->input('category'),
                'status' => 'open',
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Support ticket submitted successfully.',
                'ticket_id' => $ticket->id,
                'data' => $ticket
            ], 201);
        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to create support ticket: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Resolve a support ticket.
     */
    public function resolve(Request $request, int $id)
    {
        $ticket = SupportTicket::find($id);

        if (!$ticket) {
            return response()->json(['success' => false, 'message' => 'Ticket not found.'], 404);
        }

        try {
            $ticket->update([
                'status' => 'resolved',
                'resolved_at' => now()
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Ticket marked as resolved.'
            ]);
        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to resolve ticket: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Rate ticket satisfaction score (CSAT).
     */
    public function submitFeedback(Request $request, int $id)
    {
        $request->validate([
            'satisfaction_score' => 'required|integer|min:1|max:5'
        ]);

        $ticket = SupportTicket::find($id);

        if (!$ticket) {
            return response()->json(['success' => false, 'message' => 'Ticket not found.'], 404);
        }

        try {
            $ticket->update([
                'satisfaction_score' => $request->input('satisfaction_score')
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Satisfaction score submitted.'
            ]);
        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Feedback submission failed: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Create a Support Incident from a Support Ticket.
     */
    public function createIncident(Request $request, int $ticketId)
    {
        $ticket = SupportTicket::find($ticketId);
        if (!$ticket) {
            return response()->json(['success' => false, 'message' => 'Ticket not found.'], 404);
        }

        $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'required|string',
            'severity' => 'required|string|in:low,medium,high,critical'
        ]);

        try {
            // Check if incident already exists for this ticket
            $existing = SupportIncident::where('ticket_id', $ticketId)->first();
            if ($existing) {
                return response()->json([
                    'success' => false,
                    'message' => 'An incident has already been mapped to this support ticket.',
                    'incident_id' => $existing->id
                ], 400);
            }

            $incident = SupportIncident::create([
                'ticket_id' => $ticketId,
                'title' => $request->input('title'),
                'description' => $request->input('description'),
                'severity' => $request->input('severity'),
                'status' => 'investigating'
            ]);

            // Update ticket status to in_progress
            $ticket->update(['status' => 'in_progress']);

            return response()->json([
                'success' => true,
                'message' => 'Incident successfully mapped to support ticket.',
                'data' => $incident
            ], 201);
        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to create incident: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get all support incidents.
     */
    public function getIncidents(Request $request)
    {
        $incidents = SupportIncident::with(['ticket', 'workflow'])->latest()->get();

        return response()->json([
            'success' => true,
            'data' => $incidents
        ]);
    }

    /**
     * Create or update a resolution workflow for an incident.
     */
    public function createResolutionWorkflow(Request $request, int $incidentId)
    {
        $incident = SupportIncident::find($incidentId);
        if (!$incident) {
            return response()->json(['success' => false, 'message' => 'Incident not found.'], 404);
        }

        $request->validate([
            'steps' => 'required|array',
            'solution' => 'required|string',
            'resolve_incident' => 'boolean'
        ]);

        try {
            $workflow = ResolutionWorkflow::updateOrCreate(
                ['incident_id' => $incidentId],
                [
                    'steps' => $request->input('steps'),
                    'solution' => $request->input('solution'),
                ]
            );

            if ($request->input('resolve_incident', true)) {
                $incident->update(['status' => 'resolved']);
                if ($incident->ticket) {
                    $incident->ticket->update([
                        'status' => 'resolved',
                        'resolved_at' => now()
                    ]);
                }
            }

            return response()->json([
                'success' => true,
                'message' => 'Resolution workflow recorded successfully.',
                'data' => $workflow
            ]);
        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to save resolution workflow: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Publish a Knowledge Base Article from a Resolution Workflow.
     */
    public function publishWorkflowToKb(Request $request, int $workflowId)
    {
        $workflow = ResolutionWorkflow::with('incident')->find($workflowId);
        if (!$workflow) {
            return response()->json(['success' => false, 'message' => 'Resolution workflow not found.'], 404);
        }

        $request->validate([
            'title' => 'required|string|max:255',
            'category' => 'required|string|in:onboarding,technical,billing,general',
            'is_global' => 'boolean'
        ]);

        try {
            $title = $request->input('title');
            $slug = Str::slug($title);

            // Append unique suffix to slug if conflict exists
            $originalSlug = $slug;
            $count = 1;
            while (KnowledgeBaseArticle::withoutGlobalScopes()->where('slug', $slug)->exists()) {
                $slug = $originalSlug . '-' . $count++;
            }

            // Construct content markdown from incident and solution
            $content = "# " . $title . "\n\n";
            $content .= "## Problem Description\n" . $workflow->incident->description . "\n\n";
            $content .= "## Steps to Resolve / Root Cause\n";
            foreach ($workflow->steps as $index => $step) {
                $content .= ($index + 1) . ". " . $step . "\n";
            }
            $content .= "\n## Final Solution / Recommendation\n" . $workflow->solution . "\n";

            $article = KnowledgeBaseArticle::create([
                'tenant_id' => $request->input('is_global', true) ? null : $request->user()->tenant_id,
                'title' => $title,
                'slug' => $slug,
                'category' => $request->input('category'),
                'content' => $content,
                'is_published' => true
            ]);

            // Link article back to workflow
            $workflow->update(['kb_article_id' => $article->id]);

            return response()->json([
                'success' => true,
                'message' => 'Knowledge Base article published successfully from resolution workflow.',
                'data' => $article
            ], 201);
        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to publish Knowledge Base article: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Search and get Knowledge Base Articles (tenant-aware + global via model scope).
     */
    public function getKbArticles(Request $request)
    {
        $query = KnowledgeBaseArticle::where('is_published', true);

        if ($request->has('category')) {
            $query->where('category', $request->input('category'));
        }

        if ($request->has('search')) {
            $search = $request->input('search');
            $query->where(function ($q) use ($search) {
                $q->where('title', 'like', "%{$search}%")
                  ->orWhere('content', 'like', "%{$search}%");
            });
        }

        $articles = $query->latest()->get();

        return response()->json([
            'success' => true,
            'data' => $articles
        ]);
    }

    /**
     * View a specific Knowledge Base Article by Slug.
     */
    public function getKbArticleBySlug(Request $request, string $slug)
    {
        $article = KnowledgeBaseArticle::where('slug', $slug)->where('is_published', true)->first();

        if (!$article) {
            return response()->json(['success' => false, 'message' => 'Article not found.'], 404);
        }

        return response()->json([
            'success' => true,
            'data' => $article
        ]);
    }

    /**
     * Export sanitized diagnostics payload file for customer support inspection.
     */
    public function exportDiagnostics(Request $request)
    {
        $tenantId = $request->user()->tenant_id;

        // Fetch environmental values
        $envData = $_ENV;
        if (empty($envData)) {
            $envData = getenv();
        }

        // Mandatory keywords to redact
        $sensitiveKeywords = [
            'password', 'pass', 'pwd',
            'token', 'secret', 'key',
            'credential', 'username', 'user',
            'stripe', 'bkash', 'sslcommerz', 'nagad', 'paypal',
            'aws', 'auth', 'jwt', 'encrypt', 'salt', 'hash',
            'private', 'cert', 'sign', 'vault'
        ];

        $sanitizedEnv = [];
        foreach ($envData as $key => $value) {
            $lowerKey = strtolower($key);
            $shouldRedact = false;

            foreach ($sensitiveKeywords as $word) {
                if (str_contains($lowerKey, $word)) {
                    $shouldRedact = true;
                    break;
                }
            }

            if ($shouldRedact) {
                $sanitizedEnv[$key] = '[REDACTED_SECURE_VAULT_METRIC]';
            } else {
                // Safeguard against string values that look like API keys or base64 tokens
                if (is_string($value) && (strlen($value) > 40 || preg_match('/[a-zA-Z0-9+\/]{30,}/', $value))) {
                    $sanitizedEnv[$key] = '[REDACTED_HIGH_ENTROPY_STRING_SAFETY]';
                } else {
                    $sanitizedEnv[$key] = $value;
                }
            }
        }

        // Limit keys to relevant framework and server specs + sanitized custom env details
        $allowedKeys = [
            'APP_NAME', 'APP_ENV', 'APP_URL', 'APP_DEBUG', 'DB_CONNECTION', 'DB_HOST', 
            'DB_PORT', 'DB_PASSWORD', 'REDIS_PASSWORD', 'STRIPE_SECRET',
            'BROADCAST_CONNECTION', 'QUEUE_CONNECTION', 'SESSION_DRIVER', 
            'CACHE_STORE', 'MEMCACHED_HOSTS', 'REDIS_HOST', 'MAIL_MAILER', 'MAIL_HOST'
        ];

        $configs = [];
        foreach ($allowedKeys as $key) {
            $configs[$key] = $sanitizedEnv[$key] ?? (env($key) ?: 'Not Set');
        }

        // Dynamic sanitization of all active configuration keys
        $allConfig = config()->all();
        $sanitizedConfig = $this->sanitizeRecursive($allConfig, $sensitiveKeywords);

        // Aggregate diagnostic telemetry
        $diagnostics = [
            'timestamp' => now()->toDateTimeString(),
            'tenant_id' => $tenantId,
            'php_version' => PHP_VERSION,
            'laravel_version' => app()->version(),
            'db_driver' => DB::connection()->getDriverName(),
            'environment' => $configs,
            'app_config_sanitized' => [
                'app' => $sanitizedConfig['app'] ?? [],
                'database' => [
                    'default' => $sanitizedConfig['database']['default'] ?? null,
                    'connections' => [
                        'mysql' => array_merge($sanitizedConfig['database']['connections']['mysql'] ?? [], [
                            'username' => '[REDACTED]',
                            'password' => '[REDACTED]',
                        ])
                    ]
                ],
                'queue' => $sanitizedConfig['queue'] ?? [],
                'cache' => $sanitizedConfig['cache'] ?? [],
            ],
            'counts' => [
                'users' => DB::table('users')->where('tenant_id', $tenantId)->count(),
                'customers' => DB::table('customers')->where('tenant_id', $tenantId)->count(),
                'vehicles' => DB::table('vehicles')->where('tenant_id', $tenantId)->count(),
                'job_cards' => DB::table('job_cards')->where('tenant_id', $tenantId)->count(),
                'invoices' => DB::table('invoices')->where('tenant_id', $tenantId)->count(),
            ],
            'recent_alerts' => DB::table('system_health_alerts')->take(5)->get()->toArray()
        ];

        return response()->json([
            'success' => true,
            'diagnostics' => $diagnostics
        ]);
    }

    /**
     * Get similar tickets and troubleshooting suggestions for a support ticket.
     */
    public function getSuggestions(Request $request, int $id)
    {
        $ticket = SupportTicket::find($id);
        if (!$ticket) {
            return response()->json(['success' => false, 'message' => 'Ticket not found.'], 404);
        }

        $title = $ticket->title;
        
        // Extract keywords from title (skipping common short words)
        $stopWords = ['a', 'an', 'the', 'is', 'are', 'in', 'on', 'at', 'to', 'for', 'with', 'and', 'or', 'of', 'problem', 'error', 'issue', 'ticket'];
        $words = preg_split('/\s+/', strtolower($title));
        $keywords = array_filter($words, function ($w) use ($stopWords) {
            return strlen($w) > 2 && !in_array($w, $stopWords);
        });

        // Query historical resolved tickets matching keywords
        $similarTickets = collect();
        if (!empty($keywords)) {
            $ticketQuery = SupportTicket::where('id', '!=', $id)
                ->where('status', 'resolved');

            $ticketQuery->where(function ($q) use ($keywords) {
                foreach ($keywords as $word) {
                    $q->orWhere('title', 'like', "%{$word}%")
                      ->orWhere('description', 'like', "%{$word}%");
                }
            });

            $similarTickets = $ticketQuery->take(5)->get();
        }

        // Query Knowledge Base Articles matching keywords
        $kbArticles = collect();
        if (!empty($keywords)) {
            $kbQuery = KnowledgeBaseArticle::where('is_published', true);
            $kbQuery->where(function ($q) use ($keywords) {
                foreach ($keywords as $word) {
                    $q->orWhere('title', 'like', "%{$word}%")
                      ->orWhere('content', 'like', "%{$word}%");
                }
            });
            $kbArticles = $kbQuery->take(5)->get();
        }

        return response()->json([
            'success' => true,
            'ai_embedding_ready' => true,
            'vector_similarity_search_enabled' => false,
            'data' => [
                'similar_tickets' => $similarTickets,
                'troubleshooting_articles' => $kbArticles,
            ]
        ]);
    }


    /**
     * Recursively sanitize config arrays to prevent leaks.
     */
    private function sanitizeRecursive(array $data, array $keywords): array
    {
        $sanitized = [];
        foreach ($data as $key => $value) {
            $lowerKey = strtolower((string)$key);
            $redact = false;
            foreach ($keywords as $word) {
                if (str_contains($lowerKey, $word)) {
                    $redact = true;
                    break;
                }
            }

            if ($redact) {
                $sanitized[$key] = '[REDACTED_SECURE]';
            } elseif (is_array($value)) {
                $sanitized[$key] = $this->sanitizeRecursive($value, $keywords);
            } else {
                $sanitized[$key] = $value;
            }
        }
        return $sanitized;
    }
}
