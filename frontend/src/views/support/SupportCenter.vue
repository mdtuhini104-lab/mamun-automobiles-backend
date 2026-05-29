<template>
  <div class="min-h-screen bg-slate-950 text-slate-100 p-6 space-y-6">
    <!-- Header -->
    <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4 border-b border-slate-800 pb-5">
      <div>
        <h1 class="text-2xl font-black tracking-tight text-white">Help & Support Desk</h1>
        <p class="text-xs text-slate-400 mt-1">Search our knowledge base manuals, create support tickets, or export diagnostic payload files.</p>
      </div>
      <div class="flex gap-2">
        <router-link 
          v-if="isStaff" 
          to="/support/incidents" 
          class="px-4 py-2 bg-indigo-600 hover:bg-indigo-500 text-white text-xs font-bold rounded-xl transition flex items-center gap-1.5"
        >
          Incident Workspace
        </router-link>
        <button 
          @click="fetchTickets" 
          class="px-4 py-2 bg-slate-800 hover:bg-slate-700 text-slate-200 text-xs font-bold rounded-xl transition flex items-center gap-1.5"
          :disabled="loadingTickets"
        >
          Sync Ticket States
        </button>
      </div>
    </div>

    <!-- Searchable Knowledge Base -->
    <div class="bg-slate-900 border border-slate-800 rounded-3xl p-6 shadow-xl space-y-6">
      <div>
        <h2 class="text-lg font-black text-white uppercase tracking-wider">Searchable Knowledge Base Articles</h2>
        <p class="text-xs text-slate-400 mt-0.5">Quick setup tutorials, billing FAQ manuals, and diagnostic guidelines.</p>
      </div>

      <!-- Search Input -->
      <div class="max-w-md">
        <input 
          v-model="searchQuery" 
          type="text" 
          placeholder="Search topics (e.g. backup, invoice, branch)..." 
          class="w-full bg-slate-950 border border-slate-800 rounded-xl px-4 py-2.5 text-xs text-white focus:outline-none focus:border-indigo-500"
        />
      </div>

      <!-- Articles Grid -->
      <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
        <div 
          v-for="article in filteredArticles" 
          :key="article.id"
          class="p-4 bg-slate-950 border border-slate-850 rounded-2xl space-y-2 hover:border-slate-750 transition text-left"
        >
          <span class="text-[9px] font-black uppercase tracking-wider text-indigo-400 bg-indigo-500/10 px-2 py-0.5 rounded border border-indigo-500/20">
            {{ article.category }}
          </span>
          <h3 class="text-xs font-bold text-white">{{ article.title }}</h3>
          <p class="text-[10px] text-slate-400 leading-relaxed whitespace-pre-wrap">{{ article.content }}</p>
        </div>
        <div v-if="filteredArticles.length === 0" class="col-span-2 py-10 text-center text-slate-500 text-xs">
          No matching knowledge base articles found. Try another search.
        </div>
      </div>
    </div>

    <!-- Tickets and Submit Ticket form -->
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
      <!-- File Ticket Form -->
      <div class="bg-slate-900 border border-slate-800 rounded-3xl p-6 shadow-xl space-y-4">
        <div>
          <h2 class="text-base font-black text-white uppercase tracking-wider">Submit Help Ticket</h2>
          <p class="text-xs text-slate-400 mt-0.5">Create a trackable support request for priority SLA assistance.</p>
        </div>

        <form @submit.prevent="submitTicket" class="space-y-4">
          <div>
            <label class="block text-[10px] text-slate-400 font-extrabold uppercase mb-1.5">Ticket Summary</label>
            <input 
              v-model="ticketForm.title" 
              type="text" 
              placeholder="e.g. Stripe checkout URL generation error" 
              class="w-full bg-slate-950 border border-slate-800 rounded-xl px-3 py-2 text-xs text-white focus:outline-none focus:border-indigo-500" 
              required
            />
          </div>

          <div>
            <label class="block text-[10px] text-slate-400 font-extrabold uppercase mb-1.5">Description / Issue logs</label>
            <textarea 
              v-model="ticketForm.description" 
              rows="4"
              placeholder="Provide exact error code or steps to reproduce..." 
              class="w-full bg-slate-950 border border-slate-800 rounded-xl px-3 py-2 text-xs text-white focus:outline-none focus:border-indigo-500" 
              required
            ></textarea>
          </div>

          <div class="grid grid-cols-2 gap-4">
            <div>
              <label class="block text-[10px] text-slate-400 font-extrabold uppercase mb-1.5">Priority</label>
              <select v-model="ticketForm.priority" class="w-full bg-slate-950 border border-slate-800 rounded-xl px-3 py-2 text-xs text-white focus:outline-none focus:border-indigo-500">
                <option value="low">Low</option>
                <option value="medium">Medium</option>
                <option value="high">High</option>
                <option value="urgent">Urgent</option>
              </select>
            </div>
            <div>
              <label class="block text-[10px] text-slate-400 font-extrabold uppercase mb-1.5">Category</label>
              <select v-model="ticketForm.category" class="w-full bg-slate-950 border border-slate-800 rounded-xl px-3 py-2 text-xs text-white focus:outline-none focus:border-indigo-500">
                <option value="general">General</option>
                <option value="billing">Billing</option>
                <option value="technical">Technical</option>
                <option value="onboarding">Onboarding</option>
              </select>
            </div>
          </div>

          <button 
            type="submit" 
            class="w-full py-2.5 bg-indigo-600 hover:bg-indigo-500 text-white text-xs font-bold rounded-xl transition shadow-lg"
            :disabled="submittingTicket"
          >
            {{ submittingTicket ? 'Filing ticket...' : 'Submit Support Ticket' }}
          </button>
        </form>
      </div>

      <!-- Tickets List -->
      <div class="bg-slate-900 border border-slate-800 rounded-3xl p-6 shadow-xl space-y-4 lg:col-span-2">
        <div>
          <h2 class="text-base font-black text-white uppercase tracking-wider">Ticket Resolution Registry</h2>
          <p class="text-xs text-slate-400 mt-0.5">Track resolution status, response times, and submit satisfaction ratings.</p>
        </div>

        <div class="overflow-x-auto">
          <table class="w-full text-left text-xs text-slate-350">
            <thead class="border-b border-slate-800 text-[10px] font-extrabold uppercase tracking-widest text-slate-400">
              <tr>
                <th class="pb-3">Ticket ID</th>
                <th class="pb-3">Title</th>
                <th class="pb-3">Category</th>
                <th class="pb-3">Priority</th>
                <th class="pb-3">Status</th>
                <th class="pb-3 text-right">Actions</th>
              </tr>
            </thead>
            <tbody class="divide-y divide-slate-850">
              <tr v-for="ticket in tickets" :key="ticket.id" class="hover:bg-slate-850/30 transition">
                <td class="py-3 font-mono text-indigo-400">#TK-{{ ticket.id }}</td>
                <td class="py-3 font-semibold text-white">{{ ticket.title }}</td>
                <td class="py-3 capitalize">{{ ticket.category }}</td>
                <td class="py-3">
                  <span 
                    class="px-2 py-0.5 rounded text-[8px] font-black uppercase tracking-wider"
                    :class="getPriorityClasses(ticket.priority)"
                  >
                    {{ ticket.priority }}
                  </span>
                </td>
                <td class="py-3">
                  <span 
                    class="px-2 py-0.5 rounded text-[8px] font-black uppercase tracking-wider"
                    :class="ticket.status === 'resolved' ? 'bg-emerald-500/10 text-emerald-400 border border-emerald-500/20' : 'bg-rose-500/10 text-rose-400 border border-rose-500/20'"
                  >
                    {{ ticket.status }}
                  </span>
                </td>
                <td class="py-3 text-right">
                  <div class="flex justify-end items-center gap-2">
                    <button 
                      v-if="ticket.status === 'open' && isStaff"
                      @click="escalateTicket(ticket)"
                      class="px-2 py-0.5 text-[9px] font-extrabold uppercase text-indigo-400 hover:text-indigo-300 bg-indigo-500/10 border border-indigo-500/20 rounded"
                    >
                      Escalate
                    </button>
                    <div v-if="ticket.status === 'resolved' && !ticket.satisfaction_score" class="flex justify-end gap-1">
                      <button 
                        v-for="score in 5" 
                        :key="score"
                        @click="submitRating(ticket.id, score)"
                        class="text-[9px] hover:text-amber-400 font-bold bg-slate-850 px-1 py-0.5 rounded border border-slate-800"
                      >
                        ★{{ score }}
                      </button>
                    </div>
                    <span v-else-if="ticket.satisfaction_score" class="text-amber-400 font-bold">
                      ★{{ ticket.satisfaction_score }}/5
                    </span>
                    <span v-else-if="ticket.status !== 'open'" class="text-slate-500 font-medium">Pending Feedback</span>
                  </div>
                </td>
              </tr>
              <tr v-if="tickets.length === 0">
                <td colspan="6" class="py-10 text-center text-slate-500">
                  No support tickets created yet.
                </td>
              </tr>
            </tbody>
          </table>
        </div>
      </div>
    </div>

    <!-- Telemetry Diagnostic Export Widget -->
    <div class="bg-slate-900 border border-slate-800 rounded-3xl p-6 shadow-xl space-y-4">
      <div>
        <h2 class="text-lg font-black text-white uppercase tracking-wider">Sanitized Diagnostics Log Exporter</h2>
        <p class="text-xs text-slate-400 mt-0.5">Package environment, db stats, and recent alerts safely (all API keys, secrets, and database passwords are redacted).</p>
      </div>

      <div class="p-5 bg-slate-950 rounded-2xl border border-slate-850 flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4">
        <div class="space-y-1">
          <h4 class="text-xs font-bold text-white">Diagnostics Payload Package</h4>
          <p class="text-[10px] text-slate-400 max-w-lg leading-normal">This JSON file matches target environment values and alerts histories. Deliver this package to Mamun support teams to expedite troubleshooting.</p>
        </div>
        <button 
          @click="downloadDiagnostics"
          class="px-4 py-2.5 bg-indigo-600 hover:bg-indigo-500 text-white text-xs font-bold rounded-xl shadow-lg transition shrink-0"
          :disabled="exporting"
        >
          {{ exporting ? 'Assembling file...' : 'Export Diagnostics Package' }}
        </button>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue';
