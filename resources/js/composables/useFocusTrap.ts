import { onMounted, onBeforeUnmount, watch, type Ref } from 'vue';

const FOCUSABLE_SELECTOR = [
    'a[href]',
    'button:not([disabled])',
    'input:not([disabled])',
    'select:not([disabled])',
    'textarea:not([disabled])',
    '[tabindex]:not([tabindex="-1"])',
].join(',');

export function useFocusTrap(containerRef: Ref<HTMLElement | null>, isActive: Ref<boolean>): void {
    let previouslyFocused: HTMLElement | null = null;

    const getFocusable = (): HTMLElement[] => {
        if (!containerRef.value) return [];
        return Array.from(containerRef.value.querySelectorAll<HTMLElement>(FOCUSABLE_SELECTOR))
            .filter(el => !el.hasAttribute('disabled') && el.offsetParent !== null);
    };

    const handler = (e: KeyboardEvent) => {
        if (!isActive.value || e.key !== 'Tab') return;
        const focusable = getFocusable();
        if (focusable.length === 0) return;

        const first = focusable[0];
        const last = focusable[focusable.length - 1];
        const active = document.activeElement as HTMLElement;

        if (e.shiftKey && active === first) {
            e.preventDefault();
            last.focus();
        } else if (!e.shiftKey && active === last) {
            e.preventDefault();
            first.focus();
        }
    };

    watch(isActive, (active) => {
        if (active) {
            previouslyFocused = document.activeElement as HTMLElement;
            requestAnimationFrame(() => {
                const focusable = getFocusable();
                focusable[0]?.focus();
            });
        } else {
            previouslyFocused?.focus();
            previouslyFocused = null;
        }
    });

    onMounted(() => document.addEventListener('keydown', handler));
    onBeforeUnmount(() => document.removeEventListener('keydown', handler));
}
