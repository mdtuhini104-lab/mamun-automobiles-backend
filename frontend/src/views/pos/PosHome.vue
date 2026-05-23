<template>
  <div class="flex flex-col lg:flex-row h-[calc(100vh-64px)] lg:h-[calc(100vh-80px)] bg-slate-50 text-slate-800 gap-4 p-2 sm:p-4">
    <!-- Product Search & List -->
    <div class="flex-1 flex flex-col bg-white rounded-xl border border-slate-200 shadow-sm overflow-hidden h-full">
      <!-- Sticky Mobile Topbar / Action Bar -->
      <div class="sticky top-0 z-20 bg-white border-b border-slate-200 p-3 sm:p-4 flex flex-col sm:flex-row gap-3 items-center">
        <div class="relative w-full flex-1">
          <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
            <svg class="h-5 w-5 text-slate-400" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" /></svg>
          </div>
          <input
            ref="searchInputRef"
            type="text"
            placeholder="Search products or scan barcode (F2)..."
            v-model="posStore.search"
            @keyup.enter="handleSearchEnter"
            class="block w-full pl-10 rounded-lg border-0 bg-slate-50 py-2.5 text-slate-900 shadow-inner ring-1 ring-inset ring-slate-200 focus:bg-white focus:ring-2 focus:ring-inset focus:ring-indigo-600 sm:text-sm sm:leading-6 transition-all"
          />
        </div>
        <div class="flex gap-2 w-full sm:w-auto">
          <button 
            @click="toggleFullscreen"
            class="flex-1 sm:flex-none bg-white hover:bg-slate-50 text-slate-700 font-medium px-4 py-2.5 rounded-lg transition-colors border border-slate-300 whitespace-nowrap text-sm shadow-sm flex items-center justify-center gap-2"
          >
            <svg v-if="!isFullscreen" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-4 h-4"><path stroke-linecap="round" stroke-linejoin="round" d="M3.75 3.75v4.5m0-4.5h4.5m-4.5 0L9 9M3.75 20.25v-4.5m0 4.5h4.5m-4.5 0L9 15M20.25 3.75h-4.5m4.5 0v4.5m0-4.5L15 9m5.25 11.25h-4.5m4.5 0v-4.5m0 4.5L15 15" /></svg>
            <svg v-else xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-4 h-4"><path stroke-linecap="round" stroke-linejoin="round" d="M9 9V4.5M9 9H4.5M9 9L3.75 3.75M9 15v4.5M9 15H4.5M9 15l-5.25 5.25M15 9h4.5M15 9V4.5M15 9l5.25-5.25M15 15h4.5M15 15v4.5m0-4.5l5.25 5.25" /></svg>
            <span class="hidden sm:inline">{{ isFullscreen ? 'Exit' : 'Fullscreen' }}</span>
          </button>
          <button 
            @click="closeRegister"
            class="flex-1 sm:flex-none bg-red-600 hover:bg-red-500 text-white font-medium px-4 py-2.5 rounded-lg transition-colors whitespace-nowrap text-sm shadow-sm flex items-center justify-center gap-2"
          >
            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-4 h-4"><path stroke-linecap="round" stroke-linejoin="round" d="M15.75 9V5.25A2.25 2.25 0 0013.5 3h-6a2.25 2.25 0 00-2.25 2.25v13.5A2.25 2.25 0 007.5 21h6a2.25 2.25 0 002.25-2.25V15M12 9l-3 3m0 0l3 3m-3-3h12.75" /></svg>
            <span class="hidden sm:inline">Close</span>
          </button>
        </div>
      </div>
      
      <div class="flex-1 overflow-auto p-3 sm:p-4 bg-slate-50/50">
        <div class="grid grid-cols-2 md:grid-cols-3 xl:grid-cols-4 gap-3 sm:gap-4 content-start">
          
          <!-- Loading Skeletons -->
          <template v-if="posStore.loadingProducts">
            <div v-for="i in 8" :key="'skeleton-'+i" class="bg-white p-4 rounded-xl border border-slate-200 flex flex-col justify-between h-32 animate-pulse shadow-sm">
              <div>
                <div class="h-4 bg-slate-200 rounded w-3/4 mb-2"></div>
                <div class="h-3 bg-slate-100 rounded w-1/2"></div>
              </div>
              <div class="flex justify-between items-center mt-2">
                <div class="h-5 bg-slate-200 rounded w-12"></div>
                <div class="h-4 bg-slate-100 rounded w-16"></div>
              </div>
            </div>
          </template>
          
          <!-- Products -->
          <template v-else>
            <div
              v-for="product in posStore.products"
              :key="product.id"
              class="group bg-white p-3 sm:p-4 rounded-xl cursor-pointer hover:bg-slate-50 transition-all border border-slate-200 hover:border-indigo-200 flex flex-col justify-between h-32 shadow-sm hover:shadow-md relative overflow-hidden"
              :class="{'opacity-50 pointer-events-none grayscale': product.stock <= 0}"
              @click="handleAddToCart(product)"
            >
              <div class="absolute inset-0 bg-indigo-50 opacity-0 group-hover:opacity-100 transition-opacity pointer-events-none"></div>
              <div class="relative z-10">
                <h3 class="font-semibold text-slate-900 text-sm sm:text-base leading-tight line-clamp-2 group-hover:text-indigo-900 transition-colors">{{ product.name }}</h3>
                <p class="text-xs text-slate-500 font-mono mt-1">{{ product.sku }}</p>
              </div>
              <div class="flex justify-between items-end mt-2 relative z-10">
                <span class="text-indigo-600 font-bold text-lg leading-none">${{ Number(product.price).toFixed(2) }}</span>
                <span class="text-xs font-medium px-2 py-0.5 rounded-full" :class="product.stock > 0 ? 'bg-emerald-50 text-emerald-700 border border-emerald-200' : 'bg-red-50 text-red-700 border border-red-200'">
                  {{ product.stock }} left
                </span>
              </div>
            </div>
            
            <!-- Empty State -->
            <div v-if="posStore.products.length === 0" class="col-span-full py-16 flex flex-col items-center justify-center text-center">
              <div class="w-16 h-16 bg-white rounded-full flex items-center justify-center mb-4 shadow-sm border border-slate-200">
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-8 h-8 text-slate-300"><path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-5.197-5.197m0 0A7.5 7.5 0 105.196 5.196a7.5 7.5 0 0010.607 10.607z" /></svg>
              </div>
              <h3 class="text-base font-semibold text-slate-900 mb-1">No products found</h3>
              <p class="text-sm text-slate-500 max-w-sm">Try adjusting your search query or scanning a different barcode.</p>
            </div>
          </template>
        </div>
      </div>
    </div>

    <!-- Cart & Checkout -->
    <div class="w-full lg:w-96 flex flex-col bg-white rounded-xl border border-slate-200 shadow-sm relative lg:h-full flex-shrink-0 h-[50vh] lg:h-auto">
      <div class="p-4 border-b border-slate-200 bg-slate-50/50 rounded-t-xl flex justify-between items-center">
        <h2 class="text-lg font-bold text-slate-900 flex items-center gap-2">
          <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-5 h-5 text-indigo-600"><path stroke-linecap="round" stroke-linejoin="round" d="M2.25 3h1.386c.51 0 .955.343 1.087.835l.383 1.437M7.5 14.25a3 3 0 00-3 3h15.75m-12.75-3h11.218c1.121-2.3 2.1-4.684 2.924-7.138a60.114 60.114 0 00-16.536-1.84M7.5 14.25L5.106 5.272M6 20.25a.75.75 0 11-1.5 0 .75.75 0 011.5 0zm12.75 0a.75.75 0 11-1.5 0 .75.75 0 011.5 0z" /></svg>
          Current Order
        </h2>
        <span class="text-xs font-semibold bg-indigo-100 text-indigo-700 px-2.5 py-1 rounded-full">{{ posStore.cart.length }} items</span>
      </div>

      <div class="p-3 sm:p-4 flex flex-col h-full overflow-hidden">
        <!-- Stock Warnings -->
        <div v-if="posStore.stockWarnings.length > 0" class="mb-4 space-y-2 flex-shrink-0">
          <div v-for="warning in posStore.stockWarnings" :key="warning.id" class="bg-amber-50 border border-amber-200 text-amber-800 px-3 py-2 rounded-lg text-xs font-medium flex items-start gap-2 shadow-sm">
            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor" class="w-4 h-4 mt-0.5 flex-shrink-0"><path fill-rule="evenodd" d="M8.485 2.495c.673-1.167 2.357-1.167 3.03 0l6.28 10.875c.673 1.167-.17 2.625-1.516 2.625H3.72c-1.347 0-2.189-1.458-1.515-2.625L8.485 2.495zM10 5a.75.75 0 01.75.75v3.5a.75.75 0 01-1.5 0v-3.5A.75.75 0 0110 5zm0 9a1 1 0 100-2 1 1 0 000 2z" clip-rule="evenodd" /></svg>
            {{ warning.message }}
          </div>
        </div>
        
        <div class="mb-4 space-y-3 flex-shrink-0">
          <select
            v-model="posStore.customerId"
            class="block w-full rounded-lg border-0 bg-slate-50 py-2 pl-3 pr-10 text-slate-900 shadow-inner ring-1 ring-inset ring-slate-200 focus:bg-white focus:ring-2 focus:ring-inset focus:ring-indigo-600 sm:text-sm sm:leading-6 transition-all"
          >
            <option value="">Walk-in Customer</option>
            <option v-for="c in posStore.customers" :key="c.id" :value="c.id">{{ c.name }}</option>
          </select>

          <select
            v-model="posStore.paymentMethod"
            class="block w-full rounded-lg border-0 bg-slate-50 py-2 pl-3 pr-10 text-slate-900 shadow-inner ring-1 ring-inset ring-slate-200 focus:bg-white focus:ring-2 focus:ring-inset focus:ring-indigo-600 sm:text-sm sm:leading-6 transition-all"
          >
            <option value="cash">Cash</option>
            <option value="card">Card</option>
            <option value="split">Split Payment</option>
            <option value="due">Mark as Due</option>
          </select>

          <div v-if="posStore.paymentMethod === 'split'" class="flex gap-2">
            <input
              type="number"
              v-model.number="posStore.splitCash"
              placeholder="Cash Amount"
              class="block w-1/2 rounded-lg border-0 bg-slate-50 py-2 px-3 text-slate-900 shadow-inner ring-1 ring-inset ring-slate-200 focus:bg-white focus:ring-2 focus:ring-inset focus:ring-indigo-600 sm:text-sm sm:leading-6 transition-all"
            />
            <input
              type="number"
              v-model.number="posStore.splitCard"
              placeholder="Card Amount"
              class="block w-1/2 rounded-lg border-0 bg-slate-50 py-2 px-3 text-slate-900 shadow-inner ring-1 ring-inset ring-slate-200 focus:bg-white focus:ring-2 focus:ring-inset focus:ring-indigo-600 sm:text-sm sm:leading-6 transition-all"
            />
          </div>
        </div>
        
        <!-- Cart Items List -->
        <div class="flex-1 overflow-y-auto space-y-2 min-h-0 pr-1 custom-scrollbar">
          <div v-for="item in posStore.cart" :key="item.id" class="flex justify-between items-center bg-white p-3 rounded-lg border border-slate-200 shadow-sm hover:border-indigo-100 transition-colors group">
            <div class="flex-1 min-w-0 pr-2">
              <h4 class="font-semibold text-slate-900 text-sm truncate" :title="item.name">{{ item.name }}</h4>
              <div class="flex items-center mt-2 space-x-2">
                <div class="flex items-center bg-slate-50 rounded-md border border-slate-200">
                  <button 
                    @click="posStore.updateQuantity(item.id, item.quantity - 1)"
                    class="w-7 h-7 flex items-center justify-center text-slate-600 hover:bg-slate-200 rounded-l-md transition-colors"
                  >-</button>
                  <span class="text-sm font-bold text-slate-900 w-8 text-center">{{ item.quantity }}</span>
                  <button 
                    @click="posStore.updateQuantity(item.id, item.quantity + 1)"
                    class="w-7 h-7 flex items-center justify-center text-slate-600 hover:bg-slate-200 rounded-r-md transition-colors"
                  >+</button>
                </div>
                <span class="text-xs text-slate-500 font-medium">@ ${{ Number(item.price).toFixed(2) }}</span>
              </div>
            </div>
            <div class="flex flex-col items-end flex-shrink-0">
              <span class="font-bold text-slate-900 tabular-nums">${{ (item.price * item.quantity).toFixed(2) }}</span>
              <button
                @click="posStore.removeFromCart(item.id)"
                class="text-[10px] uppercase tracking-wider text-red-500 hover:text-red-700 mt-2 font-bold px-2 py-1 rounded hover:bg-red-50 transition-colors opacity-0 group-hover:opacity-100 focus:opacity-100"
              >Remove</button>
            </div>
          </div>
          
          <div v-if="posStore.cart.length === 0" class="h-full flex flex-col items-center justify-center text-slate-400 py-10 opacity-70">
            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1" stroke="currentColor" class="w-16 h-16 mb-3"><path stroke-linecap="round" stroke-linejoin="round" d="M2.25 3h1.386c.51 0 .955.343 1.087.835l.383 1.437M7.5 14.25a3 3 0 00-3 3h15.75m-12.75-3h11.218c1.121-2.3 2.1-4.684 2.924-7.138a60.114 60.114 0 00-16.536-1.84M7.5 14.25L5.106 5.272M6 20.25a.75.75 0 11-1.5 0 .75.75 0 011.5 0zm12.75 0a.75.75 0 11-1.5 0 .75.75 0 011.5 0z" /></svg>
            <span class="text-sm font-medium">Cart is empty</span>
            <span class="text-xs mt-1">Scan or tap items to add</span>
          </div>
        </div>

        <!-- Checkout Summary -->
        <div class="border-t border-slate-200 pt-3 mt-3 space-y-2.5 flex-shrink-0 bg-white">
          <div class="flex justify-between text-sm text-slate-500">
            <span>Subtotal</span>
            <span class="font-medium text-slate-700">${{ posStore.cartSubtotal.toFixed(2) }}</span>
          </div>
          <div class="flex justify-between text-sm text-slate-500 items-center group">
            <span>Discount</span>
            <div class="flex items-center">
              <span class="text-slate-400 mr-1 opacity-0 group-hover:opacity-100 transition-opacity">- $</span>
              <input
                v-if="authStore.hasRole('cashier') || authStore.hasRole('admin')"
                type="number"
                v-model.number="posStore.discount"
                class="w-20 rounded-md border-0 bg-slate-50 py-1 px-2 text-slate-900 text-right shadow-inner ring-1 ring-inset ring-slate-200 focus:bg-white focus:ring-2 focus:ring-inset focus:ring-indigo-600 sm:text-sm transition-all"
              />
              <span v-else class="text-slate-700 font-medium">${{ posStore.discount.toFixed(2) }}</span>
            </div>
          </div>
          <div class="flex justify-between text-xl font-bold text-slate-900 mt-3 pt-3 border-t border-slate-200">
            <span>Total</span>
            <span class="text-indigo-600">${{ posStore.cartTotal.toFixed(2) }}</span>
          </div>
          
          <div v-if="posStore.paymentMethod === 'split'" class="flex justify-between text-xs text-slate-500 mt-1 bg-slate-50 p-2 rounded border border-slate-100">
            <span>Split Check:</span>
            <span :class="{'text-red-600 font-bold': (posStore.splitCash + posStore.splitCard) !== posStore.cartTotal}" class="font-semibold tabular-nums">
              ${{ (posStore.splitCash + posStore.splitCard).toFixed(2) }}
            </span>
          </div>

          <button 
            class="w-full mt-4 bg-indigo-600 hover:bg-indigo-700 text-white font-bold py-3.5 rounded-xl disabled:opacity-50 disabled:hover:bg-indigo-600 transition-all shadow-sm hover:shadow flex justify-center items-center gap-2 text-lg"
            :disabled="posStore.cart.length === 0"
            @click="processPaymentAndPrint"
          >
            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" class="w-6 h-6"><path stroke-linecap="round" stroke-linejoin="round" d="M2.25 8.25h19.5M2.25 9h19.5m-16.5 5.25h6m-6 2.25h3m-3.75 3h15a2.25 2.25 0 002.25-2.25V6.75A2.25 2.25 0 0019.5 4.5h-15a2.25 2.25 0 00-2.25 2.25v10.5A2.25 2.25 0 004.5 19.5z" /></svg>
            Pay & Print
          </button>
        </div>
      </div>
    </div>

    <!-- Hidden Receipt for Printing -->
    <div class="hidden print:block w-[80mm] p-4 text-black bg-white absolute top-0 left-0 z-50" id="receipt">
      <img src="/logo-dark.png" alt="Mamun Automobiles" class="h-10 mx-auto mb-2" />
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
import { useAuthStore } from '../../stores/auth';

const posStore = usePosStore();
const authStore = useAuthStore();
const searchInputRef = ref(null);
const isFullscreen = ref(false);
let searchTimeout = null;

const toggleFullscreen = () => {
  if (!document.fullscreenElement) {
    document.documentElement.requestFullscreen().catch(err => {
      console.error(`Error attempting to enable fullscreen: ${err.message}`);
    });
    isFullscreen.value = true;
  } else {
    document.exitFullscreen();
    isFullscreen.value = false;
  }
};

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
  if (!authStore.hasPermission('pos.access')) return;
  
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
  const success = await posStore.processCheckout();
  if (success) {
    setTimeout(() => {
      window.print();
      posStore.clearCart();
    }, 300);
  } else {
    alert('Failed to process payment. Please try again.');
  }
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

<style scoped>
/* Custom scrollbar for cart items to keep UI clean */
.custom-scrollbar::-webkit-scrollbar {
  width: 4px;
}
.custom-scrollbar::-webkit-scrollbar-track {
  background: transparent;
}
.custom-scrollbar::-webkit-scrollbar-thumb {
  background-color: #cbd5e1;
  border-radius: 20px;
}
.custom-scrollbar:hover::-webkit-scrollbar-thumb {
  background-color: #94a3b8;
}

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
