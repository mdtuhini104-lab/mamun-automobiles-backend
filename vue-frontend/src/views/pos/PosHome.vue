<template>
  <div class="flex h-[calc(100vh-80px)] bg-slate-900 text-white gap-6 p-4">
    <!-- Product Search & List -->
    <div class="flex-1 flex flex-col bg-slate-800 p-6 rounded-lg border border-slate-700">
      <div class="mb-4 flex gap-4">
        <div class="flex-1">
          <input
            ref="searchInputRef"
            type="text"
            placeholder="Search products or scan barcode (F2)..."
            v-model="posStore.search"
            @keyup.enter="handleSearchEnter"
            class="w-full rounded-md border-0 bg-slate-700 py-2 px-4 text-white shadow-sm ring-1 ring-inset ring-slate-600 focus:ring-2 focus:ring-inset focus:ring-indigo-500"
          />
        </div>
        <button 
          @click="closeRegister"
          class="bg-red-600 hover:bg-red-500 text-white font-medium px-4 rounded-md transition"
        >
          Close Register
        </button>
      </div>
      
      <div class="flex-1 overflow-auto grid grid-cols-2 md:grid-cols-3 gap-4 content-start">
        <div v-if="posStore.loadingProducts" class="col-span-full text-center text-slate-400">Loading products...</div>
        
        <div
          v-for="product in posStore.products"
          :key="product.id"
          class="bg-slate-700 p-4 rounded-lg cursor-pointer hover:bg-slate-600 transition-colors border border-slate-600 flex flex-col justify-between h-32"
          :class="{'opacity-50 pointer-events-none': product.stock <= 0}"
          @click="handleAddToCart(product)"
        >
          <div>
            <h3 class="font-semibold text-white">{{ product.name }}</h3>
            <p class="text-sm text-slate-400">SKU: {{ product.sku }}</p>
          </div>
          <div class="flex justify-between items-center mt-2">
            <span class="text-indigo-400 font-bold">${{ product.price }}</span>
            <span class="text-xs" :class="product.stock > 0 ? 'text-slate-500' : 'text-red-500 font-bold'">
              Stock: {{ product.stock }}
            </span>
          </div>
        </div>
        
        <div v-if="!posStore.loadingProducts && posStore.products.length === 0" class="col-span-full text-center text-slate-400 mt-10">
          No products found.
        </div>
      </div>
    </div>

    <!-- Cart & Checkout -->
    <div class="w-96 flex flex-col bg-slate-800 p-6 rounded-lg border border-slate-700 relative">
      <h2 class="text-xl font-bold mb-4">Current Order</h2>

      <!-- Stock Warnings -->
      <div v-if="posStore.stockWarnings.length > 0" class="mb-4 space-y-2">
        <div v-for="warning in posStore.stockWarnings" :key="warning.id" class="bg-red-900 border border-red-500 text-red-100 px-3 py-2 rounded text-sm">
          ⚠️ {{ warning.message }}
        </div>
      </div>
      
      <div class="mb-4 space-y-2 flex-shrink-0">
        <select
          v-model="posStore.customerId"
          class="w-full rounded-md border-0 bg-slate-700 py-1.5 text-white shadow-sm ring-1 ring-inset ring-slate-600 focus:ring-2 focus:ring-inset focus:ring-indigo-500 sm:text-sm"
        >
          <option value="">Walk-in Customer</option>
          <option v-for="c in posStore.customers" :key="c.id" :value="c.id">{{ c.name }}</option>
        </select>

        <select
          v-model="posStore.paymentMethod"
          class="w-full rounded-md border-0 bg-slate-700 py-1.5 text-white shadow-sm ring-1 ring-inset ring-slate-600 focus:ring-2 focus:ring-inset focus:ring-indigo-500 sm:text-sm"
        >
          <option value="cash">Cash</option>
          <option value="card">Card</option>
          <option value="split">Split Payment</option>
          <option value="due">Mark as Due</option>
        </select>

        <div v-if="posStore.paymentMethod === 'split'" class="flex gap-2 mt-2">
          <input
            type="number"
            v-model.number="posStore.splitCash"
            placeholder="Cash Amount"
            class="w-1/2 rounded-md border-0 bg-slate-700 py-1.5 px-2 text-white shadow-sm ring-1 ring-inset ring-slate-600 focus:ring-2 focus:ring-inset focus:ring-indigo-500 sm:text-sm"
          />
          <input
            type="number"
            v-model.number="posStore.splitCard"
            placeholder="Card Amount"
            class="w-1/2 rounded-md border-0 bg-slate-700 py-1.5 px-2 text-white shadow-sm ring-1 ring-inset ring-slate-600 focus:ring-2 focus:ring-inset focus:ring-indigo-500 sm:text-sm"
          />
        </div>
      </div>
      
      <div class="flex-1 overflow-auto space-y-4">
        <div v-for="item in posStore.cart" :key="item.id" class="flex justify-between items-center bg-slate-700 p-3 rounded-lg border border-slate-600">
          <div class="flex-1">
            <h4 class="font-medium text-white text-sm truncate w-40">{{ item.name }}</h4>
            <div class="flex items-center mt-1 space-x-2">
              <button 
                @click="posStore.updateQuantity(item.id, item.quantity - 1)"
                class="w-6 h-6 flex items-center justify-center bg-slate-600 rounded hover:bg-slate-500"
              >-</button>
              <span class="text-sm">{{ item.quantity }}</span>
              <button 
                @click="posStore.updateQuantity(item.id, item.quantity + 1)"
                class="w-6 h-6 flex items-center justify-center bg-slate-600 rounded hover:bg-slate-500"
              >+</button>
              <span class="text-xs text-slate-400 ml-2">@ ${{ item.price }}</span>
            </div>
          </div>
          <div class="flex flex-col items-end ml-2">
            <span class="font-bold text-white">${{ (item.price * item.quantity).toFixed(2) }}</span>
            <button
              @click="posStore.removeFromCart(item.id)"
              class="text-xs text-red-400 hover:text-red-300 mt-1"
            >Remove</button>
          </div>
        </div>
        <div v-if="posStore.cart.length === 0" class="text-slate-400 text-center mt-10">
          Cart is empty. Scan items to add.
        </div>
      </div>

      <div class="border-t border-slate-700 pt-4 mt-4 space-y-2 flex-shrink-0">
        <div class="flex justify-between text-sm text-slate-400">
          <span>Subtotal:</span>
          <span>${{ posStore.cartSubtotal.toFixed(2) }}</span>
        </div>
        <div class="flex justify-between text-sm text-slate-400 items-center">
          <span>Discount:</span>
          <input
            type="number"
            v-model.number="posStore.discount"
            class="w-20 rounded-md border-0 bg-slate-700 py-1 px-2 text-white text-right shadow-sm ring-1 ring-inset ring-slate-600 focus:ring-2 focus:ring-inset focus:ring-indigo-500 sm:text-sm"
          />
        </div>
        <div class="flex justify-between text-lg font-bold text-white mt-2 border-t border-slate-700 pt-2">
          <span>Total:</span>
          <span>${{ posStore.cartTotal.toFixed(2) }}</span>
        </div>
        
        <div v-if="posStore.paymentMethod === 'split'" class="flex justify-between text-xs text-slate-400 mt-1">
          <span>Split Total:</span>
          <span :class="{'text-red-400': (posStore.splitCash + posStore.splitCard) !== posStore.cartTotal}">
            ${{ (posStore.splitCash + posStore.splitCard).toFixed(2) }}
          </span>
        </div>

        <button 
          class="w-full mt-4 bg-indigo-600 hover:bg-indigo-500 text-white font-bold py-3 rounded disabled:opacity-50"
          :disabled="posStore.cart.length === 0"
          @click="processPaymentAndPrint"
        >
          Pay & Print
        </button>
      </div>
    </div>

    <!-- Hidden Receipt for Printing -->
    <div class="hidden print:block w-[80mm] p-4 text-black bg-white absolute top-0 left-0 z-50" id="receipt">
      <h2 class="text-center font-bold text-lg">Mamun Automobiles</h2>
      <p class="text-center text-xs">POS Receipt</p>
      <p class="text-center text-xs">{{ new Date().toLocaleString() }}</p>
      <hr class="my-2 border-black border-dashed" />
      <div class="space-y-1">
        <div v-for="item in posStore.cart" :key="item.id" class="flex justify-between text-xs">
          <span class="truncate w-40">{{ item.name }} x {{ item.quantity }}</span>
          <span>${{ (item.price * item.quantity).toFixed(2) }}</span>
        </div>
      </div>
      <hr class="my-2 border-black border-dashed" />
      <div v-if="posStore.discount > 0" class="flex justify-between text-xs">
        <span>Discount:</span>
        <span>-${{ posStore.discount.toFixed(2) }}</span>
      </div>
      <div class="flex justify-between font-bold text-sm mt-1">
        <span>Total:</span>
        <span>${{ posStore.cartTotal.toFixed(2) }}</span>
      </div>
      <div class="flex justify-between text-xs mt-1">
        <span>Payment Method:</span>
        <span class="uppercase">{{ posStore.paymentMethod }}</span>
      </div>
      <p class="text-center text-xs mt-4">Thank you for your business!</p>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted, onUnmounted, watch } from 'vue';
