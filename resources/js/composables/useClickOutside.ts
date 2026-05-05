import { onMounted, onBeforeUnmount, type Ref } from 'vue';

export function useClickOutside(elementRef: Ref<HTMLElement | null>, callback: () => void): void {
    const handler = (e: MouseEvent) => {
        if (!elementRef.value) return;
        if (elementRef.value.contains(e.target as Node)) return;
        callback();
    };

    onMounted(() => document.addEventListener('mousedown', handler));
    onBeforeUnmount(() => document.removeEventListener('mousedown', handler));
}
