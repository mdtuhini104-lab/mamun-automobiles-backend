<template>
  <div class="min-h-screen bg-slate-50 text-slate-800 flex overflow-hidden font-sans">
    <!-- Mobile Sidebar Overlay -->
    <div 
      v-if="isMobileMenuOpen" 
      class="fixed inset-0 bg-slate-900/30 z-40 md:hidden backdrop-blur-sm transition-opacity"
      @click="isMobileMenuOpen = false"
    ></div>

    <!-- Sidebar -->
    <aside 
      class="bg-white border-r border-slate-205 flex-shrink-0 flex flex-col fixed md:relative z-50 h-screen transition-all duration-300 shadow-sm"
      :class="[
        isSidebarMini ? 'w-18' : 'w-64',
        isSidebarCompact ? 'text-[10px]' : 'text-[11px]'
      ]"
    >
      <!-- Sidebar Header -->
      <div 
        class="border-b border-slate-100 flex flex-col bg-white sticky top-0 z-10 shrink-0 transition-all duration-150"
        :class="isSidebarCompact ? 'p-2.5 gap-1.5' : 'p-3.5 gap-2.5'"
      >
        <div class="flex justify-between items-center">
          <div class="flex items-center gap-2 overflow-hidden">
            <div class="p-1.5 bg-indigo-600 rounded-lg text-white shadow-sm shrink-0">
              <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2.5">
                <path stroke-linecap="round" stroke-linejoin="round" d="M19.5 12c0-1.232-.046-2.453-.138-3.662a4.006 4.006 0 00-3.7-3.7 48.656 48.656 0 00-7.324 0 4.006 4.006 0 00-3.7 3.7c-.017.22-.032.441-.046.662M19.5 12l3-3m-3 3l-3-3m-12 3c0 1.232.046 2.453.138 3.662a4.006 4.006 0 003.7 3.7 48.656 48.656 0 007.324 0 4.006 4.006 0 003.7-3.7c.017-.22.032-.441.046-.662M4.5 12l3 3m-3-3l-3 3" />
              </svg>
            </div>
            <div v-if="!isSidebarMini" class="transition-opacity duration-150">
              <span class="text-xs font-black text-slate-800 tracking-wider uppercase block leading-none">Mamun Auto</span>
              <span class="text-[8px] text-slate-400 font-extrabold tracking-widest uppercase block font-mono mt-0.5">ERP v3.2</span>
            </div>
          </div>
          <button class="md:hidden text-slate-400 hover:text-slate-600 p-1 rounded-lg" @click="isMobileMenuOpen = false">✕</button>
        </div>

        <!-- Sidebar Search Trigger -->
        <div 
          v-if="!isSidebarMini"
          class="relative group cursor-pointer" 
          @click="showCommandPalette = true"
        >
          <span class="absolute inset-y-0 left-0 flex items-center pl-2.5">
            <svg class="w-3.5 h-3.5 text-slate-400 group-hover:text-indigo-500 transition-colors" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
            </svg>
          </span>
          <div class="w-full text-left text-slate-400 text-[10px] pl-8 pr-12 py-1.5 bg-slate-50 border border-slate-200 rounded-lg hover:border-slate-300 hover:bg-slate-100/50 transition-all select-none">
            Search Menu...
          </div>
          <span class="absolute inset-y-0 right-0 flex items-center pr-2.5">
            <kbd class="text-[8px] font-mono px-1 py-0.5 bg-slate-200 border border-slate-300 text-slate-500 rounded shadow-sm">Ctrl+K</kbd>
          </span>
        </div>
      </div>

      <!-- Navigation Container with scrollbar-thin -->
      <nav 
        class="flex-1 overflow-y-auto space-y-1.5 scrollbar-thin bg-white select-none transition-all duration-150"
        :class="isSidebarCompact ? 'p-1.5' : 'p-2.5'"
      >
        
        <!-- Pinned Modules (Favorites) Section -->
        <div v-if="pinnedItems.length > 0 && !isSidebarMini" class="space-y-0.5">
          <div class="px-2.5 text-[8.5px] font-black uppercase text-indigo-650 tracking-widest flex items-center gap-1.5">
            <svg class="w-2.5 h-2.5 text-indigo-600 fill-indigo-600" viewBox="0 0 20 20" fill="currentColor">
              <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z" />
            </svg>
            Favorites
          </div>
          <div class="pl-2 space-y-0.5 border-l border-indigo-100 py-0.5">
            <div 
              v-for="item in pinnedItems" 
              :key="item.name" 
              class="group/pinned flex items-center justify-between"
            >
              <router-link
                :to="item.to"
                class="flex-1 flex items-center justify-between px-2.5 py-1 rounded-md font-bold text-slate-700 hover:bg-slate-50 hover:text-slate-900 transition-all border-l-2 border-transparent"
                :class="isSidebarCompact ? 'text-[9.5px]' : 'text-[10px]'"
                active-class="!text-indigo-650 bg-indigo-50/40 border-indigo-650 font-black"
              >
                <span>{{ item.name }}</span>
              </router-link>
              <button 
                @click="togglePin(item)"
                class="opacity-0 group-hover/pinned:opacity-100 p-0.5 text-slate-300 hover:text-indigo-650 transition-opacity text-[10px]"
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
            class="w-full flex items-center justify-between rounded-lg font-bold uppercase tracking-wider transition text-left"
            :class="[
              isGroupActive(group) 
                ? 'text-indigo-650 bg-indigo-50/20 font-black' 
                : 'text-slate-550 hover:bg-slate-50 hover:text-slate-800',
              isSidebarCompact ? 'px-2 py-1 text-[9px]' : 'px-2.5 py-1.5 text-[9.5px]',
              isSidebarMini ? 'justify-center' : ''
            ]"
            :title="isSidebarMini ? group.name : ''"
          >
            <div class="flex items-center gap-1.5 overflow-hidden">
              <span v-html="group.icon" class="w-3.5 h-3.5 opacity-80 shrink-0"></span>
              <span v-if="!isSidebarMini" class="truncate">{{ group.name }}</span>
            </div>
            
            <svg 
              v-if="!isSidebarMini"
              class="w-2.5 h-2.5 transform transition-transform duration-200 text-slate-400 shrink-0"
              :class="isGroupExpanded(group.id) ? 'rotate-90 text-slate-655' : ''"
              fill="none" 
              stroke="currentColor" 
              viewBox="0 0 24 24"
            >
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M9 5l7 7-7 7" />
            </svg>
          </button>

          <!-- Group Sub-items -->
          <div 
            v-show="isGroupExpanded(group.id) && !isSidebarMini" 
            class="pl-2 space-y-0.5 border-l border-slate-100 ml-4 py-0.5 transition-all duration-200"
          >
            <template v-for="item in group.items" :key="item.name">
              <div 
                v-if="isItemVisible(item)"
                class="group/item flex flex-col"
              >
                <!-- Check if the item has subItems (nested collapse block) -->
                <div v-if="item.subItems && item.subItems.length > 0" class="w-full space-y-0.5">
                  <button 
                    @click="toggleSubItem(item.name)"
                    class="w-full flex items-center justify-between px-2.5 py-1 rounded-md font-bold text-slate-600 hover:bg-slate-50 hover:text-slate-900 transition-all text-left"
                    :class="isSidebarCompact ? 'text-[9.5px]' : 'text-[10px]'"
                  >
                    <span>{{ item.name }}</span>
                    <svg 
                      class="w-2.5 h-2.5 transform transition-transform duration-150 text-slate-400"
                      :class="expandedSubItems[item.name] ? 'rotate-90' : ''"
                      fill="none" 
                      stroke="currentColor" 
                      viewBox="0 0 24 24"
                    >
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M9 5l7 7-7 7" />
                    </svg>
                  </button>
                  
                  <div 
                    v-show="expandedSubItems[item.name]"
                    class="pl-2.5 space-y-0.5 border-l border-slate-100 ml-3 py-0.5"
                  >
                    <router-link
                      v-for="sub in item.subItems"
                      :key="sub.name"
                      :to="sub.to"
                      class="flex items-center justify-between px-2.5 py-1 rounded-md font-bold text-slate-500 hover:bg-slate-50 hover:text-slate-800 transition-all"
                      :class="isSidebarCompact ? 'text-[9px]' : 'text-[9.5px]'"
                      active-class="!text-indigo-650 bg-indigo-50/30 border-l-2 border-indigo-650"
                    >
                      <span class="truncate">{{ sub.name }}</span>
                    </router-link>
                  </div>
                </div>

                <!-- Ordinary single menu item link -->
                <div v-else class="flex items-center justify-between w-full">
                  <router-link
                    v-if="item.to"
                    :to="item.to"
                    class="flex-1 flex items-center justify-between rounded-md font-bold text-slate-600 hover:bg-slate-50 hover:text-slate-900 transition-all border-l-2 border-transparent"
                    :class="isSidebarCompact ? 'text-[9.5px] px-2 py-1' : 'text-[10px] px-2.5 py-1.2'"
                    active-class="!text-indigo-650 !border-indigo-650 bg-indigo-50/30 font-black"
                  >
                    <span class="truncate">{{ item.name }}</span>
                  </router-link>
                  <button
                    v-else
                    @click="triggerAction(item.action)"
                    class="flex-1 flex items-center justify-between rounded-md font-bold text-slate-600 hover:bg-slate-50 hover:text-slate-900 transition-all border-l-2 border-transparent text-left"
                    :class="isSidebarCompact ? 'text-[9.5px] px-2 py-1' : 'text-[10px] px-2.5 py-1.2'"
                  >
                    <span class="truncate">{{ item.name }}</span>
                  </button>

                  <!-- Pin/Favorite toggle trigger -->
                  <button 
                    v-if="item.to"
                    @click="togglePin(item)"
                    class="opacity-0 group-hover/item:opacity-100 px-1 text-[10px] transition-opacity shrink-0"
                    :class="isPinned(item) ? 'text-indigo-655' : 'text-slate-300 hover:text-slate-500'"
                    :title="isPinned(item) ? 'Remove from favorites' : 'Add to favorites'"
                  >
                    {{ isPinned(item) ? '★' : '☆' }}
                  </button>
                </div>
              </div>
            </template>
          </div>
        </div>
      </nav>

      <!-- Sidebar Density & Collapsed Mini Mode Toggles -->
      <div class="px-3 py-1.5 border-t border-slate-100 bg-slate-50/50 flex justify-between items-center shrink-0">
        <button 
          @click="isSidebarMini = !isSidebarMini" 
          class="p-1 text-slate-400 hover:text-slate-700 bg-white border border-slate-200 rounded shadow-sm hover:bg-slate-50 transition"
          :title="isSidebarMini ? 'Expand Sidebar' : 'Collapse Sidebar'"
        >
          <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2.5">
            <path v-if="isSidebarMini" stroke-linecap="round" stroke-linejoin="round" d="M13 5l7 7-7 7M5 5l7 7-7 7" />
            <path v-else stroke-linecap="round" stroke-linejoin="round" d="M11 19l-7-7 7-7m8 14l-7-7 7-7" />
          </svg>
        </button>

        <button 
          v-if="!isSidebarMini"
          @click="isSidebarCompact = !isSidebarCompact" 
          class="flex items-center gap-1 px-2 py-0.5 text-[8px] font-black uppercase tracking-wider text-slate-500 hover:text-slate-800 bg-white border border-slate-200 rounded shadow-sm hover:bg-slate-50 transition"
        >
          <span>{{ isSidebarCompact ? 'Standard' : 'Compact' }}</span>
        </button>
      </div>

      <!-- Sidebar Footer -->
      <div 
        class="border-t border-slate-100 bg-white sticky bottom-0 z-10 shrink-0 transition-all duration-150"
        :class="isSidebarCompact ? 'p-2 gap-1.5' : 'p-3.5 gap-2'"
      >
        <div v-if="authStore.user" class="flex items-center gap-2 px-1 overflow-hidden">
          <div class="w-6.5 h-6.5 rounded-full bg-indigo-50 flex items-center justify-center text-indigo-700 font-black text-xs uppercase border border-indigo-100 shadow-sm shrink-0">
            {{ authStore.user.name?.charAt(0) }}
          </div>
          <div v-if="!isSidebarMini" class="truncate transition-opacity duration-150">
            <span class="text-[9.5px] font-bold text-slate-800 block truncate">{{ authStore.user.name }}</span>
            <span class="text-[8px] text-slate-400 uppercase font-black tracking-wider block truncate font-mono">{{ authStore.user.role }}</span>
          </div>
        </div>

        <button 
          @click="handleLogout" 
          class="w-full flex items-center justify-center gap-1 bg-rose-50 text-rose-650 border border-rose-200 rounded-md text-[8.5px] font-black uppercase tracking-wider hover:bg-rose-100 hover:text-rose-700 transition-colors"
          :class="isSidebarCompact ? 'py-1' : 'py-1.5'"
        >
          <svg class="w-3 h-3 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2.5">
            <path stroke-linecap="round" stroke-linejoin="round" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"></path>
          </svg>
          <span v-if="!isSidebarMini">Logout</span>
        </button>
      </div>
    </aside>

    <!-- Main Content Area -->
    <main class="flex-1 flex flex-col h-screen overflow-hidden">
      <!-- Sticky Header -->
      <header class="bg-white border-b border-slate-200 px-4 md:px-6 py-2.5 flex justify-between items-center shrink-0 z-35 sticky top-0 shadow-sm select-none">
        
        <!-- Header Left -->
        <div class="flex items-center gap-3">
          <button class="text-slate-500 hover:text-slate-900 md:hidden p-1.5 hover:bg-slate-50 rounded-xl" @click="isMobileMenuOpen = true">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
              <path stroke-linecap="round" stroke-linejoin="round" d="M4 6h16M4 12h16M4 18h16"></path>
            </svg>
          </button>
          
          <!-- Dynamic Breadcrumbs -->
          <div class="hidden lg:flex items-center gap-1.5 text-xs text-slate-400 font-bold">
            <template v-for="(crumb, idx) in breadcrumbs" :key="idx">
              <span v-if="idx > 0" class="text-slate-300">/</span>
              <router-link 
                v-if="crumb.to" 
                :to="crumb.to" 
                class="hover:text-indigo-650 transition"
              >
                {{ crumb.name }}
              </router-link>
              <span v-else class="text-slate-655 font-black">{{ crumb.name }}</span>
            </template>
          </div>

          <!-- Enterprise Clock -->
          <span class="hidden sm:inline-flex items-center px-2.5 py-1 bg-slate-100 text-slate-500 rounded-lg text-[10px] font-bold font-mono border border-slate-200">
            🕒 {{ currentFormattedTime }}
          </span>
        </div>

        <!-- Header Right -->
        <div class="flex items-center gap-2 md:gap-3">
          <!-- Active Branch Selector -->
          <div class="relative shrink-0 hidden md:block">
            <select 
              v-model="selectedBranch" 
              class="appearance-none bg-slate-50 border border-slate-200 rounded-xl text-[10.5px] font-bold text-slate-700 pl-3 pr-8 py-1.5 focus:outline-none focus:ring-1 focus:ring-indigo-500 cursor-pointer hover:border-slate-300"
            >
              <option>Dhaka Main Branch</option>
              <option>Chittagong Branch</option>
              <option>Sylhet Express Hub</option>
            </select>
            <div class="absolute inset-y-0 right-0 flex items-center pr-3 pointer-events-none text-slate-400 text-xs">▼</div>
          </div>

          <!-- Latency Indicator -->
          <span 
            class="hidden sm:inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-[9px] font-black tracking-wide border border-emerald-250 bg-emerald-50 text-emerald-700"
          >
            <span class="w-1.5 h-1.5 rounded-full bg-emerald-500 animate-pulse"></span>
            <span>WS LIVE (14ms)</span>
          </span>

          <!-- Command Search Button -->
          <button 
            @click="showCommandPalette = true"
            class="flex items-center gap-1.5 px-3 py-1.5 border border-slate-200 rounded-xl text-slate-500 hover:bg-slate-50 hover:text-slate-800 text-[11px] transition duration-150"
            title="Open command palette (Ctrl+K)"
          >
            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
              <path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
            </svg>
            <span class="hidden md:inline font-bold">Search Menu</span>
          </button>

          <!-- Notification Center -->
          <router-link
            :to="{ name: 'dashboard.ai-inbox' }"
            class="p-1.5 border border-slate-200 text-slate-500 hover:text-slate-800 hover:bg-slate-50 rounded-xl transition relative shrink-0"
            title="Notification Center"
          >
            <svg class="w-4.5 h-4.5" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
              <path stroke-linecap="round" stroke-linejoin="round" d="M14.857 17.082a23.848 23.848 0 005.454-1.31A8.967 8.967 0 0118 9.75v-.7V9A6 6 0 006 9v.75a8.967 8.967 0 01-2.312 6.022c1.733.64 3.56 1.085 5.455 1.31m5.714 0a24.255 24.255 0 01-5.714 0m5.714 0a3 3 0 11-5.714 0" />
            </svg>
            <span class="absolute top-1 right-1 w-1.5 h-1.5 bg-indigo-600 rounded-full animate-ping"></span>
          </router-link>

          <!-- Quick Actions -->
          <button 
            @click="showQuickActions = true"
            class="flex items-center gap-1 px-2.5 py-1.5 bg-indigo-600 hover:bg-indigo-700 text-white rounded-xl text-[10.5px] font-black tracking-wide shadow-sm shadow-indigo-150 transition"
          >
            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2.5">
              <path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15" />
            </svg>
            <span class="hidden sm:inline">Actions</span>
          </button>
        </div>
      </header>

      <!-- Recents Quick-Links bar -->
      <div v-if="recentPages.length > 0" class="bg-slate-100/50 border-b border-slate-200 px-6 py-1 flex items-center gap-2 text-[9px] text-slate-400 font-black select-none shrink-0 overflow-x-auto">
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
      <div class="flex-1 overflow-y-auto p-4 md:p-6 pb-20 md:pb-6 relative">
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

      <!-- BOTTOM NAVIGATION BAR (Mobile quick-nav) -->
      <div class="md:hidden fixed bottom-0 inset-x-0 bg-white border-t border-slate-200 py-1.5 px-6 flex justify-between items-center z-45 shadow-lg select-none">
        <router-link 
          :to="{ name: 'dashboard-home' }"
          class="flex flex-col items-center gap-0.5 text-slate-400 hover:text-indigo-600"
          active-class="!text-indigo-600 font-bold"
        >
          <span class="text-sm">🏠</span>
          <span class="text-[9px]">Home</span>
        </router-link>
        
        <router-link 
          :to="{ name: 'workshop.technician-tasks' }"
          v-show="authStore.hasRole('Technician') || authStore.hasRole('Super Admin') || authStore.hasRole('Manager')"
          class="flex flex-col items-center gap-0.5 text-slate-400 hover:text-indigo-600"
          active-class="!text-indigo-600 font-bold"
        >
          <span class="text-sm">🛠️</span>
          <span class="text-[9px]">Tasks</span>
        </router-link>

        <router-link 
          :to="{ name: 'workshop.intake' }"
          v-show="authStore.hasRole('Frontdesk') || authStore.hasRole('Super Admin') || authStore.hasRole('Manager')"
          class="flex flex-col items-center gap-0.5 text-slate-400 hover:text-indigo-600"
          active-class="!text-indigo-600 font-bold"
        >
          <span class="text-sm">📋</span>
          <span class="text-[9px]">Intake</span>
        </router-link>

        <router-link 
          :to="{ name: 'workshop.inspection' }"
          class="flex flex-col items-center gap-0.5 text-slate-400 hover:text-indigo-600"
          active-class="!text-indigo-600 font-bold"
        >
          <span class="text-sm">🔍</span>
          <span class="text-[9px]">Inspect</span>
        </router-link>

        <button 
          @click="showCommandPalette = true"
          class="flex flex-col items-center gap-0.5 text-slate-400 hover:text-indigo-600"
        >
          <span class="text-sm">🔎</span>
          <span class="text-[9px]">Search</span>
        </button>
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
          <button @click="showCommandPalette = false" class="text-xs text-slate-400 hover:text-slate-660 bg-slate-100 px-2 py-1 rounded-lg border border-slate-200">ESC</button>
        </div>

        <!-- Result matches list -->
        <div class="flex-1 overflow-y-auto p-2 space-y-0.5 scrollbar-thin">
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
                <span class="text-[10px] text-slate-405 uppercase font-black tracking-wider block font-mono">{{ match.category }}</span>
              </div>
            </div>
            <kbd class="text-[9px] font-mono px-1.5 py-0.5 bg-white border border-slate-200 text-slate-404 rounded shadow-sm">Enter</kbd>
          </div>
          
          <div v-if="filteredCommandList.length === 0" class="text-center py-12 text-slate-404 text-xs italic">
            No command matches found. Try searching for "intake", "invoice", "inspection", or "QC".
          </div>
        </div>

        <div class="p-3 border-t border-slate-100 bg-slate-50/50 flex justify-between items-center text-[10px] text-slate-404 font-bold shrink-0">
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
          <button @click="showQuickActions = false" class="text-slate-400 hover:text-slate-655 font-bold p-1">✕</button>
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

