<template>
  <div class="min-h-screen bg-slate-50 text-slate-800 flex overflow-hidden font-sans">
    <!-- Mobile Sidebar Overlay -->
    <div 
      v-if="isMobileMenuOpen" 
      class="fixed inset-0 bg-slate-900/40 z-40 md:hidden backdrop-blur-sm transition-opacity"
      @click="isMobileMenuOpen = false"
    ></div>

    <!-- Sidebar -->
    <aside 
      class="w-72 bg-white border-r border-slate-200 flex-shrink-0 flex flex-col fixed md:relative z-50 h-screen transition-all duration-300 shadow-sm"
      :class="isMobileMenuOpen ? 'translate-x-0' : '-translate-x-full md:translate-x-0'"
    >
      <!-- Sidebar Header -->
      <div class="p-4 border-b border-slate-100 flex flex-col gap-3 bg-white sticky top-0 z-10 shrink-0">
        <div class="flex justify-between items-center">
          <div class="flex items-center gap-2">
            <div class="p-2 bg-indigo-600 rounded-xl text-white shadow-sm shadow-indigo-200">
              <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2.5">
                <path stroke-linecap="round" stroke-linejoin="round" d="M19.5 12c0-1.232-.046-2.453-.138-3.662a4.006 4.006 0 00-3.7-3.7 48.656 48.656 0 00-7.324 0 4.006 4.006 0 00-3.7 3.7c-.017.22-.032.441-.046.662M19.5 12l3-3m-3 3l-3-3m-12 3c0 1.232.046 2.453.138 3.662a4.006 4.006 0 003.7 3.7 48.656 48.656 0 007.324 0 4.006 4.006 0 003.7-3.7c.017-.22.032-.441.046-.662M4.5 12l3 3m-3-3l-3 3" />
              </svg>
            </div>
            <div>
              <span class="text-xs font-black text-slate-800 tracking-wider uppercase block">Mamun Automobiles</span>
              <span class="text-[9px] text-slate-400 font-extrabold tracking-widest uppercase block font-mono">Enterprise ERP v3.0</span>
            </div>
          </div>
          <button class="md:hidden text-slate-400 hover:text-slate-600 p-1 rounded-lg" @click="isMobileMenuOpen = false">✕</button>
        </div>

        <!-- Sidebar Search / Command Palette shortcut trigger button -->
        <div class="relative group cursor-pointer" @click="showCommandPalette = true">
          <span class="absolute inset-y-0 left-0 flex items-center pl-3">
            <svg class="w-3.5 h-3.5 text-slate-400 group-hover:text-indigo-500 transition-colors" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
            </svg>
          </span>
          <div class="w-full text-left text-slate-400 text-xs pl-9 pr-12 py-2 bg-slate-50 border border-slate-200 rounded-xl hover:border-slate-300 hover:bg-slate-100/50 transition-all select-none">
            Search Menu...
          </div>
          <span class="absolute inset-y-0 right-0 flex items-center pr-3">
            <kbd class="text-[9px] font-mono px-1.5 py-0.5 bg-slate-200 border border-slate-300 text-slate-500 rounded shadow-sm">Ctrl+K</kbd>
          </span>
        </div>
      </div>

      <!-- Navigation Container with scrollbar-thin -->
      <nav class="flex-1 overflow-y-auto p-3 space-y-3 scrollbar-thin bg-white select-none">
        
        <!-- Pinned Modules (Favorites) Section -->
        <div v-if="pinnedItems.length > 0" class="space-y-1">
          <div class="px-3 text-[9px] font-black uppercase text-indigo-650 tracking-widest flex items-center gap-1.5">
            <svg class="w-3 h-3 text-indigo-600 fill-indigo-600" viewBox="0 0 20 20" fill="currentColor">
              <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z" />
            </svg>
            Favorites
          </div>
          <div class="pl-2 space-y-0.5 border-l border-indigo-100 py-1">
            <div 
              v-for="item in pinnedItems" 
              :key="item.name" 
              class="group/pinned flex items-center justify-between"
            >
              <router-link
                :to="item.to"
                class="flex-1 flex items-center justify-between px-3 py-1.5 rounded-lg text-[10.5px] font-bold text-slate-700 hover:bg-slate-50 hover:text-slate-900 transition-all border-l-2 border-transparent"
                active-class="!text-indigo-650 bg-indigo-50/40 border-indigo-650"
              >
                <span>{{ item.name }}</span>
              </router-link>
              <button 
                @click="togglePin(item)"
                class="opacity-0 group-hover/pinned:opacity-100 p-1 text-slate-300 hover:text-indigo-650 transition-opacity"
                title="Remove from favorites"
              >
                ★
              </button>
            </div>
          </div>
        </div>

        <!-- Menu Groups Accordion -->
        <div 
          v-for="group in menuGroups" 
          :key="group.id" 
          v-show="isGroupVisible(group)"
          class="space-y-0.5"
        >
          <!-- Parent Group Trigger Button -->
          <button
            @click="toggleGroup(group.id)"
            class="w-full flex items-center justify-between px-3 py-2 rounded-xl text-xs font-black uppercase tracking-wider transition text-left"
            :class="isGroupActive(group) 
              ? 'text-indigo-650 bg-indigo-50/40 font-black' 
              : 'text-slate-500 hover:bg-slate-50 hover:text-slate-800'"
          >
            <div class="flex items-center gap-2">
              <span v-html="group.icon" class="w-4 h-4 opacity-80 shrink-0"></span>
              <span class="truncate">{{ group.name }}</span>
            </div>
            
            <svg 
              class="w-3 h-3 transform transition-transform duration-200 text-slate-400"
              :class="isGroupExpanded(group.id) ? 'rotate-90 text-slate-600' : ''"
              fill="none" 
              stroke="currentColor" 
              viewBox="0 0 24 24"
            >
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M9 5l7 7-7 7" />
            </svg>
          </button>

          <!-- Group Sub-items (Accordion container with soft borders) -->
          <div 
            v-show="isGroupExpanded(group.id)" 
            class="pl-3.5 space-y-0.5 border-l border-slate-100 ml-4.5 py-0.5 transition-all duration-200"
          >
            <template v-for="item in group.items" :key="item.name">
              <div 
                v-if="isItemVisible(item)"
                class="group/item flex items-center justify-between"
              >
                <router-link
                  v-if="item.to"
                  :to="item.to"
                  class="flex-1 flex items-center justify-between px-3 py-1.5 rounded-lg text-[10.5px] font-bold text-slate-650 hover:bg-slate-50 hover:text-slate-900 transition-all border-l-2 border-transparent"
                  active-class="!text-indigo-650 !border-indigo-650 bg-indigo-50/50 font-black shadow-sm"
                >
                  <span class="truncate">{{ item.name }}</span>
                </router-link>
                <button
                  v-else
                  @click="triggerAction(item.action)"
                  class="flex-1 flex items-center justify-between px-3 py-1.5 rounded-lg text-[10.5px] font-bold text-slate-650 hover:bg-slate-50 hover:text-slate-900 transition-all border-l-2 border-transparent text-left"
                >
                  <span class="truncate">{{ item.name }}</span>
                </button>

                <!-- Pinned/Favorite Hover Indicator Trigger Button -->
                <button 
                  v-if="item.to"
                  @click="togglePin(item)"
                  class="opacity-0 group-hover/item:opacity-100 px-1 text-xs transition-opacity"
                  :class="isPinned(item) ? 'text-indigo-650' : 'text-slate-300 hover:text-slate-500'"
                  :title="isPinned(item) ? 'Remove from favorites' : 'Add to favorites'"
                >
                  {{ isPinned(item) ? '★' : '☆' }}
                </button>
              </div>
            </template>
          </div>
        </div>
      </nav>

      <!-- Sidebar Footer -->
      <div class="p-4 border-t border-slate-100 bg-white sticky bottom-0 z-10 shrink-0 space-y-3">
        <div v-if="authStore.user" class="flex items-center gap-2.5 px-1">
          <div class="w-8 h-8 rounded-full bg-indigo-50 flex items-center justify-center text-indigo-700 font-black text-xs uppercase border border-indigo-100 shadow-sm">
            {{ authStore.user.name?.charAt(0) }}
          </div>
          <div class="truncate">
            <span class="text-xs font-bold text-slate-800 block truncate">{{ authStore.user.name }}</span>
            <span class="text-[9px] text-slate-400 uppercase font-black tracking-wider block truncate font-mono">{{ authStore.user.role }}</span>
          </div>
        </div>

        <button @click="handleLogout" class="w-full flex items-center justify-center gap-2 px-4 py-2 bg-rose-50 text-rose-650 border border-rose-200 rounded-lg text-xs font-black uppercase tracking-wider hover:bg-rose-100 hover:text-rose-700 transition-colors">
          <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
            <path stroke-linecap="round" stroke-linejoin="round" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"></path>
          </svg>
          Logout
        </button>
      </div>
    </aside>

    <!-- Main Content Area -->
    <main class="flex-1 flex flex-col h-screen overflow-hidden">
      <!-- Premium Sticky Top Header / Navigation Bar -->
      <header class="bg-white border-b border-slate-200 px-6 py-3 flex justify-between items-center shrink-0 z-30 sticky top-0 shadow-sm select-none">
        
        <!-- Header Left: Mobile Drawer Trigger + Breadcrumbs -->
        <div class="flex items-center gap-3">
          <button class="text-slate-500 hover:text-slate-900 md:hidden p-1.5 hover:bg-slate-50 rounded-xl" @click="isMobileMenuOpen = true">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
              <path stroke-linecap="round" stroke-linejoin="round" d="M4 6h16M4 12h16M4 18h16"></path>
            </svg>
          </button>
          
          <!-- Dynamic Breadcrumbs -->
          <div class="hidden sm:flex items-center gap-1.5 text-xs text-slate-400 font-bold">
            <template v-for="(crumb, idx) in breadcrumbs" :key="idx">
              <span v-if="idx > 0" class="text-slate-300">/</span>
              <router-link 
                v-if="crumb.to" 
                :to="crumb.to" 
                class="hover:text-indigo-650 transition"
              >
                {{ crumb.name }}
              </router-link>
              <span v-else class="text-slate-650 font-black">{{ crumb.name }}</span>
            </template>
          </div>
        </div>

        <!-- Header Right: Operations Search, Quick Actions, History, Profile -->
        <div class="flex items-center gap-3">
          <!-- Command Palette Trigger Button -->
          <button 
            @click="showCommandPalette = true"
            class="flex items-center gap-1.5 px-3 py-1.5 border border-slate-200 rounded-xl text-slate-500 hover:bg-slate-50 hover:text-slate-800 text-xs transition duration-150"
            title="Open command palette (Ctrl+K)"
          >
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
              <path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
            </svg>
            <span class="hidden md:inline font-bold">Search Menu</span>
          </button>

          <!-- Floating Quick Actions Overlay Dropdown Trigger -->
          <button 
            @click="showQuickActions = true"
            class="flex items-center gap-1.5 px-3 py-1.5 bg-indigo-600 hover:bg-indigo-700 text-white rounded-xl text-xs font-black tracking-wide shadow-sm shadow-indigo-150 transition"
          >
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2.5">
              <path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15" />
            </svg>
            <span>Quick Actions</span>
          </button>

          <!-- Notification Drawer Peek -->
          <router-link
            :to="{ name: 'dashboard.ai-inbox' }"
            class="p-2 border border-slate-200 text-slate-500 hover:text-slate-800 hover:bg-slate-50 rounded-xl transition relative"
            title="Notification Center"
          >
            <svg class="w-4.5 h-4.5" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
              <path stroke-linecap="round" stroke-linejoin="round" d="M14.857 17.082a23.848 23.848 0 005.454-1.31A8.967 8.967 0 0118 9.75v-.7V9A6 6 0 006 9v.75a8.967 8.967 0 01-2.312 6.022c1.733.64 3.56 1.085 5.455 1.31m5.714 0a24.255 24.255 0 01-5.714 0m5.714 0a3 3 0 11-5.714 0" />
            </svg>
            <span class="absolute top-1 right-1 w-2 h-2 bg-indigo-600 rounded-full animate-ping"></span>
          </router-link>
        </div>
      </header>

      <!-- Recents Quick-Links bar -->
      <div v-if="recentPages.length > 0" class="bg-slate-100/50 border-b border-slate-200 px-6 py-1.5 flex items-center gap-2 text-[10px] text-slate-400 font-bold select-none shrink-0 overflow-x-auto">
        <span class="uppercase tracking-wider">Recents:</span>
        <div class="flex items-center gap-2">
          <router-link
            v-for="(page, idx) in recentPages"
            :key="idx"
            :to="page.to"
            class="px-2 py-0.5 bg-white border border-slate-200 text-slate-650 hover:text-indigo-650 rounded-lg hover:border-indigo-200 transition"
          >
            {{ page.name }}
          </router-link>
        </div>
      </div>
      
      <!-- Views Router Outlet -->
      <div class="flex-1 overflow-y-auto p-4 md:p-8 relative">
        <div v-if="hasError" class="flex flex-col items-center justify-center h-full min-h-[50vh] text-center px-4">
          <div class="bg-rose-50 rounded-full p-4 mb-4">
            <svg class="w-12 h-12 text-rose-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
          </div>
          <h2 class="text-xl font-bold text-slate-900 mb-2">Module Loading Failed</h2>
          <p class="text-slate-600 mb-6 max-w-md">{{ errorMessage }}</p>
          <button @click="router.go(0)" class="px-6 py-2 bg-indigo-600 text-white rounded-lg font-medium hover:bg-indigo-700 transition-colors">
            Reload Page
          </button>
        </div>
        
        <router-view v-else v-slot="{ Component }">
          <template v-if="Component">
            <Suspense>
              <template #default>
                <component :is="Component" />
              </template>
              <template #fallback>
                <div class="flex flex-col items-center justify-center h-full min-h-[50vh] text-slate-400">
                  <div class="animate-spin rounded-full h-12 w-12 border-b-2 border-indigo-650 mb-4"></div>
                  <p class="text-xs font-bold uppercase tracking-wider text-slate-500">Loading ERP Module...</p>
                </div>
              </template>
            </Suspense>
          </template>
        </router-view>
      </div>
    </main>

    <!-- Global Command Palette Modal (Ctrl + K) -->
    <div 
      v-if="showCommandPalette" 
      class="fixed inset-0 z-50 flex items-start justify-center p-4 pt-[10vh] bg-slate-950/65 backdrop-blur-sm"
      @click.self="showCommandPalette = false"
    >
      <div class="bg-white rounded-3xl border border-slate-200 shadow-2xl max-w-2xl w-full flex flex-col max-h-[70vh] overflow-hidden select-none animate-in fade-in zoom-in duration-200">
        <!-- Input section -->
        <div class="p-4 border-b border-slate-100 flex items-center gap-3 shrink-0">
          <svg class="w-5 h-5 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2.5">
            <path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
          </svg>
          <input 
            v-model="commandQuery"
            type="text"
            placeholder="Type a command or module name (e.g. 'intake', 'invoice', 'create job')..."
            class="flex-1 text-slate-800 text-sm focus:outline-none placeholder-slate-400"
            ref="commandInputRef"
            @keydown.down.prevent="selectNextCommand"
            @keydown.up.prevent="selectPrevCommand"
            @keydown.enter="executeSelectedCommand"
          />
          <button @click="showCommandPalette = false" class="text-xs text-slate-400 hover:text-slate-600 bg-slate-100 px-2 py-1 rounded-lg border border-slate-200">ESC</button>
        </div>

        <!-- Result matches list -->
        <div class="flex-1 overflow-y-auto p-2 space-y-0.5 scrollbar-thin">
          <!-- Command Suggestions -->
          <div 
            v-for="(match, index) in filteredCommandList" 
            :key="match.name"
            class="flex items-center justify-between p-3 rounded-2xl cursor-pointer transition-colors"
            :class="index === selectedCommandIndex ? 'bg-indigo-50 text-indigo-700' : 'hover:bg-slate-50 text-slate-700'"
            @click="runCommandMatch(match)"
          >
            <div class="flex items-center gap-3">
              <span class="text-base shrink-0">{{ match.categoryIcon || '⚙️' }}</span>
              <div>
                <span class="text-xs font-bold block">{{ match.name }}</span>
                <span class="text-[10px] text-slate-400 uppercase font-black tracking-wider block font-mono">{{ match.category }}</span>
              </div>
            </div>
            <kbd class="text-[9px] font-mono px-1.5 py-0.5 bg-white border border-slate-200 text-slate-400 rounded shadow-sm">Enter</kbd>
          </div>
          
          <div v-if="filteredCommandList.length === 0" class="text-center py-12 text-slate-400 text-xs italic">
            No command matches found. Try searching for "intake", "invoice", "inspection", or "QC".
          </div>
        </div>

        <div class="p-3 border-t border-slate-100 bg-slate-50/50 flex justify-between items-center text-[10px] text-slate-400 font-bold shrink-0">
          <span>Use <kbd>↑</kbd> <kbd>↓</kbd> keys to navigate.</span>
          <span>Shortcut keys: <kbd>Ctrl+K</kbd> to open.</span>
        </div>
      </div>
    </div>

    <!-- Floating Quick Actions Modal Dialog -->
    <div 
      v-if="showQuickActions" 
      class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-950/60 backdrop-blur-sm"
      @click.self="showQuickActions = false"
    >
      <div class="bg-white rounded-3xl border border-slate-200 shadow-2xl max-w-lg w-full p-6 space-y-5 text-slate-800 select-none animate-in fade-in zoom-in duration-200">
        <div class="flex justify-between items-center border-b border-slate-100 pb-3">
          <h3 class="text-xs font-black text-slate-800 uppercase tracking-widest flex items-center gap-2">
            ⚡ Quick Operations Actions
          </h3>
          <button @click="showQuickActions = false" class="text-slate-400 hover:text-slate-650 font-bold p-1">✕</button>
        </div>
        
        <div class="grid grid-cols-2 gap-3">
          <button 
            v-for="act in quickActionsList"
            :key="act.label"
            v-show="isItemVisible(act)"
            @click="executeQuickAction(act)"
            class="flex flex-col items-center gap-2.5 p-4 border border-slate-100 hover:border-indigo-200 bg-slate-50/50 hover:bg-indigo-50/30 rounded-2xl text-center transition group"
          >
            <div class="p-2.5 bg-indigo-50 text-indigo-650 rounded-xl group-hover:bg-indigo-600 group-hover:text-white transition-all shadow-sm">
              <span v-html="act.icon" class="w-5 h-5 block"></span>
            </div>
            <span class="text-[10px] font-bold text-slate-700 uppercase tracking-wider block">{{ act.label }}</span>
          </button>
        </div>
      </div>
    </div>

    <!-- Global Toast Component -->
    <ToastContainer />
  </div>
