<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Setting;

class SettingsSeeder extends Seeder
{
    public function run(): void
    {
        $defaults = [
            // General Settings
            'company_name' => 'Mamun Automobiles ERP',
            'company_address' => '123 Workshop Avenue, Auto City',
            'company_phone' => '+1234567890',
            'company_email' => 'info@mamunautomobiles.com',
            'company_website' => 'https://mamunautomobiles.com',
            'currency' => 'USD',
            'timezone' => 'UTC',
            'language' => 'en',

            // Invoice Settings
            'invoice_prefix' => 'INV-',
            'invoice_footer' => 'Thank you for your business!',
            'tax_percentage' => '15',
            'discount_enabled' => 'true',
            'due_warning_days' => '7',
            'auto_invoice_numbering' => 'true',
            'thermal_invoice' => 'false',
            'a4_invoice' => 'true',

            // Security
            'session_timeout' => '120',
            'min_password_length' => '8',
            'login_attempt_limit' => '5',
            'two_factor_auth' => 'false',
            'maintenance_mode' => 'false',

            // Notifications
            'email_notifications' => 'true',
            'sms_notifications' => 'false',
            'whatsapp_notifications' => 'false',
            'low_stock_alerts' => 'true',
            'appointment_reminders' => 'true',
            'payroll_alerts' => 'true',

            // SMTP
            'smtp_host' => 'smtp.mailtrap.io',
            'smtp_port' => '2525',
            'smtp_username' => '',
            'smtp_password' => '',
            'smtp_encryption' => 'tls',

            // Backup
            'auto_backup' => 'true',
            'backup_retention_days' => '30',

            // POS/Printer
            'thermal_printer_width' => '80mm',
            'paper_size' => 'A4',
            'barcode_toggle' => 'true',
            'qrcode_toggle' => 'true',
            'receipt_footer' => 'No refunds after 7 days.',

            // SaaS
            'tenant_registration' => 'true',
            'trial_duration_days' => '14',
            'subscription_mode' => 'active',
            'branch_limit' => '3',
        ];

        foreach ($defaults as $key => $value) {
            Setting::updateOrCreate(['key' => $key], ['value' => $value]);
        }
    }
}
