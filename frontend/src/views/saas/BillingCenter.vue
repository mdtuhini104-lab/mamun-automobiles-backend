<template>
  <div class="min-h-screen bg-slate-950 text-slate-100 p-6 space-y-6">
    <!-- Header -->
    <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4 border-b border-slate-800 pb-5">
      <div>
        <h1 class="text-2xl font-black tracking-tight text-white">SaaS Billing & Subscription Center</h1>
        <p class="text-xs text-slate-400 mt-1">Manage subscription tiers, plan parameters, branch limits, and billing ledger audits.</p>
      </div>
      <div class="flex gap-2">
        <button 
          @click="fetchSubscriptionDetails" 
          class="px-4 py-2 bg-slate-800 hover:bg-slate-700 text-slate-200 text-xs font-bold rounded-xl transition flex items-center gap-1.5"
          :disabled="loading"
        >
          <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor" class="w-4 h-4" :class="{ 'animate-spin': loading }">
            <path stroke-linecap="round" stroke-linejoin="round" d="M16.023 9.348h4.992v-.001M2.985 19.644v-4.992m0 0h4.992m-4.993 0l3.181 3.183a8.25 8.25 0 0013.803-3.7M4.031 9.865a8.25 8.25 0 0113.803-3.7l3.181 3.182m0-4.991v4.99" />
          </svg>
          Refresh Subscription Status
        </button>
      </div>
    </div>

    <!-- Active Subscription Status banner -->
    <div class="bg-slate-900 border border-slate-800 rounded-3xl p-6 shadow-xl flex flex-col md:flex-row justify-between items-start md:items-center gap-6">
      <div class="space-y-2">
        <span class="text-[10px] text-indigo-400 font-extrabold uppercase tracking-widest bg-indigo-500/10 px-2 py-0.5 rounded-lg border border-indigo-500/20">Active Plan Summary</span>
        <h2 class="text-xl font-black text-white capitalize">
          Current Tier: {{ planDetails.plan_name || 'base' }}
        </h2>
        <div class="flex flex-wrap gap-4 text-xs text-slate-400">
          <p>
            Status: 
            <span class="font-bold" :class="planDetails.active_subscription?.status === 'active' ? 'text-emerald-400' : 'text-amber-400'">
              {{ planDetails.active_subscription?.status || 'No Active Subscription' }}
            </span>
          </p>
          <p>Branch Limit: <span class="font-bold text-white">{{ planDetails.branch_limit || 1 }}</span></p>
          <p v-if="planDetails.active_subscription?.ends_at">
            Renews/Expires: <span class="font-bold text-white">{{ formatDate(planDetails.active_subscription.ends_at) }}</span>
          </p>
        </div>
      </div>

      <div class="flex gap-2 shrink-0">
        <button 
          v-if="planDetails.active_subscription && planDetails.active_subscription.status === 'active'"
          @click="cancelSubscription"
          class="px-4 py-2.5 bg-rose-600 hover:bg-rose-500 text-white text-xs font-bold rounded-xl transition shadow-lg"
          :disabled="processing"
        >
          Cancel Subscription
        </button>
      </div>
    </div>

    <!-- Tiers Comparison -->
    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
      <!-- Base Plan -->
      <div class="bg-slate-900 border rounded-3xl p-6 flex flex-col justify-between shadow-lg relative overflow-hidden" 
           :class="planDetails.plan_name === 'base' ? 'border-indigo-500/60' : 'border-slate-800'">
        <div v-if="planDetails.plan_name === 'base'" class="absolute top-0 right-0 bg-indigo-600 text-white font-extrabold text-[8px] uppercase tracking-widest px-3 py-1 rounded-bl-xl">
          Active Plan
        </div>
        <div class="space-y-4">
          <div>
            <h3 class="text-lg font-black text-white">Baseline Plan</h3>
            <p class="text-xs text-slate-400 mt-1">Perfect for single workshops initializing digital operations.</p>
          </div>
          <div class="text-3xl font-black text-white">$49<span class="text-xs font-normal text-slate-400">/mo</span></div>
          <ul class="text-xs text-slate-300 space-y-2 border-t border-slate-800 pt-4">
            <li class="flex items-center gap-2">✓ 1 Active Branch</li>
            <li class="flex items-center gap-2">✓ Standard Job Cards & Quotations</li>
            <li class="flex items-center gap-2">✓ Basic Invoice Generation</li>
            <li class="flex items-center gap-2 text-slate-500">✗ AI Pricing Assistant</li>
            <li class="flex items-center gap-2 text-slate-500">✗ Fleet Management Portal</li>
          </ul>
        </div>
        <button 
          @click="startCheckout('base', 'stripe')"
          class="mt-6 w-full py-2.5 rounded-xl text-xs font-bold text-center transition"
          :class="planDetails.plan_name === 'base' ? 'bg-slate-800 text-slate-400 cursor-not-allowed' : 'bg-slate-800 hover:bg-slate-700 text-white'"
          :disabled="planDetails.plan_name === 'base' || processing"
        >
          {{ planDetails.plan_name === 'base' ? 'Current Active Tier' : 'Upgrade to Base' }}
        </button>
      </div>

      <!-- Pro Plan -->
      <div class="bg-slate-900 border rounded-3xl p-6 flex flex-col justify-between shadow-lg relative overflow-hidden"
           :class="planDetails.plan_name === 'pro' ? 'border-indigo-500/60' : 'border-slate-800'">
        <div v-if="planDetails.plan_name === 'pro'" class="absolute top-0 right-0 bg-indigo-600 text-white font-extrabold text-[8px] uppercase tracking-widest px-3 py-1 rounded-bl-xl">
          Active Plan
        </div>
        <div class="space-y-4">
          <div>
            <h3 class="text-lg font-black text-white">Professional Plan</h3>
            <p class="text-xs text-slate-400 mt-1">Engineered for expanding multi-branch garage locations.</p>
          </div>
          <div class="text-3xl font-black text-white">$149<span class="text-xs font-normal text-slate-400">/mo</span></div>
          <ul class="text-xs text-slate-300 space-y-2 border-t border-slate-800 pt-4">
            <li class="flex items-center gap-2">✓ Up to 3 Active Branches</li>
            <li class="flex items-center gap-2">✓ AI Quotation Assistant (15% Max Warning)</li>
            <li class="flex items-center gap-2">✓ Granular RBAC Permissions</li>
            <li class="flex items-center gap-2">✓ Audit Log Tracking</li>
            <li class="flex items-center gap-2 text-slate-500">✗ Custom White-Labeling</li>
          </ul>
        </div>
        <button 
          @click="startCheckout('pro', 'stripe')"
          class="mt-6 w-full py-2.5 rounded-xl text-xs font-bold text-center transition"
          :class="planDetails.plan_name === 'pro' ? 'bg-slate-800 text-slate-400 cursor-not-allowed' : 'bg-indigo-600 hover:bg-indigo-500 text-white'"
          :disabled="planDetails.plan_name === 'pro' || processing"
        >
          {{ planDetails.plan_name === 'pro' ? 'Current Active Tier' : 'Upgrade to Pro' }}
        </button>
      </div>

      <!-- Enterprise Plan -->
      <div class="bg-slate-900 border rounded-3xl p-6 flex flex-col justify-between shadow-lg relative overflow-hidden"
           :class="planDetails.plan_name === 'enterprise' ? 'border-indigo-500/60' : 'border-slate-800'">
        <div v-if="planDetails.plan_name === 'enterprise'" class="absolute top-0 right-0 bg-indigo-600 text-white font-extrabold text-[8px] uppercase tracking-widest px-3 py-1 rounded-bl-xl">
          Active Plan
        </div>
        <div class="space-y-4">
          <div>
            <h3 class="text-lg font-black text-white">Enterprise Suite</h3>
            <p class="text-xs text-slate-400 mt-1">The complete white-labeled fleet & automotive network platform.</p>
          </div>
          <div class="text-3xl font-black text-white">$499<span class="text-xs font-normal text-slate-400">/mo</span></div>
          <ul class="text-xs text-slate-300 space-y-2 border-t border-slate-800 pt-4">
            <li class="flex items-center gap-2">✓ Unlimited Branch Isolation</li>
            <li class="flex items-center gap-2">✓ Custom Domain White-Labeling</li>
            <li class="flex items-center gap-2">✓ Fleet Management Portal</li>
            <li class="flex items-center gap-2">✓ AI Predictive Repair Forecaster</li>
            <li class="flex items-center gap-2">✓ Webhook Developer APIs</li>
          </ul>
        </div>
        <button 
          @click="startCheckout('enterprise', 'stripe')"
          class="mt-6 w-full py-2.5 rounded-xl text-xs font-bold text-center transition"
          :class="planDetails.plan_name === 'enterprise' ? 'bg-slate-800 text-slate-400 cursor-not-allowed' : 'bg-gradient-to-r from-indigo-600 to-violet-600 hover:from-indigo-500 hover:to-violet-500 text-white'"
          :disabled="planDetails.plan_name === 'enterprise' || processing"
        >
          {{ planDetails.plan_name === 'enterprise' ? 'Current Active Tier' : 'Upgrade to Enterprise' }}
        </button>
      </div>
    </div>

    <!-- Simulated Webhook Billing Panel (Offline testing / CI-CD Validation tool) -->
    <div class="bg-slate-900 border border-slate-800 rounded-3xl p-6 shadow-xl space-y-4">
      <div>
        <h2 class="text-lg font-black text-white uppercase tracking-wider">Local Mock Webhook Sandbox</h2>
        <p class="text-xs text-slate-400 mt-0.5">Test billing tier toggles instantly without hitting network adapters (deterministic replay protection test environment).</p>
      </div>

      <div class="grid grid-cols-1 md:grid-cols-4 gap-4 items-end bg-slate-950 p-4 rounded-2xl border border-slate-850">
        <div>
          <label class="block text-[10px] text-slate-400 font-extrabold uppercase mb-1.5">Target Plan</label>
          <select v-model="mockWebhookForm.plan_name" class="w-full bg-slate-900 border border-slate-800 rounded-xl px-3 py-2 text-xs text-white focus:outline-none focus:border-indigo-500">
            <option value="base">Base</option>
            <option value="pro">Pro</option>
            <option value="enterprise">Enterprise</option>
          </select>
        </div>
        <div>
          <label class="block text-[10px] text-slate-400 font-extrabold uppercase mb-1.5">Payment Gateway</label>
          <select v-model="mockWebhookForm.gateway" class="w-full bg-slate-900 border border-slate-800 rounded-xl px-3 py-2 text-xs text-white focus:outline-none focus:border-indigo-500">
            <option value="stripe">Stripe</option>
            <option value="bkash">bKash</option>
            <option value="sslcommerz">SSLCommerz</option>
          </select>
        </div>
        <div>
          <label class="block text-[10px] text-slate-400 font-extrabold uppercase mb-1.5">Status</label>
          <select v-model="mockWebhookForm.status" class="w-full bg-slate-900 border border-slate-800 rounded-xl px-3 py-2 text-xs text-white focus:outline-none focus:border-indigo-500">
            <option value="active">Active</option>
            <option value="cancelled">Cancelled</option>
          </select>
        </div>
        <button 
          @click="fireMockWebhook"
          class="w-full py-2 bg-indigo-600 hover:bg-indigo-500 text-white text-xs font-bold rounded-xl transition"
          :disabled="processing"
        >
          Dispatch Simulated webhook
        </button>
      </div>
    </div>

    <!-- Invoice / Subscription Audits Table -->
    <div class="bg-slate-900 border border-slate-800 rounded-3xl p-6 shadow-xl space-y-4">
      <div>
        <h2 class="text-lg font-black text-white uppercase tracking-wider">Tenant Subscription Billing Ledgers</h2>
        <p class="text-xs text-slate-400 mt-0.5">Immutable records of billing activations, upgrades, and cancellations.</p>
      </div>

      <div class="overflow-x-auto">
        <table class="w-full text-left text-xs text-slate-350">
          <thead class="border-b border-slate-800 text-[10px] font-extrabold uppercase tracking-widest text-slate-400">
            <tr>
              <th class="pb-3">Subscription ID</th>
              <th class="pb-3">Plan Name</th>
              <th class="pb-3">Gateway</th>
              <th class="pb-3">Status</th>
              <th class="pb-3">Starts At</th>
              <th class="pb-3">Ends At</th>
              <th class="pb-3 text-right">Created</th>
            </tr>
          </thead>
          <tbody class="divide-y divide-slate-850">
            <tr v-for="invoice in planDetails.invoices" :key="invoice.id" class="hover:bg-slate-850/30 transition">
              <td class="py-3 font-mono text-indigo-400">#{{ invoice.gateway_subscription_id || invoice.id }}</td>
              <td class="py-3 font-semibold capitalize text-white">{{ invoice.plan_name }}</td>
              <td class="py-3 capitalize">{{ invoice.payment_gateway }}</td>
              <td class="py-3">
                <span 
                  class="px-2 py-0.5 rounded text-[9px] font-black uppercase tracking-wider"
                  :class="invoice.status === 'active' ? 'bg-emerald-500/10 text-emerald-400 border border-emerald-500/20' : 'bg-slate-800 text-slate-400'"
                >
                  {{ invoice.status }}
                </span>
              </td>
              <td class="py-3 text-slate-400">{{ formatDate(invoice.starts_at) }}</td>
              <td class="py-3 text-slate-400">{{ formatDate(invoice.ends_at) }}</td>
              <td class="py-3 text-right text-slate-400">{{ formatDate(invoice.created_at) }}</td>
            </tr>
            <tr v-if="!planDetails.invoices || planDetails.invoices.length === 0">
              <td colspan="7" class="py-10 text-center text-slate-500">
                No billing activations or ledger entries recorded.
              </td>
            </tr>
          </tbody>
        </table>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue';