import { usePosStore } from '../../stores/pos';
import { useStockSocket } from '../../composables/useStockSocket';
import { usePosSocket } from '../../composables/usePosSocket';

const posStore = usePosStore();
const searchInputRef = ref(null);
let searchTimeout = null;

// Debounce search to avoid massive API requests
watch(() => posStore.search, (newVal) => {
  clearTimeout(searchTimeout);
  searchTimeout = setTimeout(() => {
    posStore.fetchProducts(newVal);
  }, 300);
});

onMounted(() => {
  posStore.fetchProducts('');
  posStore.fetchCustomers();
  
  // F2 Shortcut for scanner
  window.addEventListener('keydown', handleGlobalKeydown);
});

onUnmounted(() => {
  window.removeEventListener('keydown', handleGlobalKeydown);
});

const handleGlobalKeydown = (e) => {
  if (e.key === 'F2') {
    e.preventDefault();
    if (searchInputRef.value) {
      searchInputRef.value.focus();
    }
  }
};

const handleSearchEnter = () => {
  // Scanner behavior: auto add first result and clear
  if (posStore.products && posStore.products.length > 0) {
    const success = posStore.addToCart(posStore.products[0]);
    if (success) {
      posStore.search = '';
    }
  }
};

const handleAddToCart = (product) => {
  if (product.stock > 0) {
    posStore.addToCart(product);
  }
};

const closeRegister = () => {
  alert('Daily Closing Report:\nTotal Sales: $1,250.00\nCash: $800.00\nCard: $350.00\nDue: $100.00');
};

const processPaymentAndPrint = async () => {
  // In a real flow, this would call the POS API POST /checkout endpoint
  // Then clear cart, then print. For now we mimic the previous page.tsx behavior
  setTimeout(() => {
    window.print();
    posStore.clearCart();
  }, 300);
};

// Listen to inventory stock sync to dynamically patch products and cart safety
useStockSocket((data) => {
  posStore.patchStock(data);
});

// Optionally listen to POS sync events if needed for other terminals
usePosSocket((data) => {
  console.log('POS transaction received from another terminal:', data);
});
</script>

<style>
@media print {
  body * {
    visibility: hidden;
  }
  #receipt, #receipt * {
    visibility: visible;
  }
  #receipt {
    position: absolute;
    left: 0;
    top: 0;
    width: 80mm !important;
    background: white !important;
    color: black !important;
    padding: 0 !important;
    margin: 0 !important;
  }
}
</style>
