<template>
  <div class="max-w-7xl mx-auto space-y-6 p-6 bg-slate-900 border border-slate-800 rounded-3xl shadow-2xl text-slate-100 min-h-screen">
    
    <!-- Fallback Stage Selector -->
    <WorkspaceJobSelector 
      v-if="!route.params.id" 
      stage="quotation" 
      title="Select Vehicle for Quotation Builder" 
      @selected="handleJobSelected"
    />

    <JobDetailsLayout v-else :jobCard="jobCard" :activeStage="3">
      <!-- Header -->
      <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4 border-b border-slate-850 pb-5">
        <div class="flex items-center space-x-4">
          <div v-if="jobCard">
            <h1 class="text-2xl font-black tracking-tight text-white uppercase">Quotation Workspace</h1>
            <p class="text-xs text-slate-400 mt-1">JC #{{ String(jobCard.id).padStart(5, '0') }}</p>
          </div>
        </div>
        <div v-if="jobCard && jobCard.diagnosis" class="bg-indigo-950/40 border border-indigo-900/60 p-3 rounded-xl max-w-md text-[11px]">
          <span class="text-indigo-400 font-extrabold uppercase block tracking-wider mb-0.5">Diagnosed Findings:</span>
          <span class="text-slate-300 italic">"{{ jobCard.diagnosis }}"</span>
        </div>
      </div>

    <div v-if="loadingJob" class="animate-pulse space-y-6">
      <div class="h-8 bg-slate-800 rounded w-1/4"></div>
      <div class="h-96 bg-slate-800 rounded"></div>
    </div>

    <div v-else-if="jobCard">
      <!-- Embedded Quotation engine core logic -->
      <div v-if="!quotation" class="bg-slate-950/40 border border-slate-850 rounded-2xl p-8 text-center max-w-xl mx-auto space-y-4 my-12">
        <div class="inline-flex p-3 bg-indigo-500/10 rounded-full text-indigo-450 border border-indigo-500/20">
          <svg class="w-8 h-8" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
          </svg>
        </div>
        <h3 class="text-lg font-bold text-white">Generate Quotation Draft</h3>
        <p class="text-xs text-slate-400">No quotation has been drafted for this Job Card yet. Initiate a versioned draft applying the corporate contract pricing engine.</p>
        <button
          @click="initiateQuotationDraft"
          :disabled="creatingDraft"
          class="px-6 py-2.5 bg-indigo-600 hover:bg-indigo-700 text-white rounded-xl text-xs font-black uppercase tracking-wider transition disabled:opacity-50"
        >
          {{ creatingDraft ? 'Generating Draft...' : 'Generate Corporate Quotation Draft' }}
        </button>
      </div>

      <!-- Quotation edit view -->
      <div v-else class="grid grid-cols-1 xl:grid-cols-3 gap-6">
        
        <!-- Left details panel -->
        <div class="xl:col-span-2 space-y-6">
          <div class="bg-slate-950/20 border border-slate-850 rounded-2xl overflow-hidden shadow-xl">
            <!-- Details Header -->
            <div class="px-6 py-5 border-b border-slate-850 bg-slate-950/40 flex justify-between items-center flex-wrap gap-4">
              <div>
                <div class="flex items-center space-x-3">
                  <h3 class="text-base font-black text-white font-mono">{{ quotation.quotation_number }}</h3>
                  <span class="px-2.5 py-0.5 rounded-md text-[10px] font-black bg-indigo-500/10 text-indigo-400 border border-indigo-500/20 uppercase">
                    Version {{ quotation.version }}
                  </span>
                  <span :class="getQuotationStatusClass(quotation.status)" class="px-2.5 py-0.5 rounded-md text-[10px] font-black uppercase tracking-wider">
                    {{ quotation.status?.replace('_', ' ') }}
                  </span>
                </div>
              </div>
              <div class="flex space-x-2">
                <router-link
                  v-if="quotation.status === 'draft' || quotation.status === 'revised'"
                  :to="{ name: 'workshop.approvals' }"
                  class="px-3.5 py-2 bg-green-600 hover:bg-green-700 text-white rounded-lg font-black text-[10px] uppercase tracking-wider"
                >
                  Go to Approvals Screen
                </router-link>
              </div>
            </div>

            <!-- Table elements -->
            <div class="p-6 space-y-6">
              
              <!-- Product lines -->
              <div>
                <h4 class="text-xs font-black text-indigo-400 uppercase tracking-widest mb-3">Product Line Items</h4>
                <div class="overflow-x-auto">
                  <table class="min-w-full text-left text-xs">
                    <thead>
                      <tr class="border-b border-slate-800 text-slate-500 font-bold uppercase text-[9px] tracking-wider">
                        <th class="pb-3 pl-2">Product Name</th>
                        <th class="pb-3">Ownership</th>
                        <th class="pb-3 text-right">Qty</th>
                        <th class="pb-3 text-right">Contract Rate</th>
                        <th class="pb-3 text-right">Total Billed</th>
                        <th class="pb-3 text-center">Action</th>
                      </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-850">
                      <tr v-for="item in productItems" :key="item.id" class="text-slate-350">
                        <td class="py-3 pl-2 font-bold text-white">{{ item.part?.name || 'Part' }}</td>
                        <td class="py-3">
                          <span :class="item.source_type === 'workshop_supplied' ? 'bg-indigo-500/10 text-indigo-400' : 'bg-amber-500/10 text-amber-400'" class="px-2 py-0.5 rounded text-[8px] font-black uppercase tracking-wider">
                            {{ item.source_type === 'workshop_supplied' ? 'Workshop Supplied' : 'Customer Supplied (৳0.00)' }}
                          </span>
                        </td>
                        <td class="py-3 text-right font-mono">{{ item.quantity }}</td>
                        <td class="py-3 text-right font-mono">{{ formatCurrency(item.source_type === 'workshop_supplied' ? item.unit_price : 0) }}</td>
                        <td class="py-3 text-right font-mono font-black text-slate-100">{{ formatCurrency(item.source_type === 'workshop_supplied' ? item.quantity * item.unit_price : 0) }}</td>
                        <td class="py-3 text-center">
                          <button
                            v-if="quotation.status === 'draft' || quotation.status === 'revised'"
                            @click="promptRemoveItem(item)"
                            class="text-rose-400 hover:text-rose-350 text-[10px] font-black uppercase tracking-wide"
                          >
                            Remove
                          </button>
                          <span v-else class="text-slate-600 text-[10px] font-bold">Locked</span>
                        </td>
                      </tr>
                      <tr v-if="productItems.length === 0">
                        <td colspan="6" class="py-4 text-center text-slate-500 text-xs italic">No products added.</td>
                      </tr>
                    </tbody>
                  </table>
                </div>
              </div>

              <!-- Service lines -->
              <div>
                <h4 class="text-xs font-black text-indigo-400 uppercase tracking-widest mb-3">Service Line Items</h4>
                <div class="overflow-x-auto">
                  <table class="min-w-full text-left text-xs">
                    <thead>
                      <tr class="border-b border-slate-800 text-slate-500 font-bold uppercase text-[9px] tracking-wider">
                        <th class="pb-3 pl-2">Service Description</th>
                        <th class="pb-3 text-right">Est. Hours</th>
                        <th class="pb-3 text-right">Labor Charge</th>
                        <th class="pb-3 text-center">Action</th>
                      </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-850">
                      <tr v-for="item in serviceItems" :key="item.id" class="text-slate-350">
                        <td class="py-3 pl-2 font-bold text-white">{{ item.service_name }}</td>
                        <td class="py-3 text-right font-mono">{{ item.estimated_hours }} hrs</td>
                        <td class="py-3 text-right font-mono font-black text-slate-100">{{ formatCurrency(item.labor_cost) }}</td>
                        <td class="py-3 text-center">
                          <button
                            v-if="quotation.status === 'draft' || quotation.status === 'revised'"
                            @click="promptRemoveItem(item)"
                            class="text-rose-400 hover:text-rose-350 text-[10px] font-black uppercase tracking-wide"
                          >
                            Remove
                          </button>
                          <span v-else class="text-slate-600 text-[10px] font-bold">Locked</span>
                        </td>
                      </tr>
                      <tr v-if="serviceItems.length === 0">
                        <td colspan="4" class="py-4 text-center text-slate-500 text-xs italic">No services added.</td>
                      </tr>
                    </tbody>
                  </table>
                </div>
              </div>

            </div>
          </div>

          <!-- Add Line Item Panel -->
          <div v-if="quotation.status === 'draft' || quotation.status === 'revised'" class="bg-slate-950/40 p-5 rounded-2xl border border-slate-850 space-y-4">
            <h4 class="text-xs font-black text-white uppercase tracking-wider">Add Line Item (Pricing Engine Autofill)</h4>
            <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
              <div>
                <label class="block text-[10px] text-slate-400 mb-1">Item Type</label>
                <select v-model="newItem.item_type" class="w-full text-xs bg-slate-900 border border-slate-800 rounded-lg p-2.5 text-white">
                  <option value="product">Product (Parts/Materials)</option>
                  <option value="service">Service Task (Labor)</option>
                </select>
              </div>

              <div v-if="newItem.item_type === 'product'">
                <label class="block text-[10px] text-slate-400 mb-1">Product (Part)</label>
                <select v-model="newItem.part_id" @change="calculatePricingEngineRate" class="w-full text-xs bg-slate-900 border border-slate-800 rounded-lg p-2.5 text-white">
                  <option value="">Select Part...</option>
                  <option v-for="part in partsList" :key="part.id" :value="part.id">
                    {{ part.name }} (SKU: {{ part.sku }})
                  </option>
                </select>
              </div>

              <div v-else>
                <label class="block text-[10px] text-slate-400 mb-1">Service Description</label>
                <input
                  v-model="newItem.service_name"
                  @blur="calculatePricingEngineRate"
                  type="text"
                  placeholder="e.g. Brake Caliper Rebuild"
                  class="w-full text-xs bg-slate-900 border border-slate-800 rounded-lg p-2.5 text-white"
                />
              </div>

              <div>
                <label class="block text-[10px] text-slate-400 mb-1">Billed Unit Price (Auto-suggested)</label>
                <input
                  v-model.number="newItem.price"
                  type="number"
                  class="w-full text-xs bg-slate-900 border border-slate-800 rounded-lg p-2.5 text-white"
                />
              </div>

              <div>
                <label class="block text-[10px] text-slate-400 mb-1">Qty / Est. Hours</label>
                <input
                  v-model.number="newItem.quantity"
                  type="number"
                  class="w-full text-xs bg-slate-900 border border-slate-800 rounded-lg p-2.5 text-white"
                />
              </div>
            </div>

            <div class="flex justify-between items-center pt-2">
              <span class="text-[10px] text-indigo-400 font-bold flex items-center" v-if="calculatingRate">
                <svg class="animate-spin -ml-1 mr-2 h-4 w-4 text-indigo-400" fill="none" viewBox="0 0 24 24">
                  <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                  <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                </svg>
                Pricing engine matching contract fallback rates...
              </span>
              <span v-else class="text-[10px] text-emerald-450 font-bold">
                ✔ Customer rate synced
              </span>
              <button
                @click="addNewItemLine"
                class="px-4 py-2 bg-indigo-600 hover:bg-indigo-700 text-white rounded-lg text-xs font-black uppercase tracking-wider"
              >
                Add Line Item
              </button>
            </div>
          </div>

        </div>

        <!-- Right info panels -->
        <div class="xl:col-span-1 space-y-6">
          
          <!-- Summary card -->
          <div class="bg-slate-950/20 border border-slate-850 rounded-2xl overflow-hidden shadow-xl">
            <div class="px-6 py-4 border-b border-slate-850 bg-slate-950/40">
              <h3 class="text-xs font-black text-white uppercase tracking-wider">Quotation Summary</h3>
            </div>
            <div class="p-6 space-y-4">
              <dl class="space-y-3 text-xs text-slate-400">
                <div class="flex justify-between">
                  <dt>Products Total:</dt>
                  <dd class="font-bold text-white font-mono">{{ formatCurrency(quotation.total_product_cost) }}</dd>
                </div>
                <div class="flex justify-between">
                  <dt>Labor Service Total:</dt>
                  <dd class="font-bold text-white font-mono">{{ formatCurrency(quotation.total_labor_cost) }}</dd>
                </div>

                <!-- Editable Discount/Tax for draft/revised -->
                <div v-if="quotation.status === 'draft' || quotation.status === 'revised'" class="space-y-2 pt-2 border-t border-slate-850">
                  <div class="flex items-center justify-between">
                    <label class="text-[10px] text-slate-400 font-bold uppercase">Discount (৳):</label>
                    <input
                      v-model.number="tempDiscount"
                      type="number"
                      class="w-24 text-xs bg-slate-900 border border-slate-800 rounded px-2 py-1 text-right text-white font-mono"
                    />
                  </div>
                  <div class="flex items-center justify-between">
                    <label class="text-[10px] text-slate-400 font-bold uppercase">Tax/VAT (৳):</label>
                    <input
                      v-model.number="tempTax"
                      type="number"
                      class="w-24 text-xs bg-slate-900 border border-slate-800 rounded px-2 py-1 text-right text-white font-mono"
                    />
                  </div>
                  <div class="pt-2 flex justify-end">
                    <button
                      @click="openDiscountTaxModal"
                      class="px-3 py-1 bg-indigo-650 hover:bg-indigo-700 text-white rounded text-[10px] font-black uppercase tracking-wider"
                    >
                      Apply Changes
                    </button>
                  </div>
                </div>

                <!-- Read-only for other statuses -->
                <div v-else class="space-y-2 pt-2 border-t border-slate-850">
                  <div class="flex justify-between">
                    <dt>Discount (৳):</dt>
                    <dd class="font-bold text-white font-mono">-{{ formatCurrency(quotation.discount) }}</dd>
                  </div>
                  <div class="flex justify-between">
                    <dt>Vat/Tax (৳):</dt>
                    <dd class="font-bold text-white font-mono">+{{ formatCurrency(quotation.tax) }}</dd>
                  </div>
                </div>

                <div class="flex justify-between items-center border-t border-slate-800 pt-3 text-sm">
                  <dt class="font-extrabold text-white">Grand Total</dt>
                  <dd class="font-black text-indigo-400 font-mono text-base">{{ formatCurrency(quotation.grand_total) }}</dd>
                </div>
              </dl>
            </div>
          </div>

          <!-- Print & Dispatch Controls -->
          <div class="bg-slate-950/20 border border-slate-850 rounded-2xl overflow-hidden shadow-xl">
            <div class="px-6 py-4 border-b border-slate-850 bg-slate-950/40">
              <h3 class="text-xs font-black text-white uppercase tracking-wider">Print & Dispatch</h3>
            </div>
            <div class="p-6 space-y-4">
              <p class="text-xs text-slate-400">Generate an official corporate PDF or send the versioned quotation to the customer for digital approval.</p>

              <div class="grid grid-cols-2 gap-3">
                <!-- PDF Download -->
                <button
                  @click="downloadQuotationPdf"
                  class="flex flex-col items-center justify-center p-3 border border-slate-800 hover:border-slate-700 bg-slate-900 hover:bg-slate-850 rounded-xl transition text-center group"
                >
                  <svg class="w-6 h-6 text-slate-400 group-hover:text-indigo-400 mb-1" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                  </svg>
                  <span class="text-[10px] font-black text-slate-300 group-hover:text-white uppercase tracking-wider">Download PDF</span>
                </button>

                <!-- Send to Customer -->
                <button
                  @click="sendQuotationToCustomer"
                  :disabled="sendingQuotation"
                  class="flex flex-col items-center justify-center p-3 border border-slate-800 hover:border-slate-700 bg-slate-900 hover:bg-slate-850 rounded-xl transition text-center group disabled:opacity-50"
                >
                  <svg class="w-6 h-6 text-slate-400 group-hover:text-emerald-400 mb-1" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"></path>
                  </svg>
                  <span class="text-[10px] font-black text-slate-300 group-hover:text-white uppercase tracking-wider">
                    {{ sendingQuotation ? 'Sending...' : 'Send to Customer' }}
                  </span>
                </button>
              </div>
            </div>
          </div>

          <!-- Version histories -->
          <div class="bg-slate-950/20 border border-slate-850 rounded-2xl overflow-hidden shadow-xl">
            <div class="px-6 py-4 border-b border-slate-850 bg-slate-950/40">
              <h3 class="text-xs font-black text-white uppercase tracking-wider">Revision Version Snapshot Logs</h3>
            </div>
            <div class="p-6 max-h-40 overflow-y-auto">
              <div v-if="!quotation.snapshots || quotation.snapshots.length === 0" class="text-center py-4 text-xs text-slate-500 italic">
                Initial quotation draft version.
              </div>
              <div v-else class="space-y-4 text-xs">
                <div v-for="snap in quotation.snapshots" :key="snap.id" class="border-l-2 border-indigo-500 pl-3 py-0.5">
                  <div class="font-bold text-white">Version {{ snap.version }} Snapshot</div>
                  <div class="text-[9px] text-slate-500 font-mono">Date: {{ formatDate(snap.created_at) }}</div>
                  <div class="text-slate-400 italic">"{{ snap.snapshot_data?.notes || 'Revision snapshot.' }}"</div>
                </div>
              </div>
            </div>
          </div>

          <!-- Deletion audits -->
          <div class="bg-slate-950/20 border border-slate-850 rounded-2xl overflow-hidden shadow-xl">
            <div class="px-6 py-4 border-b border-slate-850 bg-slate-950/40">
              <h3 class="text-xs font-black text-white uppercase tracking-wider">Removed Items Audits</h3>
            </div>
            <div class="p-6 max-h-40 overflow-y-auto">
              <div v-if="!quotation.removed_items || quotation.removed_items.length === 0" class="text-center py-4 text-xs text-slate-500 italic">
                No items have been removed.
              </div>
              <div v-else class="space-y-4 text-xs">
                <div v-for="audit in quotation.removed_items" :key="audit.id" class="bg-red-950/10 border-l-2 border-red-500 pl-3 py-1 rounded">
                  <div class="font-bold text-slate-200 flex justify-between">
                    <span>{{ audit.item_name }}</span>
                    <span class="font-mono text-red-400">{{ formatCurrency(audit.previous_price) }}</span>
                  </div>
                  <div class="text-[9px] text-slate-500">Deleted by: {{ audit.removed_by?.name || 'Advisor' }}</div>
                  <div class="text-[10px] text-red-300 italic mt-1 bg-slate-900 p-1 rounded border border-red-950">
                    "{{ audit.removal_reason }}"
                  </div>
                </div>
              </div>
            </div>
          </div>

        </div>

      </div>
    </div>

    <!-- Auditable Removal Reason Modal -->
    <div v-if="showRemovalModal" class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-950/60 backdrop-blur-sm">
      <div class="bg-white rounded-2xl border border-slate-200 shadow-2xl max-w-md w-full p-6 space-y-4 text-slate-900">
        <h3 class="text-lg font-black text-slate-950 flex items-center">
          ⚠️ Auditable Item Removal Required
        </h3>
        <p class="text-xs text-slate-500">To maintain safety compliance and audit tracking, please input the rationale for deleting this item line (minimum 10 characters).</p>
        <textarea
          v-model="removalReason"
          rows="3"
          required
          class="w-full text-xs rounded-xl border-slate-300 p-3 border focus:ring-2 focus:ring-red-500 focus:border-red-500"
          placeholder="e.g. Customer requested diagnostic validation of spark plugs before ordering new parts..."
        ></textarea>
        <p class="text-[10px] text-rose-600 font-bold" v-if="removalReason.length < 10 && removalReason.length > 0">
          Reason too short! Supply at least 10 characters (currently: {{ removalReason.length }}).
        </p>
        <div class="flex justify-end gap-3 pt-2">
          <button
            type="button"
            @click="showRemovalModal = false"
            class="px-4 py-2 border border-slate-300 rounded-xl text-xs font-bold text-slate-700 bg-white hover:bg-slate-50"
          >
            Cancel
          </button>
          <button
            type="button"
            @click="removeItemConfirmed"
            :disabled="removalReason.length < 10 || deletingItem"
            class="px-4 py-2 bg-red-650 hover:bg-red-700 disabled:opacity-50 text-white rounded-xl text-xs font-black uppercase tracking-wider shadow-md"
          >
            {{ deletingItem ? 'Deleting...' : 'Log & Delete Line' }}
          </button>
        </div>
      </div>
    </div>

    <!-- Auditable Discount/Tax Revision Modal -->
    <div v-if="showDiscountModal" class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-950/60 backdrop-blur-sm">
      <div class="bg-white rounded-2xl border border-slate-200 shadow-2xl max-w-md w-full p-6 space-y-4 text-slate-900">
        <h3 class="text-lg font-black text-slate-950 flex items-center">
          ⚠️ Auditable Pricing Change
        </h3>
        <p class="text-xs text-slate-500">To maintain safety compliance and audit tracking, please input the rationale for revising the discount and tax values (minimum 10 characters).</p>
        <textarea
          v-model="discountTaxReason"
          rows="3"
          required
          class="w-full text-xs rounded-xl border-slate-300 p-3 border focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500"
          placeholder="e.g. Approved 5% corporate loyalty discount override after supervisor review..."
        ></textarea>
        <p class="text-[10px] text-rose-600 font-bold" v-if="discountTaxReason.length < 10 && discountTaxReason.length > 0">
          Reason too short! Supply at least 10 characters (currently: {{ discountTaxReason.length }}).
        </p>
        <div class="flex justify-end gap-3 pt-2">
          <button
            type="button"
            @click="showDiscountModal = false"
            class="px-4 py-2 border border-slate-300 rounded-xl text-xs font-bold text-slate-700 bg-white hover:bg-slate-50"
          >
            Cancel
          </button>
          <button
            type="button"
            @click="applyDiscountTaxConfirmed"
            :disabled="discountTaxReason.length < 10 || applyingDiscountTax"
            class="px-4 py-2 bg-indigo-650 hover:bg-indigo-700 disabled:opacity-50 text-white rounded-xl text-xs font-black uppercase tracking-wider shadow-md"
          >
            {{ applyingDiscountTax ? 'Applying...' : 'Log & Apply Changes' }}
          </button>
        </div>
      </div>
    </div>

    </JobDetailsLayout>
  </div>
