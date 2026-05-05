<script setup lang="ts">
import { ref } from 'vue';
import { router } from '@inertiajs/vue3';
import { toast } from 'vue-sonner';
import { useActiveRole } from '@/composables/useActiveRole';

const emit = defineEmits<{ switched: [] }>();

const { isMasterActive, isSeekerActive, isMaster, isSeeker } = useActiveRole();
const switching = ref(false);

function switchTo(role: 'master' | 'seeker') {
    if (switching.value) return;
    switching.value = true;

    router.post(route('role.switch'), { target_role: role }, {
        onFinish:  () => { switching.value = false; },
        onSuccess: () => emit('switched'),
        onError:   () => toast.error('Neizdevās pārslēgt lomu.'),
    });
}
</script>

<template>
    <div class="px-4 py-3 border-t border-gray-100">
        <p class="text-[10px] font-semibold text-gray-400 uppercase tracking-wider mb-2">Aktīvais skats</p>
        <div class="space-y-1">
            <button
                v-if="isMaster"
                @click="switchTo('master')"
                :disabled="switching"
                type="button"
                class="w-full flex items-center gap-2.5 px-3 py-2 rounded-lg text-sm transition-colors text-left disabled:opacity-60"
                :class="isMasterActive
                    ? 'bg-yellow-50 text-yellow-800 font-semibold'
                    : 'text-gray-600 hover:bg-gray-50'"
            >
                <span
                    class="w-3 h-3 rounded-full border-2 flex-shrink-0 transition-colors"
                    :class="isMasterActive ? 'bg-yellow-400 border-yellow-400' : 'border-gray-300'"
                />
                Meistars
                <span v-if="isMasterActive" class="ml-auto text-[10px] font-bold text-yellow-600 uppercase tracking-wide">Aktīvs</span>
            </button>

            <button
                v-if="isSeeker"
                @click="switchTo('seeker')"
                :disabled="switching"
                type="button"
                class="w-full flex items-center gap-2.5 px-3 py-2 rounded-lg text-sm transition-colors text-left disabled:opacity-60"
                :class="isSeekerActive
                    ? 'bg-emerald-50 text-emerald-800 font-semibold'
                    : 'text-gray-600 hover:bg-gray-50'"
            >
                <span
                    class="w-3 h-3 rounded-full border-2 flex-shrink-0 transition-colors"
                    :class="isSeekerActive ? 'bg-emerald-400 border-emerald-400' : 'border-gray-300'"
                />
                Meklētājs
                <span v-if="isSeekerActive" class="ml-auto text-[10px] font-bold text-emerald-600 uppercase tracking-wide">Aktīvs</span>
            </button>
        </div>
    </div>
</template>