import api from '../../services/api';
import { useToastStore } from '../../stores/toast';
import { useAuthStore } from '../../stores/auth';

const toast = useToastStore();
const authStore = useAuthStore();

const loadingTickets = ref(false);
const submittingTicket = ref(false);
const exporting = ref(false);

const searchQuery = ref('');
const tickets = ref([]);
const articles = ref([]);

const ticketForm = ref({
  title: '',
  description: '',
  priority: 'medium',
  category: 'general'
});

const isStaff = computed(() => {
  return authStore.user?.roles?.some(r => ['Super Admin', 'Admin', 'Manager'].includes(r.name)) || authStore.hasRole('Super Admin');
});

const filteredArticles = computed(() => {
  if (!searchQuery.value) return articles.value;
  const q = searchQuery.value.toLowerCase();
  return articles.value.filter(a => 
    a.title.toLowerCase().includes(q) || 
    a.content.toLowerCase().includes(q) ||
    a.category.toLowerCase().includes(q)
  );
});

const fetchTickets = async () => {
  loadingTickets.value = true;
  try {
    const response = await api.get('/support/tickets');
    tickets.value = response.data.data;
  } catch (error) {
    console.error('Failed to load support tickets', error);
    toast.error('Could not fetch tickets from database.');
  } finally {
    loadingTickets.value = false;
  }
};