</template>

<script setup>
import { ref, reactive, computed, onMounted, watch } from 'vue';
import { useRoute, useRouter } from 'vue-router';
import api from '../../services/api';
import { useToastStore } from '../../stores/toast';
import JobDetailsLayout from '../../components/workshop/JobDetailsLayout.vue';
import WorkspaceJobSelector from '../../components/workshop/WorkspaceJobSelector.vue';

const route = useRoute();
const router = useRouter();
const toast = useToastStore();

const jobCard = ref(null);
const quotation = ref(null);
const loadingJob = ref(true);
const creatingDraft = ref(false);
const calculatingRate = ref(false);
const partsList = ref([]);

// Line Item Form
const newItem = ref({
  item_type: 'product',
  part_id: '',
  service_name: '',
  price: 0,
  quantity: 1,
});

// Modal Audit state
const showRemovalModal = ref(false);
const itemToRemove = ref(null);
const removalReason = ref('');
const deletingItem = ref(false);

const tempDiscount = ref(0);
const tempTax = ref(0);
const showDiscountModal = ref(false);
const discountTaxReason = ref('');
const applyingDiscountTax = ref(false);
const sendingQuotation = ref(false);

const handleJobSelected = (id) => {
  router.push({ name: 'workshop.quotation', params: { id } });
};

