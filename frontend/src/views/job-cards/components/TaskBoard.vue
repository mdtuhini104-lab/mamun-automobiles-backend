<template>
  <div class="space-y-6">
    <!-- Tasks Section -->
    <div class="bg-white shadow-sm rounded-xl border border-slate-200 overflow-hidden">
      <div class="px-6 py-5 border-b border-slate-200 bg-slate-50 flex justify-between items-center">
        <div>
          <h3 class="text-base font-bold text-slate-900">Decomposed Repair Tasks</h3>
          <p class="text-xs text-slate-500 mt-0.5">Break down repairs into individual components</p>
        </div>
        <button
          v-if="canEdit && !showAddTaskForm"
          @click="showAddTaskForm = true"
          class="inline-flex items-center px-3 py-1.5 border border-transparent rounded-lg shadow-sm text-xs font-bold text-white bg-indigo-600 hover:bg-indigo-700 transition-colors"
        >
          Add Task
        </button>
      </div>

      <!-- Add Task Inline Form -->
      <div v-if="showAddTaskForm" class="p-6 bg-slate-50 border-b border-slate-200 animate-in slide-in-from-top-4 duration-200">
        <h4 class="text-sm font-bold text-slate-900 mb-3">Create Repair Task</h4>
        <form @submit.prevent="submitAddTask" class="space-y-4">
          <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
            <div>
              <label class="block text-xs font-semibold text-slate-500 uppercase tracking-wider mb-1">Task Name *</label>
              <input v-model="newTask.name" type="text" required placeholder="e.g. Oil Filter Replacement" class="w-full text-sm rounded-lg border-slate-300 p-2 border" />
            </div>
            <div>
              <label class="block text-xs font-semibold text-slate-500 uppercase tracking-wider mb-1">Estimated Minutes</label>
              <input v-model.number="newTask.estimated_minutes" type="number" min="0" placeholder="e.g. 30" class="w-full text-sm rounded-lg border-slate-300 p-2 border" />
            </div>
          </div>
          <div>
            <label class="block text-xs font-semibold text-slate-500 uppercase tracking-wider mb-1">Description</label>
            <textarea v-model="newTask.description" rows="2" placeholder="Provide extra findings or context..." class="w-full text-sm rounded-lg border-slate-300 p-2 border"></textarea>
          </div>
          <div class="flex justify-end space-x-3">
            <button
              type="button"
              @click="showAddTaskForm = false"
              class="px-3.5 py-1.5 border border-slate-300 rounded-lg text-xs font-bold text-slate-700 bg-white hover:bg-slate-50"
            >
              Cancel
            </button>
            <button
              type="submit"
              :disabled="tasksStore.saving"
              class="inline-flex items-center px-3.5 py-1.5 border border-transparent rounded-lg shadow-sm text-xs font-bold text-white bg-indigo-600 hover:bg-indigo-700 disabled:opacity-50"
            >
              Save Task
            </button>
          </div>
        </form>
      </div>

      <!-- Tasks List -->
      <div class="px-6 py-5">
        <div v-if="!jobCard.tasks || jobCard.tasks.length === 0" class="text-center py-8 text-slate-400 text-sm">
          No repair tasks added yet. Create one above to delegate work.
        </div>
        <div v-else class="space-y-3">
          <div
            v-for="task in jobCard.tasks"
            :key="task.id"
            class="p-4 border border-slate-200 rounded-xl bg-white hover:border-slate-300 transition-all flex flex-col sm:flex-row justify-between sm:items-center space-y-3 sm:space-y-0"
          >
            <div class="space-y-1 pr-4">
              <h4 class="text-sm font-bold text-slate-900 flex items-center space-x-2">
                <span>{{ task.name }}</span>
                <span class="text-xs font-normal text-slate-400">({{ task.estimated_minutes || 0 }} mins est)</span>
              </h4>
              <p class="text-xs text-slate-500">{{ task.description || 'No description provided.' }}</p>
              
              <!-- Assigned Tech status -->
              <div class="flex items-center space-x-2 mt-1">
                <span class="text-[10px] font-semibold text-slate-400 uppercase tracking-wider">Technician:</span>
                <span v-if="getTaskTechnician(task)" class="text-xs font-bold text-slate-800">
                  {{ getTaskTechnician(task).first_name }} {{ getTaskTechnician(task).last_name }}
                </span>
                <span v-else class="text-xs text-slate-400 italic">Unassigned</span>
              </div>
            </div>

            <!-- Task Actions -->
            <div class="flex items-center space-x-3 self-end sm:self-center">
              <!-- Unassigned allocation -->
              <button
                v-if="!getTaskTechnician(task) && canEdit"
                @click="assignTaskTech(task)"
                class="px-3.5 py-1.5 border border-slate-300 rounded-lg text-xs font-bold text-indigo-600 bg-white hover:bg-slate-50 shadow-sm"
              >
                Assign Staff
              </button>
              
              <!-- Active assignment completion -->
              <button
                v-if="getTaskTechnician(task) && isTaskActive(task) && canEdit"
                @click="completeTask(task)"
                class="px-3.5 py-1.5 border border-transparent rounded-lg shadow-sm text-xs font-bold text-white bg-emerald-600 hover:bg-emerald-700"
              >
                Complete Task
              </button>

              <!-- Completed badge -->
              <span
                v-if="getTaskTechnician(task) && !isTaskActive(task)"
                class="px-2.5 py-0.5 rounded-full text-xs font-semibold bg-emerald-50 text-emerald-700 ring-1 ring-emerald-600/20 uppercase tracking-wider"
              >
                Completed
              </span>
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- Labor tracking & Completing assignment board -->
    <div class="bg-white shadow-sm rounded-xl border border-slate-200 overflow-hidden">
      <div class="px-6 py-5 border-b border-slate-200 bg-slate-50">
        <h3 class="text-base font-bold text-slate-900">Workforce Labor Times & Progress</h3>
        <p class="text-xs text-slate-500 mt-0.5">Log labor hours on active technician allocations</p>
      </div>

      <div class="px-6 py-5">
        <div v-if="activeAssignments.length === 0" class="text-center py-6 text-slate-400 text-sm">
          No technician allocations have been registered yet.
        </div>
        <div v-else class="space-y-3">
          <div
            v-for="ast in activeAssignments"
            :key="ast.id"
            class="p-4 border border-slate-100 bg-slate-50/50 rounded-xl flex flex-col sm:flex-row justify-between sm:items-center space-y-3 sm:space-y-0"
          >
            <div class="space-y-1">
              <div class="flex items-center space-x-2">
                <span class="text-sm font-bold text-slate-800">
                  {{ ast.employee?.first_name }} {{ ast.employee?.last_name }}
                </span>
                <span class="inline-flex items-center rounded-md bg-indigo-50 px-2 py-0.5 text-[10px] font-semibold text-indigo-700 ring-1 ring-inset ring-indigo-700/10 uppercase tracking-wider">
                  {{ ast.assignment_type?.replace('_', ' ') }}
                </span>
              </div>
              <p class="text-xs text-slate-500 font-mono">
                Allocated at: {{ formatDateTime(ast.started_at) }}
              </p>
              <div v-if="ast.ended_at" class="text-xs text-slate-400 font-mono">
                Ended at: {{ formatDateTime(ast.ended_at) }}
              </div>
            </div>

            <!-- Labor Complete and Log hour control -->
            <div class="flex items-center space-x-4 self-end sm:self-center">
              <div class="text-left sm:text-right">
                <span class="block text-[10px] font-semibold text-slate-400 uppercase">Labor Hours</span>
                <span class="text-sm font-bold text-slate-800">
                  {{ ast.labor_hours !== null ? ast.labor_hours + ' hrs' : '-' }}
                </span>
              </div>

              <!-- Log labor trigger -->
              <button
                v-if="ast.status === 'active' && canEdit"
                @click="openLaborPrompt(ast)"
                class="px-3.5 py-1.5 border border-transparent rounded-lg shadow-sm text-xs font-bold text-white bg-indigo-600 hover:bg-indigo-700"
              >
                Log Labor & Complete
              </button>

              <!-- Completed badge -->
              <span
                v-else
                class="px-2.5 py-0.5 rounded-full text-xs font-semibold bg-slate-100 text-slate-600 uppercase tracking-wider"
              >
                Completed (Immutable)
              </span>
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- Modals -->
    <TaskAssignmentModal
      :is-open="isAssignModalOpen"
      :task-id="selectedTaskForAssign?.id || 0"
      :task-name="selectedTaskForAssign?.name || ''"
      :job-card-id="jobCard.id"
      @close="isAssignModalOpen = false"
      @assigned="$emit('updated')"
    />

    <!-- Labor hours log inline overlay -->
    <div v-if="showLaborModal" class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-900/60 backdrop-blur-sm">
      <div class="bg-white rounded-2xl border border-slate-200 shadow-2xl max-w-sm w-full p-6 animate-in fade-in zoom-in-95 duration-150 space-y-4">
        <div>
          <h3 class="text-base font-bold text-slate-950">Log Labor Hours</h3>
          <p class="text-xs text-slate-500 mt-0.5">
            Logging for: {{ selectedAssignmentForLabor?.employee?.first_name }}
          </p>
        </div>
        <div>
          <label class="block text-xs font-semibold text-slate-500 uppercase tracking-wider mb-1">Labor Hours *</label>
          <input v-model.number="laborHoursInput" type="number" step="0.1" min="0" required placeholder="e.g. 2.5" class="w-full text-sm rounded-lg border-slate-300 p-2 border" />
        </div>
        <div class="flex justify-end space-x-3">
          <button
            @click="showLaborModal = false"
            class="px-3.5 py-1.5 border border-slate-300 rounded-lg text-xs font-bold text-slate-700 bg-white hover:bg-slate-50"
          >
            Cancel
          </button>
          <button
            @click="submitLabor"
            :disabled="tasksStore.saving"
            class="px-4 py-2 border border-transparent rounded-lg shadow-sm text-xs font-bold text-white bg-indigo-600 hover:bg-indigo-700 disabled:opacity-50"
          >
            Log & Complete
          </button>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, reactive, computed } from 'vue';
