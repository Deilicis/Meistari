import { ref } from 'vue';

const notifications = ref([]);

export function useNotifications() {
    
    //pievienošana
    const notify = (message, type = 'info', duration = 4000) => {
        const id = Date.now() + Math.random(); // Unikāls ID
        
        notifications.value.push({
            id,
            message,
            type
        });

        // Automātiski dzēst 
        if (duration > 0) {
            setTimeout(() => {
                removeNotification(id);
            }, duration);
        }
    };

    const removeNotification = (id) => {
        notifications.value = notifications.value.filter(n => n.id !== id);
    };

    return {
        notifications,
        notify,
        removeNotification
    };
}