watch(() => route.params.id, () => {
  fetchJobDetails();
});

const productItems = computed(() => {
  if (!quotation.value || !quotation.value.items) return [];
  return quotation.value.items.filter(i => i.item_type === 'product');
});

const serviceItems = computed(() => {
  if (!quotation.value || !quotation.value.items) return [];
  return quotation.value.items.filter(i => i.item_type === 'service');
});

const fetchJobDetails = async () => {
  if (!route.params.id) {
    jobCard.value = null;
    quotation.value = null;
    loadingJob.value = false;
    return;
  }
  loadingJob.value = true;
  try {
    const jcRes = await api.get(`/job-cards/${route.params.id}`);
    jobCard.value = jcRes.data.data;
    
    // Fetch quotations associated with this job
    const qoRes = await api.get('/quotations', { params: { job_card_id: jobCard.value.id } });
    if (qoRes.data.data && qoRes.data.data.length > 0) {
      const details = await api.get(`/quotations/${qoRes.data.data[0].id}`);
      quotation.value = details.data.data;
      tempDiscount.value = quotation.value.discount || 0;
      tempTax.value = quotation.value.tax || 0;
    }
  } catch (err) {
    toast.error('Failed to load Job Card or associated Quotations');
    router.push({ name: 'workshop.quotation' });
  } finally {
    loadingJob.value = false;
  }
};