import api from '../../services/api';
import { useToastStore } from '../../stores/toast';

const toast = useToastStore();
const loading = ref(false);
const processing = ref(false);

const planDetails = ref({
  active_subscription: null,
  plan_name: 'base',
  branch_limit: 1,
  features: {},
  invoices: []
});

const mockWebhookForm = ref({
  plan_name: 'enterprise',
  gateway: 'stripe',
  status: 'active'
});

const fetchSubscriptionDetails = async () => {
  loading.value = true;
  try {
    const response = await api.get('/saas/subscription');
    planDetails.value = response.data.data;
  } catch (error) {
    console.error('Failed to load SaaS subscription details', error);
    toast.error('Could not fetch billing details from enterprise gateway.');
  } finally {
    loading.value = false;
  }
};

const startCheckout = async (planName, gateway) => {
  processing.value = true;
  try {
    const response = await api.post('/saas/subscription/checkout', {
      plan_name: planName,
      payment_gateway: gateway
    });
    toast.success(`Checkout initialized: Redirecting to mock ${gateway} portal.`);
    // Simulate redirection/success checkout trigger directly for sandboxed environment
    if (response.data.checkout_url) {
      window.open(response.data.checkout_url, '_blank');
    }
    await fetchSubscriptionDetails();
  } catch (error) {
    console.error('Checkout failed', error);
    toast.error('Payment checkout session failed. Try another gateway.');
  } finally {
    processing.value = false;
  }
};