import { useJobCardTasksStore } from '../../../stores/jobCardTasks';
import TaskAssignmentModal from './TaskAssignmentModal.vue';

const props = defineProps({
  jobCard: {
    type: Object,
    required: true
  },
  canEdit: {
    type: Boolean,
    default: true
  }
});

const emit = defineEmits(['updated']);
const tasksStore = useJobCardTasksStore();

const showAddTaskForm = ref(false);
const isAssignModalOpen = ref(false);
const selectedTaskForAssign = ref(null);

const showLaborModal = ref(false);
const selectedAssignmentForLabor = ref(null);
const laborHoursInput = ref(0);

const newTask = reactive({
  name: '',
  description: '',
  estimated_minutes: 30
});

const activeAssignments = computed(() => {
  if (!props.jobCard?.assignments) return [];
  // Return all assignments (both active and completed) ordered logically
  return [...props.jobCard.assignments].sort((a, b) => {
    if (a.status === b.status) return b.id - a.id;
    return a.status === 'active' ? -1 : 1;
  });
});

const getTaskTechnician = (task) => {
  if (!task.assignments || task.assignments.length === 0) return null;
  // Get active or first assignment employee
  const active = task.assignments.find(a => a.status === 'active');
  if (active) return active.employee;
  return task.assignments[0].employee;
};

