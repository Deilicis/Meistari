import { ref, computed } from 'vue';
import axios from 'axios';
import { toast } from 'vue-sonner';
import type { Notification } from '@/types/notification';

export function useNotifications() {
    const notifications = ref<Notification[]>([]);
    const loading = ref(false);

    const unreadCount = computed(() =>
        notifications.value.filter(n => !n.is_read).length
    );

    async function fetchNotifications() {
        loading.value = true;
        try {
            const res = await axios.get(route('notifications.index'));
            notifications.value = res.data.notifications;
        } finally {
            loading.value = false;
        }
    }

    async function markAsRead(id: number) {
        await axios.post(route('notifications.read', id));
        const n = notifications.value.find(n => n.id === id);
        if (n) {
            n.is_read = true;
            n.read_at = new Date().toISOString();
        }
    }

    async function markAllAsRead() {
        await axios.post(route('notifications.read-all'));
        notifications.value.forEach(n => {
            n.is_read = true;
            n.read_at = new Date().toISOString();
        });
    }

    async function deleteNotification(id: number) {
        await axios.delete(route('notifications.destroy', id));
        notifications.value = notifications.value.filter(n => n.id !== id);
    }

    function subscribeToRealtime(userId: number) {
        const echo = (window as any).Echo;
        if (!echo) return;

        // Drop any existing listener before re-subscribing so remounts don't stack listeners.
        echo.leaveChannel(`private-notifications.${userId}`);

        echo.private(`notifications.${userId}`)
            .listen('.NotificationCreated', (notification: Notification) => {
                if (notifications.value.find(n => n.id === notification.id)) return;
                notifications.value.unshift(notification);
                toast.info(notification.title, {
                    description: notification.body ?? undefined,
                    duration: 4000,
                });
            });
    }

    // Jāizsauc onUnmounted, lai novērstu atstātos klausītājus komponenta atkārtotas izmantošanas gadījumā.
    function unsubscribe(userId: number) {
        (window as any).Echo?.leave(`notifications.${userId}`);
    }

    return {
        notifications,
        unreadCount,
        loading,
        fetchNotifications,
        markAsRead,
        markAllAsRead,
        deleteNotification,
        subscribeToRealtime,
        unsubscribe,
    };
}