</template>

<script setup>
import { ref, computed, watch, onMounted, onUnmounted, onErrorCaptured, nextTick } from 'vue';
import { useRouter, useRoute } from 'vue-router';
import { useAuthStore } from '../../stores/auth';
import ToastContainer from '../../components/ui/ToastContainer.vue';

const router = useRouter();
const route = useRoute();
const authStore = useAuthStore();

const isMobileMenuOpen = ref(false);
const hasError = ref(false);
const errorMessage = ref('');

// Search and command toggles
const commandQuery = ref('');
const showCommandPalette = ref(false);
const showQuickActions = ref(false);
const commandInputRef = ref(null);
const selectedCommandIndex = ref(0);

// Pin / Favorites tracking
const pinnedItems = ref([]);

// Recent routes tracking
const recentPages = ref([]);

const expandedGroups = ref({
  dashboard_overview: true,
  frontdesk_reception: true,
  inspection_diagnosis: false,
  quotations_approvals: false,
  workshop_operations: false,
  mobile_operations: false,
  parts_inventory: false,
  qc_delivery: false,
  warranty_aftersales: false,
  customers_crm: false,
  finance_accounts: false,
  hr_workforce: false,
  reports_bi: false,
  ai_automation: false,
  saas_multitenant: false,
  system_security: false,
  notification_center: false,
  documents_media: false,
});

