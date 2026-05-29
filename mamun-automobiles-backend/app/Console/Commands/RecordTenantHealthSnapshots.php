<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Tenant;
use App\Services\CustomerSuccessService;

class RecordTenantHealthSnapshots extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'saas:record-health-snapshots';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Compute and archive daily customer success health scores and trigger retention warning workflows for all active tenants.';

    protected $successService;

    /**
     * Create a new command instance.
     */
    public function __construct(CustomerSuccessService $successService)
    {
        parent::__construct();
        $this->successService = $successService;
    }

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info("Compiling daily tenant success health telemetry snapshots...");

        $tenants = Tenant::all();
        $count = 0;

        foreach ($tenants as $tenant) {
            try {
                $this->successService->recordHealthSnapshot($tenant->id);
                $this->info("Successfully archived health snapshot for Tenant #{$tenant->id} ({$tenant->company_name})");
                $count++;
            } catch (\Exception $e) {
                $this->error("Failed to compile snapshot for Tenant #{$tenant->id}: " . $e->getMessage());
            }
        }

        $this->info("Completed success profiling snapshots execution. Processed {$count} tenant(s).");
        return Command::SUCCESS;
    }
}
