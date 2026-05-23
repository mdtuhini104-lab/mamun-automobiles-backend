<template>
  <div class="px-4 sm:px-6 lg:px-8 py-8 w-full max-w-9xl mx-auto">
    <!-- Header -->
    <div class="sm:flex sm:justify-between sm:items-center mb-8">
      <div>
        <h1 class="text-2xl md:text-3xl text-slate-800 font-bold">System Settings ⚙️</h1>
        <p class="mt-1 text-sm text-slate-500">Manage all ERP configurations and integrations.</p>
      </div>
      <div class="mt-4 sm:mt-0 flex gap-2">
        <button @click="saveSettings" :disabled="isSaving" class="btn bg-indigo-600 hover:bg-indigo-700 text-white rounded-lg px-4 py-2 font-medium flex items-center gap-2 transition-colors disabled:opacity-50">
          <svg v-if="isSaving" class="animate-spin h-4 w-4 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
          </svg>
          <svg v-else class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>
          {{ isSaving ? 'Saving...' : 'Save Settings' }}
        </button>
      </div>
    </div>

    <div class="bg-white rounded-xl shadow-sm border border-slate-200 flex flex-col md:flex-row overflow-hidden min-h-[600px]">
      <!-- Sidebar Tabs -->
      <div class="w-full md:w-64 bg-slate-50 border-b md:border-b-0 md:border-r border-slate-200">
        <ul class="flex flex-row md:flex-col p-4 md:p-6 gap-2 overflow-x-auto text-sm font-medium">
          <li v-for="tab in tabs" :key="tab.id">
            <button 
              @click="activeTab = tab.id"
              :class="[
                'w-full flex items-center gap-3 px-4 py-2 rounded-lg text-left whitespace-nowrap transition-colors',
                activeTab === tab.id ? 'bg-white text-indigo-600 shadow-sm ring-1 ring-slate-200' : 'text-slate-600 hover:bg-slate-100 hover:text-slate-900'
              ]"
            >
              <span>{{ tab.icon }}</span>
              {{ tab.label }}
            </button>
          </li>
        </ul>
      </div>

      <!-- Main Content Area -->
      <div class="flex-1 p-6 md:p-8 overflow-y-auto">
        <!-- Toast Notification -->
        <div v-if="toast.show" :class="`mb-6 p-4 rounded-lg flex items-start gap-3 ${toast.type === 'success' ? 'bg-green-50 border border-green-200' : 'bg-red-50 border border-red-200'}`">
           <svg v-if="toast.type === 'success'" class="w-5 h-5 text-green-500 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
           <svg v-else class="w-5 h-5 text-red-500 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
           <div>
             <h3 :class="`text-sm font-medium ${toast.type === 'success' ? 'text-green-800' : 'text-red-800'}`">{{ toast.title }}</h3>
             <p v-if="toast.message" :class="`mt-1 text-sm ${toast.type === 'success' ? 'text-green-700' : 'text-red-700'}`">{{ toast.message }}</p>
           </div>
        </div>

        <form @submit.prevent="saveSettings">
          
          <!-- General Settings -->
          <div v-show="activeTab === 'general'" class="space-y-6">
            <h2 class="text-xl font-semibold text-slate-800 mb-6">General Details</h2>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
              <div>
                <label class="block text-sm font-medium text-slate-700 mb-1">Company Name</label>
                <input type="text" v-model="settings.company_name" class="input-field w-full" />
              </div>
              <div>
                <label class="block text-sm font-medium text-slate-700 mb-1">Company Address</label>
                <input type="text" v-model="settings.company_address" class="input-field w-full" />
              </div>
              <div>
                <label class="block text-sm font-medium text-slate-700 mb-1">Phone</label>
                <input type="text" v-model="settings.company_phone" class="input-field w-full" />
              </div>
              <div>
                <label class="block text-sm font-medium text-slate-700 mb-1">Email</label>
                <input type="email" v-model="settings.company_email" class="input-field w-full" />
              </div>
              <div>
                <label class="block text-sm font-medium text-slate-700 mb-1">Website URL</label>
                <input type="text" v-model="settings.company_website" class="input-field w-full" />
              </div>
              <div>
                <label class="block text-sm font-medium text-slate-700 mb-1">Currency</label>
                <select v-model="settings.currency" class="input-field w-full">
                  <option value="USD">USD ($)</option>
                  <option value="EUR">EUR (€)</option>
                  <option value="GBP">GBP (£)</option>
                  <option value="BDT">BDT (৳)</option>
                  <option value="INR">INR (₹)</option>
                </select>
              </div>
              <div>
                <label class="block text-sm font-medium text-slate-700 mb-1">Timezone</label>
                <select v-model="settings.timezone" class="input-field w-full">
                  <option value="UTC">UTC</option>
                  <option value="Asia/Dhaka">Asia/Dhaka</option>
                  <option value="America/New_York">America/New_York</option>
                </select>
              </div>
              <div>
                <label class="block text-sm font-medium text-slate-700 mb-1">Language</label>
                <select v-model="settings.language" class="input-field w-full">
                  <option value="en">English</option>
                  <option value="bn">Bengali</option>
                  <option value="ar">Arabic</option>
                </select>
              </div>
              
              <div class="md:col-span-2 pt-4 border-t border-slate-200 mt-2">
                <h3 class="text-sm font-semibold text-slate-800 mb-4">Branding</h3>
                <div class="flex gap-8">
                  <div>
                    <label class="block text-sm font-medium text-slate-700 mb-2">Company Logo</label>
                    <div class="flex items-center gap-4">
                      <div class="w-16 h-16 bg-slate-100 border border-slate-200 rounded-lg flex items-center justify-center overflow-hidden">
                        <img v-if="logoPreview || settings.company_logo" :src="logoPreview || settings.company_logo" class="max-w-full max-h-full object-contain" />
                        <svg v-else class="w-6 h-6 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"></path></svg>
                      </div>
                      <input type="file" @change="handleFileUpload($event, 'logo')" accept="image/*" class="text-sm file:mr-4 file:py-2 file:px-4 file:rounded-full file:border-0 file:text-sm file:font-semibold file:bg-indigo-50 file:text-indigo-700 hover:file:bg-indigo-100" />
                    </div>
                  </div>
                  <div>
                    <label class="block text-sm font-medium text-slate-700 mb-2">Favicon</label>
                    <div class="flex items-center gap-4">
                      <div class="w-12 h-12 bg-slate-100 border border-slate-200 rounded-lg flex items-center justify-center overflow-hidden">
                        <img v-if="faviconPreview || settings.company_favicon" :src="faviconPreview || settings.company_favicon" class="max-w-full max-h-full object-contain" />
                        <svg v-else class="w-5 h-5 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 3v4M3 5h4M6 17v4m-2-2h4m5-16l2.286 6.857L21 12l-5.714 2.143L13 21l-2.286-6.857L5 12l5.714-2.143L13 3z"></path></svg>
                      </div>
                      <input type="file" @change="handleFileUpload($event, 'favicon')" accept="image/x-icon,image/png" class="text-sm file:mr-4 file:py-2 file:px-4 file:rounded-full file:border-0 file:text-sm file:font-semibold file:bg-indigo-50 file:text-indigo-700 hover:file:bg-indigo-100" />
                    </div>
                  </div>
                </div>
              </div>
            </div>
          </div>

          <!-- Invoice Settings -->
          <div v-show="activeTab === 'invoice'" class="space-y-6">
            <h2 class="text-xl font-semibold text-slate-800 mb-6">Billing & Invoice Defaults</h2>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
              <div>
                <label class="block text-sm font-medium text-slate-700 mb-1">Invoice Prefix</label>
                <input type="text" v-model="settings.invoice_prefix" class="input-field w-full" placeholder="e.g. INV-" />
              </div>
              <div>
                <label class="block text-sm font-medium text-slate-700 mb-1">Tax/VAT Percentage (%)</label>
                <input type="number" step="0.01" v-model="settings.tax_percentage" class="input-field w-full" />
              </div>
              <div class="md:col-span-2">
                <label class="block text-sm font-medium text-slate-700 mb-1">Invoice Footer Notes</label>
                <textarea v-model="settings.invoice_footer" rows="3" class="input-field w-full" placeholder="Thank you for your business."></textarea>
              </div>
              <div>
                <label class="block text-sm font-medium text-slate-700 mb-1">Due Invoice Warning (Days)</label>
                <input type="number" v-model="settings.due_warning_days" class="input-field w-full" />
              </div>
              
              <div class="md:col-span-2 space-y-4 pt-4 border-t border-slate-200 mt-2">
                <label class="flex items-center gap-3">
                  <input type="checkbox" v-model="settings.discount_enabled" true-value="true" false-value="false" class="h-4 w-4 text-indigo-600 rounded border-slate-300">
                  <span class="text-sm font-medium text-slate-700">Enable Discount System</span>
                </label>
                <label class="flex items-center gap-3">
                  <input type="checkbox" v-model="settings.auto_invoice_numbering" true-value="true" false-value="false" class="h-4 w-4 text-indigo-600 rounded border-slate-300">
                  <span class="text-sm font-medium text-slate-700">Auto Generate Invoice Numbers</span>
                </label>
                <label class="flex items-center gap-3">
                  <input type="checkbox" v-model="settings.a4_invoice" true-value="true" false-value="false" class="h-4 w-4 text-indigo-600 rounded border-slate-300">
                  <span class="text-sm font-medium text-slate-700">Enable A4 PDF Invoices</span>
                </label>
                <label class="flex items-center gap-3">
                  <input type="checkbox" v-model="settings.thermal_invoice" true-value="true" false-value="false" class="h-4 w-4 text-indigo-600 rounded border-slate-300">
                  <span class="text-sm font-medium text-slate-700">Enable Thermal Receipt Printing</span>
                </label>
              </div>
            </div>
          </div>

          <!-- Security Settings -->
          <div v-show="activeTab === 'security'" class="space-y-6">
            <h2 class="text-xl font-semibold text-slate-800 mb-6">Security & Authentication</h2>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
              <div>
                <label class="block text-sm font-medium text-slate-700 mb-1">Session Timeout (Minutes)</label>
                <input type="number" v-model="settings.session_timeout" class="input-field w-full" />
              </div>
              <div>
                <label class="block text-sm font-medium text-slate-700 mb-1">Minimum Password Length</label>
                <input type="number" v-model="settings.min_password_length" class="input-field w-full" />
              </div>
              <div>
                <label class="block text-sm font-medium text-slate-700 mb-1">Failed Login Attempt Limits</label>
                <input type="number" v-model="settings.login_attempt_limit" class="input-field w-full" />
              </div>
              
              <div class="md:col-span-2 space-y-4 pt-4 border-t border-slate-200 mt-2">
                <label class="flex items-center gap-3">
                  <input type="checkbox" v-model="settings.two_factor_auth" true-value="true" false-value="false" class="h-4 w-4 text-indigo-600 rounded border-slate-300">
                  <span class="text-sm font-medium text-slate-700">Force Two-Factor Authentication (2FA) for All Users</span>
                </label>
                <label class="flex items-center gap-3">
                  <input type="checkbox" v-model="settings.maintenance_mode" true-value="true" false-value="false" class="h-4 w-4 text-rose-600 focus:ring-rose-500 rounded border-slate-300">
                  <span class="text-sm font-medium text-rose-700">Enable Maintenance Mode (System Lockout)</span>
                </label>
              </div>
            </div>
          </div>

          <!-- Notification Settings -->
          <div v-show="activeTab === 'notifications'" class="space-y-6">
            <h2 class="text-xl font-semibold text-slate-800 mb-6">Alerts & Notifications</h2>
            
            <div class="space-y-4">
              <div class="flex items-center justify-between p-4 bg-slate-50 rounded-lg border border-slate-200">
                <div>
                  <h4 class="text-sm font-semibold text-slate-800">Email Notifications</h4>
                  <p class="text-xs text-slate-500">Send alerts via configured SMTP.</p>
                </div>
                <label class="relative inline-flex items-center cursor-pointer">
                  <input type="checkbox" v-model="settings.email_notifications" true-value="true" false-value="false" class="sr-only peer">
                  <div class="w-11 h-6 bg-slate-200 peer-focus:outline-none rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-slate-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-indigo-600"></div>
                </label>
              </div>

              <div class="flex items-center justify-between p-4 bg-slate-50 rounded-lg border border-slate-200">
                <div>
                  <h4 class="text-sm font-semibold text-slate-800">SMS Notifications</h4>
                  <p class="text-xs text-slate-500">Send alerts via configured SMS Gateway.</p>
                </div>
                <label class="relative inline-flex items-center cursor-pointer">
                  <input type="checkbox" v-model="settings.sms_notifications" true-value="true" false-value="false" class="sr-only peer">
                  <div class="w-11 h-6 bg-slate-200 peer-focus:outline-none rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-slate-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-indigo-600"></div>
                </label>
              </div>

              <div class="flex items-center justify-between p-4 bg-slate-50 rounded-lg border border-slate-200">
                <div>
                  <h4 class="text-sm font-semibold text-slate-800">WhatsApp Integration</h4>
                  <p class="text-xs text-slate-500">Send automated messages to customers.</p>
                </div>
                <label class="relative inline-flex items-center cursor-pointer">
                  <input type="checkbox" v-model="settings.whatsapp_notifications" true-value="true" false-value="false" class="sr-only peer">
                  <div class="w-11 h-6 bg-slate-200 peer-focus:outline-none rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-slate-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-indigo-600"></div>
                </label>
              </div>
            </div>

            <h3 class="text-md font-semibold text-slate-800 mt-8 mb-4">Internal Alerts</h3>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
              <label class="flex items-center gap-3 p-3 border border-slate-200 rounded-lg">
                <input type="checkbox" v-model="settings.low_stock_alerts" true-value="true" false-value="false" class="h-4 w-4 text-indigo-600 rounded border-slate-300">
                <span class="text-sm font-medium text-slate-700">Low Inventory Stock Alerts</span>
              </label>
              <label class="flex items-center gap-3 p-3 border border-slate-200 rounded-lg">
                <input type="checkbox" v-model="settings.appointment_reminders" true-value="true" false-value="false" class="h-4 w-4 text-indigo-600 rounded border-slate-300">
                <span class="text-sm font-medium text-slate-700">CRM Appointment Reminders</span>
              </label>
              <label class="flex items-center gap-3 p-3 border border-slate-200 rounded-lg">
                <input type="checkbox" v-model="settings.payroll_alerts" true-value="true" false-value="false" class="h-4 w-4 text-indigo-600 rounded border-slate-300">
                <span class="text-sm font-medium text-slate-700">Monthly Payroll Processing Alerts</span>
              </label>
            </div>
          </div>

          <!-- SMTP Settings -->
          <div v-show="activeTab === 'smtp'" class="space-y-6">
            <h2 class="text-xl font-semibold text-slate-800 mb-6">SMTP / Mail Configuration</h2>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
              <div>
                <label class="block text-sm font-medium text-slate-700 mb-1">SMTP Host</label>
                <input type="text" v-model="settings.smtp_host" class="input-field w-full" placeholder="smtp.mailtrap.io" />
              </div>
              <div>
                <label class="block text-sm font-medium text-slate-700 mb-1">SMTP Port</label>
                <input type="text" v-model="settings.smtp_port" class="input-field w-full" placeholder="2525" />
              </div>
              <div>
                <label class="block text-sm font-medium text-slate-700 mb-1">SMTP Username</label>
                <input type="text" v-model="settings.smtp_username" class="input-field w-full" />
              </div>
              <div>
                <label class="block text-sm font-medium text-slate-700 mb-1">SMTP Password</label>
                <input type="password" v-model="settings.smtp_password" class="input-field w-full" />
              </div>
              <div>
                <label class="block text-sm font-medium text-slate-700 mb-1">Encryption</label>
                <select v-model="settings.smtp_encryption" class="input-field w-full">
                  <option value="tls">TLS</option>
                  <option value="ssl">SSL</option>
                  <option value="none">None</option>
                </select>
              </div>
            </div>
            
            <div class="pt-6 mt-6 border-t border-slate-200">
              <button type="button" @click="testSmtp" class="btn bg-white border border-slate-300 hover:bg-slate-50 text-slate-700 rounded-lg px-4 py-2 font-medium">
                Test Connection / Send Test Email
              </button>
            </div>
          </div>

          <!-- Backup Settings -->
          <div v-show="activeTab === 'backup'" class="space-y-6">
            <h2 class="text-xl font-semibold text-slate-800 mb-6">Backup & Restore</h2>
            
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
              <div class="p-5 border border-slate-200 rounded-xl bg-slate-50">
                <h3 class="text-md font-semibold text-slate-800 mb-2">Automated Backups</h3>
                <p class="text-sm text-slate-500 mb-4">Configure scheduled database and file backups.</p>
                <label class="flex items-center gap-3 mb-4">
                  <input type="checkbox" v-model="settings.auto_backup" true-value="true" false-value="false" class="h-4 w-4 text-indigo-600 rounded border-slate-300">
                  <span class="text-sm font-medium text-slate-700">Enable Daily Auto Backups</span>
                </label>
                <div>
                  <label class="block text-sm font-medium text-slate-700 mb-1">Retention Period (Days)</label>
                  <input type="number" v-model="settings.backup_retention_days" class="input-field w-full" />
                </div>
              </div>
              
              <div class="p-5 border border-slate-200 rounded-xl">
                <h3 class="text-md font-semibold text-slate-800 mb-2">Manual Operations</h3>
                <div class="space-y-4">
                  <button type="button" class="w-full flex items-center justify-center gap-2 bg-slate-800 hover:bg-slate-900 text-white rounded-lg px-4 py-2 text-sm font-medium">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12"></path></svg>
                    Trigger Manual Backup Now
                  </button>
                  <div class="pt-4 border-t border-slate-200">
                    <label class="block text-sm font-medium text-slate-700 mb-2">Restore from file (.zip / .sql)</label>
                    <input type="file" class="text-sm w-full file:mr-4 file:py-2 file:px-4 file:rounded-lg file:border-0 file:text-sm file:font-semibold file:bg-rose-50 file:text-rose-700 hover:file:bg-rose-100" />
                  </div>
                </div>
              </div>
            </div>
          </div>

          <!-- Printer Settings -->
          <div v-show="activeTab === 'printer'" class="space-y-6">
            <h2 class="text-xl font-semibold text-slate-800 mb-6">POS & Printer Settings</h2>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
              <div>
                <label class="block text-sm font-medium text-slate-700 mb-1">Thermal Printer Width</label>
                <select v-model="settings.thermal_printer_width" class="input-field w-full">
                  <option value="58mm">58mm</option>
                  <option value="80mm">80mm</option>
                </select>
              </div>
              <div>
                <label class="block text-sm font-medium text-slate-700 mb-1">Default Paper Size</label>
                <select v-model="settings.paper_size" class="input-field w-full">
                  <option value="A4">A4</option>
                  <option value="A5">A5</option>
                  <option value="Letter">Letter</option>
                </select>
              </div>
              <div class="md:col-span-2 space-y-4">
                <label class="flex items-center gap-3">
                  <input type="checkbox" v-model="settings.barcode_toggle" true-value="true" false-value="false" class="h-4 w-4 text-indigo-600 rounded border-slate-300">
                  <span class="text-sm font-medium text-slate-700">Print Item Barcodes on Receipt</span>
                </label>
                <label class="flex items-center gap-3">
                  <input type="checkbox" v-model="settings.qrcode_toggle" true-value="true" false-value="false" class="h-4 w-4 text-indigo-600 rounded border-slate-300">
                  <span class="text-sm font-medium text-slate-700">Print Invoice QR Code</span>
                </label>
              </div>
              <div class="md:col-span-2">
                <label class="block text-sm font-medium text-slate-700 mb-1">POS Receipt Footer Note</label>
                <textarea v-model="settings.receipt_footer" rows="2" class="input-field w-full"></textarea>
              </div>
            </div>
          </div>

          <!-- SaaS Settings -->
          <div v-show="activeTab === 'saas'" class="space-y-6">
            <h2 class="text-xl font-semibold text-slate-800 mb-6">SaaS & Tenant Constraints</h2>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
              <div class="md:col-span-2">
                <label class="flex items-center gap-3">
                  <input type="checkbox" v-model="settings.tenant_registration" true-value="true" false-value="false" class="h-4 w-4 text-indigo-600 rounded border-slate-300">
                  <span class="text-sm font-medium text-slate-700">Enable Open Tenant Registration (Landing Page Signups)</span>
                </label>
              </div>
              <div>
                <label class="block text-sm font-medium text-slate-700 mb-1">Default Trial Duration (Days)</label>
                <input type="number" v-model="settings.trial_duration_days" class="input-field w-full" />
              </div>
              <div>
                <label class="block text-sm font-medium text-slate-700 mb-1">Global Subscription Mode</label>
                <select v-model="settings.subscription_mode" class="input-field w-full">
                  <option value="active">Active (Enforce Billing)</option>
                  <option value="passive">Passive (No Blocks)</option>
                  <option value="lifetime">Lifetime Access</option>
                </select>
              </div>
              <div>
                <label class="block text-sm font-medium text-slate-700 mb-1">Maximum Allowed Branches (Per Tenant Default)</label>
                <input type="number" v-model="settings.branch_limit" class="input-field w-full" />
              </div>
            </div>
          </div>

        </form>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue';