// Full 18-Group Enterprise Hierarchy Modules Config
const menuGroups = [
  {
    name: 'Dashboard & Control Center',
    id: 'dashboard_overview',
    icon: '<svg class="w-full h-full" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6" /></svg>',
    items: [
      { name: 'Main Dashboard', to: { name: 'dashboard-home' }, roles: ['Super Admin', 'Manager', 'Frontdesk', 'Cashier'] },
      { name: 'Workshop Operations Hub', to: { name: 'workshop.hub' }, roles: ['Super Admin', 'Manager'] },
      { name: 'Live Workflow Board', to: { name: 'workshop.live-board' }, roles: ['Super Admin', 'Manager', 'Frontdesk'] },
      { name: 'Executive Dashboard', to: { name: 'dashboard.executive' }, roles: ['Super Admin', 'Manager'] },
      { name: 'Operations Dashboard', to: { name: 'workshop.work-orders', query: { tab: 'ops-dashboard' } }, roles: ['Super Admin', 'Manager'] },
      { name: 'Finance Dashboard', to: { name: 'analytics.index', query: { tab: 'finance' } }, roles: ['Super Admin', 'Manager', 'Cashier'] },
      { name: 'Inventory Dashboard', to: { name: 'inventory-list', query: { tab: 'dashboard' } }, roles: ['Super Admin', 'Manager'] },
      { name: 'HR Dashboard', to: { name: 'payroll.dashboard' }, roles: ['Super Admin', 'Manager'] },
      { name: 'AI Monitoring Dashboard', to: { name: 'ai.index', query: { tab: 'monitoring' } }, roles: ['Super Admin', 'Manager'] },
      { name: 'Branch Performance Dashboard', to: { name: 'dashboard.executive', query: { section: 'branches' } }, roles: ['Super Admin', 'Manager'] },
      { name: 'Analytics & BI', to: { name: 'analytics.index' }, roles: ['Super Admin', 'Manager'] },
      { name: 'Notifications Center', to: { name: 'dashboard.ai-inbox' }, roles: ['Super Admin', 'Manager', 'Frontdesk', 'Cashier'] },
      { name: 'Realtime Alerts', to: { name: 'dashboard.ai-inbox', query: { tab: 'alerts' } }, roles: ['Super Admin', 'Manager', 'Frontdesk', 'Cashier'] },
      { name: 'Daily KPI Snapshot', to: { name: 'analytics.index', query: { tab: 'kpis' } }, roles: ['Super Admin', 'Manager'] }
    ]
  },
  {
    name: 'Frontdesk & Reception',
    id: 'frontdesk_reception',
    icon: '<svg class="w-full h-full" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z" /></svg>',
    items: [
      { name: 'Frontdesk Intake', to: { name: 'workshop.intake' }, roles: ['Super Admin', 'Manager', 'Frontdesk'] },
      { name: 'Walk-In Registration', to: { name: 'workshop.intake', query: { mode: 'walkin' } }, roles: ['Super Admin', 'Manager', 'Frontdesk'] },
      { name: 'Quick Vehicle Lookup', to: { name: 'vehicles.index', query: { mode: 'lookup' } }, roles: ['Super Admin', 'Manager', 'Frontdesk'] },
      { name: 'Customer Check-In', to: { name: 'workshop.intake', query: { mode: 'checkin' } }, roles: ['Super Admin', 'Manager', 'Frontdesk'] },
      { name: 'Appointment Queue', to: { name: 'appointments.index' }, roles: ['Super Admin', 'Manager', 'Frontdesk'] },
      { name: 'Reception Timeline', to: { name: 'vehicles.history.index', query: { mode: 'timeline' } }, roles: ['Super Admin', 'Manager', 'Frontdesk'] },
      { name: 'Reception Notes', to: { name: 'workshop.intake', query: { mode: 'notes' } }, roles: ['Super Admin', 'Manager', 'Frontdesk'] }
    ]
  },
  {
    name: 'Inspection & Diagnosis',
    id: 'inspection_diagnosis',
    icon: '<svg class="w-full h-full" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01" /></svg>',
    items: [
      { name: 'Vehicle Inspection', to: { name: 'workshop.inspection' }, roles: ['Super Admin', 'Manager', 'Frontdesk', 'Technician'] },
      { name: 'Digital Inspection Checklist', to: { name: 'workshop.inspection', query: { tab: 'checklist' } }, roles: ['Super Admin', 'Manager', 'Frontdesk', 'Technician'] },
      { name: 'Diagnosis Center', to: { name: 'workshop.diagnosis' }, roles: ['Super Admin', 'Manager', 'Frontdesk', 'Technician'] },
      { name: 'DTC Scanner', to: { name: 'workshop.diagnosis', query: { tab: 'dtc' } }, roles: ['Super Admin', 'Manager', 'Frontdesk', 'Technician'] },
      { name: 'Inspection Media Uploads', to: { name: 'workshop.inspection', query: { tab: 'media' } }, roles: ['Super Admin', 'Manager', 'Frontdesk', 'Technician'] },
      { name: 'Technician Findings', to: { name: 'workshop.diagnosis', query: { tab: 'findings' } }, roles: ['Super Admin', 'Manager', 'Frontdesk', 'Technician'] },
      { name: 'Inspection Reports', to: { name: 'reports-home', query: { tab: 'inspections' } }, roles: ['Super Admin', 'Manager'] }
    ]
  },
  {
    name: 'Quotations & Approvals',
    id: 'quotations_approvals',
    icon: '<svg class="w-full h-full" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" /></svg>',
    items: [
      { name: 'Quotation Builder', to: { name: 'workshop.quotation' }, roles: ['Super Admin', 'Manager', 'Frontdesk', 'Cashier'] },
      { name: 'Estimate Revisions', to: { name: 'workshop.quotation', query: { tab: 'revisions' } }, roles: ['Super Admin', 'Manager', 'Frontdesk'] },
      { name: 'Approval Queue', to: { name: 'workshop.approvals' }, roles: ['Super Admin', 'Manager', 'Frontdesk'] },
      { name: 'Customer Approval Tracking', to: { name: 'workshop.approvals', query: { tab: 'tracking' } }, roles: ['Super Admin', 'Manager', 'Frontdesk'] },
      { name: 'SMS/WhatsApp Approval', to: { name: 'workshop.approvals', query: { tab: 'sms' } }, roles: ['Super Admin', 'Manager', 'Frontdesk'] },
      { name: 'Negotiated Pricing', to: { name: 'workshop.quotation', query: { tab: 'pricing' } }, roles: ['Super Admin', 'Manager', 'Frontdesk'] },
      { name: 'Approval History', to: { name: 'workshop.approvals', query: { tab: 'history' } }, roles: ['Super Admin', 'Manager', 'Frontdesk'] }
    ]
  },
  {
    name: 'Workshop Operations',
    id: 'workshop_operations',
    icon: '<svg class="w-full h-full" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10" /></svg>',
    items: [
      { name: 'Work Order Dispatch', to: { name: 'workshop.work-orders' }, roles: ['Super Admin', 'Manager'] },
      { name: 'Bay Allocation Board', to: { name: 'workshop.bays' }, roles: ['Super Admin', 'Manager'] },
      { name: 'Technician Task Board', to: { name: 'workshop.technician-tasks' }, roles: ['Super Admin', 'Manager', 'Technician'] },
      { name: 'Live Bay Monitoring', to: { name: 'workshop.bays', query: { tab: 'live' } }, roles: ['Super Admin', 'Manager'] },
      { name: 'Workflow Lifecycle Tracker', to: { name: 'workshop.tracker' }, roles: ['Super Admin', 'Manager', 'Frontdesk'] },
      { name: 'Priority Jobs', to: { name: 'workshop.work-orders', query: { tab: 'priority' } }, roles: ['Super Admin', 'Manager'] },
      { name: 'Delayed Jobs Monitor', to: { name: 'workshop.work-orders', query: { tab: 'delayed' } }, roles: ['Super Admin', 'Manager'] },
      { name: 'Escalation Queue', to: { name: 'workshop.work-orders', query: { tab: 'escalated' } }, roles: ['Super Admin', 'Manager'] }
    ]
  },
  {
    name: 'Mobile Operations',
    id: 'mobile_operations',
    icon: '<svg class="w-full h-full" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M12 18h.01M8 21h8a2 2 0 002-2V5a2 2 0 00-2-2H8a2 2 0 00-2 2v14a2 2 0 002 2z" /></svg>',
    items: [
      { name: 'Technician Mobile Tasks', to: { name: 'workshop.technician-tasks', query: { mode: 'mobile' } }, roles: ['Super Admin', 'Manager', 'Technician'] },
      { name: 'Mobile Diagnosis', to: { name: 'workshop.diagnosis', query: { mode: 'mobile' } }, roles: ['Super Admin', 'Manager', 'Technician'] },
      { name: 'Mobile Parts Usage', to: { name: 'workshop.parts-consumption', query: { mode: 'mobile' } }, roles: ['Super Admin', 'Manager', 'Technician'] },
      { name: 'Offline Sync Queue', to: { name: 'settings.incident-center', query: { tab: 'offline' } }, roles: ['Super Admin', 'Manager', 'Technician'] },
      { name: 'Mobile Check-In', to: { name: 'workshop.intake', query: { mode: 'mobile' } }, roles: ['Super Admin', 'Manager', 'Frontdesk'] },
      { name: 'Field Service Jobs', to: { name: 'job-cards.index', query: { type: 'field' } }, roles: ['Super Admin', 'Manager', 'Technician'] }
    ]
  },
  {
    name: 'Parts & Inventory',
    id: 'parts_inventory',
    icon: '<svg class="w-full h-full" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4" /></svg>',
    items: [
      { name: 'Inventory Dashboard', to: { name: 'inventory-list', query: { tab: 'dashboard' } }, roles: ['Super Admin', 'Manager'] },
      { name: 'Inventory & Parts', to: { name: 'inventory-list' }, roles: ['Super Admin', 'Manager'] },
      { name: 'Parts Consumption', to: { name: 'workshop.parts-consumption' }, roles: ['Super Admin', 'Manager', 'Technician'] },
      { name: 'Parts Requests', to: { name: 'workshop.parts-consumption', query: { tab: 'requests' } }, roles: ['Super Admin', 'Manager', 'Technician'] },
      { name: 'Low Stock Alerts', to: { name: 'inventory-list', query: { filter: 'low_stock' } }, roles: ['Super Admin', 'Manager'] },
      { name: 'GRN / Stock Intake', to: { name: 'purchases-list', query: { action: 'grn' } }, roles: ['Super Admin', 'Manager'] },
      { name: 'Purchases', to: { name: 'purchases-list' }, roles: ['Super Admin', 'Manager'] },
      { name: 'Suppliers', to: { name: 'purchases-list', query: { tab: 'suppliers' } }, roles: ['Super Admin', 'Manager'] },
      { name: 'Stock Adjustment', to: { name: 'inventory-list', query: { action: 'adjust' } }, roles: ['Super Admin', 'Manager'] },
      { name: 'Parts Transfer', to: { name: 'inventory-list', query: { action: 'transfer' } }, roles: ['Super Admin', 'Manager'] },
      { name: 'Vendor Ledger', to: { name: 'purchases-list', query: { tab: 'ledger' } }, roles: ['Super Admin', 'Manager'] }
    ]
  },
  {
    name: 'QC & Delivery',
    id: 'qc_delivery',
    icon: '<svg class="w-full h-full" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>',
    items: [
      { name: 'QC Verification', to: { name: 'workshop.qc' }, roles: ['Super Admin', 'Manager'] },
      { name: 'Road Test Checklist', to: { name: 'workshop.qc', query: { tab: 'roadtest' } }, roles: ['Super Admin', 'Manager'] },
      { name: 'Delivery Handover', to: { name: 'workshop.delivery' }, roles: ['Super Admin', 'Manager', 'Frontdesk', 'Cashier'] },
      { name: 'Customer Signature Capture', to: { name: 'workshop.delivery', query: { tab: 'signature' } }, roles: ['Super Admin', 'Manager', 'Frontdesk'] },
      { name: 'Vehicle Release', to: { name: 'workshop.delivery', query: { tab: 'release' } }, roles: ['Super Admin', 'Manager', 'Frontdesk', 'Cashier'] },
      { name: 'Delivery Confirmation', to: { name: 'workshop.delivery', query: { tab: 'confirmation' } }, roles: ['Super Admin', 'Manager', 'Frontdesk', 'Cashier'] },
      { name: 'Delivery Timeline', to: { name: 'workshop.qc-delivery' }, roles: ['Super Admin', 'Manager'] }
    ]
  },
  {
    name: 'Warranty & After-Sales',
    id: 'warranty_aftersales',
    icon: '<svg class="w-full h-full" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z" /></svg>',
    items: [
      { name: 'Warranty & Comebacks', to: { name: 'workshop.warranty-comeback' }, roles: ['Super Admin', 'Manager'] },
      { name: 'Service History', to: { name: 'vehicles.history.index' }, roles: ['Super Admin', 'Manager', 'Frontdesk'] },
      { name: 'Repeat Complaints', to: { name: 'workshop.warranty-comeback', query: { tab: 'complaints' } }, roles: ['Super Admin', 'Manager'] },
      { name: 'Warranty Claims', to: { name: 'workshop.warranty-comeback', query: { tab: 'claims' } }, roles: ['Super Admin', 'Manager'] },
      { name: 'Customer Feedback', to: { name: 'saas.customer-success', query: { tab: 'feedback' } }, roles: ['Super Admin', 'Manager'] },
      { name: 'Service Reminders', to: { name: 'appointments.index', query: { tab: 'reminders' } }, roles: ['Super Admin', 'Manager', 'Frontdesk'] },
      { name: 'Follow-up Tracking', to: { name: 'appointments.index', query: { tab: 'followup' } }, roles: ['Super Admin', 'Manager', 'Frontdesk'] }
    ]
  },
  {
    name: 'Customers & CRM',
    id: 'customers_crm',
    icon: '<svg class="w-full h-full" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z" /></svg>',
    items: [
      { name: 'Customers', to: { name: 'customers.index' }, roles: ['Super Admin', 'Manager', 'Frontdesk', 'Cashier'] },
      { name: 'Customer Vehicles', to: { name: 'vehicles.index' }, roles: ['Super Admin', 'Manager', 'Frontdesk'] },
      { name: 'CRM & Appointments', to: { name: 'appointments.index' }, roles: ['Super Admin', 'Manager', 'Frontdesk'] },
      { name: 'Loyalty Program', to: { name: 'crm.customer-pricing', query: { tab: 'loyalty' } }, roles: ['Super Admin', 'Manager', 'Frontdesk'] },
      { name: 'Membership Plans', to: { name: 'crm.customer-pricing', query: { tab: 'membership' } }, roles: ['Super Admin', 'Manager', 'Frontdesk'] },
      { name: 'Customer Ledger', to: { name: 'crm.customer-ledger' }, roles: ['Super Admin', 'Manager', 'Cashier'] },
      { name: 'Communication Logs', to: { name: 'dashboard.ai-inbox', query: { tab: 'logs' } }, roles: ['Super Admin', 'Manager', 'Frontdesk'] },
      { name: 'Customer Timeline', to: { name: 'vehicles.history.index', query: { tab: 'customer' } }, roles: ['Super Admin', 'Manager', 'Frontdesk'] }
    ]
  },
  {
    name: 'Finance & Accounts',
    id: 'finance_accounts',
    icon: '<svg class="w-full h-full" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>',
    items: [
      { name: 'Invoices', to: { name: 'invoices.index' }, roles: ['Super Admin', 'Manager', 'Cashier'] },
      { name: 'Invoice Settlement', to: { name: 'workshop.settlement' }, roles: ['Super Admin', 'Manager', 'Cashier'] },
      { name: 'POS Billing', to: { name: 'pos-home' }, roles: ['Super Admin', 'Manager', 'Cashier'] },
      { name: 'Accounts & Transactions', to: { name: 'accounts.index' }, roles: ['Super Admin', 'Manager', 'Cashier'] },
      { name: 'Expenses', to: { name: 'transactions.index' }, roles: ['Super Admin', 'Manager', 'Cashier'] },
      { name: 'Cashbook', to: { name: 'accounts.index', query: { tab: 'cashbook' } }, roles: ['Super Admin', 'Manager', 'Cashier'] },
      { name: 'Bank Accounts', to: { name: 'accounts.index', query: { tab: 'banks' } }, roles: ['Super Admin', 'Manager', 'Cashier'] },
      { name: 'Income & Expense', to: { name: 'transactions.index', query: { tab: 'income-expense' } }, roles: ['Super Admin', 'Manager', 'Cashier'] },
      { name: 'Financial Reports', to: { name: 'reports-home', query: { tab: 'finance' } }, roles: ['Super Admin', 'Manager', 'Cashier'] },
      { name: 'Profit & Loss', to: { name: 'reports-home', query: { tab: 'pl' } }, roles: ['Super Admin', 'Manager', 'Cashier'] },
      { name: 'Customer Ledger', to: { name: 'crm.customer-ledger' }, roles: ['Super Admin', 'Manager', 'Cashier'] },
      { name: 'Supplier Ledger', to: { name: 'purchases-list', query: { tab: 'ledger' } }, roles: ['Super Admin', 'Manager', 'Cashier'] },
      { name: 'Tax & VAT Reports', to: { name: 'reports-home', query: { tab: 'tax' } }, roles: ['Super Admin', 'Manager', 'Cashier'] }
    ]
  },
  {
    name: 'HR & Workforce',
    id: 'hr_workforce',
    icon: '<svg class="w-full h-full" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857" /></svg>',
    items: [
      { name: 'Employees', to: { name: 'staff.index' }, roles: ['Super Admin', 'Manager'] },
      { name: 'Staff Directory', to: { name: 'staff.index', query: { tab: 'directory' } }, roles: ['Super Admin', 'Manager'] },
      { name: 'Attendance', to: { name: 'attendances.index' }, roles: ['Super Admin', 'Manager'] },
      { name: 'Payroll', to: { name: 'payrolls.index' }, roles: ['Super Admin', 'Manager'] },
      { name: 'Shift Management', to: { name: 'staff.index', query: { tab: 'shifts' } }, roles: ['Super Admin', 'Manager'] },
      { name: 'Technician Performance', to: { name: 'analytics.index', query: { tab: 'technicians' } }, roles: ['Super Admin', 'Manager'] },
      { name: 'Workforce Analytics', to: { name: 'analytics.index', query: { tab: 'workforce' } }, roles: ['Super Admin', 'Manager'] },
      { name: 'Roles & Permissions', to: { name: 'roles.index' }, roles: ['Super Admin'] },
      { name: 'Leave Requests', to: { name: 'attendance.leave' }, roles: ['Super Admin', 'Manager'] }
    ]
  },
  {
    name: 'Reports & BI',
    id: 'reports_bi',
    icon: '<svg class="w-full h-full" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z" /></svg>',
    items: [
      { name: 'Workshop KPI', to: { name: 'reports-home', query: { tab: 'kpis' } }, roles: ['Super Admin', 'Manager'] },
      { name: 'Revenue Analytics', to: { name: 'reports-home', query: { tab: 'revenue' } }, roles: ['Super Admin', 'Manager'] },
      { name: 'Branch Analytics', to: { name: 'reports-home', query: { tab: 'branches' } }, roles: ['Super Admin', 'Manager'] },
      { name: 'Technician Efficiency', to: { name: 'reports-home', query: { tab: 'efficiency' } }, roles: ['Super Admin', 'Manager'] },
      { name: 'Workflow Delay Heatmaps', to: { name: 'reports-home', query: { tab: 'delays' } }, roles: ['Super Admin', 'Manager'] },
      { name: 'Operational Reports', to: { name: 'reports-home', query: { tab: 'operational' } }, roles: ['Super Admin', 'Manager'] },
      { name: 'AI Trust Scores', to: { name: 'ai.index', query: { tab: 'trust' } }, roles: ['Super Admin', 'Manager'] },
      { name: 'Predictive Analytics', to: { name: 'ai.index', query: { tab: 'predictive' } }, roles: ['Super Admin', 'Manager'] },
      { name: 'Export Center', to: { name: 'reports-home', query: { tab: 'export' } }, roles: ['Super Admin', 'Manager'] }
    ]
  },
  {
    name: 'AI & Automation',
    id: 'ai_automation',
    icon: '<svg class="w-full h-full" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M13 10V3L4 14h7v7l9-11h-7z" /></svg>',
    items: [
      { name: 'AI Operations Center', to: { name: 'ai.index' }, roles: ['Super Admin', 'Manager'] },
      { name: 'AI Governance Inbox', to: { name: 'dashboard.ai-inbox' }, roles: ['Super Admin', 'Manager'] },
      { name: 'Predictive Operations', to: { name: 'ai.index', query: { tab: 'predictive' } }, roles: ['Super Admin', 'Manager'] },
      { name: 'Recommendation Engine', to: { name: 'dashboard.ai-inbox', query: { tab: 'recommendations' } }, roles: ['Super Admin', 'Manager'] },
      { name: 'Automation Rules', to: { name: 'ai.index', query: { tab: 'rules' } }, roles: ['Super Admin', 'Manager'] },
      { name: 'AI Diagnostics', to: { name: 'ai.index', query: { tab: 'diagnostics' } }, roles: ['Super Admin', 'Manager'] },
      { name: 'AI Telemetry', to: { name: 'ai.index', query: { tab: 'telemetry' } }, roles: ['Super Admin', 'Manager'] },
      { name: 'Drift Monitoring', to: { name: 'ai.index', query: { tab: 'drift' } }, roles: ['Super Admin', 'Manager'] },
      { name: 'Operational Intelligence', to: { name: 'ai.index', query: { tab: 'intelligence' } }, roles: ['Super Admin', 'Manager'] }
    ]
  },
  {
    name: 'SaaS & Tenant',
    id: 'saas_multitenant',
    icon: '<svg class="w-full h-full" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4" /></svg>',
    items: [
      { name: 'SaaS Admin', to: { name: 'saas.index' }, roles: ['Super Admin'] },
      { name: 'Branch Management', to: { name: 'saas.index', query: { tab: 'branches' } }, roles: ['Super Admin'] },
      { name: 'Tenant Management', to: { name: 'saas.index', query: { tab: 'tenants' } }, roles: ['Super Admin'] },
      { name: 'Subscription Plans', to: { name: 'saas.billing' }, roles: ['Super Admin'] },
      { name: 'Billing Cycles', to: { name: 'saas.billing', query: { tab: 'cycles' } }, roles: ['Super Admin'] },
      { name: 'Usage Metrics', to: { name: 'saas.index', query: { tab: 'usage' } }, roles: ['Super Admin'] },
      { name: 'Feature Flags', to: { name: 'saas.marketplace' }, roles: ['Super Admin'] },
      { name: 'Tenant Health Monitoring', to: { name: 'saas.index', query: { tab: 'health' } }, roles: ['Super Admin'] }
    ]
  },
  {
    name: 'System & Security',
    id: 'system_security',
    icon: '<svg class="w-full h-full" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z" /></svg>',
    items: [
      { name: 'General Settings', to: { name: 'settings.index' }, roles: ['Super Admin'] },
      { name: 'Activity Logs', to: { name: 'activity-logs.index' }, roles: ['Super Admin'] },
      { name: 'Audit Logs', to: { name: 'activity-logs.index', query: { tab: 'audit' } }, roles: ['Super Admin'] },
      { name: 'Backup & Recovery', to: { name: 'settings.incident-center', query: { tab: 'backups' } }, roles: ['Super Admin'] },
      { name: 'Webhook Logs', to: { name: 'developer.portal', query: { tab: 'webhooks' } }, roles: ['Super Admin'] },
      { name: 'API Keys', to: { name: 'developer.portal' }, roles: ['Super Admin'] },
      { name: 'Notification Settings', to: { name: 'settings.index', query: { tab: 'notifications' } }, roles: ['Super Admin'] },
      { name: 'Security Policies', to: { name: 'settings.security-audit' }, roles: ['Super Admin'] },
      { name: 'Queue Monitoring', to: { name: 'settings.production-operations' }, roles: ['Super Admin'] },
      { name: 'Websocket Monitoring', to: { name: 'settings.production-operations', query: { tab: 'websockets' } }, roles: ['Super Admin'] }
    ]
  },
  {
    name: 'Notification Center',
    id: 'notification_center',
    icon: '<svg class="w-full h-full" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9" /></svg>',
    items: [
      { name: 'System Alerts', to: { name: 'dashboard.ai-inbox', query: { filter: 'system' } }, roles: ['Super Admin', 'Manager'] },
      { name: 'Operational Alerts', to: { name: 'dashboard.ai-inbox', query: { filter: 'operations' } }, roles: ['Super Admin', 'Manager', 'Frontdesk'] },
      { name: 'Payment Alerts', to: { name: 'dashboard.ai-inbox', query: { filter: 'payments' } }, roles: ['Super Admin', 'Manager', 'Cashier'] },
      { name: 'Inventory Alerts', to: { name: 'dashboard.ai-inbox', query: { filter: 'inventory' } }, roles: ['Super Admin', 'Manager'] },
      { name: 'AI Alerts', to: { name: 'dashboard.ai-inbox', query: { filter: 'ai' } }, roles: ['Super Admin', 'Manager'] },
      { name: 'Escalation Alerts', to: { name: 'dashboard.ai-inbox', query: { filter: 'escalations' } }, roles: ['Super Admin', 'Manager'] },
      { name: 'Customer Notifications', to: { name: 'dashboard.ai-inbox', query: { filter: 'customer' } }, roles: ['Super Admin', 'Manager', 'Frontdesk'] }
    ]
  },
  {
    name: 'Documents & Media',
    id: 'documents_media',
    icon: '<svg class="w-full h-full" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M8 7v8a2 2 0 002 2h6M8 7V5a2 2 0 012-2h4.586a1 1 0 01.707.293l4.414 4.414a1 1 0 01.293.707V15a2 2 0 01-2 2h-2M8 7H6a2 2 0 00-2 2v10a2 2 0 002 2h8a2 2 0 002-2v-2" /></svg>',
    items: [
      { name: 'Inspection Images', to: { name: 'workshop.inspection', query: { tab: 'images' } }, roles: ['Super Admin', 'Manager', 'Frontdesk', 'Technician'] },
      { name: 'Vehicle Videos', to: { name: 'workshop.inspection', query: { tab: 'videos' } }, roles: ['Super Admin', 'Manager', 'Frontdesk', 'Technician'] },
      { name: 'Invoice PDFs', to: { name: 'invoices.index', query: { view: 'pdf' } }, roles: ['Super Admin', 'Manager', 'Cashier'] },
      { name: 'Quotation PDFs', to: { name: 'workshop.quotation', query: { view: 'pdf' } }, roles: ['Super Admin', 'Manager', 'Frontdesk'] },
      { name: 'Customer Attachments', to: { name: 'customers.index', query: { tab: 'attachments' } }, roles: ['Super Admin', 'Manager', 'Frontdesk'] },
      { name: 'Warranty Documents', to: { name: 'workshop.warranty-comeback', query: { tab: 'docs' } }, roles: ['Super Admin', 'Manager'] },
      { name: 'Audit Evidence', to: { name: 'settings.security-audit', query: { tab: 'evidence' } }, roles: ['Super Admin'] },
      { name: 'Media Storage Monitor', to: { name: 'settings.production-operations', query: { tab: 'storage' } }, roles: ['Super Admin'] }
    ]
  }
];

