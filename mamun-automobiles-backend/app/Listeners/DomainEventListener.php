<?php

namespace App\Listeners;

use App\Models\User;
use App\Notifications\SystemNotification;
use Illuminate\Support\Facades\Notification;

class DomainEventListener
{
    /**
     * Get list of administrative users (Super Admin, Admin, Manager, Supervisor).
     */
    protected function getAdminsAndManagers()
    {
        return User::whereHas('roles', function($q) {
            $q->whereIn('name', ['Super Admin', 'Admin', 'Manager', 'Supervisor']);
        })->get();
    }

    /**
     * Get list of managers and supervisors.
     */
    protected function getManagersAndSupervisors()
    {
        return User::whereHas('roles', function($q) {
            $q->whereIn('name', ['Super Admin', 'Admin', 'Manager', 'Supervisor']);
        })->get();
    }

    /**
     * Get list of cashiers and managers.
     */
    protected function getCashiersAndManagers()
    {
        return User::whereHas('roles', function($q) {
            $q->whereIn('name', ['Super Admin', 'Admin', 'Manager', 'Cashier']);
        })->get();
    }

    /**
     * Handle QuotationApproved event.
     */
    public function handleQuotationApproved(\App\Events\QuotationApproved $event): void
    {
        $quotation = $event->quotation;
        $title = 'Quotation Approved';
        $message = "Quotation {$quotation->quotation_no} has been approved by the customer. Ready for Work Order creation.";
        $type = 'quotation_approved';
        
        $recipients = $this->getManagersAndSupervisors();
        Notification::send($recipients, new SystemNotification($title, $message, $type, [
            'quotation_id' => $quotation->id,
            'quotation_no' => $quotation->quotation_no,
            'customer_name' => $quotation->jobCard->customer->name ?? 'Customer'
        ]));
    }

    /**
     * Handle WorkOrderCreated event.
     */
    public function handleWorkOrderCreated(\App\Events\WorkOrderCreated $event): void
    {
        $workOrder = $event->workOrder;
        $jobCard = $workOrder->jobCard;
        $title = 'Work Order Created';
        $regNo = $jobCard->vehicle->registration_no ?? 'Unknown';
        $message = "Work Order {$workOrder->work_order_no} has been created for vehicle {$regNo}. Ready for technician allocation.";
        $type = 'work_order_created';

        $recipients = $this->getManagersAndSupervisors();
        Notification::send($recipients, new SystemNotification($title, $message, $type, [
            'work_order_id' => $workOrder->id,
            'work_order_no' => $workOrder->work_order_no,
            'job_card_id' => $jobCard->id,
            'vehicle_reg' => $regNo
        ]));
    }

    /**
     * Handle TechnicianAssigned event.
     */
    public function handleTechnicianAssigned(\App\Events\TechnicianAssigned $event): void
    {
        $workOrder = $event->workOrder;
        $technician = $event->technician;
        $title = 'Technician Assigned';
        $message = "You have been assigned to Work Order {$workOrder->work_order_no}.";
        $type = 'technician_assigned';

        // Notify technician
        $technician->notify(new SystemNotification($title, $message, $type, [
            'work_order_id' => $workOrder->id,
            'work_order_no' => $workOrder->work_order_no
        ]));

        // Notify supervisors as well
        $supervisors = $this->getManagersAndSupervisors()->filter(function($user) use ($technician) {
            return $user->id !== $technician->id;
        });
        
        Notification::send($supervisors, new SystemNotification(
            'Technician Allocated',
            "Technician {$technician->name} has been assigned to Work Order {$workOrder->work_order_no}.",
            'technician_allocated',
            [
                'work_order_id' => $workOrder->id,
                'technician_id' => $technician->id,
                'technician_name' => $technician->name
            ]
        ));
    }

    /**
     * Handle TaskStarted event.
     */
    public function handleTaskStarted(\App\Events\TaskStarted $event): void
    {
        $task = $event->task;
        $workOrder = $task->workOrder ?? null;
        $technicianName = $task->assignedTechnician->name ?? 'Technician';
        $title = 'Task Started';
        $message = "{$technicianName} started task '{$task->task_name}' on Work Order " . ($workOrder->work_order_no ?? '');
        $type = 'task_started';

        $recipients = $this->getManagersAndSupervisors();
        Notification::send($recipients, new SystemNotification($title, $message, $type, [
            'task_id' => $task->id,
            'task_name' => $task->task_name,
            'work_order_id' => $workOrder->id ?? null
        ]));
    }

    /**
     * Handle TaskCompleted event.
     */
    public function handleTaskCompleted(\App\Events\TaskCompleted $event): void
    {
        $task = $event->task;
        $workOrder = $task->workOrder ?? null;
        $technicianName = $task->assignedTechnician->name ?? 'Technician';
        $title = 'Task Completed';
        $message = "Task '{$task->task_name}' has been completed by {$technicianName} on Work Order " . ($workOrder->work_order_no ?? '');
        $type = 'task_completed';

        $recipients = $this->getManagersAndSupervisors();
        Notification::send($recipients, new SystemNotification($title, $message, $type, [
            'task_id' => $task->id,
            'task_name' => $task->task_name,
            'work_order_id' => $workOrder->id ?? null
        ]));
    }

    /**
     * Handle AdditionalConsumptionAdded event.
     */
    public function handleAdditionalConsumptionAdded(\App\Events\AdditionalConsumptionAdded $event): void
    {
        $consumption = $event->consumption;
        $workOrder = $consumption->workOrder ?? null;
        $title = 'Additional Consumption';
        $message = "Additional materials/parts consumed on Work Order " . ($workOrder->work_order_no ?? '') . " awaiting approval.";
        $type = 'additional_consumption';

        $recipients = $this->getManagersAndSupervisors();
        Notification::send($recipients, new SystemNotification($title, $message, $type, [
            'consumption_id' => $consumption->id,
            'work_order_id' => $workOrder->id ?? null,
            'part_id' => $consumption->part_id,
            'qty' => $consumption->quantity
        ]));
    }

    /**
     * Handle InvoiceGenerated event.
     */
    public function handleInvoiceGenerated(\App\Events\InvoiceGenerated $event): void
    {
        $invoice = $event->invoice;
        $title = 'Invoice Generated';
        $regNo = $invoice->jobCard->vehicle->registration_no ?? 'Unknown';
        $message = "Invoice {$invoice->invoice_no} has been generated for vehicle {$regNo}.";
        $type = 'invoice_generated';

        $recipients = $this->getCashiersAndManagers();
        Notification::send($recipients, new SystemNotification($title, $message, $type, [
            'invoice_id' => $invoice->id,
            'invoice_no' => $invoice->invoice_no,
            'job_card_id' => $invoice->job_card_id,
            'amount' => $invoice->grand_total
        ]));
    }

    /**
     * Handle VehicleDelivered event.
     */
    public function handleVehicleDelivered(\App\Events\VehicleDelivered $event): void
    {
        $delivery = $event->delivery;
        $jobCard = $delivery->jobCard;
        $title = 'Vehicle Delivered';
        $regNo = $jobCard->vehicle->registration_no ?? 'Unknown';
        $message = "Vehicle {$regNo} has been successfully delivered to {$delivery->delivered_to}.";
        $type = 'vehicle_delivered';

        $recipients = $this->getManagersAndSupervisors();
        Notification::send($recipients, new SystemNotification($title, $message, $type, [
            'delivery_id' => $delivery->id,
            'job_card_id' => $jobCard->id,
            'delivered_to' => $delivery->delivered_to
        ]));
    }
}
