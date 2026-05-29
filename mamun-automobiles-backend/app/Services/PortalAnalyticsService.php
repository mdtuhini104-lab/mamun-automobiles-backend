<?php

namespace App\Services;

use Illuminate\Support\Facades\Cache;
use Carbon\Carbon;

class PortalAnalyticsService
{
    /**
     * Log a portal engagement event.
     */
    public function logEvent(string $uuid, string $eventType, array $metadata = []): array
    {
        $cacheKey = "portal_events_{$uuid}";
        $events = Cache::get($cacheKey, []);

        $eventRecord = [
            'event_type' => $eventType,
            'metadata' => $metadata,
            'timestamp' => now()->toDateTimeString()
        ];

        $events[] = $eventRecord;
        Cache::put($cacheKey, $events, now()->addDays(30));

        // Aggregate stats globally for Super Admin overview
        $globalKey = "portal_global_analytics";
        $globalStats = Cache::get($globalKey, [
            'total_actions' => 0,
            'by_event' => [],
            'active_uuids' => []
        ]);

        $globalStats['total_actions']++;
        $globalStats['by_event'][$eventType] = ($globalStats['by_event'][$eventType] ?? 0) + 1;
        if (!in_array($uuid, $globalStats['active_uuids'])) {
            $globalStats['active_uuids'][] = $uuid;
        }

        Cache::put($globalKey, $globalStats, now()->addDays(30));

        return $eventRecord;
    }

    /**
     * Analyze a portal session for abandonment and segmentations.
     */
    public function analyzeSession(string $uuid): array
    {
        $cacheKey = "portal_events_{$uuid}";
        $events = Cache::get($cacheKey, []);

        if (empty($events)) {
            return [
                'status' => 'inactive',
                'events_count' => 0,
                'segment' => 'No session activity tracked.'
            ];
        }

        $eventsCollection = collect($events);
        $firstView = Carbon::parse($eventsCollection->where('event_type', 'quotation_view')->first()['timestamp'] ?? $eventsCollection->first()['timestamp']);
        $lastAction = Carbon::parse($eventsCollection->last()['timestamp']);
        
        $hasApproved = $eventsCollection->contains('event_type', 'approval_complete') || 
                       $eventsCollection->contains('event_type', 'payment_success');

        if ($hasApproved) {
            return [
                'status' => 'completed',
                'first_view' => $firstView->toDateTimeString(),
                'last_action' => $lastAction->toDateTimeString(),
                'duration_seconds' => $firstView->diffInSeconds($lastAction),
                'segment' => 'Conversion successful.'
            ];
        }

        $now = now();
        $elapsedMinutes = $firstView->diffInMinutes($now);
        $elapsedHours = $firstView->diffInHours($now);

        $segment = 'active';
        if ($elapsedHours >= 72) {
            $segment = 'retention recovery candidate';
        } elseif ($elapsedHours >= 24) {
            $segment = 'follow-up candidate';
        } elseif ($elapsedHours >= 1) {
            $segment = 'abandonment';
        } elseif ($elapsedMinutes >= 15) {
            $segment = 'hesitation';
        }

        return [
            'status' => 'pending',
            'first_view' => $firstView->toDateTimeString(),
            'last_action' => $lastAction->toDateTimeString(),
            'elapsed_minutes' => $elapsedMinutes,
            'segment' => $segment,
            'events' => $events
        ];
    }

    /**
     * Get aggregate portal analytics overview for Super Admin.
     */
    public function getGlobalStats(): array
    {
        $globalKey = "portal_global_analytics";
        $stats = Cache::get($globalKey, [
            'total_actions' => 0,
            'by_event' => [],
            'active_uuids' => []
        ]);

        $segmented = [
            'hesitation' => 0,
            'abandonment' => 0,
            'follow_up' => 0,
            'retention_recovery' => 0,
            'active' => 0,
            'completed' => 0
        ];

        foreach ($stats['active_uuids'] as $uuid) {
            $analysis = $this->analyzeSession($uuid);
            if (($analysis['status'] ?? '') === 'completed') {
                $segmented['completed']++;
            } else {
                $seg = $analysis['segment'] ?? '';
                if ($seg === 'retention recovery candidate') {
                    $segmented['retention_recovery']++;
                } elseif ($seg === 'follow-up candidate') {
                    $segmented['follow_up']++;
                } elseif ($seg === 'abandonment') {
                    $segmented['abandonment']++;
                } elseif ($seg === 'hesitation') {
                    $segmented['hesitation']++;
                } else {
                    $segmented['active']++;
                }
            }
        }

        return [
            'total_active_sessions' => count($stats['active_uuids']),
            'total_logged_actions' => $stats['total_actions'],
            'event_breakdowns' => $stats['by_event'],
            'segments' => $segmented
        ];
    }
}
