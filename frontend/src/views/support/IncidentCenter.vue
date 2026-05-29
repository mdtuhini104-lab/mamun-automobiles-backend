<template>
  <div class="min-h-screen bg-slate-950 text-slate-100 p-6 space-y-6">
    <!-- Header -->
    <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4 border-b border-slate-800 pb-5">
      <div>
        <h1 class="text-2xl font-black tracking-tight text-white">Support Incident & Resolution Center</h1>
        <p class="text-xs text-slate-400 mt-1">Convert user tickets into operational incidents, record step-by-step resolution workflows, and generate dynamic Knowledge Base guides.</p>
      </div>
      <div class="flex gap-2">
        <router-link 
          to="/support" 
          class="px-4 py-2 bg-slate-800 hover:bg-slate-700 text-slate-200 text-xs font-bold rounded-xl transition flex items-center gap-1.5"
        >
          Support Desk Home
        </router-link>
        <button 
          @click="fetchIncidents" 
          class="px-4 py-2 bg-indigo-600 hover:bg-indigo-500 text-white text-xs font-bold rounded-xl shadow-lg transition"
          :disabled="loading"
        >
          {{ loading ? 'Syncing...' : 'Sync Incident Registry' }}
        </button>
      </div>
    </div>

    <!-- Active Support Incident Registry -->
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
      
      <!-- Incidents List -->
      <div class="bg-slate-900 border border-slate-800 rounded-3xl p-6 shadow-xl space-y-4 lg:col-span-1">
        <div>
          <h2 class="text-base font-black text-white uppercase tracking-wider">Incident Registry</h2>
          <p class="text-xs text-slate-400 mt-0.5">Select a logged incident to review troubleshooting history.</p>
        </div>

        <div class="space-y-3 max-h-[600px] overflow-y-auto pr-2 scrollbar-thin">
          <div 
            v-for="inc in incidents" 
            :key="inc.id" 
            @click="selectIncident(inc)"
            class="p-4 rounded-2xl border transition text-left cursor-pointer"
            :class="selectedIncident?.id === inc.id 
              ? 'bg-indigo-500/10 border-indigo-500/40' 
              : 'bg-slate-950 border-slate-850 hover:border-slate-800'"
          >
            <div class="flex justify-between items-start gap-2">
              <span class="text-[9px] font-mono text-indigo-400">#INC-{{ inc.id }}</span>
              <span 
                class="px-1.5 py-0.5 rounded text-[8px] font-black uppercase tracking-wider"
                :class="inc.severity === 'critical' ? 'bg-rose-600 text-white font-extrabold' : 'bg-slate-850 text-slate-400'"
              >
                {{ inc.severity }}
              </span>
            </div>
            <h4 class="text-xs font-bold text-white mt-1.5 line-clamp-1">{{ inc.title }}</h4>
            <p class="text-[10px] text-slate-400 line-clamp-2 mt-1">{{ inc.description }}</p>
            <div class="flex justify-between items-center mt-3 pt-2.5 border-t border-slate-850">
              <span 
                class="px-2 py-0.5 rounded text-[8px] font-black uppercase tracking-wider"
                :class="inc.status === 'resolved' 
                  ? 'bg-emerald-500/10 text-emerald-400 border border-emerald-500/20' 
                  : 'bg-amber-500/10 text-amber-450 border border-amber-500/20'"
              >
                {{ inc.status }}
              </span>
              <span class="text-[9px] text-slate-500 font-mono">{{ formatDate(inc.created_at) }}</span>
            </div>
          </div>

          <div v-if="incidents.length === 0" class="py-10 text-center text-slate-500 text-xs">
            No incidents logged. Convert tickets inside the Support Desk.
          </div>
        </div>
      </div>

      <!-- Incident Details & Resolution Workflow Workspace -->
      <div class="lg:col-span-2 space-y-6">
        <div v-if="selectedIncident" class="bg-slate-900 border border-slate-800 rounded-3xl p-6 shadow-xl space-y-6 animate-fadeIn">
          
          <!-- Selected Incident Header -->
          <div class="flex justify-between items-start border-b border-slate-800 pb-4">
            <div>
              <div class="flex items-center gap-2">
                <span class="text-xs font-mono text-indigo-400">#INC-{{ selectedIncident.id }}</span>
                <span class="text-slate-500">•</span>
                <span class="text-[10px] text-slate-400 capitalize">Category: {{ selectedIncident.ticket?.category || 'General' }}</span>
              </div>
              <h3 class="text-lg font-black text-white mt-1">{{ selectedIncident.title }}</h3>
            </div>
            <span 
              class="px-2 py-0.5 rounded text-[8px] font-black uppercase tracking-wider"
              :class="selectedIncident.status === 'resolved' ? 'bg-emerald-500/10 text-emerald-400 border border-emerald-500/20' : 'bg-amber-500/10 text-amber-450 border border-amber-500/20'"
            >
              {{ selectedIncident.status }}
            </span>
          </div>

          <!-- Problem Description -->
          <div class="space-y-2">
            <h4 class="text-[10px] font-extrabold uppercase tracking-wider text-slate-400">Customer Description</h4>
            <div class="p-4 bg-slate-950 rounded-2xl border border-slate-850 text-xs leading-relaxed text-slate-200">
              {{ selectedIncident.description }}
            </div>
          </div>

          <!-- AUTOMATED TROUBLESHOOTING SUGGESTIONS & SIMILARITY MATCHES -->
          <div class="space-y-3 bg-slate-950 p-5 rounded-2xl border border-slate-850">
            <div class="flex justify-between items-center">
              <h4 class="text-[10px] font-extrabold uppercase tracking-wider text-indigo-400">Lightweight Similarity & Suggestions Engine</h4>
              <span class="px-2 py-0.5 rounded text-[8px] bg-slate-900 text-slate-500 border border-slate-800">Keyword Intersect Matching</span>
            </div>

            <div v-if="loadingSuggestions" class="text-center py-4 text-xs text-slate-500">
              Querying similarity indices...
            </div>

            <div v-else-if="suggestions.similar_tickets?.length || suggestions.troubleshooting_articles?.length" class="space-y-4">
              <!-- Similar Tickets -->
              <div v-if="suggestions.similar_tickets?.length" class="space-y-2">
                <h5 class="text-[9px] font-black uppercase tracking-wider text-slate-500">Similar Historical Tickets (Resolved)</h5>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-3">
                  <div 
                    v-for="st in suggestions.similar_tickets" 
                    :key="st.id"
                    class="p-3 bg-slate-900 rounded-xl border border-slate-800 flex justify-between items-start gap-2"
                  >
                    <div>
                      <h6 class="text-xs font-bold text-white line-clamp-1">{{ st.title }}</h6>
                      <p class="text-[9px] text-slate-400 mt-1 line-clamp-2">{{ st.description }}</p>
                    </div>
                  </div>
                </div>
              </div>

              <!-- Matching KB Articles -->
              <div v-if="suggestions.troubleshooting_articles?.length" class="space-y-2">
                <h5 class="text-[9px] font-black uppercase tracking-wider text-slate-500">Suggested Knowledge Base Manuals</h5>
                <div class="space-y-2">
                  <div 
                    v-for="art in suggestions.troubleshooting_articles" 
                    :key="art.id"
                    class="p-3 bg-slate-900 rounded-xl border border-slate-800"
                  >
                    <h6 class="text-xs font-bold text-white">{{ art.title }}</h6>
                    <p class="text-[9px] text-indigo-300 font-mono mt-0.5">Category: {{ art.category }}</p>
                    <p class="text-[9px] text-slate-400 mt-1.5 line-clamp-2 leading-relaxed">{{ art.content }}</p>
                  </div>
                </div>
              </div>
            </div>

            <div v-else class="text-[10px] text-slate-500 text-center py-2">
              No matching resolved tickets or troubleshooting manuals found for this issue topic.
            </div>
          </div>

          <!-- Resolution Workflow Builder -->
          <div class="space-y-4 border-t border-slate-800 pt-5">
            <div class="flex justify-between items-center">
              <div>
                <h4 class="text-xs font-extrabold uppercase text-white tracking-wider">Resolution Workflow Form</h4>
                <p class="text-[10px] text-slate-400">Map troubleshooting steps and solution logs.</p>
              </div>
              <button 
                v-if="selectedIncident.status !== 'resolved'"
                @click="addWorkflowStep" 
                class="px-2.5 py-1 bg-slate-800 hover:bg-slate-750 text-slate-350 text-[10px] font-extrabold rounded-lg border border-slate-700"
              >
                + Add Action Step
              </button>
            </div>

            <!-- Steps checklist input -->
            <div class="space-y-2">
              <div 
                v-for="(step, idx) in workflowForm.steps" 
                :key="idx"
                class="flex gap-2 items-center"
              >
                <span class="text-[10px] font-mono text-slate-500">Step {{ idx + 1 }}:</span>
                <input 
                  v-model="workflowForm.steps[idx]" 
                  type="text" 
                  placeholder="e.g. Check system configuration cache logs..."
                  class="flex-grow bg-slate-950 border border-slate-800 rounded-lg px-3 py-1.5 text-xs text-white focus:outline-none focus:border-indigo-500" 
                  :disabled="selectedIncident.status === 'resolved'"
                />
                <button 
                  v-if="selectedIncident.status !== 'resolved' && workflowForm.steps.length > 1"
                  @click="removeWorkflowStep(idx)" 
                  class="text-rose-500 font-bold px-2 py-1 text-xs hover:bg-rose-500/10 rounded-lg"
                >
                  Remove
                </button>
              </div>
            </div>

            <!-- Final Solution text -->
            <div class="space-y-1.5">
              <label class="block text-[10px] text-slate-400 font-extrabold uppercase">Final Solution / Workaround</label>
              <textarea 
                v-model="workflowForm.solution" 
                rows="3"
                placeholder="Log the final diagnostic resolution..." 
                class="w-full bg-slate-950 border border-slate-800 rounded-xl px-3 py-2 text-xs text-white focus:outline-none focus:border-indigo-500"
                required
                :disabled="selectedIncident.status === 'resolved'"
              ></textarea>
            </div>

            <!-- Save Workflow button -->
            <div class="flex justify-end gap-2" v-if="selectedIncident.status !== 'resolved'">
              <button 
                @click="saveResolutionWorkflow(true)" 
                class="px-4 py-2.5 bg-emerald-600 hover:bg-emerald-500 text-white text-xs font-bold rounded-xl shadow-lg transition"
                :disabled="savingWorkflow"
              >
                Save & Resolve Incident
              </button>
            </div>
          </div>

          <!-- PUBLISH TO KNOWLEDGE BASE (Only for resolved workflows) -->
          <div 
            v-if="selectedIncident.status === 'resolved'" 
            class="bg-indigo-500/5 border border-indigo-500/10 p-5 rounded-2xl space-y-4"
          >
            <div>
              <h4 class="text-xs font-extrabold uppercase text-indigo-400 tracking-wider">Publish to Searchable Knowledge Base</h4>
              <p class="text-[10px] text-slate-400 mt-0.5">Instantly convert this resolution workflow into a searchable guide article to improve help desk response times.</p>
            </div>

            <div v-if="selectedIncident.workflow?.kb_article_id" class="p-3 bg-emerald-500/10 border border-emerald-500/20 text-[10px] text-emerald-400 rounded-xl font-bold flex items-center gap-2">
              ✓ Already Published to Knowledge Base. Article Linked.
            </div>

            <form v-else @submit.prevent="publishToKb" class="grid grid-cols-1 md:grid-cols-3 gap-4 items-end">
              <div class="space-y-1.5 md:col-span-1">
                <label class="block text-[10px] text-slate-450 font-extrabold uppercase">Article Title</label>
                <input 
                  v-model="kbForm.title" 
                  type="text" 
                  placeholder="How to resolve..." 
                  class="w-full bg-slate-950 border border-slate-800 rounded-xl px-3 py-2 text-xs text-white focus:outline-none focus:border-indigo-500" 
                  required
                />
              </div>

              <div class="space-y-1.5">
                <label class="block text-[10px] text-slate-455 font-extrabold uppercase">Documentation Category</label>
                <select v-model="kbForm.category" class="w-full bg-slate-950 border border-slate-800 rounded-xl px-3 py-2 text-xs text-white focus:outline-none focus:border-indigo-500">
                  <option value="general">General Guide</option>
                  <option value="billing">Billing FAQ</option>
                  <option value="technical">Technical Manual</option>
                  <option value="onboarding">Onboarding Walkthrough</option>
                </select>
              </div>

              <button 
                type="submit" 
                class="py-2.5 bg-indigo-600 hover:bg-indigo-500 text-white text-xs font-bold rounded-xl shadow-lg transition"
                :disabled="publishingKb"
              >
                Publish KB Article
              </button>
            </form>
          </div>

        </div>

        <!-- Placeholder when no incident is selected -->
        <div v-else class="bg-slate-900 border border-slate-800 rounded-3xl p-16 text-center shadow-xl space-y-3">
          <div class="w-16 h-16 rounded-full bg-indigo-500/10 border border-indigo-500/20 text-indigo-400 flex items-center justify-center mx-auto text-xl font-bold">
            ℹ
          </div>
          <div>
            <h3 class="text-base font-black text-white uppercase tracking-wider">Incident Workspace</h3>
            <p class="text-xs text-slate-450 mt-1 max-w-sm mx-auto leading-relaxed">
              Select an incident record from the list to review traceback logs, trigger matching recommendations, and log workflow steps.
            </p>
          </div>
        </div>
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
const incidents = ref([]);
const selectedIncident = ref(null);

