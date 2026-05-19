import { computed } from 'vue';
import { usePage } from '@inertiajs/vue3';

export function useActiveRole() {
    const page = usePage();

    const user = computed(() => page.props.auth?.user as any);
    const activeRole = computed(() => user.value?.active_role as 'master' | 'seeker' | null);
    const isMasterActive = computed(() => activeRole.value === 'master');
    const isSeekerActive = computed(() => activeRole.value === 'seeker');
    const isMaster = computed(() => user.value?.is_master ?? false);
    const isSeeker = computed(() => user.value?.is_seeker ?? false);
    const hasBothRoles = computed(() => user.value?.has_both_roles ?? false);

    const roleLabel = computed(() => isMasterActive.value ? 'Meistars' : 'Meklētājs');
    const accentTextClass = computed(() => isMasterActive.value ? 'text-yellow-400' : 'text-emerald-400');
    const accentBgClass = computed(() => isMasterActive.value ? 'bg-yellow-400 text-navy' : 'bg-emerald-400 text-white');
    const accentBorderClass = computed(() => isMasterActive.value ? 'border-b-yellow-400' : 'border-b-emerald-400');
    const accentDotClass = computed(() => isMasterActive.value ? 'bg-yellow-400' : 'bg-emerald-400');

    return {
        activeRole, isMasterActive, isSeekerActive,
        isMaster, isSeeker, hasBothRoles,
        roleLabel, accentTextClass, accentBgClass, accentBorderClass, accentDotClass,
    };
}
