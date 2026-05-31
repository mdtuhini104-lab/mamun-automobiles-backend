<template>
  <div class="max-w-7xl mx-auto space-y-6 p-6 bg-slate-50 border border-gray-200 rounded-3xl shadow-sm text-slate-800 min-h-screen">
    <JobDetailsLayout :jobCard="workOrdersList[0]?.job_card || null" :activeStage="5">
      <!-- Header -->
      <div class="flex items-center justify-between border-b border-gray-200 pb-5">
        <div class="flex items-center space-x-4">
          <div>
            <h1 class="text-2xl font-black tracking-tight text-slate-900 uppercase">Work Order & Bay Management</h1>
            <p class="text-xs text-slate-500 mt-1">Real-time workshop command hub. Dispatch repairs, allocate service bays, delegate tech tasks, and manage bay occupancy flows.</p>
          </div>
        </div>
      </div>

      <!-- Tab Selection -->
      <div class="border-b border-gray-200 mb-6">
        <nav class="flex space-x-6" aria-label="Tabs">
          <button
            @click="activeTab = 'dispatch'"
            :class="[
              activeTab === 'dispatch'
                ? 'border-indigo-600 text-indigo-650 font-bold border-b-2'
                : 'border-transparent text-slate-500 hover:text-slate-800'
            ]"
            class="pb-4 px-1 font-black text-xs uppercase tracking-wider transition-all focus:outline-none"
          >
            Work Order Dispatch
          </button>
          <button
            @click="activeTab = 'bays'"
            :class="[
              activeTab === 'bays'
                ? 'border-indigo-600 text-indigo-650 font-bold border-b-2'
                : 'border-transparent text-slate-500 hover:text-slate-800'
            ]"
            class="pb-4 px-1 font-black text-xs uppercase tracking-wider transition-all focus:outline-none"
          >
            Bay Allocation Board
          </button>
        </nav>
      </div>

      <!-- TAB 1: WORK ORDER DISPATCH -->
      <div v-if="activeTab === 'dispatch'" class="space-y-6">
        <!-- Alert / Live Diagnostics Deck -->
        <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
          
          <!-- Stalled/Delayed Alerts -->
          <div class="bg-rose-50 border border-rose-100 p-4 rounded-2xl space-y-3 shadow-sm">
            <h3 class="text-[10px] font-black uppercase tracking-wider text-rose-700 flex items-center gap-2">
              <span class="w-2 h-2 bg-rose-500 rounded-full animate-ping"></span>
              Operational Warnings (Delayed Tasks)
            </h3>
            <div v-if="delayedTasks.length === 0" class="text-xs text-slate-400 italic">No tasks currently exceeding estimations.</div>
            <div v-else class="space-y-2 max-h-24 overflow-y-auto pr-1">
              <div v-for="t in delayedTasks" :key="t.id" class="text-[11px] bg-white p-2 rounded border border-rose-100 flex justify-between shadow-sm">
                <span>{{ t.task_name }} (JC #{{ t.jcId }})</span>
                <span class="font-bold text-rose-650 font-mono">+{{ t.delayedBy }}m</span>
              </div>
            </div>
          </div>

          <!-- Overloaded Bays Alert -->
          <div class="bg-amber-50 border border-amber-100 p-4 rounded-2xl space-y-3 shadow-sm">
            <h3 class="text-[10px] font-black uppercase tracking-wider text-amber-700 flex items-center gap-2">
              <span class="w-2 h-2 bg-amber-500 rounded-full"></span>
              Congestion Warnings (Overloaded Bays)
            </h3>
            <div v-if="overloadedBays.length === 0" class="text-xs text-slate-400 italic">All workshop bays running within nominal limits.</div>
            <div v-else class="space-y-2 max-h-24 overflow-y-auto pr-1">
              <div v-for="b in overloadedBays" :key="b.id" class="text-[11px] bg-white p-2 rounded border border-amber-100 flex justify-between shadow-sm">
                <span>{{ b.name }} (Capacity Limit)</span>
                <span class="font-bold text-amber-700 font-mono">{{ b.current_load }}/{{ b.max_vehicle_capacity }} vh</span>
              </div>
            </div>
          </div>

          <!-- Technician workload summary -->
          <div class="bg-indigo-50 border border-indigo-100 p-4 rounded-2xl space-y-3 shadow-sm">
            <h3 class="text-[10px] font-black uppercase tracking-wider text-indigo-705 flex items-center gap-2 text-indigo-700">
              <span class="w-2 h-2 bg-indigo-500 rounded-full"></span>
              Technician Allocation Load
            </h3>
            <div class="flex justify-between items-center text-xs text-slate-500">
              <span>Active Staff Checked-In:</span>
              <span class="font-bold text-slate-805 font-mono text-slate-800">{{ technicians.length }}</span>
            </div>
            <div class="text-[10px] text-slate-500 max-h-20 overflow-y-auto">
              <div v-for="t in technicians.slice(0, 3)" :key="t.id" class="flex justify-between py-0.5 border-b border-gray-150">
                <span>{{ t.name }}</span>
                <span class="font-bold text-indigo-650 font-mono">{{ t.active_jobs_count || 0 }} Active jobs</span>
              </div>
            </div>
          </div>

        </div>

        <!-- Active Work Orders Table / Deck -->
        <div class="grid grid-cols-1 gap-6">
          <div class="bg-white border border-gray-200 rounded-3xl p-5 shadow-sm">
            <h3 class="text-xs font-black uppercase tracking-widest text-slate-500 mb-4">Active Workshop Repairs</h3>
            
            <div v-if="loading" class="flex justify-center py-12">
              <div class="w-8 h-8 border-3 border-indigo-600 border-t-transparent rounded-full animate-spin"></div>
            </div>

            <div v-else-if="workOrdersList.length === 0" class="text-center py-20 text-slate-400 text-xs italic">
              No active work orders. Approve quotations on the approvals tab to dispatch repair orders.
            </div>

            <div v-else class="space-y-6">
              <div 
                v-for="wo in workOrdersList" 
                :key="wo.id"
                class="border border-gray-200 rounded-2xl p-5 bg-slate-50 space-y-4 hover:border-gray-300 transition"
              >
                <!-- Top bar -->
                <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-3 border-b border-gray-200 pb-3">
                  <div>
                    <div class="flex items-center gap-3">
                      <h4 class="text-sm font-black text-slate-900 font-mono">{{ wo.work_order_number }}</h4>
                      <span class="text-[10px] font-bold text-slate-500 font-mono">Job ID: #{{ wo.job_card_id }}</span>
                      <span class="px-2 py-0.5 rounded text-[8px] bg-indigo-50 text-indigo-750 border border-indigo-150 font-mono">
                        {{ wo.department_allocations?.join(', ') || 'Diagnostics' }}
                      </span>
                    </div>
                    <p class="text-[11px] text-slate-500 font-bold mt-1">Vehicle: {{ wo.job_card?.vehicle?.make }} {{ wo.job_card?.vehicle?.model }} ({{ wo.job_card?.vehicle?.license_plate || wo.job_card?.vehicle?.registration_no }})</p>
                  </div>

                  <!-- Status controllers -->
                  <div class="flex items-center gap-2">
                    <select 
                      v-model="wo.status" 
                      @change="updateWorkOrderStatus(wo)"
                      class="text-[10px] font-bold uppercase tracking-wider bg-white border border-gray-200 rounded-lg p-1.5 text-slate-800"
                    >
                      <option value="pending">Pending</option>
                      <option value="in_progress">In Progress</option>
                      <option value="paused">Paused / Shortage</option>
                      <option value="completed">Completed (Triggers QC)</option>
                      <option value="cancelled">Cancelled</option>
                    </select>
                    <router-link
                      :to="{ name: 'workshop.parts-consumption', params: { id: wo.id } }"
                      class="px-3 py-1.5 bg-white hover:bg-gray-50 border border-gray-200 text-slate-700 rounded-lg text-[10px] font-bold uppercase tracking-wider shadow-sm"
                    >
                      Log Mid-Repair Parts
                    </router-link>
                  </div>
                </div>

                <!-- Workforce & Decomposed Tasks Grid -->
                <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                  
                  <!-- Assigned Mechanics list -->
                  <div class="bg-white p-4 border border-gray-200 rounded-xl space-y-3 shadow-sm">
                    <div class="flex justify-between items-center text-[10px] font-black uppercase tracking-wider text-slate-500">
                      <span>Assigned Workforce</span>
                      <button @click="openDelegationDrawer(wo)" class="text-indigo-600 hover:text-indigo-700">Assign Staff</button>
                    </div>
                    <div class="space-y-2 text-xs">
                      <div v-if="wo.job_card?.assignments?.length === 0" class="text-slate-400 italic p-1 text-[11px]">No technicians delegated yet.</div>
                      <div 
                        v-else
                        v-for="asm in wo.job_card?.assignments" 
                        :key="asm.id"
                        class="flex justify-between bg-slate-50 border border-gray-200 p-2 rounded text-[11px] items-center text-slate-700 shadow-sm"
                      >
                        <div>
                          <span class="font-bold text-slate-850 block text-slate-800">{{ asm.employee?.first_name }} {{ asm.employee?.last_name }}</span>
                          <span class="text-[9px] text-slate-500 uppercase tracking-widest">{{ asm.assignment_type }}</span>
                        </div>
                        <button 
                          v-if="asm.status === 'active'"
                          @click="completeTechnicianAssignment(asm.id)" 
                          class="text-emerald-600 hover:text-emerald-700 text-[9px] uppercase font-black"
                        >
                          Complete
                        </button>
                        <span v-else class="text-slate-500 text-[9px] uppercase">Completed</span>
                      </div>
                    </div>
                  </div>

                  <!-- Tasks tracking -->
                  <div class="md:col-span-2 bg-white p-4 border border-gray-200 rounded-xl space-y-3 shadow-sm">
                    <div class="flex justify-between items-center text-[10px] font-black uppercase tracking-wider text-slate-500">
                      <span>Task checklist items</span>
                      <button @click="promptCreateTask(wo)" class="text-indigo-600 hover:text-indigo-700">+ Add Task</button>
                    </div>
                    <div class="space-y-2 text-xs max-h-40 overflow-y-auto pr-1">
                      <div v-if="wo.job_card?.tasks?.length === 0" class="text-slate-400 italic p-1 text-[11px]">No tasks decomposed. Assign lines.</div>
                      <div 
                        v-else
                        v-for="task in wo.job_card?.tasks" 
                        :key="task.id"
                        class="bg-slate-50 border border-gray-200 p-2.5 rounded-lg flex flex-col sm:flex-row justify-between sm:items-center gap-3 text-slate-705 text-slate-700 shadow-sm"
                      >
                        <div>
                          <span class="font-bold text-slate-900 block">{{ task.name }}</span>
                          <span class="text-[9px] text-slate-500 font-mono">Est: {{ task.estimated_minutes }} mins | Actual: {{ task.actual_minutes }} mins</span>
                        </div>

                        <div class="flex items-center gap-2">
                          <!-- Assign button for task -->
                          <button 
                            v-if="task.status === 'pending'"
                            @click="promptAssignTask(task)"
                            class="px-2 py-1 bg-white hover:bg-gray-50 text-slate-700 border border-gray-200 rounded text-[9px] uppercase font-bold shadow-sm"
                          >
                            Assign Task
                          </button>
                          <span :class="getTaskStatusClass(task.status)" class="px-2 py-0.5 rounded text-[8px] font-black uppercase tracking-widest">
                            {{ task.status }}
                          </span>
                        </div>
                      </div>
                    </div>
                  </div>

                </div>
              </div>
            </div>
          </div>
        </div>
      </div>

      <!-- TAB 2: BAY ALLOCATION BOARD -->
      <div v-else-if="activeTab === 'bays'" class="space-y-6">
        <!-- Stats Grid Panel -->
        <div class="grid grid-cols-2 md:grid-cols-5 gap-4">
          <div class="bg-white border border-gray-200 p-4.5 rounded-2xl shadow-sm">
            <span class="text-[9px] font-black uppercase text-slate-500 block tracking-widest font-mono">Busy Bays</span>
            <div class="text-2xl font-black text-indigo-650 font-mono mt-1">{{ busyBaysCount }}</div>
          </div>
          <div class="bg-white border border-gray-200 p-4.5 rounded-2xl shadow-sm">
            <span class="text-[9px] font-black uppercase text-slate-500 block tracking-widest font-mono">Idle Bays</span>
            <div class="text-2xl font-black text-slate-700 font-mono mt-1">{{ idleBaysCount }}</div>
          </div>
          <div class="bg-white border border-gray-200 p-4.5 rounded-2xl shadow-sm">
            <span class="text-[9px] font-black uppercase text-slate-500 block tracking-widest font-mono">Waiting Vehicles</span>
            <div class="text-2xl font-black text-amber-600 font-mono mt-1">{{ waitingVehiclesCount }}</div>
          </div>
          <div class="bg-white border border-gray-200 p-4.5 rounded-2xl shadow-sm">
            <span class="text-[9px] font-black uppercase text-slate-500 block tracking-widest font-mono">QC Occupied Bays</span>
            <div class="text-2xl font-black text-purple-650 font-mono mt-1">{{ qcBaysCount }}</div>
          </div>
          <div class="bg-white border border-gray-200 p-4.5 rounded-2xl shadow-sm">
            <span class="text-[9px] font-black uppercase text-slate-500 block tracking-widest font-mono">Delayed Vehicles</span>
            <div class="text-2xl font-black text-rose-600 font-mono mt-1">{{ delayedVehiclesCount }}</div>
          </div>
        </div>

        <!-- Layout Grid -->
        <div class="grid grid-cols-1 lg:grid-cols-4 gap-6">
          
          <!-- Left side: Unallocated Job Cards Queue -->
          <div class="lg:col-span-1 bg-white border border-gray-200 rounded-2xl p-4 space-y-4 flex flex-col justify-between h-[600px] shadow-sm">
            <div>
              <h3 class="text-xs font-black uppercase tracking-wider text-slate-550 mb-3 flex items-center justify-between">
                <span>Awaiting Bay Allocation</span>
                <span class="bg-indigo-50 text-indigo-700 border border-indigo-100 text-[10px] px-2 py-0.5 rounded-full border">
                  {{ unallocatedJobs.length }}
                </span>
              </h3>

              <div v-if="loading" class="flex justify-center py-12">
                <div class="w-6 h-6 border-2 border-indigo-600 border-t-transparent rounded-full animate-spin"></div>
              </div>

              <div v-else-if="unallocatedJobs.length === 0" class="text-center py-16 text-slate-400 text-xs italic">
                All active vehicles are currently allocated to bays.
              </div>

              <div v-else class="space-y-3 overflow-y-auto max-h-[480px] pr-1">
                <div 
                  v-for="job in unallocatedJobs" 
                  :key="job.id"
                  class="bg-slate-50 border border-gray-200 rounded-xl p-3.5 space-y-3 hover:border-gray-300 transition flex flex-col justify-between shadow-sm"
                >
                  <div>
                    <span class="text-[9px] font-mono text-slate-500">#JC-{{ String(job.id).padStart(5, '0') }}</span>
                    <h4 class="font-bold text-slate-800 text-xs mt-1">{{ job.vehicle?.make }} {{ job.vehicle?.model }}</h4>
                    <p class="text-[9px] text-slate-500 font-mono mt-0.5">Plate: {{ job.vehicle?.license_plate || job.vehicle?.registration_no }}</p>
                    <p class="text-[10px] text-rose-650 italic mt-2">"{{ job.complaint }}"</p>
                  </div>

                  <!-- Bay select dropdown -->
                  <div class="pt-2 border-t border-gray-250 flex gap-2">
                    <select 
                      v-model="job.tempBayId"
                      class="flex-1 bg-white border border-gray-200 rounded p-1 text-[10px] text-slate-800 focus:bg-white"
                    >
                      <option value="" disabled>Select Bay...</option>
                      <option 
                        v-for="b in baysList" 
                        :key="b.id" 
                        :value="b.id"
                        :disabled="b.current_load >= b.max_vehicle_capacity"
                      >
                        {{ b.name }} ({{ b.current_load }}/{{ b.max_vehicle_capacity }})
                      </option>
                    </select>
                    <button 
                      @click="allocateBay(job.id, job.tempBayId)"
                      :disabled="!job.tempBayId"
                      class="px-2.5 py-1 bg-indigo-600 hover:bg-indigo-700 disabled:opacity-50 text-white rounded text-[10px] font-black uppercase tracking-wider"
                    >
                      Set
                    </button>
                  </div>
                </div>
              </div>
            </div>
          </div>

          <!-- Right side: Visual Bay Grid -->
          <div class="lg:col-span-3 bg-white border border-gray-200 rounded-3xl p-5 shadow-sm min-h-[500px]">
            <h3 class="text-xs font-black uppercase tracking-widest text-slate-500 mb-4">Workshop Bay Status Grid</h3>

            <div v-if="loading" class="flex justify-center py-20">
              <div class="w-10 h-10 border-4 border-indigo-600 border-t-transparent rounded-full animate-spin"></div>
            </div>

            <div v-else class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-3 gap-6">
              <div 
                v-for="bay in baysList" 
                :key="bay.id"
                :class="getBayOccupancyClass(bay)"
                class="border rounded-2xl p-5 flex flex-col justify-between gap-5 transition hover:shadow-md shadow-sm"
              >
                <div>
                  <!-- Title -->
                  <div class="flex justify-between items-start">
                    <div>
                      <h4 class="text-sm font-black text-slate-800">{{ bay.name }}</h4>
                      <span class="text-[9px] text-slate-500 font-mono">{{ bay.code }}</span>
                    </div>
                    <span :class="getUtilizationBadgeClass(bay)" class="px-2 py-0.5 rounded text-[9px] font-black uppercase font-mono tracking-wider">
                      {{ bay.current_load }} / {{ bay.max_vehicle_capacity }} vehicles
                    </span>
                  </div>

                  <!-- Utilization Progress Bar -->
                  <div class="w-full bg-gray-200 rounded-full h-1.5 mt-3">
                    <div 
                      :class="getProgressBarClass(bay)"
                      class="h-1.5 rounded-full transition-all duration-300"
                      :style="{ width: getUtilizationPercentage(bay) + '%' }"
                    ></div>
                  </div>

                  <!-- Vehicles list inside this bay -->
                  <div class="mt-4 space-y-2 border-t border-gray-200 pt-4">
                    <span class="text-[9px] font-black uppercase tracking-wider text-slate-500 block">Occupying Vehicles</span>
                    <div v-if="getVehiclesInBay(bay.id).length === 0" class="text-[10px] text-slate-400 italic py-1">Bay is currently vacant.</div>
                    <div 
                      v-else
                      v-for="v in getVehiclesInBay(bay.id)" 
                      :key="v.id"
                      class="bg-white border border-gray-200 p-2.5 rounded-lg text-xs flex justify-between items-center text-slate-700 shadow-sm"
                    >
                      <div>
                        <span class="font-bold text-slate-900 block">{{ v.vehicle?.make }} {{ v.vehicle?.model }}</span>
                        <span class="text-[9px] text-slate-500 font-mono">Plate: {{ v.vehicle?.license_plate || v.vehicle?.registration_no }}</span>
                      </div>
                      <button 
                        @click="releaseBay(v.id)"
                        class="text-rose-600 hover:text-rose-700 text-[9px] uppercase font-black tracking-wide"
                      >
                        Release
                      </button>
                    </div>
                  </div>
                </div>
              </div>
            </div>
          </div>

        </div>
      </div>

    </JobDetailsLayout>

    <!-- Assign Workforce Delegation Drawer / Modal -->
    <div v-if="showDelegationDrawer" class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-950/60 backdrop-blur-sm">
      <div class="bg-white border border-gray-200 rounded-3xl shadow-2xl max-w-md w-full p-6 space-y-4 text-slate-800 animate-fade-in">
        <h3 class="text-sm font-black uppercase tracking-widest text-indigo-650">Assign Technician / helpers</h3>
        
        <form @submit.prevent="submitDelegation" class="space-y-4">
          <div>
            <label class="block text-xs text-slate-500 mb-1">Select Lead Technician *</label>
            <select v-model="delegationForm.lead_technician_id" required class="w-full text-xs bg-slate-50 border border-gray-200 rounded-lg p-2.5 text-slate-800 focus:bg-white">
              <option value="">Select Technician...</option>
              <option v-for="t in activeTechnicians" :key="t.id" :value="t.id">
                {{ t.name }} (Active Jobs: {{ t.active_jobs_count }})
              </option>
            </select>
          </div>

          <div>
            <label class="block text-xs text-slate-500 mb-1">Select Assistant Technicians</label>
            <div class="border border-gray-200 rounded-xl p-3 bg-slate-50 text-[10px] space-y-2 max-h-32 overflow-y-auto">
              <label v-for="t in activeTechnicians" :key="t.id" class="flex items-center gap-2 cursor-pointer text-slate-700">
                <input 
                  type="checkbox" 
                  :value="t.id" 
                  v-model="delegationForm.assistant_technician_ids" 
                  class="rounded text-indigo-600 bg-white border-gray-300"
                />
                <span>{{ t.name }}</span>
              </label>
            </div>
          </div>

          <div class="flex justify-end gap-3 pt-2 border-t border-gray-200">
            <button type="button" @click="showDelegationDrawer = false" class="px-4 py-2 border border-gray-200 rounded-xl text-xs font-bold text-slate-600 bg-white hover:bg-gray-50 shadow-sm">Cancel</button>
            <button type="submit" class="px-5 py-2 bg-indigo-600 hover:bg-indigo-700 text-white rounded-xl text-xs font-black uppercase tracking-wider">Assign</button>
          </div>
        </form>
      </div>
    </div>

    <!-- Create Custom Task Modal -->
    <div v-if="showTaskModal" class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-950/60 backdrop-blur-sm">
      <div class="bg-white border border-gray-200 rounded-3xl shadow-2xl max-w-md w-full p-6 space-y-4 text-slate-800">
        <h3 class="text-sm font-black uppercase tracking-widest text-indigo-650">Add Custom Repair Task</h3>
        
        <form @submit.prevent="submitCreateTask" class="space-y-4">
          <div>
            <label class="block text-xs text-slate-500 mb-1">Task Name *</label>
            <input 
              v-model="taskForm.name" 
              type="text" 
              required 
              placeholder="e.g. Clean brake sensors"
              class="w-full text-xs bg-slate-50 border border-gray-200 rounded-lg p-2.5 text-slate-800 focus:bg-white"
            />
          </div>
          <div>
            <label class="block text-xs text-slate-500 mb-1">Estimated minutes</label>
            <input 
              v-model.number="taskForm.estimated_minutes" 
              type="number" 
              class="w-full text-xs bg-slate-50 border border-gray-200 rounded-lg p-2.5 text-slate-800 font-mono focus:bg-white"
            />
          </div>
          <div class="flex justify-end gap-3 pt-2 border-t border-gray-200">
            <button type="button" @click="showTaskModal = false" class="px-4 py-2 border border-gray-200 rounded-xl text-xs font-bold text-slate-600 bg-white hover:bg-gray-50 shadow-sm">Cancel</button>
            <button type="submit" class="px-5 py-2 bg-indigo-600 hover:bg-indigo-700 text-white rounded-xl text-xs font-black uppercase tracking-wider">Create</button>
          </div>
        </form>
      </div>
    </div>

    <!-- Assign Specific Task to Employee Modal -->
    <div v-if="showAssignTaskModal" class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-950/60 backdrop-blur-sm">
      <div class="bg-white border border-gray-200 rounded-3xl shadow-2xl max-w-md w-full p-6 space-y-4 text-slate-800">
        <h3 class="text-sm font-black uppercase tracking-widest text-indigo-650">Assign Technician to Task</h3>
        <p class="text-xs text-slate-500">Select staff member for task: "{{ selectedTask?.name }}"</p>
        
        <form @submit.prevent="submitAssignTask" class="space-y-4">
          <div>
            <label class="block text-xs text-slate-500 mb-1">Select staff *</label>
            <select v-model="taskAssignForm.employee_id" required class="w-full text-xs bg-slate-50 border border-gray-200 rounded-lg p-2.5 text-slate-800 focus:bg-white">
              <option value="">Select Staff...</option>
              <option v-for="t in activeTechnicians" :key="t.id" :value="t.id">
                {{ t.name }}
              </option>
            </select>
          </div>
          <div class="flex justify-end gap-3 pt-2 border-t border-gray-200">
            <button type="button" @click="showAssignTaskModal = false" class="px-4 py-2 border border-gray-200 rounded-xl text-xs font-bold text-slate-600 bg-white hover:bg-gray-50 shadow-sm">Cancel</button>
            <button type="submit" class="px-5 py-2 bg-indigo-600 hover:bg-indigo-700 text-white rounded-xl text-xs font-black uppercase tracking-wider">Assign</button>
          </div>
        </form>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, reactive, computed, onMounted } from 'vue';
