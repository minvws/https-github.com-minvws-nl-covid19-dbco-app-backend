// mocked `node_modules` module does not seem to work without this reset
// NOTE: This reset needs to be at the top of this file!
// @see https://github.com/vitest-dev/vitest/issues/1484#issuecomment-1155518865
vi.resetModules();

import { faker } from '@faker-js/faker';
import { mount } from '@vue/test-utils';
import type { AnimeParams } from 'animejs';
import anime from 'animejs';
import type { MockInstance } from 'vitest';
import { defineComponent, toRef } from 'vue';
import { createDefaultLocalVue } from '../../test/local-vue';
import { useCollapse } from './use-collapse';

vi.mock('animejs');

type Props = {
    collapsedSize?: number;
    isOpen?: boolean;
};

const ComponentA = defineComponent({
    template: `<div ref="collapseRef"><slot /></div>`,
    props: {
        collapsedSize: {
            type: Number,
        },
        isOpen: {
            type: Boolean,
        },
    },
    setup(props) {
        const { collapsedSize } = props;
        const isOpen = toRef(props, 'isOpen');
        const { collapseRef } = useCollapse({ collapsedSize, isOpen });
        return { collapseRef };
    },
});

function createComponent(propsData: Props = {}) {
    return mount(ComponentA as any, {
        localVue: createDefaultLocalVue(),
        propsData,
    });
}

afterEach(() => {
    vi.clearAllMocks();
});

describe('useCollapse', () => {
    it('renders without max-height if initially open', () => {
        const wrapper = createComponent({ isOpen: true });
        expect(wrapper.attributes().style).not.toContain('max-height');
    });

    it('renders with max-height if initially closed', () => {
        const wrapper = createComponent();
        expect(wrapper.attributes().style).toContain('max-height: 0px;');
    });

    it('renders with a max-height set to `collapsedSize` when configured', () => {
        const collapsedSize = faker.number.int();
        const wrapper = createComponent({ collapsedSize });
        expect(wrapper.attributes().style).toContain(`max-height: ${collapsedSize}px;`);
    });

    it('animation is triggered when collapsed prop changes', async () => {
        const wrapper = createComponent();
        await wrapper.setProps({ isOpen: true });
        expect(anime).toHaveBeenCalledTimes(1);
        await wrapper.setProps({ isOpen: false });
        expect(anime).toHaveBeenCalledTimes(2);
    });

    it('style is cleared after expanding animation is complete', async () => {
        (anime as unknown as MockInstance).mockImplementation((params: AnimeParams) => {
            if (params.complete) params.complete({} as any);
        });
        const wrapper = createComponent();
        expect(wrapper.attributes().style).toContain(`max-height: 0px;`);
        await wrapper.setProps({ isOpen: true });
        expect(wrapper.attributes().style).not.toContain(`max-height`);
    });

    it('should clean up on unmount', () => {
        const wrapper = createComponent();
        wrapper.destroy();
        expect(anime.remove).toHaveBeenCalledOnce();
    });
});