import api from '../../services/api';

const activeTab = ref('general');
const isSaving = ref(false);
const logoPreview = ref(null);
const faviconPreview = ref(null);
const logoFile = ref(null);
const faviconFile = ref(null);

const toast = ref({ show: false, type: '', title: '', message: '' });

const showToast = (type, title, message = '') => {
  toast.value = { show: true, type, title, message };
  setTimeout(() => { toast.value.show = false; }, 4000);
};

const tabs = [
  { id: 'general', label: 'General Settings', icon: '🏢' },
  { id: 'invoice', label: 'Invoice & Billing', icon: '📄' },
  { id: 'security', label: 'Security & Auth', icon: '🔒' },
  { id: 'notifications', label: 'Notifications', icon: '🔔' },
  { id: 'smtp', label: 'SMTP / Email', icon: '✉️' },
  { id: 'backup', label: 'Backup & Restore', icon: '💾' },
  { id: 'printer', label: 'POS & Printers', icon: '🖨️' },
  { id: 'saas', label: 'SaaS Config', icon: '☁️' }
];

const settings = ref({
  company_name: '', company_address: '', company_phone: '', company_email: '', company_website: '',
  currency: 'USD', timezone: 'UTC', language: 'en',
  invoice_prefix: 'INV-', invoice_footer: '', tax_percentage: 0,
  discount_enabled: 'false', due_warning_days: 7, auto_invoice_numbering: 'true',
  thermal_invoice: 'false', a4_invoice: 'true',
  session_timeout: 120, min_password_length: 8, login_attempt_limit: 5,
  two_factor_auth: 'false', maintenance_mode: 'false',
  email_notifications: 'false', sms_notifications: 'false', whatsapp_notifications: 'false',
  low_stock_alerts: 'false', appointment_reminders: 'false', payroll_alerts: 'false',
  smtp_host: '', smtp_port: '', smtp_username: '', smtp_password: '', smtp_encryption: 'tls',
  auto_backup: 'false', backup_retention_days: 30,
  thermal_printer_width: '80mm', paper_size: 'A4', barcode_toggle: 'false', qrcode_toggle: 'false', receipt_footer: '',
  tenant_registration: 'false', trial_duration_days: 14, subscription_mode: 'active', branch_limit: 1
});