import api from '../../services/api';
import { useToastStore } from '../../stores/toast';
import JobDetailsLayout from '../../components/workshop/JobDetailsLayout.vue';

const toast = useToastStore();
const loading = ref(true);
const activeTab = ref('dispatch');

const workOrdersList = ref([]);
const technicians = ref([]);
const activeTechnicians = ref([]);

// Bays specific states
const baysList = ref([]);
const activeJobsList = ref([]);

// Alert analytics states
const delayedTasks = ref([]);
const overloadedBays = ref([]);

// Delegation Form
const showDelegationDrawer = ref(false);
const selectedWo = ref(null);
const delegationForm = reactive({
  lead_technician_id: '',
  assistant_technician_ids: [],
});

// Custom Task Form
const showTaskModal = ref(false);
const taskForm = reactive({
  name: '',
  estimated_minutes: 30,
});

// Assign Task Modal
const showAssignTaskModal = ref(false);
const selectedTask = ref(null);
const taskAssignForm = reactive({
  employee_id: '',
});

// Bays Computed properties
const busyBaysCount = computed(() => {
  return baysList.value.filter(b => b.current_load > 0).length;
});

const idleBaysCount = computed(() => {
  return baysList.value.filter(b => b.current_load === 0).length;
});

const waitingVehiclesCount = computed(() => {
  return unallocatedJobs.value.length;
});

