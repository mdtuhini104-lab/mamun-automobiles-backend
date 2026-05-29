import { defineStore } from 'pinia';
import api from '../services/api';
import { getEcho } from '../services/echo';
import { useToastStore } from './toast';

export const useNotificationStore = defineStore('notification', {
  state: () => ({
    notifications: [],
    unreadCount: 0,
    loading: false
  }),
  actions: {
    async fetchNotifications() {
      this.loading = true;
      try {
        const response = await api.get('/notifications');
        // The API returns notifications and unread_count
        this.notifications = response.data.notifications.data || response.data.notifications || [];
        this.unreadCount = response.data.unread_count ?? 0;
      } catch (error) {
        console.error('Failed to fetch notifications:', error);
      } finally {
        this.loading = false;
      }
    },
    async markAsRead(id) {
      const toast = useToastStore();
      try {
        const response = await api.put(`/notifications/${id}/read`);
        this.unreadCount = response.data.unread_count ?? Math.max(0, this.unreadCount - 1);
        
        // Update local notification state
        this.notifications = this.notifications.map(n => 
          n.id === id ? { ...n, read_at: new Date().toISOString() } : n
        );
        toast.info('Notification marked as read');
      } catch (error) {
        console.error('Failed to mark notification as read:', error);
      }
    },
    async clearReadNotifications() {
      const toast = useToastStore();
      try {
        const response = await api.delete('/notifications/clear');
        this.unreadCount = response.data.unread_count ?? 0;
        this.notifications = this.notifications.filter(n => !n.read_at);
        toast.success('Read notifications cleared');
      } catch (error) {
        console.error('Failed to clear read notifications:', error);
      }
    },
    setupRealtimeListener() {
      try {
        const echo = getEcho();
        if (!echo) return;

        // Listen on the public workshop-updates channel
        echo.channel('workshop-updates')
          .listen('.quotation.approved', (e) => this.handleIncomingRealtimeEvent(e, 'Quotation Approved'))
          .listen('.workorder.created', (e) => this.handleIncomingRealtimeEvent(e, 'Work Order Created'))
          .listen('.technician.assigned', (e) => this.handleIncomingRealtimeEvent(e, 'Technician Assigned'))
          .listen('.task.started', (e) => this.handleIncomingRealtimeEvent(e, 'Task Started'))
          .listen('.task.completed', (e) => this.handleIncomingRealtimeEvent(e, 'Task Completed'))
          .listen('.consumption.added', (e) => this.handleIncomingRealtimeEvent(e, 'Materials Consumed'))
          .listen('.vehicle.delivered', (e) => this.handleIncomingRealtimeEvent(e, 'Vehicle Delivered'));

        // Listen on financial-updates channel
        echo.channel('financial-updates')
          .listen('.invoice.generated', (e) => this.handleIncomingRealtimeEvent(e, 'Invoice Generated'));

      } catch (err) {
        console.error('Failed to initialize Reverb listener in store:', err);
      }
    },
    handleIncomingRealtimeEvent(eventData, title) {
      const toast = useToastStore();
      
      // Flash toast notification on screen
      toast.info(`${title}: Event received in realtime!`);
      
      // Auto-increment unread count and fetch latest to keep in sync
      this.unreadCount++;
      this.fetchNotifications();
    }
  }
});
