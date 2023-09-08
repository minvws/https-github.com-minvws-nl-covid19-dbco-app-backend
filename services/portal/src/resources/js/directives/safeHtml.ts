import { isSafeHtml } from '@/utils/safeHtml';
import type { VNodeDirective } from 'vue';

const updateElement = (el: HTMLElement, binding: VNodeDirective) => {
    if (isSafeHtml(binding.value)) {
        el.innerHTML = binding.value.html;
    } else {
        el.innerText = binding.value;
    }
};

export default {
    inserted: updateElement,
    update: updateElement,
};
