import { tailwindMergeDirective } from './tw-merge/tw-merge';
import { ariaReadonlyDirective } from './aria-readonly/aria-readonly';

import type { VueConstructor } from 'vue';

export function registerDirectives(vue: VueConstructor) {
    vue.directive('tw-merge', tailwindMergeDirective);
    vue.directive('aria-readonly', ariaReadonlyDirective);
}
