<template>
  <div class="fixed top-24 right-5 z-[100] flex flex-col gap-3 pointer-events-none">
    
    <TransitionGroup name="toast">
      <div 
        v-for="notif in notifications" 
        :key="notif.id"
        class="pointer-events-auto min-w-[300px] max-w-md p-4 rounded-lg shadow-lg border-l-4 flex items-start gap-3 bg-white transform transition-all duration-300"
        :class="getTypeClasses(notif.type)"
      >
        <div class="mt-0.5">
            <font-awesome-icon :icon="getIcon(notif.type)" class="text-lg" />
        </div>

        <div class="flex-1 text-sm font-medium text-gray-700 break-words">
            {{ notif.message }}
        </div>

        <button @click="removeNotification(notif.id)" class="text-gray-400 hover:text-gray-600 transition">
            <font-awesome-icon icon="times" />
        </button>
      </div>
    </TransitionGroup>

  </div>
</template>

<script setup>
import { useNotifications } from '@/composables/useNotifications';
import { faCheckCircle, faExclamationCircle, faInfoCircle, faExclamationTriangle } from '@fortawesome/free-solid-svg-icons';

const { notifications, removeNotification } = useNotifications();

const getTypeClasses = (type) => {
    switch (type) {
        case 'success': return 'border-green-500 bg-green-50';
        case 'error': return 'border-red-500 bg-red-50';
        case 'warning': return 'border-yellow-500 bg-yellow-50';
        default: return 'border-blue-500 bg-blue-50';
    }
};

const getIcon = (type) => {
    switch (type) {
        case 'success': return faCheckCircle;
        case 'error': return faExclamationCircle;
        case 'warning': return faExclamationTriangle;
        default: return faInfoCircle;
    }
};
</script>

<style scoped>
/* Animācijas */
.toast-enter-active,
.toast-leave-active {
  transition: all 0.4s ease;
}
.toast-enter-from {
  opacity: 0;
  transform: translateX(30px);
}
.toast-leave-to {
  opacity: 0;
  transform: translateX(30px);
}
</style>