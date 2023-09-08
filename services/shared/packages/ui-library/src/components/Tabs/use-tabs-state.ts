import type { Ref } from 'vue';
import { computed, getCurrentInstance, inject, provide, ref } from 'vue';
import type { EventBus } from '../../utils';
import { createEventBus } from '../../utils';
import { uniqueId } from 'lodash';
import type { TabClickEvent } from './types';

type TabEvents = {
    'tab-click': TabClickEvent;
};

export type TabVariant = 'pill' | 'underline';

type TabsContextProps = {
    index: Ref<number | undefined>;
    variant: Ref<TabVariant | undefined>;
};

const injectionKeys = {
    eventBus: Symbol('eventBus'),
    tabsId: Symbol('tabsId'),
    variant: Symbol('variant'),
    currentIndex: Symbol('currentIndex'),
};

export function provideTabsState({ index, variant }: TabsContextProps) {
    const isControlled = index.value !== undefined;
    const eventBus = createEventBus<TabEvents>();
    const currentIndex = isControlled ? index : ref(0);

    provide(injectionKeys.eventBus, eventBus);
    provide(injectionKeys.currentIndex, currentIndex);
    provide(injectionKeys.tabsId, uniqueId('tabs-'));
    provide(injectionKeys.variant, variant);

    return { eventBus, currentIndex, isControlled, variant };
}

type TabProps = {
    isActive?: Ref<boolean | null>;
};

export function injectTabsState({ isActive: controlledIsActive }: TabProps = {}) {
    const eventBus = inject(injectionKeys.eventBus, null) as EventBus<TabEvents> | null;
    const currentIndex = inject(injectionKeys.currentIndex, null) as Ref<number> | null;
    const tabsId = inject(injectionKeys.tabsId, null) as string | null;
    const variant = inject(injectionKeys.variant, ref('underline')) as Ref<TabVariant>;

    const tabIndex = (getCurrentInstance()?.proxy?.$parent?.$children?.length || 0) - 1;
    const isActive = computed(() => {
        if (controlledIsActive && controlledIsActive.value !== null) {
            return controlledIsActive.value;
        }
        return currentIndex && tabIndex === currentIndex.value;
    });

    const tabId = tabsId ? `${tabsId}-tab-${tabIndex}` : undefined;
    const tabPanelId = tabsId ? `${tabsId}-tab-panel-${tabIndex}` : undefined;

    return { eventBus, currentIndex, tabIndex, isActive, tabId, tabPanelId, variant };
}
