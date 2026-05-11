import { useI18n } from 'vue-i18n';
import dayjs from 'dayjs';

export function useLocale() {
    const { locale } = useI18n();

    function setLocale(lang: 'lv' | 'en') {
        locale.value = lang;
        dayjs.locale(lang);
        localStorage.setItem('meistari_locale', lang);
        document.documentElement.lang = lang;
    }

    return { locale, setLocale };
}