const fetchParts = async () => {
  try {
    const res = await api.get('/parts');
    partsList.value = res.data.data || res.data || [];
  } catch (err) {
    console.error('Failed to fetch parts list', err);
  }
};

// Initiate quotation draft based on complaint details
const initiateQuotationDraft = async () => {
  creatingDraft.value = true;
  try {
    const res = await api.post('/quotations', {
      job_card_id: jobCard.value.id,
      notes: `Intake draft compiled for diagnostics: ${jobCard.value.complaint}`,
      discount: 0,
      tax: 0,
      items: [
        {
          item_type: 'service',
          service_name: 'Diagnostic Scan & Initial System Clearance',
          estimated_hours: 1.0,
          source_type: 'workshop_supplied'
        }
      ]
    });
    quotation.value = res.data.data;
    toast.success('Quotation draft successfully generated.');
    await fetchJobDetails();
  } catch (err) {
    toast.error('Failed to initiate quotation draft.');
  } finally {
    creatingDraft.value = false;
  }
};

// Autofill pricing calculate lookups
const calculatePricingEngineRate = async () => {
  if (newItem.value.item_type === 'product' && !newItem.value.part_id) return;
  if (newItem.value.item_type === 'service' && !newItem.value.service_name) return;

  calculatingRate.value = true;
  try {
    const response = await api.get(`/customers/${jobCard.value.customer_id}/pricing/calculate`, {
      params: {
        part_id: newItem.value.part_id || undefined,
        labor_service_name: newItem.value.service_name || undefined,
      }
    });
    newItem.value.price = response.data.data?.price || 0;
  } catch (err) {
    console.error('Customer custom rate calculation lookup failed', err);
    newItem.value.price = 0; // Fallback
  } finally {
    calculatingRate.value = false;
  }
};