const qcBaysCount = computed(() => {
  return baysList.value.filter(b => {
    const jobs = getVehiclesInBay(b.id);
    return jobs.some(j => j.service_status === 'qc' || j.service_status === 'completed');
  }).length;
});

const delayedVehiclesCount = computed(() => {
  return activeJobsList.value.filter(j => j.priority_level === 'critical' || j.priority_level === 'urgent' || j.priority_level === 'high').length;
});

const unallocatedJobs = computed(() => {
  return activeJobsList.value.filter(j => !j.workshop_bay_id);
});

const getVehiclesInBay = (bayId) => {
  return activeJobsList.value.filter(j => j.workshop_bay_id === bayId);
};

const fetchData = async () => {
  loading.value = true;
  try {
    const [woRes, techRes, bayRes, jcRes] = await Promise.all([
      api.get('/work-orders', { params: { per_page: 150 } }),
      api.get('/users', { params: { role: 'Technician' } }),
      api.get('/workshop-bays'),
      api.get('/job-cards', { params: { per_page: 200 } })
    ]);

    workOrdersList.value = woRes.data?.data || woRes.data || [];
    technicians.value = techRes.data?.data || techRes.data || [];

    // Filter overloaded bays
    baysList.value = bayRes.data?.data || bayRes.data || [];
    overloadedBays.value = baysList.value.filter(b => b.current_load >= b.max_vehicle_capacity);

    // Job cards / active jobs
    const rawJobs = jcRes.data?.data || jcRes.data || [];
    activeJobsList.value = rawJobs.filter(
      jc => jc.service_status === 'pending' || jc.service_status === 'in_progress'
    ).map(jc => ({
      ...jc,
      tempBayId: ''
    }));

    // Fetch employee workload list explicitly to get active workload
    const emplRes = await api.get('/workforce/employees'); // Fetch list of employees
    const emplList = emplRes.data?.data || [];
    activeTechnicians.value = emplList.filter(e => e.department?.name?.toLowerCase().includes('technic') || e.department?.name?.toLowerCase().includes('worksh') || e.designation?.name?.toLowerCase().includes('mechan'));

    // Filter delayed active tasks
    delayedTasks.value = [];
    workOrdersList.value.forEach(wo => {
      if (wo.job_card && Array.isArray(wo.job_card.tasks)) {
        wo.job_card.tasks.forEach(t => {
          if (t.status === 'in_progress' && t.actual_minutes > t.estimated_minutes) {
            delayedTasks.value.push({
              id: t.id,
              task_name: t.name,
              jcId: wo.job_card_id,
              delayedBy: t.actual_minutes - t.estimated_minutes
            });
          }
        });
      }
    });

  } catch (err) {
    console.error('Failed to sync Command center data', err);
    toast.error('Command center synchronization failed.');
  } finally {
    loading.value = false;
  }
};