// Sidebar layout density & collapses
const isSidebarMini = ref(false);
const isSidebarCompact = ref(true); // Default compact mode active
const selectedBranch = ref('Dhaka Main Branch');
const currentFormattedTime = ref('');

// Subitem collapsible mappings
const expandedSubItems = ref({
  'Purchases & Suppliers': false,
  'Advanced Analytics': false,
  'Advanced Tools': false,
});

const toggleSubItem = (name) => {
  expandedSubItems.value[name] = !expandedSubItems.value[name];
};

// Command Search Toggles
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
  dashboard: true,
  frontdesk: true,
  workshop: false,
  qc_delivery: false,
  customers: false,
  inventory: false,
  finance: false,
  hr_staff: false,
  reports_ai: false,
  system: false,
});

// Final 10-Group Operational SIMPLICITY Sidebar Hierarchy
const menuGroups = [
  {
    name: 'Dashboard',
    id: 'dashboard',
    icon: '<svg class="w-full h-full" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><rect x="3" y="3" width="7" height="7" rx="1" /><rect x="14" y="3" width="7" height="7" rx="1" /><rect x="14" y="14" width="7" height="7" rx="1" /><rect x="3" y="14" width="7" height="7" rx="1" /></svg>',
    items: [
      { name: 'Enterprise Cockpit', to: { name: 'dashboard-home' }, roles: ['Super Admin', 'Manager', 'Frontdesk', 'Cashier'] },
      { name: 'Live Workflow', to: { name: 'workshop.live-board' }, roles: ['Super Admin', 'Manager', 'Frontdesk'] },
      { name: 'Notifications', to: { name: 'dashboard.ai-inbox' }, roles: ['Super Admin', 'Manager', 'Frontdesk', 'Cashier'] },
      { name: 'Quick Actions', action: 'quickActions', roles: ['Super Admin', 'Manager', 'Frontdesk', 'Cashier'] },
      { name: "Today's Tasks", to: { name: 'workshop.technician-tasks' }, roles: ['Super Admin', 'Manager', 'Technician'] },
      { name: 'Pending Approvals', to: { name: 'workshop.approvals' }, roles: ['Super Admin', 'Manager', 'Frontdesk'] }
    ]
  },
  {
    name: 'Frontdesk',
    id: 'frontdesk',
    icon: '<svg class="w-full h-full" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z" /></svg>',
    items: [
      { name: 'Customer Intake', to: { name: 'workshop.intake' }, roles: ['Super Admin', 'Manager', 'Frontdesk'] },
      { name: 'Reception Queue', to: { name: 'workshop.inspection' }, roles: ['Super Admin', 'Manager', 'Frontdesk'] },
      { name: 'Appointments', to: { name: 'appointments.index' }, roles: ['Super Admin', 'Manager', 'Frontdesk'] },
      { name: 'Quotations', to: { name: 'workshop.quotation' }, roles: ['Super Admin', 'Manager', 'Frontdesk', 'Cashier'] },
      { name: 'Walk-In Queue', to: { name: 'workshop.intake', query: { mode: 'walkin' } }, roles: ['Super Admin', 'Manager', 'Frontdesk'] },
      { name: 'Waiting Customers', to: { name: 'workshop.live-board', query: { filter: 'waiting' } }, roles: ['Super Admin', 'Manager', 'Frontdesk'] }
    ]
  },
  {
    name: 'Workshop',
    id: 'workshop',
    icon: '<svg class="w-full h-full" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z" /><path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" /></svg>',
    items: [
      { name: 'Active Jobs', to: { name: 'workshop.work-orders' }, roles: ['Super Admin', 'Manager'] },
      { name: 'Technician Board', to: { name: 'workshop.technician-tasks' }, roles: ['Super Admin', 'Manager', 'Technician'] },
      { name: 'Bay Board', to: { name: 'workshop.bays' }, roles: ['Super Admin', 'Manager'] },
      { name: 'Parts Usage', to: { name: 'workshop.parts-consumption' }, roles: ['Super Admin', 'Manager', 'Technician'] },
      { name: 'Diagnosis', to: { name: 'workshop.diagnosis' }, roles: ['Super Admin', 'Manager', 'Technician'] }
    ]
  },
  {
    name: 'QC & Delivery',
    id: 'qc_delivery',
    icon: '<svg class="w-full h-full" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>',
    items: [
      { name: 'QC Verification', to: { name: 'workshop.qc' }, roles: ['Super Admin', 'Manager'] },
      { name: 'Delivery Queue', to: { name: 'workshop.delivery' }, roles: ['Super Admin', 'Manager', 'Frontdesk', 'Cashier'] },
      { name: 'Warranty & Comeback', to: { name: 'workshop.warranty-comeback' }, roles: ['Super Admin', 'Manager'] },
      { name: 'Ready For Delivery', to: { name: 'workshop.delivery', query: { filter: 'ready' } }, roles: ['Super Admin', 'Manager', 'Frontdesk', 'Cashier'] },
      { name: 'Pending QC', to: { name: 'workshop.qc', query: { filter: 'pending' } }, roles: ['Super Admin', 'Manager'] },
      { name: 'Customer Pickup Queue', to: { name: 'workshop.delivery', query: { filter: 'pickup' } }, roles: ['Super Admin', 'Manager', 'Frontdesk', 'Cashier'] }
    ]
  },
  {
    name: 'Customers',
    id: 'customers',
    icon: '<svg class="w-full h-full" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z" /></svg>',
    items: [
      { name: 'Customers', to: { name: 'customers.index' }, roles: ['Super Admin', 'Manager', 'Frontdesk', 'Cashier'] },
      { name: 'Vehicles', to: { name: 'vehicles.index' }, roles: ['Super Admin', 'Manager', 'Frontdesk'] },
      { name: 'CRM', to: { name: 'appointments.index', query: { mode: 'crm' } }, roles: ['Super Admin', 'Manager', 'Frontdesk'] },
      { name: 'Service History', to: { name: 'vehicles.history.index' }, roles: ['Super Admin', 'Manager', 'Frontdesk'] }
    ]
  },
  {
    name: 'Inventory',
    id: 'inventory',
    icon: '<svg class="w-full h-full" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4" /></svg>',
    items: [
      { name: 'Parts', to: { name: 'inventory-list' }, roles: ['Super Admin', 'Manager'] },
      { 
        name: 'Purchases & Suppliers', 
        subItems: [
          { name: 'Purchases', to: { name: 'purchases-list' } },
          { name: 'Suppliers', to: { name: 'purchases-list', query: { tab: 'suppliers' } } }
        ],
        roles: ['Super Admin', 'Manager']
      },
      { name: 'Low Stock', to: { name: 'inventory-list', query: { filter: 'low_stock' } }, roles: ['Super Admin', 'Manager'] }
    ]
  },
  {
    name: 'Finance',
    id: 'finance',
    icon: '<svg class="w-full h-full" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>',
    items: [
      { name: 'POS Billing', to: { name: 'pos-home' }, roles: ['Super Admin', 'Manager', 'Cashier'] },
      { name: 'Invoices', to: { name: 'invoices.index' }, roles: ['Super Admin', 'Manager', 'Cashier'] },
      { name: 'Transactions', to: { name: 'accounts.index' }, roles: ['Super Admin', 'Manager', 'Cashier'] },
      { name: 'Expenses', to: { name: 'transactions.index' }, roles: ['Super Admin', 'Manager', 'Cashier'] },
      { name: 'Reports', to: { name: 'reports-home', query: { section: 'finance' } }, roles: ['Super Admin', 'Manager', 'Cashier'] }
    ]
  },
  {
    name: 'HR & Staff',
    id: 'hr_staff',
    icon: '<svg class="w-full h-full" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857" /></svg>',
    items: [
      { name: 'Employees', to: { name: 'staff.index' }, roles: ['Super Admin', 'Manager'] },
      { name: 'Attendance', to: { name: 'attendances.index' }, roles: ['Super Admin', 'Manager'] },
      { name: 'Payroll', to: { name: 'payrolls.index' }, roles: ['Super Admin', 'Manager'] }
    ]
  },
  {
    name: 'Reports & AI',
    id: 'reports_ai',
    icon: '<svg class="w-full h-full" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z" /></svg>',
    items: [
      { name: 'Analytics', to: { name: 'analytics.index' }, roles: ['Super Admin', 'Manager'] },
      { name: 'Business Reports', to: { name: 'reports-home' }, roles: ['Super Admin', 'Manager'] },
      { name: 'Operational Insights', to: { name: 'reports-home', query: { tab: 'operational' } }, roles: ['Super Admin', 'Manager'] },
      {
        name: 'Advanced Analytics',
        subItems: [
          { name: 'AI Monitoring', to: { name: 'ai.index' } },
          { name: 'Telemetry Monitoring', to: { name: 'ai.index', query: { tab: 'telemetry' } } },
          { name: 'Predictive Analytics', to: { name: 'ai.index', query: { tab: 'predictive' } } },
          { name: 'AI Governance', to: { name: 'dashboard.ai-inbox', query: { tab: 'governance' } } }
        ],
        roles: ['Super Admin', 'Manager']
      }
    ]
  },
  {
    name: 'System',
    id: 'system',
    icon: '<svg class="w-full h-full" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z" /></svg>',
    items: [
      { name: 'Settings', to: { name: 'settings.index' }, roles: ['Super Admin'] },
      { name: 'Roles & Permissions', to: { name: 'roles.index' }, roles: ['Super Admin'] },
      { name: 'Activity Logs', to: { name: 'activity-logs.index' }, roles: ['Super Admin'] },
      { name: 'SaaS Admin', to: { name: 'saas.index' }, roles: ['Super Admin'] }, // Restricted: Super Admin only
      {
        name: 'Advanced Tools',
        subItems: [
          { name: 'Escalation Center', to: { name: 'settings.incident-center' } },
          { name: 'AI Governance Inbox', to: { name: 'dashboard.ai-inbox', query: { tab: 'governance' } } },
          { name: 'Calibration Engine', to: { name: 'settings.production-operations' } },
          { name: 'Audit Evidence Repository', to: { name: 'settings.security-audit' } },
          { name: 'Queue Monitoring', to: { name: 'settings.production-operations', query: { tab: 'queue' } } }
        ],
        roles: ['Super Admin'] // Restricted: Super Admin only
      }
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

// Global Command List for CMD+K palette search (flattens subItems too!)
const globalCommandList = computed(() => {
  const list = [];
  menuGroups.forEach(group => {
    group.items.forEach(item => {
      if (isItemVisible(item)) {
        if (item.subItems && item.subItems.length > 0) {
          item.subItems.forEach(sub => {
            list.push({
              name: sub.name,
              category: `${group.name} > ${item.name}`,
              categoryIcon: group.icon,
              to: sub.to
            });
          });
        } else {
          list.push({
            name: item.name,
            category: group.name,
            categoryIcon: group.icon,
            to: item.to,
            action: item.action
          });
        }
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
  
  const userRole = authStore.user?.role;
  
  // Technician visibility rule overrides (strict gate: My Tasks, Active Jobs, Parts Usage, Diagnosis only)
  if (userRole === 'Technician') {
    const allowedSubmodules = ['Today\'s Tasks', 'Active Jobs', 'Parts Usage', 'Diagnosis', 'Technician Board'];
    return allowedSubmodules.includes(item.name);
  }
  
  // Cashier visibility rule overrides (blocked from AI, HR, SaaS)
  if (userRole === 'Cashier') {
    if (item.roles.includes('Super Admin') && !item.roles.includes('Cashier')) return false;
    const blockedKeywords = ['SaaS', 'AI', 'Employees', 'Attendance', 'Payroll', 'Shift', 'Roles', 'Advanced'];
    if (blockedKeywords.some(kw => item.name.includes(kw))) return false;
  }

  // Frontdesk visibility rule overrides (no AI, Payroll, SaaS, Advanced Settings)
  if (userRole === 'Frontdesk') {
    const blockedKeywords = ['SaaS', 'SaaS Admin', 'Activity Logs', 'Audit Logs', 'Advanced Tools', 'Advanced Analytics', 'Employees', 'Payroll'];
    if (blockedKeywords.some(kw => item.name.includes(kw))) return false;
  }

  // Manager: Limited System Configuration (blocked from SaaS Admin and Advanced Tools)
  if (userRole === 'Manager') {
    if (item.name === 'SaaS Admin' || item.name === 'Advanced Tools') return false;
  }

  return item.roles.some(role => authStore.hasRole(role));
};

const isGroupVisible = (group) => {
  return group.items.some(item => isItemVisible(item));
};

const isGroupActive = (group) => {
  return group.items.some(item => {
    if (item.to && item.to.name === route.name) return true;
    if (item.subItems) {
      return item.subItems.some(sub => sub.to && sub.to.name === route.name);
    }
    return false;
  });
};

const isGroupExpanded = (groupId) => {
  return !!expandedGroups.value[groupId];
};

const toggleGroup = (groupId) => {
  expandedGroups.value[groupId] = !expandedGroups.value[groupId];
};

// Favorites / Pinning handlers
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

// Recent pages tracker
const trackVisitedPage = (currentRoute) => {
  if (!currentRoute.name || currentRoute.name === 'login') return;
  let displayName = currentRoute.name;
  for (const group of menuGroups) {
    for (const item of group.items) {
      if (item.to && item.to.name === currentRoute.name) {
        displayName = item.name;
        break;
      }
      if (item.subItems) {
        const matchSub = item.subItems.find(sub => sub.to && sub.to.name === currentRoute.name);
        if (matchSub) {
          displayName = matchSub.name;
          break;
        }
      }
    }
  }

  recentPages.value = recentPages.value.filter(p => p.name !== displayName);
  recentPages.value.unshift({
    name: displayName,
    to: { name: currentRoute.name, query: currentRoute.query }
  });

  if (recentPages.value.length > 5) {
    recentPages.value.pop();
  }

  localStorage.setItem('recent_pages', JSON.stringify(recentPages.value));
};

// Breadcrumbs calculations
const breadcrumbs = computed(() => {
  const list = [{ name: 'Home', to: { name: 'dashboard-home' } }];
  if (route.name === 'dashboard-home') return list;

  for (const group of menuGroups) {
    for (const item of group.items) {
      if (item.to && item.to.name === route.name) {
        list.push({ name: group.name });
        list.push({ name: item.name, to: item.to });
        return list;
      }
      if (item.subItems) {
        const matchSub = item.subItems.find(sub => sub.to && sub.to.name === route.name);
        if (matchSub) {
          list.push({ name: group.name });
          list.push({ name: item.name });
          list.push({ name: matchSub.name, to: matchSub.to });
          return list;
        }
      }
    }
  }
  
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

// Command Search
const filteredCommandList = computed(() => {
  const query = commandQuery.value.trim().toLowerCase();
  if (!query) return globalCommandList.value.slice(0, 8);
  
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

// Global key down watcher
const handleGlobalKeydown = (event) => {
  if ((event.ctrlKey || event.metaKey) && event.key === 'k') {
    event.preventDefault();
    showCommandPalette.value = !showCommandPalette.value;
  }
};

// Route watchers
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

watch(() => route.name, (newRouteName) => {
  menuGroups.forEach(group => {
    const hasMatch = group.items.some(item => {
      if (item.to && item.to.name === newRouteName) return true;
      if (item.subItems) {
        return item.subItems.some(sub => sub.to && sub.to.name === newRouteName);
      }
      return false;
    });
    if (hasMatch) {
      expandedGroups.value[group.id] = true;
    }
  });
}, { immediate: true });

onMounted(() => {
  window.addEventListener('keydown', handleGlobalKeydown);
  
  const updateClock = () => {
    const d = new Date();
    currentFormattedTime.value = d.toLocaleDateString(undefined, { 
      weekday: 'short', 
      month: 'short', 
      day: 'numeric' 
    }) + ' ' + d.toLocaleTimeString(undefined, { 
      hour: '2-digit', 
      minute: '2-digit', 
      second: '2-digit',
      hour12: true 
    });
  };
  updateClock();
  const timer = setInterval(updateClock, 1000);
  
  pinnedItems.value = JSON.parse(localStorage.getItem('pinned_modules') || '[]');
  recentPages.value = JSON.parse(localStorage.getItem('recent_pages') || '[]');
  
  onUnmounted(() => {
    clearInterval(timer);
    window.removeEventListener('keydown', handleGlobalKeydown);
  });
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