const fetchArticles = async () => {
  try {
    const response = await api.get('/support/kb');
    articles.value = response.data.data;
  } catch (error) {
    console.error('Failed to load articles', error);
  }
};

const submitTicket = async () => {
  submittingTicket.value = true;
  try {
    await api.post('/support/tickets', ticketForm.value);
    toast.success('Support ticket submitted successfully. Assigned to help queue.');
    ticketForm.value = { title: '', description: '', priority: 'medium', category: 'general' };
    await fetchTickets();
  } catch (error) {
    console.error('Submit ticket failed', error);
    toast.error('Could not file support ticket. Review validation.');
  } finally {
    submittingTicket.value = false;
  }
};

const escalateTicket = async (ticket) => {
  try {
    await api.post(`/support/tickets/${ticket.id}/incident`, {
      title: 'Escalated: ' + ticket.title,
      description: ticket.description,
      severity: ticket.priority === 'urgent' ? 'critical' : (ticket.priority === 'high' ? 'high' : 'medium')
    });
    toast.success('Ticket escalated to Incident Workspace.');
    await fetchTickets();
  } catch (error) {
    console.error('Escalation failed', error);
    toast.error(error.response?.data?.message || 'Failed to escalate ticket.');
  }
};

const submitRating = async (ticketId, score) => {
  try {
    await api.post(`/support/tickets/${ticketId}/feedback`, { satisfaction_score: score });
    toast.success('CSAT Feedback rating submitted. Thank you!');
    await fetchTickets();
  } catch (error) {
    console.error('Feedback rating failed', error);
    toast.error('Failed to register CSAT rating.');
  }
};

const downloadDiagnostics = async () => {
  exporting.value = true;
  try {
    const response = await api.get('/support/diagnostics');
    const dataStr = JSON.stringify(response.data.diagnostics, null, 2);
    const dataUri = 'data:application/json;charset=utf-8,'+ encodeURIComponent(dataStr);
    
    const exportFileDefaultName = `mamun_diagnostics_${response.data.diagnostics.tenant_id}_${Date.now()}.json`;
    
    const linkElement = document.createElement('a');
    linkElement.setAttribute('href', dataUri);
    linkElement.setAttribute('download', exportFileDefaultName);
    linkElement.click();
    
    toast.success('Diagnostics telemetry file downloaded safely.');
  } catch (error) {
    console.error('Diagnostics export failed', error);
    toast.error('Failed to compile diagnostics metadata.');
  } finally {
    exporting.value = false;
  }
};

const getPriorityClasses = (priority) => {
  if (priority === 'urgent') return 'bg-rose-600 text-white font-extrabold';
  if (priority === 'high') return 'bg-amber-500/10 border-amber-500/20 text-amber-450';
  if (priority === 'medium') return 'bg-indigo-500/10 border-indigo-500/20 text-indigo-400';
  return 'bg-slate-800 text-slate-400';
};

onMounted(() => {
  fetchTickets();
  fetchArticles();
});
</script>

<style scoped>
</style>