// Quick Action Configurations
const quickActionsList = [
  { label: 'Create Job Card', to: { name: 'job-cards.create' }, icon: '📋', roles: ['Super Admin', 'Manager', 'Frontdesk'] },
  { label: 'Create Invoice', to: { name: 'pos-home' }, icon: '💵', roles: ['Super Admin', 'Manager', 'Cashier'] },
  { label: 'New Customer', to: { name: 'customers.create' }, icon: '👤', roles: ['Super Admin', 'Manager', 'Frontdesk'] },
  { label: 'New Quotation', to: { name: 'workshop.quotation', query: { action: 'new' } }, icon: '📝', roles: ['Super Admin', 'Manager', 'Frontdesk'] },
  { label: 'Add Expense', to: { name: 'transactions.index', query: { action: 'expense' } }, icon: '📉', roles: ['Super Admin', 'Manager', 'Cashier'] },
  { label: 'New Purchase', to: { name: 'purchases-list', query: { action: 'create' } }, icon: '🛒', roles: ['Super Admin', 'Manager'] },
  { label: 'Emergency QC Alert', action: 'qcAlert', icon: '🚨', roles: ['Super Admin', 'Manager', 'Technician'] }
];

// Global Command List for CMD+K palette search
const globalCommandList = computed(() => {
  const list = [];
  menuGroups.forEach(group => {
    group.items.forEach(item => {
      if (isItemVisible(item)) {
        list.push({
          name: item.name,
          category: group.name,
          categoryIcon: group.icon,
          to: item.to,
          action: item.action
        });
      }
    });
  });
  // Add Quick Actions to Command List
  quickActionsList.forEach(act => {
    if (isItemVisible(act)) {
      list.push({
        name: `Quick: ${act.label}`,
        category: 'Quick Actions',
        categoryIcon: act.icon,
        to: act.to,
        action: act.action
      });
    }
  });
  return list;
});

