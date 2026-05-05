import { onMounted, onBeforeUnmount } from 'vue';

export function useEscapeKey(callback: () => void, isActive?: () => boolean): void {
    const handler = (e: KeyboardEvent) => {
        if (e.key !== 'Escape') return;
        if (isActive && !isActive()) return;
        callback();
    };

    onMounted(() => document.addEventListener('keydown', handler));
    onBeforeUnmount(() => document.removeEventListener('keydown', handler));
}