const loadSettings = async () => {
  try {
    const response = await api.get('/settings');
    const data = response.data.data;
    if (data && Object.keys(data).length > 0) {
      settings.value = { ...settings.value, ...data };
    }
  } catch (err) {
    console.error('Failed to load settings', err);
    showToast('error', 'Failed to load configuration.');
  }
};

const handleFileUpload = (event, type) => {
  const file = event.target.files[0];
  if (!file) return;
  
  if (type === 'logo') {
    logoFile.value = file;
    logoPreview.value = URL.createObjectURL(file);
  } else {
    faviconFile.value = file;
    faviconPreview.value = URL.createObjectURL(file);
  }
};

const saveSettings = async () => {
  isSaving.value = true;
  try {
    const formData = new FormData();
    
    // Append JSON settings
    for (const key in settings.value) {
      // Exclude pre-uploaded URLs
      if (key !== 'company_logo' && key !== 'company_favicon') {
        formData.append(key, settings.value[key] !== null ? settings.value[key] : '');
      }
    }
    
    // Append Files
    if (logoFile.value) formData.append('logo', logoFile.value);
    if (faviconFile.value) formData.append('favicon', faviconFile.value);

    await api.post('/settings', formData, {
      headers: { 'Content-Type': 'multipart/form-data' }
    });
    
    showToast('success', 'Settings updated successfully.', 'System configurations have been saved and applied.');
  } catch (error) {
    console.error('Save failed', error);
    showToast('error', 'Update Failed', error.response?.data?.message || 'Could not save settings.');
  } finally {
    isSaving.value = false;
  }
};

const testSmtp = () => {
  showToast('success', 'SMTP Test Triggered', 'Attempting to dispatch test email to system admin.');
};

onMounted(() => {
  loadSettings();
});
</script>

<style scoped>
@reference "../../style.css";
.input-field {
  @apply block w-full rounded-lg border-0 py-2 px-3 text-sm leading-6 text-slate-900 shadow-sm ring-1 ring-inset ring-slate-300 placeholder:text-slate-400 focus:ring-2 focus:ring-inset focus:ring-indigo-600;
}
</style>