const updateWorkOrderStatus = async (wo) => {
  try {
    await api.put(`/work-orders/${wo.id}/status`, { status: wo.status });
    toast.success(`Work Order status updated to ${wo.status}.`);
    fetchData();
  } catch (err) {
    toast.error('Failed to update Work Order status.');
  }
};

const openDelegationDrawer = (wo) => {
  selectedWo.value = wo;
  delegationForm.lead_technician_id = '';
  delegationForm.assistant_technician_ids = [];
  showDelegationDrawer.value = true;
};

const submitDelegation = async () => {
  try {
    await api.post(`/job-cards/${selectedWo.value.job_card_id}/assign`, {
      lead_technician_id: delegationForm.lead_technician_id,
      assistant_technician_ids: delegationForm.assistant_technician_ids
    });
    toast.success('Technician workforce delegated successfully.');
    showDelegationDrawer.value = false;
    fetchData();
  } catch (err) {
    toast.error(err.response?.data?.message || 'Delegation failed.');
  }
};

const completeTechnicianAssignment = async (assignmentId) => {
  try {
    await api.post(`/job-cards/assignments/${assignmentId}/complete`, { labor_hours: 2.5 });
    toast.success('Technician assignment marked completed.');
    fetchData();
  } catch (err) {
    toast.error('Failed to complete technician assignment.');
  }
};

