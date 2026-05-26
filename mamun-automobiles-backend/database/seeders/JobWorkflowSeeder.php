<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\JobWorkflowStage;

class JobWorkflowSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $stages = [
            ['name' => 'Pending', 'slug' => 'pending', 'color' => '#6B7280', 'sort_order' => 1, 'is_default' => true, 'is_completed_stage' => false],
            ['name' => 'Diagnosing', 'slug' => 'diagnosing', 'color' => '#F59E0B', 'sort_order' => 2, 'is_default' => false, 'is_completed_stage' => false],
            ['name' => 'Waiting Parts', 'slug' => 'waiting-parts', 'color' => '#EF4444', 'sort_order' => 3, 'is_default' => false, 'is_completed_stage' => false],
            ['name' => 'In Progress', 'slug' => 'in-progress', 'color' => '#3B82F6', 'sort_order' => 4, 'is_default' => false, 'is_completed_stage' => false],
            ['name' => 'Quality Check', 'slug' => 'quality-check', 'color' => '#8B5CF6', 'sort_order' => 5, 'is_default' => false, 'is_completed_stage' => false],
            ['name' => 'Ready Delivery', 'slug' => 'ready-delivery', 'color' => '#10B981', 'sort_order' => 6, 'is_default' => false, 'is_completed_stage' => false],
            ['name' => 'Delivered', 'slug' => 'delivered', 'color' => '#059669', 'sort_order' => 7, 'is_default' => false, 'is_completed_stage' => true],
            ['name' => 'Cancelled', 'slug' => 'cancelled', 'color' => '#111827', 'sort_order' => 8, 'is_default' => false, 'is_completed_stage' => true],
        ];

        foreach ($stages as $stage) {
            JobWorkflowStage::create($stage);
        }
    }
}
