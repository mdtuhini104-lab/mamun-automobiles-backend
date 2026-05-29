<?php

namespace Database\Seeders;

use App\Models\KnowledgeBaseArticle;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class KnowledgeBaseSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $articles = [
            [
                'title' => 'SaaS Tenant Guided Onboarding Walkthrough',
                'category' => 'onboarding',
                'content' => "# Onboarding Walkthrough\n\nWelcome to Mamun Automobiles ERP. Follow these steps to set up your workshop instance:\n\n1. **Configure Branch Setup**: Go to Multi-Branch Settings and create your primary workshop branch.\n2. **Import Initial Customers**: Go to the Onboarding wizard and upload your customer records CSV file.\n3. **Set Up Parts & Stock**: Upload your current spare parts list with target thresholds for auto-purchasing.\n4. **Map Chart of Accounts**: Ensure your cashbooks, bank ledger accounts, and revenue tracking codes are mapped correctly.",
            ],
            [
                'title' => 'Configuring TOTP MFA Security',
                'category' => 'technical',
                'content' => "# Multi-Factor Authentication (MFA) Setup\n\nProtect your SaaS credentials with TOTP:\n\n1. Navigate to **Security Center**.\n2. Click the **Enable TOTP MFA** button.\n3. Open your Authenticator App (Google Authenticator, Authy) and scan the QR code.\n4. Enter the verification code to activate.\n5. **Important**: Store the generated backup recovery codes offline. You have a **3-day grace period** before MFA setup becomes strictly enforced.",
            ],
            [
                'title' => 'Stripe Subscription and Invoice Gateway Settings',
                'category' => 'billing',
                'content' => "# Billing & Invoice Settings\n\nConfigure your billing and customer payments:\n\n* **Subscription Renewal**: View plan tier limits under SaaS Subscription.\n* **SSLCommerz & Stripe Webhooks**: We support replay safety and retry queues. If callbacks are delayed, verify hook URLs under integration settings.\n* **Client Payments**: Generate invoice items from resolved Job Cards and mail a secure checkout link directly to the customer.",
            ],
            [
                'title' => 'Technician Shift Allocation & Workshop Bay Scheduling',
                'category' => 'general',
                'content' => "# Workshop Execution Guide\n\nMaximize bay capacity and mechanic efficiency:\n\n* **Shift Management**: Assign morning/evening shifts under Workforce Scheduling.\n* **Automatic Job Assignment**: Enable the smart scheduling engine to allocate jobs based on skills and workloads.\n* **Bay Saturation**: Monitor bay utilization ratios dynamically under Live Workshop board.",
            ],
        ];

        foreach ($articles as $art) {
            KnowledgeBaseArticle::updateOrCreate(
                ['slug' => Str::slug($art['title'])],
                [
                    'title' => $art['title'],
                    'category' => $art['category'],
                    'content' => $art['content'],
                    'is_published' => true,
                    'tenant_id' => null // Null makes it globally accessible
                ]
            );
        }
    }
}