// Decomposed Custom Task creation
const promptCreateTask = (wo) => {
  selectedWo.value = wo;
  taskForm.name = '';
  taskForm.estimated_minutes = 30;
  showTaskModal.value = true;
};

const submitCreateTask = async () => {
  try {
    await api.post(`/job-cards/${selectedWo.value.job_card_id}/tasks`, { ...taskForm });
    toast.success('Custom task registered on work order.');
    showTaskModal.value = false;
    fetchData();
  } catch (err) {
    toast.error('Failed to register custom task.');
  }
};

// Specific task assignment
const promptAssignTask = (task) => {
  selectedTask.value = task;
  taskAssignForm.employee_id = '';
  showAssignTaskModal.value = true;
};

const submitAssignTask = async () => {
  try {
    await api.post(`/job-cards/${selectedTask.value.job_card_id}/tasks/${selectedTask.value.id}/assign`, {
      employee_id: taskAssignForm.employee_id
    });
    toast.success('Staff allocated to task successfully.');
    showAssignTaskModal.value = false;
    fetchData();
  } catch (err) {
    toast.error(err.response?.data?.message || 'Task assignment failed.');
  }
};

const getTaskStatusClass = (status) => {
  const map = {
    'pending': 'bg-slate-100 text-slate-600 border border-gray-200',
    'in_progress': 'bg-blue-50 text-blue-700 border border-blue-200',
    'completed': 'bg-emerald-50 text-emerald-700 border border-emerald-200',
  };
  return map[status] || 'bg-slate-100 text-slate-600';
};

