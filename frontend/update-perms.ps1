$content = Get-Content 'src\views\dashboard\DashboardLayout.vue' -Raw
$content = $content -replace 'customer.view', 'manage_customers'
$content = $content -replace 'vehicle.view', 'manage_vehicles'
$content = $content -replace 'job_card.view', 'manage_job_cards'
$content = $content -replace 'stock.view', 'manage_inventory'
$content = $content -replace 'purchase.view', 'manage_purchases'
$content = $content -replace 'invoice.view', 'manage_invoices'
$content = $content -replace 'staff.view', 'manage_users'
$content = $content -replace 'account.view', 'manage_finance'
$content = $content -replace 'transaction.view', 'manage_finance'
$content = $content -replace 'report.view', 'manage_finance'
Set-Content 'src\views\dashboard\DashboardLayout.vue' -Value $content

