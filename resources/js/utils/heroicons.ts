import * as outlineIcons from '@heroicons/vue/24/outline';

export function resolveIcon(name: string | null | undefined): any {
    if (!name) return outlineIcons.QuestionMarkCircleIcon;
    return (outlineIcons as Record<string, any>)[name] ?? outlineIcons.QuestionMarkCircleIcon;
}
