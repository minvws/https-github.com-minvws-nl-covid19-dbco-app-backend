import { mount } from '@vue/test-utils';
import { createDefaultLocalVue } from '../../test/local-vue';
import ButtonOrLink from './ButtonOrLink.vue';

type Props = {
    href: string;
    rel: string;
    target: string;
    ariaLabel: string;
    to: string;
    disabled: boolean;
    type: 'button' | 'submit' | 'reset';
};

function createComponent(propsData?: Partial<Props>) {
    return mount(ButtonOrLink, {
        localVue: createDefaultLocalVue(),
        propsData,
        slots: { default: 'Label' },
        stubs: {
            'router-link': true,
        },
    });
}

describe('ButtonOrLink.vue', () => {
    it.each<[Partial<Props>, string, Record<string, string | boolean>]>([
        [{}, 'BUTTON', { type: 'button' }],
        [{}, 'BUTTON', { type: 'button' }],
        [{ type: 'submit' }, 'BUTTON', { type: 'submit' }],
        [{ href: '' }, 'A', { href: '' }],
        [
            { href: '', target: '_blank', ariaLabel: 'aria label' },
            'A',
            { href: '', target: '_blank', ['aria-label']: 'aria label', rel: 'noopener' },
        ],
        [{ to: '' }, 'ROUTER-LINK-STUB', { to: '' }],
        [
            { to: '', target: '_blank', ariaLabel: 'aria label' },
            'ROUTER-LINK-STUB',
            { to: '', target: '_blank', ['aria-label']: 'aria label', rel: 'noopener' },
        ],
        [{ href: '', disabled: true }, 'SPAN', { disabled: 'disabled' }],
        [{ to: '', disabled: true }, 'SPAN', { disabled: 'disabled' }],
        [{ disabled: true }, 'BUTTON', { disabled: 'disabled', type: 'button' }],
    ])('should render properties %j as element %j and with attributes %j', (props, expectedTag, expectedAttributes) => {
        const wrapper = createComponent(props);

        expect(wrapper.element.tagName).toBe(expectedTag);
        expect(wrapper.attributes()).toEqual(expectedAttributes);
    });

    it('should log a warning if the target is _blank and no aria-label has been set', async () => {
        const spy = vi.spyOn(console, 'warn').mockImplementation(() => {});
        createComponent({ target: '_blank' });
        expect(spy.mock.calls[0][0]).toBe(
            `ButtonOrLink :: Outgoing links (target="_blank") must have an \`aria-label\` set!`
        );
        spy.mockRestore();
    });

    it('should log a warning if both href and to properties are used', async () => {
        const spy = vi.spyOn(console, 'warn').mockImplementation(() => {});
        createComponent({ to: '/', href: '/' });
        expect(spy.mock.calls[0][0]).toBe(
            `ButtonOrLink :: Can not use both \`href\` and \`to\` props at the same time, \`href\` will be ignored!`
        );
        spy.mockRestore();
    });
});