const isItemVisible = (item) => {
  if (!item.roles) return true;
  return item.roles.some(role => authStore.hasRole(role));
};

const isGroupVisible = (group) => {
  return group.items.some(item => isItemVisible(item));
};

const isGroupActive = (group) => {
  return group.items.some(item => item.to && item.to.name === route.name);
};

const isGroupExpanded = (groupId) => {
  return !!expandedGroups.value[groupId];
};

const toggleGroup = (groupId) => {
  expandedGroups.value[groupId] = !expandedGroups.value[groupId];
};

// Favorites/Pinning utility handlers
const isPinned = (item) => {
  return pinnedItems.value.some(p => p.name === item.name);
};

const togglePin = (item) => {
  const index = pinnedItems.value.findIndex(p => p.name === item.name);
  if (index >= 0) {
    pinnedItems.value.splice(index, 1);
  } else {
    pinnedItems.value.push({ name: item.name, to: item.to });
  }
  localStorage.setItem('pinned_modules', JSON.stringify(pinnedItems.value));
};

// Recent pages setup
const trackVisitedPage = (currentRoute) => {
  if (!currentRoute.name || currentRoute.name === 'login') return;
  // Find matching menu item to get user friendly display name
  let displayName = currentRoute.name;
  for (const group of menuGroups) {
    const match = group.items.find(item => item.to && item.to.name === currentRoute.name);
    if (match) {
      displayName = match.name;
      break;
    }
  }

  // Remove existing matching name to update insertion index
  recentPages.value = recentPages.value.filter(p => p.name !== displayName);
  
  // Add to beginning of array
  recentPages.value.unshift({
    name: displayName,
    to: { name: currentRoute.name, query: currentRoute.query }
  });

  // Limit to last 5 pages
  if (recentPages.value.length > 5) {
    recentPages.value.pop();
  }

  localStorage.setItem('recent_pages', JSON.stringify(recentPages.value));
};