// Add item to draft with revisioning
const addNewItemLine = async () => {
  if (newItem.value.item_type === 'product' && !newItem.value.part_id) {
    toast.warning('Select an inventory product part.');
    return;
  }
  if (newItem.value.item_type === 'service' && !newItem.value.service_name) {
    toast.warning('Service description cannot be empty.');
    return;
  }

  try {
    const revisionItems = quotation.value.items.map(item => ({
      item_type: item.item_type,
      part_id: item.part_id,
      service_name: item.service_name,
      quantity: item.quantity,
      unit_price: item.unit_price,
      labor_cost: item.labor_cost,
      estimated_hours: item.estimated_hours,
      source_type: item.source_type,
    }));

    revisionItems.push({
      item_type: newItem.value.item_type,
      part_id: newItem.value.part_id || undefined,
      service_name: newItem.value.service_name || undefined,
      quantity: newItem.value.quantity,
      unit_price: newItem.value.item_type === 'product' ? newItem.value.price : undefined,
      labor_cost: newItem.value.item_type === 'service' ? newItem.value.price : undefined,
      estimated_hours: newItem.value.item_type === 'service' ? newItem.value.quantity : undefined,
      source_type: 'workshop_supplied',
    });

    const response = await api.put(`/quotations/${quotation.value.id}/revise`, {
      reason: `Line item added mid-workshop evaluation: ${newItem.value.service_name || 'Part Added'}`,
      items: revisionItems,
    });

    quotation.value = response.data.data;
    toast.success('Line item added and quotation version updated.');
    
    // Reset forms
    newItem.value.part_id = '';
    newItem.value.service_name = '';
    newItem.value.price = 0;
    newItem.value.quantity = 1;
  } catch (err) {
    toast.error('Failed to add quotation line item.');
  }
};

