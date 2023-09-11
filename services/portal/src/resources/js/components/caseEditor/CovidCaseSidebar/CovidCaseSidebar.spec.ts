import { shallowMount } from '@vue/test-utils';
import CovidCaseSidebar from './CovidCaseSidebar.vue';
import { createContainer, setupTest } from '@/utils/test';
import FormRenderer from '@/components/form/FormRenderer/FormRenderer.vue';
import Vuex from 'vuex';

import indexStore from '@/store/index/indexStore';
import type { VueConstructor } from 'vue';

let storeInstance: any = null;

const createComponent = setupTest((localVue: VueConstructor, props?: object, state: object = {}, isModal = false) => {
    const container = createContainer();
    const store = {
        ...indexStore,
        state: {
            ...(indexStore as any).state,
            ...state,
        },
        $refs: {
            sidebar: HTMLDivElement,
        },
    };

    Object.defineProperty(window, 'removeEventListener', {
        value: vi.fn(),
    });

    if (isModal) {
        // The full-screen-modal decides that this is a modal, so it needs to be in the DOM
        document.body.setAttribute('class', 'full-screen-modal');
    } else {
        // The navtabs element is necessary because CovidCaseSidebar needs it to reposition
        const navtabs = document.createElement('div');
        navtabs.setAttribute('id', 'navtabs');
        document.body.appendChild(navtabs);
    }

    localVue.component('FormRenderer', FormRenderer);

    storeInstance = new Vuex.Store({
        modules: {
            index: store,
        },
    });

    return shallowMount(CovidCaseSidebar, {
        localVue,
        propsData: props,
        store: storeInstance,
        attachTo: container,
    });
});

describe('CovidCaseSidebar.vue', () => {
    afterEach(() => {
        document.getElementsByTagName('html')[0].innerHTML = '';
    });

    it('should have the "collapsed" class when the toggle button has been clicked', async () => {
        const wrapper = createComponent({
            schema: [],
        });

        const collapseButton = wrapper.find('.toggle');
        await collapseButton.trigger('click');

        expect(wrapper.classes('collapsed')).toBe(true);
    });

    it('should have an icon with the "icon--collapse-open" when the sidebar is collapsed', async () => {
        const wrapper = createComponent({
            schema: [],
        });

        const collapseButton = wrapper.find('.toggle');
        await collapseButton.trigger('click');

        const buttonIcon = wrapper.find('.icon');

        expect(buttonIcon.classes('icon--collapse-open')).toBe(true);
    });

    it('should have an icon with the "icon--collapse-close" when the sidebar is not collapsed', () => {
        const wrapper = createComponent({
            schema: [],
        });

        const buttonIcon = wrapper.find('.icon');

        expect(buttonIcon.classes('icon--collapse-close')).toBe(true);
    });

    it('should call this.resize() on window.eventListener("resize") ', () => {
        const spyResize = vi.spyOn((CovidCaseSidebar as any).methods, 'resize');

        createComponent({
            schema: [],
        });
        spyResize.mockReset();

        window.dispatchEvent(new Event('resize'));
        expect(spyResize).toHaveBeenCalledTimes(1);
    });

    it('should call this.scroll() on window.eventListener("setHeight") ', () => {
        const spySetHeight = vi.spyOn((CovidCaseSidebar as any).methods, 'setHeight');

        createComponent({
            schema: [],
        });
        spySetHeight.mockReset();

        window.dispatchEvent(new Event('scroll'));
        expect(spySetHeight).toHaveBeenCalledTimes(1);
    });

    it('should fire this.setHeight() on scroll if an element with class ".full-screen-modal" is found', () => {
        const spySetHeight = vi.spyOn((CovidCaseSidebar as any).methods, 'setHeight');

        createComponent(
            {
                schema: [],
            },
            undefined,
            true
        );
        spySetHeight.mockReset();

        document.querySelector('.full-screen-modal')?.dispatchEvent(new Event('scroll'));
        expect(spySetHeight).toHaveBeenCalledTimes(1);
    });

    it('should add scroll/resize event listener', () => {
        window.addEventListener = vi.fn().mockImplementationOnce((event, callback) => {
            callback();
        });

        createComponent({
            schema: [],
        });

        expect(window.addEventListener).toBeCalledWith('scroll', expect.any(Function));
        expect(window.addEventListener).toBeCalledWith('resize', expect.any(Function));
    });

    it('should remove scroll/resize event listener', async () => {
        const wrapper = createComponent({
            schema: [],
        });

        await wrapper.destroy();

        expect(window.removeEventListener).toBeCalledWith('scroll', expect.any(Function));
        expect(window.removeEventListener).toBeCalledWith('resize', expect.any(Function));
    });

    it('should resize when `textAreaErrors` changes', async () => {
        const spyInit = vi.spyOn((CovidCaseSidebar as any).methods, 'init');

        createComponent({
            schema: [],
        });

        // Reset spy
        spyInit.mockReset();

        await storeInstance.dispatch('index/CHANGE', {
            path: 'errors',
            values: {
                general: {
                    notes: 'New error',
                },
            },
        });

        expect(spyInit).toHaveBeenCalledTimes(1);
    });
});