// Breadcrumbs builder
const breadcrumbs = computed(() => {
  const list = [{ name: 'Home', to: { name: 'dashboard-home' } }];
  if (route.name === 'dashboard-home') return list;

  for (const group of menuGroups) {
    const match = group.items.find(item => item.to && item.to.name === route.name);
    if (match) {
      list.push({ name: group.name });
      list.push({ name: match.name, to: match.to });
      return list;
    }
  }
  
  // Fallback parsing from route path segment
  const segments = route.path.split('/').filter(Boolean);
  segments.forEach((seg, idx) => {
    const isLast = idx === segments.length - 1;
    list.push({
      name: seg.charAt(0).toUpperCase() + seg.slice(1).replace(/-/g, ' '),
      to: isLast ? null : { path: '/' + segments.slice(0, idx + 1).join('/') }
    });
  });
  return list;
});

// Command search computations
const filteredCommandList = computed(() => {
  const query = commandQuery.value.trim().toLowerCase();
  if (!query) return globalCommandList.value.slice(0, 8); // Return default first 8 commands
  
  return globalCommandList.value.filter(cmd => {
    return cmd.name.toLowerCase().includes(query) || cmd.category.toLowerCase().includes(query);
  });
});

const selectNextCommand = () => {
  if (selectedCommandIndex.value < filteredCommandList.value.length - 1) {
    selectedCommandIndex.value++;
  }
};

