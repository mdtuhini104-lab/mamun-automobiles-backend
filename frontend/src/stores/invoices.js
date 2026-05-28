import { defineStore } from 'pinia';
import api from '../services/api';
import { useToastStore } from './toast';

export const useInvoiceStore = defineStore('invoices', {
  state: () => ({
    invoices: [],
    pagination: {
      current_page: 1,
      last_page: 1,
      total: 0,
      per_page: 15
    },
    filters: {
      search: '',
      status: '',
      sort: 'created_at',
      direction: 'desc'
    },
    loading: false,
    error: null,
    
    // Payment Modal State
    paymentModalOpen: false,
    selectedInvoice: null,
    paymentAmount: 0,
    paymentMethod: 'cash',
    payingIds: [] // Track processing payments to prevent double-click
  }),
  actions: {
    async fetchInvoices(page = 1) {
      this.loading = true;
      this.error = null;
      try {
        const params = {
          page,
          search: this.filters.search,
          status: this.filters.status,
          sort: this.filters.sort,
          direction: this.filters.direction
        };
        const response = await api.get('/invoices', { params });
        
        const data = response.data.data;
        if (data.data && data.current_page) {
          this.invoices = data.data;
          this.pagination = {
            current_page: data.current_page,
            last_page: data.last_page,
            total: data.total,
            per_page: data.per_page
          };
        } else {
          this.invoices = data;
        }
      } catch (err) {
        this.error = 'Failed to fetch invoices.';
        console.error(err);
      } finally {
        this.loading = false;
      }
    },
    setFilter(key, value) {
      this.filters[key] = value;
      this.fetchInvoices(1);
    },
    
    // Modal Management
    openPaymentModal(invoice) {
      this.selectedInvoice = { ...invoice };
      // Default payment amount to remaining due
      this.paymentAmount = invoice.due_amount || (invoice.total_amount - (invoice.paid_amount || 0));
      this.paymentMethod = 'cash';
      this.paymentModalOpen = true;
    },
    closePaymentModal() {
      this.paymentModalOpen = false;
      this.selectedInvoice = null;
    },
    
    // Safe Payment Workflow
    async submitPayment() {
      if (!this.selectedInvoice || this.paymentAmount <= 0) return false;
      
      const invoiceId = this.selectedInvoice.id;
      
      if (this.payingIds.includes(invoiceId)) return false; // duplicate prevention
      
      // Prevent stale modal submission if externally paid
      const currentInvoice = this.invoices.find(i => i.id === invoiceId);
      if (currentInvoice && currentInvoice.payment_status === 'paid') {
        const toast = useToastStore();
        toast.warning('This invoice was already fully paid.');
        this.closePaymentModal();
        return false;
      }

      this.payingIds.push(invoiceId);
      try {
        const payload = {
          amount: this.paymentAmount,
          method: this.paymentMethod
        };
        const res = await api.post(`/invoices/${invoiceId}/payments`, payload);
        
        // Update local state safely based on response or manually calculate
        if (currentInvoice) {
          currentInvoice.paid_amount = (parseFloat(currentInvoice.paid_amount || 0) + parseFloat(this.paymentAmount)).toFixed(2);
          currentInvoice.due_amount = Math.max(0, parseFloat(currentInvoice.total_amount) - parseFloat(currentInvoice.paid_amount)).toFixed(2);
          
          if (parseFloat(currentInvoice.due_amount) <= 0) {
            currentInvoice.payment_status = 'paid';
          } else {
            currentInvoice.payment_status = 'partial';
          }
        }
        
        this.closePaymentModal();
        return true;
      } catch (err) {
        console.error('Failed to submit payment', err);
        throw err;
      } finally {
        this.payingIds = this.payingIds.filter(id => id !== invoiceId);
      }
    },
    
    // Websocket state patch for external payments
    patchInvoicePayment(eventData) {
      if (!eventData || !eventData.invoice) return;
      
      const invoiceId = eventData.invoice.id || eventData.invoice_id;
      const status = eventData.invoice.payment_status || eventData.payment_status;
      const paidAmount = eventData.invoice.paid_amount || eventData.paid_amount;
      const dueAmount = eventData.invoice.due_amount || eventData.due_amount;
      
      const index = this.invoices.findIndex(i => i.id === invoiceId);
      if (index !== -1) {
        this.invoices[index].payment_status = status;
        if (paidAmount !== undefined) this.invoices[index].paid_amount = paidAmount;
        if (dueAmount !== undefined) this.invoices[index].due_amount = dueAmount;
        
        // If modal is open for this invoice and it's fully paid externally, safely close it
        if (this.selectedInvoice && this.selectedInvoice.id === invoiceId && status === 'paid') {
          this.closePaymentModal();
        } else if (this.selectedInvoice && this.selectedInvoice.id === invoiceId) {
          // Update modal due amount
          this.selectedInvoice.due_amount = dueAmount;
          this.paymentAmount = dueAmount;
        }
      }
    }
  }
});
