import { useI18n } from 'vue-i18n';

export function formatCurrency(amount: number | null | undefined): string {
    if (amount == null) return '-';
    const { locale } = useI18n();
    return new Intl.NumberFormat(locale.value === 'lv' ? 'lv-LV' : 'en-GB', {
        style: 'currency',
        currency: 'EUR',
        minimumFractionDigits: 0,
        maximumFractionDigits: 2,
    }).format(amount);
}

export function formatDate(date: string | null | undefined): string {
    if (!date) return '-';
    const { locale } = useI18n();
    return new Intl.DateTimeFormat(locale.value === 'lv' ? 'lv-LV' : 'en-GB', {
        year: 'numeric',
        month: 'long',
        day: 'numeric',
    }).format(new Date(date));
}
