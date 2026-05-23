$content = Get-Content 'src\views\dashboard\DashboardLayout.vue' -Raw
$content = $content -replace 'v-if="authStore.hasPermission\(''dashboard.view''\)" ', ''
$content = $content -replace 'pos.access', 'manage_invoices'
Set-Content 'src\views\dashboard\DashboardLayout.vue' -Value $content

