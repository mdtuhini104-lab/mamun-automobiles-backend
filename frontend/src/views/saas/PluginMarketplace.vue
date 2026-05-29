<template>
  <div class="min-h-screen bg-slate-950 text-slate-100 p-6 space-y-6">
    <!-- Header -->
    <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4 border-b border-slate-800 pb-5">
      <div>
        <h1 class="text-2xl font-black tracking-tight text-white">Modular Plugin Marketplace</h1>
        <p class="text-xs text-slate-400 mt-1">Activate optional enterprise modules, SMS/WhatsApp integrations, and custom financial export plugins.</p>
      </div>
      <div class="flex gap-2">
        <button 
          @click="fetchPlugins" 
          class="px-4 py-2 bg-slate-800 hover:bg-slate-700 text-slate-200 text-xs font-bold rounded-xl transition flex items-center gap-1.5"
          :disabled="loading"
        >
          <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor" class="w-4 h-4" :class="{ 'animate-spin': loading }">
            <path stroke-linecap="round" stroke-linejoin="round" d="M16.023 9.348h4.992v-.001M2.985 19.644v-4.992m0 0h4.992m-4.993 0l3.181 3.183a8.25 8.25 0 0013.803-3.7M4.031 9.865a8.25 8.25 0 0113.803-3.7l3.181 3.182m0-4.991v4.99" />
          </svg>
          Refresh Catalog
        </button>
      </div>
    </div>

    <!-- Active Plugins Catalog Grid -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
      <div 
        v-for="plugin in plugins" 
        :key="plugin.key" 
        class="bg-slate-900 border rounded-3xl p-6 flex flex-col justify-between shadow-lg relative overflow-hidden transition hover:border-slate-750"
        :class="plugin.is_installed ? 'border-emerald-500/30' : 'border-slate-800'"
      >
        <!-- Badge for Active status -->
        <span 
          v-if="plugin.is_installed"
          class="absolute top-0 right-0 bg-emerald-600 text-white font-extrabold text-[8px] uppercase tracking-widest px-3 py-1 rounded-bl-xl"
        >
          Installed & Active
        </span>

        <div class="space-y-4">
          <div class="flex items-center justify-between">
            <span class="text-[9px] font-black uppercase tracking-wider text-slate-400 bg-slate-800 px-2 py-0.5 rounded border border-slate-700">
              {{ plugin.category }}
            </span>
            <span 
              class="text-[9px] font-black uppercase tracking-wider px-2 py-0.5 rounded border"
              :class="getTierClasses(plugin.tier_requirement)"
            >
              {{ plugin.tier_requirement }} Tier
            </span>
          </div>

          <div>
            <h3 class="text-sm font-black text-white">{{ plugin.name }}</h3>
            <p class="text-xs text-slate-400 mt-2 leading-relaxed">{{ plugin.description }}</p>
          </div>
        </div>

        <div class="mt-6 border-t border-slate-850 pt-4 flex items-center justify-between gap-4">
          <div v-if="!plugin.is_allowed" class="text-[10px] text-amber-400 font-medium flex items-center gap-1">
            ⚠️ Requires {{ plugin.tier_requirement }} plan
          </div>
          <div v-else class="text-[10px] text-emerald-400 font-medium flex items-center gap-1">
            ✓ Available on plan
          </div>

          <button 
            @click="togglePluginInstallation(plugin)" 
            class="px-4 py-2 text-xs font-bold rounded-xl transition"
            :class="getButtonClasses(plugin)"
            :disabled="togglingKey === plugin.key || (!plugin.is_installed && !plugin.is_allowed)"
          >
            <span v-if="togglingKey === plugin.key" class="w-3.5 h-3.5 border-2 border-white border-t-transparent rounded-full animate-spin inline-block"></span>
            <span v-else>{{ plugin.is_installed ? 'Uninstall' : 'Install Module' }}</span>
          </button>
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
const togglingKey = ref(null);
const plugins = ref([]);

const fetchPlugins = async () => {
  loading.value = true;
  try {
    const response = await api.get('/saas/plugins');
    plugins.value = response.data.data;
  } catch (error) {
    console.error('Failed to load plugin registry', error);
    toast.error('Could not load plugins directory.');
  } finally {
    loading.value = false;
  }
};

const togglePluginInstallation = async (plugin) => {
  togglingKey.value = plugin.key;
  const isInstalling = !plugin.is_installed;
  
  try {
    await api.post('/saas/plugins/toggle', {
      plugin_key: plugin.key,
      install: isInstalling
    });
    toast.success(isInstalling ? `Installed ${plugin.name} module.` : `Removed ${plugin.name} module.`);
    await fetchPlugins();
  } catch (error) {
    console.error('Plugin toggle failed', error);
    toast.error(error.response?.data?.message || 'Action failed.');
  } finally {
    togglingKey.value = null;
  }
};

const getTierClasses = (tier) => {
  if (tier === 'enterprise') return 'bg-violet-500/10 border-violet-500/20 text-violet-400';
  if (tier === 'pro') return 'bg-indigo-500/10 border-indigo-500/20 text-indigo-400';
  return 'bg-slate-800 border-slate-700 text-slate-300';
};

const getButtonClasses = (plugin) => {
  if (plugin.is_installed) {
    return 'bg-slate-800 hover:bg-rose-950 hover:text-rose-400 border border-slate-700 text-slate-350';
  }
  if (!plugin.is_allowed) {
    return 'bg-slate-800 text-slate-500 cursor-not-allowed';
  }
  return 'bg-indigo-600 hover:bg-indigo-500 text-white shadow-lg shadow-indigo-600/15';
};

onMounted(() => {
  fetchPlugins();
});
</script>

<style scoped>
</style>
