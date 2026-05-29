<?php

namespace App\Services;

use Illuminate\Support\Facades\DB;
use Exception;

class SaaSPluginManager
{
    protected $featureFlag;

    public function __construct(FeatureFlagService $featureFlag)
    {
        $this->featureFlag = $featureFlag;
    }

    /**
     * Get list of all marketplace plugins with their active installation status for a tenant.
     */
    public function getPlugins(int $tenantId): array
    {
        $activePlugins = DB::table('tenant_feature_limits')
            ->where('tenant_id', $tenantId)
            ->where('feature_name', 'like', 'plugin_%')
            ->pluck('feature_name')
            ->toArray();

        $registry = [
            [
                'key' => 'plugin_whatsapp_crm',
                'name' => 'Automated WhatsApp CRM & Follow-ups',
                'description' => 'Dispatches service progress alerts, comeback warnings, and reminders directly via WhatsApp APIs.',
                'tier_requirement' => 'pro',
                'category' => 'Marketing & CRM',
            ],
            [
                'key' => 'plugin_insurance_claims',
                'name' => 'Corporate Insurance Claims Engine',
                'description' => 'Automates document submissions, workflow states tracking, and direct insurer claims processing.',
                'tier_requirement' => 'pro',
                'category' => 'Billing',
            ],
            [
                'key' => 'plugin_bkash_gateway',
                'name' => 'bKash Merchant Pay Gateway',
                'description' => 'Processes instant client deposits, advance payments, and invoice settlements over bKash networks.',
                'tier_requirement' => 'base',
                'category' => 'Finance',
            ],
            [
                'key' => 'plugin_fleet_portal',
                'name' => 'Fleet Logistics & Bulk Approval Portal',
                'description' => 'Exposes specialized fleet customer dashboards for corporate fleet accounts tracking.',
                'tier_requirement' => 'enterprise',
                'category' => 'Operations',
            ],
            [
                'key' => 'plugin_ai_diagnostics',
                'name' => 'AI Predictive Load & Vehicle Diagnostics',
                'description' => 'Uses machine learning models to forecast peak AC maintenance seasons and customer repeat rates.',
                'tier_requirement' => 'enterprise',
                'category' => 'AI Operations',
            ]
        ];

        return array_map(function ($item) use ($activePlugins, $tenantId) {
            $item['is_installed'] = in_array($item['key'], $activePlugins);
            // Check if tenant's tier qualifies
            $item['is_allowed'] = $this->featureFlag->isEnabled($item['tier_requirement'] === 'enterprise' ? 'fleet_portal' : ($item['tier_requirement'] === 'pro' ? 'ai_quota_assistant' : 'view_dashboard'), $tenantId);
            return $item;
        }, $registry);
    }

    /**
     * Install or toggle installation status of a marketplace plugin.
     */
    public function togglePlugin(int $tenantId, string $pluginKey, bool $install): bool
    {
        $plugins = $this->getPlugins($tenantId);
        $target = null;
        foreach ($plugins as $p) {
            if ($p['key'] === $pluginKey) {
                $target = $p;
                break;
            }
        }

        if (!$target) {
            throw new Exception("Marketplace plugin not found in active registry.");
        }

        if ($install && !$target['is_allowed']) {
            throw new Exception("Your current subscription plan does not allow installing this plugin. Please upgrade.");
        }

        return DB::transaction(function () use ($tenantId, $pluginKey, $install) {
            if ($install) {
                DB::table('tenant_feature_limits')->updateOrInsert(
                    ['tenant_id' => $tenantId, 'feature_name' => $pluginKey],
                    ['limit_value' => 1, 'updated_at' => now(), 'created_at' => now()]
                );
            } else {
                DB::table('tenant_feature_limits')
                    ->where('tenant_id', $tenantId)
                    ->where('feature_name', $pluginKey)
                    ->delete();
            }

            // Log incident/audit
            DB::table('audit_logs')->insert([
                'tenant_id' => $tenantId,
                'user_id' => auth()->id(),
                'action' => $install ? 'install_plugin' : 'uninstall_plugin',
                'module' => 'marketplace',
                'details' => json_encode(['plugin_key' => $pluginKey]),
                'ip_address' => request()->ip(),
                'created_at' => now(),
                'updated_at' => now()
            ]);

            return true;
        });
    }
}