// Suggestions data
const loadingSuggestions = ref(false);
const suggestions = ref({
  similar_tickets: [],
  troubleshooting_articles: []
});

// Resolution Form
const workflowForm = ref({
  steps: [''],
  solution: ''
});
const savingWorkflow = ref(false);

// KB form
const kbForm = ref({
  title: '',
  category: 'technical',
  is_global: true
});
const publishingKb = ref(false);

// Fetch All support incidents
const fetchIncidents = async () => {
  loading.value = true;
  try {
    const response = await api.get('/support/incidents');
    incidents.value = response.data.data;
  } catch (error) {
    console.error('Failed to load incidents', error);
    toast.error('Failed to query incidents logs.');
  } finally {
    loading.value = false;
  }
};

// Select Incident
const selectIncident = async (inc) => {
  selectedIncident.value = inc;
  
  // Fill resolution workflow if already resolved/logged
  if (inc.workflow) {
    workflowForm.value.steps = inc.workflow.steps || [''];
    workflowForm.value.solution = inc.workflow.solution || '';
  } else {
    workflowForm.value.steps = [''];
    workflowForm.value.solution = '';
  }

  // Pre-fill KB title
  kbForm.value.title = 'How to Resolve: ' + inc.title;

  // Query Similarity and troubleshooting recommendations
  if (inc.ticket_id) {
    loadingSuggestions.value = true;
    try {
      const response = await api.get(`/support/tickets/${inc.ticket_id}/suggestions`);
      suggestions.value = response.data.data;
    } catch (error) {
      console.error('Failed to fetch recommendations', error);
    } finally {
      loadingSuggestions.value = false;
    }
  }
};

