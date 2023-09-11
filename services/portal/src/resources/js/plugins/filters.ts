import { useFilters } from '@/filters/useFilters';
import type { PluginObject } from 'vue';

const FiltersPlugin: PluginObject<void> = {
    install(Vue) {
        Vue.prototype.$filters = useFilters();
    },
};

export default FiltersPlugin;