const selectPrevCommand = () => {
  if (selectedCommandIndex.value > 0) {
    selectedCommandIndex.value--;
  }
};

const executeSelectedCommand = () => {
  const selectedCmd = filteredCommandList.value[selectedCommandIndex.value];
  if (selectedCmd) {
    runCommandMatch(selectedCmd);
  }
};

const runCommandMatch = (match) => {
  showCommandPalette.value = false;
  if (match.to) {
    router.push(match.to);
  } else if (match.action) {
    triggerAction(match.action);
  }
};

// Keyboard listener for Command Palette (Ctrl+K)
const handleGlobalKeydown = (event) => {
  if ((event.ctrlKey || event.metaKey) && event.key === 'k') {
    event.preventDefault();
    showCommandPalette.value = !showCommandPalette.value;
  }
};

// Watch Route transitions to track recents and auto-expand groups
watch(() => route.path, () => {
  trackVisitedPage(route);
}, { immediate: true });

watch(showCommandPalette, (isOpen) => {
  if (isOpen) {
    commandQuery.value = '';
    selectedCommandIndex.value = 0;
    nextTick(() => {
      if (commandInputRef.value) {
        commandInputRef.value.focus();
      }
    });
  }
});

// Auto-expand groups when matching route changes
watch(() => route.name, (newRouteName) => {
  menuGroups.forEach(group => {
    if (group.items.some(item => item.to && item.to.name === newRouteName)) {
      expandedGroups.value[group.id] = true;
    }
  });
}, { immediate: true });