const cancelSubscription = async () => {
  if (!confirm('Are you sure you want to cancel your current subscription plan? Limits will immediately rollback to standard.')) {
    return;
  }
  processing.value = true;
  try {
    await api.post('/saas/subscription/cancel');
    toast.success('Subscription cancelled successfully.');
    await fetchSubscriptionDetails();
  } catch (error) {
    console.error('Cancellation failed', error);
    toast.error('Subscription cancellation request failed.');
  } finally {
    processing.value = false;
  }
};

const fireMockWebhook = async () => {
  processing.value = true;
  const eventId = 'evt_' + Math.random().toString(36).substr(2, 9);
  const subId = 'sub_' + Math.random().toString(36).substr(2, 9);
  
  try {
    await api.post('/saas/subscription/mock-webhook', {
      gateway: mockWebhookForm.value.gateway,
      payload: {
        id: eventId,
        subscription_id: subId,
        status: mockWebhookForm.value.status,
        plan_name: mockWebhookForm.value.plan_name,
        tenant_id: 1
      }
    });
    toast.success(`Deterministic billing webhook dispatched for transaction: ${eventId}`);
    await fetchSubscriptionDetails();
  } catch (error) {
    console.error('Webhook simulation failed', error);
    toast.error('Sandbox webhook rejection. Check validation rules.');
  } finally {
    processing.value = false;
  }
};

const formatDate = (dateStr) => {
  if (!dateStr) return '';
  const date = new Date(dateStr);
  return date.toLocaleDateString('en-US', {
    year: 'numeric',
    month: 'short',
    day: 'numeric'
  });
};

onMounted(() => {
  fetchSubscriptionDetails();
});
</script>

<style scoped>
</style>
