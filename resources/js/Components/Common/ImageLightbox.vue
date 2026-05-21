<script setup lang="ts">
import { ref, watch, onMounted, onUnmounted } from 'vue';
import { XMarkIcon, ChevronLeftIcon, ChevronRightIcon } from '@heroicons/vue/24/outline';

const props = defineProps<{
    images: string[];
    index: number | null;
}>();

const emit = defineEmits<{ close: [] }>();

const current = ref(0);

watch(() => props.index, (val) => {
    if (val !== null) current.value = val;
});

const prev = () => { current.value = (current.value - 1 + props.images.length) % props.images.length; };
const next = () => { current.value = (current.value + 1) % props.images.length; };

const onKey = (e: KeyboardEvent) => {
    if (props.index === null) return;
    if (e.key === 'ArrowLeft')  prev();
    if (e.key === 'ArrowRight') next();
    if (e.key === 'Escape')     emit('close');
};

onMounted(() => window.addEventListener('keydown', onKey));
onUnmounted(() => window.removeEventListener('keydown', onKey));
</script>

<template>
    <Transition
        enter-active-class="transition duration-150"
        enter-from-class="opacity-0"
        leave-active-class="transition duration-150"
        leave-to-class="opacity-0"
    >
        <div
            v-if="index !== null && images.length > 0"
            class="fixed inset-0 z-[100] bg-black/90 flex items-center justify-center"
            @click.self="emit('close')"
        >
            
            <button
                @click="emit('close')"
                class="absolute top-4 right-4 p-2 rounded-full bg-white/10 hover:bg-white/20 text-white transition-colors"
            >
                <XMarkIcon class="w-6 h-6" />
            </button>

            
            <button
                v-if="images.length > 1"
                @click="prev"
                class="absolute left-4 p-3 rounded-full bg-white/10 hover:bg-white/20 text-white transition-colors"
            >
                <ChevronLeftIcon class="w-6 h-6" />
            </button>

            
            <img
                :src="`/storage/${images[current]}`"
                class="max-h-[85vh] max-w-[90vw] object-contain rounded-lg select-none"
                draggable="false"
            />

            
            <button
                v-if="images.length > 1"
                @click="next"
                class="absolute right-4 p-3 rounded-full bg-white/10 hover:bg-white/20 text-white transition-colors"
            >
                <ChevronRightIcon class="w-6 h-6" />
            </button>

            
            <div
                v-if="images.length > 1"
                class="absolute bottom-5 left-1/2 -translate-x-1/2 text-white/70 text-sm font-medium tabular-nums"
            >
                {{ current + 1 }} / {{ images.length }}
            </div>
        </div>
    </Transition>
</template>