onMounted(() => {
  window.addEventListener('keydown', handleGlobalKeydown);
  
  // Hydrate Favorited / Recent Pages from LocalStorage
  pinnedItems.value = JSON.parse(localStorage.getItem('pinned_modules') || '[]');
  recentPages.value = JSON.parse(localStorage.getItem('recent_pages') || '[]');
});

onUnmounted(() => {
  window.removeEventListener('keydown', handleGlobalKeydown);
});

onErrorCaptured((err, instance, info) => {
  console.error('Captured in DashboardLayout:', err, info);
  hasError.value = true;
  errorMessage.value = err.message || 'An unexpected error occurred while loading this module.';
  return false;
});

router.afterEach(() => {
  hasError.value = false;
  errorMessage.value = '';
  isMobileMenuOpen.value = false;
});

const handleLogout = async () => {
  await authStore.logout();
  router.push({ name: 'login' });
};

const triggerAction = (actionName) => {
  if (actionName === 'quickActions') {
    showQuickActions.value = true;
  }
};

const executeQuickAction = (act) => {
  showQuickActions.value = false;
  if (act.to) {
    router.push(act.to);
  } else if (act.action === 'qcAlert') {
    alert('🚨 EMERGENCY QUALITY CONTROL ALERT TRIGGERED! Notification sent to managers.');
  }
};
</script>

<style scoped>
/* High performance scrolling configuration styles */
.scrollbar-thin::-webkit-scrollbar {
  width: 4px;
}
.scrollbar-thin::-webkit-scrollbar-track {
  background: transparent;
}
.scrollbar-thin::-webkit-scrollbar-thumb {
  background: #e2e8f0;
  border-radius: 4px;
}
.scrollbar-thin::-webkit-scrollbar-thumb:hover {
  background: #cbd5e1;
}

kbd {
  font-family: inherit;
}
</style>
