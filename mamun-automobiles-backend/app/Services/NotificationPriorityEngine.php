<?php

namespace App\Services;

class NotificationPriorityEngine
{
    /**
     * Compute severity score and priority category for a notification alert.
     */
    public function prioritize(string $type, array $data): array
    {
        $score = 10.00; // base score
        $priority = 'low';
        $explanation = 'Standard operational update.';

        switch (strtolower($type)) {
            // Overdue repair tasks
            case 'overdue_repair':
                $score = 95.00;
                $priority = 'critical';
                $explanation = 'Critical Alert: technician active repair task time exceeds estimation, risking delayed vehicle handover.';
                break;
                
            // Comeback fault alerts
            case 'comeback_risk':
                $score = 80.00;
                $priority = 'high';
                $explanation = 'High Risk Alert: vehicle repeat diagnostics recorded within warranty coverage period, supervisor audit flagged.';
                break;

            // Unpaid supplier liabilities
            case 'supplier_debt':
                $score = 60.00;
                $priority = 'medium';
                $explanation = 'Medium Priority Alert: supplier payment deadline approaching within 48 hours, ledger action required.';
                break;

            // Workshop bay idle
            case 'idle_bay':
                $score = 25.00;
                $priority = 'low';
                $explanation = 'Low Priority Alert: workshop repair bay remains vacant with pending vehicles in intake queue.';
                break;

            default:
                $score = 15.00;
                $priority = 'low';
                $explanation = 'Standard notification alert.';
                break;
        }

        return [
            'type' => $type,
            'severity_score' => $score,
            'priority_category' => $priority,
            'explanation' => $explanation,
            'data' => $data,
            'confidence_score' => 98.00
        ];
    }
}
