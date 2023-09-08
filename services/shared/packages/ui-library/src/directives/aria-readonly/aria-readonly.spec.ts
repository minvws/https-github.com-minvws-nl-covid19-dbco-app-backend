import { mount } from '@vue/test-utils';
import { defineComponent } from 'vue';
import { createDefaultLocalVue } from '../../test/local-vue';
import { uniqueId } from 'lodash';

const TestComponent = defineComponent({
    props: {
        checked: Boolean,
        readOnly: Boolean,
    },
    template: `
    <div>
        <input v-aria-readonly="readOnly" type="checkbox" />
    </div>`,
});

type Props = {
    readOnly: boolean;
};

function createComponent(propsData?: Props) {
    const div = document.createElement('div');
    div.id = uniqueId('root-');
    document.body.appendChild(div);

    return mount(TestComponent as any, {
        localVue: createDefaultLocalVue(),
        propsData,
        attachTo: `#${div.id}`,
    });
}

describe('v-aria-readonly', () => {
    it('should not disable element interactivity when not read-only', async () => {
        const input = createComponent({ readOnly: false }).find('input');
        const preventDefault = vi.fn();

        await input.trigger('mousedown', { preventDefault });
        await input.trigger('click', { preventDefault });

        expect(preventDefault).not.toHaveBeenCalled();
    });

    it('should disable element click behavior when read-only, but still set the focus', async () => {
        const input = createComponent({ readOnly: true }).find('input');
        const preventDefault = vi.fn();

        await input.trigger('mousedown', { preventDefault });
        await input.trigger('click', { preventDefault });

        expect(input.element).toBe(document.activeElement);

        expect(preventDefault).toHaveBeenCalledTimes(2);
    });

    it('should disable element key behaviors when read-only', async () => {
        const input = createComponent({ readOnly: true }).find('input');
        const preventDefault = vi.fn();

        await input.trigger('keydown', { preventDefault, key: ' ' });
        await input.trigger('keydown', { preventDefault, key: 'ArrowUp' });
        await input.trigger('keydown', { preventDefault, key: 'ArrowDown' });

        expect(preventDefault).toHaveBeenCalledTimes(3);
    });

    it('should be able to switch from read-only back to read', async () => {
        const wrapper = createComponent({ readOnly: true });
        const input = wrapper.find('input');
        const preventDefault = vi.fn();

        await input.trigger('click', { preventDefault });
        expect(input.attributes()['aria-readonly']).toBe('true');
        expect(preventDefault).toHaveBeenCalledTimes(1);

        await wrapper.setProps({ readOnly: false });

        await input.trigger('click', { preventDefault });
        expect(input.attributes()['aria-readonly']).toBe(undefined);
        expect(preventDefault).toHaveBeenCalledTimes(1);

        await wrapper.setProps({ readOnly: true });

        await input.trigger('click', { preventDefault });
        expect(input.attributes()['aria-readonly']).toBe('true');
        expect(preventDefault).toHaveBeenCalledTimes(2);
    });

    it('should clean up the listeners on unmount', async () => {
        const wrapper = createComponent({ readOnly: true });
        const input = wrapper.find('input');

        const removeEventListener = vi.spyOn(input.element, 'removeEventListener');
        await wrapper.destroy();

        expect(removeEventListener).toHaveBeenCalledWith('click', expect.any(Function));
        expect(removeEventListener).toHaveBeenCalledWith('mousedown', expect.any(Function));
        expect(removeEventListener).toHaveBeenCalledWith('keydown', expect.any(Function));
    });
});