const promptRemoveItem = (item) => {
  itemToRemove.value = item;
  removalReason.value = '';
  showRemovalModal.value = true;
};

const removeItemConfirmed = async () => {
  deletingItem.value = true;
  try {
    await api.delete(`/quotation-items/${itemToRemove.value.id}`, {
      data: { reason: removalReason.value }
    });
    toast.success('Line item removed and audited.');
    showRemovalModal.value = false;
    await fetchJobDetails();
  } catch (err) {
    toast.error(err.response?.data?.message || 'Removal failed.');
  } finally {
    deletingItem.value = false;
  }
};

const formatCurrency = (value) => {
  return new Intl.NumberFormat('en-BD', { style: 'currency', currency: 'BDT' }).format(value || 0);
};

const formatDate = (dateString) => {
  if (!dateString) return '';
  const date = new Date(dateString);
  return date.toLocaleDateString(undefined, { year: 'numeric', month: 'short', day: 'numeric', hour: '2-digit', minute: '2-digit' });
};

const getQuotationStatusClass = (status) => {
  const map = {
    'draft': 'bg-slate-800 text-slate-350 border border-slate-700/50',
    'revised': 'bg-yellow-500/10 text-yellow-400 border border-yellow-500/20',
    'approved': 'bg-emerald-500/10 text-emerald-400 border border-emerald-500/20',
    'partially_approved': 'bg-blue-500/10 text-blue-400 border border-blue-500/20',
    'rejected': 'bg-rose-500/10 text-rose-400 border border-rose-500/20',
  };
  return map[status] || 'bg-slate-800 text-slate-405';
};

