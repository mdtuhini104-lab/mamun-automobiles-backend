<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\NotificationTemplate;

class NotificationSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $templates = [
            [
                'name' => 'Invoice Generated (SMS)',
                'slug' => 'invoice-generated-sms',
                'channel' => 'sms',
                'event_key' => 'invoice.generated',
                'message_body' => 'Dear {{customer_name}}, your invoice {{invoice_no}} for amount {{payment_amount}} is generated. Please pay soon.',
                'variables' => json_encode(['customer_name', 'invoice_no', 'payment_amount']),
                'is_active' => true
            ],
            [
                'name' => 'Vehicle Ready (WhatsApp)',
                'slug' => 'vehicle-ready-whatsapp',
                'channel' => 'whatsapp',
                'event_key' => 'vehicle.ready',
                'message_body' => 'Hello {{customer_name}}, your vehicle {{vehicle_number}} is ready for delivery at {{workshop_name}}.',
                'variables' => json_encode(['customer_name', 'vehicle_number', 'workshop_name']),
                'is_active' => true
            ]
        ];

        foreach ($templates as $template) {
            NotificationTemplate::create($template);
        }
    }
}
