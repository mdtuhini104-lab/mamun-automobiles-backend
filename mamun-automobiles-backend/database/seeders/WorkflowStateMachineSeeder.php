<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\WorkflowState;
use App\Models\WorkflowTransition;

class WorkflowStateMachineSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // 1. Seed Workflow States
        $states = [
            ['name' => 'pending_inspection', 'label' => 'Pending Inspection', 'entity_type' => 'job_card', 'is_active' => true],
            ['name' => 'inspected', 'label' => 'Inspected', 'entity_type' => 'job_card', 'is_active' => true],
            ['name' => 'quotation_draft', 'label' => 'Quotation Draft', 'entity_type' => 'job_card', 'is_active' => true],
            ['name' => 'quotation_approved', 'label' => 'Quotation Approved', 'entity_type' => 'job_card', 'is_active' => true],
            ['name' => 'work_order_active', 'label' => 'Work Order Active', 'entity_type' => 'job_card', 'is_active' => true],
            ['name' => 'completed', 'label' => 'Repair Completed', 'entity_type' => 'job_card', 'is_active' => true],
            ['name' => 'delivered', 'label' => 'Delivered', 'entity_type' => 'job_card', 'is_active' => true],
            ['name' => 'cancelled', 'label' => 'Cancelled', 'entity_type' => 'job_card', 'is_active' => true],
        ];

        $stateModels = [];
        foreach ($states as $state) {
            $stateModels[$state['name']] = WorkflowState::firstOrCreate(
                ['name' => $state['name']],
                $state
            );
        }

        // 2. Seed Allowed Transitions
        $transitions = [
            ['from' => 'pending_inspection', 'to' => 'inspected', 'role' => 'Technician'],
            ['from' => 'inspected', 'to' => 'quotation_draft', 'role' => 'Service Advisor'],
            ['from' => 'quotation_draft', 'to' => 'quotation_approved', 'role' => 'Service Advisor'],
            ['from' => 'quotation_approved', 'to' => 'work_order_active', 'role' => 'Workshop Manager'],
            ['from' => 'work_order_active', 'to' => 'completed', 'role' => 'Technician'],
            ['from' => 'completed', 'to' => 'delivered', 'role' => 'Service Advisor'],
            
            // Allow cancelling from any non-completed state
            ['from' => 'pending_inspection', 'to' => 'cancelled', 'role' => 'Service Advisor'],
            ['from' => 'inspected', 'to' => 'cancelled', 'role' => 'Service Advisor'],
            ['from' => 'quotation_draft', 'to' => 'cancelled', 'role' => 'Service Advisor'],
            ['from' => 'work_order_active', 'to' => 'cancelled', 'role' => 'Workshop Manager'],
        ];

        foreach ($transitions as $trans) {
            $fromState = $stateModels[$trans['from']];
            $toState = $stateModels[$trans['to']];

            WorkflowTransition::firstOrCreate(
                [
                    'from_state_id' => $fromState->id,
                    'to_state_id' => $toState->id,
                ],
                [
                    'role_required' => $trans['role'],
                    'trigger_event' => 'transition.' . $trans['from'] . '.' . $trans['to'],
                ]
            );
        }
    }
}