const addWorkflowStep = () => {
  workflowForm.value.steps.push('');
};

const removeWorkflowStep = (idx) => {
  workflowForm.value.steps.splice(idx, 1);
};

// Save Resolution workflow and resolve incident
const saveResolutionWorkflow = async (resolve = true) => {
  if (!selectedIncident.value) return;
  savingWorkflow.value = true;
  try {
    await api.post(`/support/incidents/${selectedIncident.value.id}/workflow`, {
      steps: workflowForm.value.steps.filter(s => s.trim() !== ''),
      solution: workflowForm.value.solution,
      resolve_incident: resolve
    });
    toast.success('Resolution workflow recorded successfully.');
    await fetchIncidents();
    // Refresh selected incident detail
    const updated = incidents.value.find(i => i.id === selectedIncident.value.id);
    if (updated) selectIncident(updated);
  } catch (error) {
    console.error('Workflow save failed', error);
    toast.error('Could not log resolution workflow.');
  } finally {
    savingWorkflow.value = false;
  }
};

// Publish resolution workflow to searchable Knowledge Base article
const publishToKb = async () => {
  if (!selectedIncident.value?.workflow?.id) return;
  publishingKb.value = true;
  try {
    await api.post(`/support/workflows/${selectedIncident.value.workflow.id}/publish-kb`, {
      title: kbForm.value.title,
      category: kbForm.value.category,
      is_global: kbForm.value.is_global
    });
    toast.success('Knowledge Base article published successfully.');
    await fetchIncidents();
    const updated = incidents.value.find(i => i.id === selectedIncident.value.id);
    if (updated) selectIncident(updated);
  } catch (error) {
    console.error('KB publishing failed', error);
    toast.error('Failed to publish KB documentation.');
  } finally {
    publishingKb.value = false;
  }
};

const formatDate = (dateStr) => {
  if (!dateStr) return '';
  const d = new Date(dateStr);
  return d.toLocaleDateString();
};

onMounted(() => {
  fetchIncidents();
});
</script>

<style scoped>
.animate-fadeIn {
  animation: fadeIn 0.2s ease-out;
}

@keyframes fadeIn {
  from { opacity: 0; transform: translateY(4px); }
  to { opacity: 1; transform: translateY(0); }
}
</style>