const isTaskActive = (task) => {
  if (!task.assignments || task.assignments.length === 0) return false;
  return task.assignments.some(a => a.status === 'active');
};

const submitAddTask = async () => {
  try {
    await tasksStore.createTask(props.jobCard.id, { ...newTask });
    showAddTaskForm.value = false;
    newTask.name = '';
    newTask.description = '';
    newTask.estimated_minutes = 30;
    emit('updated');
  } catch (error) {
    // Toast handles validation errors
  }
};

const assignTaskTech = (task) => {
  selectedTaskForAssign.value = task;
  isAssignModalOpen.value = true;
};

const completeTask = async (task) => {
  const activeAssignment = task.assignments.find(a => a.status === 'active');
  if (!activeAssignment) return;
  try {
    await tasksStore.completeTaskAssignment(activeAssignment.id);
    emit('updated');
  } catch (error) {
    // Handled by global interceptor toast
  }
};

const openLaborPrompt = (assignment) => {
  selectedAssignmentForLabor.value = assignment;
  laborHoursInput.value = 1.0;
  showLaborModal.value = true;
};

const submitLabor = async () => {
  if (laborHoursInput.value < 0) return;
  try {
    await tasksStore.completeJobAssignment(selectedAssignmentForLabor.value.id, laborHoursInput.value);
    showLaborModal.value = false;
    emit('updated');
  } catch (error) {
    // Handled by global interceptor toast
  }
};

const formatDateTime = (dateString) => {
  if (!dateString) return '';
  const date = new Date(dateString);
  return date.toLocaleString(undefined, {
    month: 'short',
    day: 'numeric',
    hour: '2-digit',
    minute: '2-digit'
  });
};
</script>