// Bays Operations methods
const allocateBay = async (jobCardId, bayId) => {
  try {
    await api.post(`/job-cards/${jobCardId}/assign`, { workshop_bay_id: bayId });
    toast.success('Vehicle allocated to bay successfully.');
    fetchData();
  } catch (err) {
    toast.error(err.response?.data?.message || 'Allocation failed.');
  }
};

const releaseBay = async (jobCardId) => {
  try {
    await api.post(`/job-cards/${jobCardId}/assign`, { workshop_bay_id: null });
    toast.success('Vehicle released from bay.');
    fetchData();
  } catch (err) {
    toast.error('Release failed.');
  }
};

// Styling calculations
const getUtilizationPercentage = (bay) => {
  if (!bay || !bay.max_vehicle_capacity) return 0;
  return Math.min(100, Math.round((bay.current_load / bay.max_vehicle_capacity) * 100));
};

const getBayOccupancyClass = (bay) => {
  const pct = getUtilizationPercentage(bay);
  if (pct >= 100) return 'border-red-200 bg-red-50/50';
  if (pct >= 75) return 'border-amber-250 bg-amber-50/50';
  return 'border-gray-200 bg-slate-50';
};

const getUtilizationBadgeClass = (bay) => {
  const pct = getUtilizationPercentage(bay);
  if (pct >= 100) return 'bg-rose-50 text-rose-700 border border-rose-200';
  if (pct >= 75) return 'bg-amber-50 text-amber-700 border border-amber-200';
  return 'bg-emerald-50 text-emerald-700 border border-emerald-200';
};

const getProgressBarClass = (bay) => {
  const pct = getUtilizationPercentage(bay);
  if (pct >= 100) return 'bg-rose-500';
  if (pct >= 75) return 'bg-amber-500';
  return 'bg-indigo-500';
};

onMounted(() => {
  fetchData();
});
</script>