const downloadQuotationPdf = async () => {
  try {
    const response = await api.get(`/print/quotation/${quotation.value.id}`, {
      responseType: 'blob'
    });
    const blob = new Blob([response.data], { type: 'application/pdf' });
    const link = document.createElement('a');
    link.href = window.URL.createObjectURL(blob);
    link.download = `Quotation_${String(quotation.value.quotation_number).replace(/[^a-zA-Z0-9]/g, '_')}.pdf`;
    document.body.appendChild(link);
    link.click();
    document.body.removeChild(link);
    toast.success('PDF downloaded successfully.');
  } catch (err) {
    toast.error('Failed to download quotation PDF.');
  }
};

const sendQuotationToCustomer = async () => {
  sendingQuotation.value = true;
  try {
    await api.post(`/quotations/${quotation.value.id}/send`);
    toast.success('Quotation sent to customer successfully.');
    await fetchJobDetails();
  } catch (err) {
    toast.error(err.response?.data?.message || 'Failed to send quotation to customer.');
  } finally {
    sendingQuotation.value = false;
  }
};

const openDiscountTaxModal = () => {
  discountTaxReason.value = '';
  showDiscountModal.value = true;
};

const applyDiscountTaxConfirmed = async () => {
  if (discountTaxReason.value.length < 10) {
    toast.warning('Please provide a reason of at least 10 characters.');
    return;
  }
  
  applyingDiscountTax.value = true;
  try {
    const revisionItems = quotation.value.items.map(item => ({
      item_type: item.item_type,
      part_id: item.part_id,
      service_name: item.service_name,
      quantity: item.quantity,
      unit_price: item.unit_price,
      labor_cost: item.labor_cost,
      estimated_hours: item.estimated_hours,
      source_type: item.source_type,
    }));

    const response = await api.put(`/quotations/${quotation.value.id}/revise`, {
      reason: discountTaxReason.value,
      discount: tempDiscount.value,
      tax: tempTax.value,
      items: revisionItems,
    });

    quotation.value = response.data.data;
    toast.success('Discount and Tax updated successfully (version incremented).');
    showDiscountModal.value = false;
  } catch (err) {
    toast.error(err.response?.data?.message || 'Failed to update discount and tax.');
  } finally {
    applyingDiscountTax.value = false;
  }
};

onMounted(() => {
  if (route.params.id) {
    fetchJobDetails();
  } else {
    loadingJob.value = false;
  }
  fetchParts();
});
</script>
