import Modal from '@/components/plugins/modal';
import safeHtml from '@/directives/safeHtml';
import FiltersPlugin from '@/plugins/filters';
import TypingHelpers from '@/plugins/typings';
import VueFormulate from '@braid/vue-formulate';
import { registerDirectives } from '@dbco/ui-library';
import type { DecoratedWrapper } from '@dbco/ui-library/test';
import { flushCallStack, decorateWrapper } from '@dbco/ui-library/test';
import { faker } from '@faker-js/faker';
import type { RenderResult } from '@testing-library/vue';
import type { Wrapper, WrapperArray } from '@vue/test-utils';
import { createLocalVue } from '@vue/test-utils';
import BootstrapVue from 'bootstrap-vue';
import { PiniaVuePlugin } from 'pinia';
import VueMask from 'v-mask';
import type Vue from 'vue';
import { defineComponent, type ComponentCustomProperties, type VueConstructor } from 'vue';
import VueI18n from 'vue-i18n';
import Vuex from 'vuex';
import { compileToFunctions } from 'vue-template-compiler';

export { flushCallStack, decorateWrapper };

export type UntypedWrapper<T extends Vue = Vue> = Wrapper<T> & {
    // Currently the component types are not available outside the Vue component files.
    // To prevent (wrapper as any) in all the tests we provided this workaround.
    // eslint-disable-next-line @typescript-eslint/no-explicit-any
    vm: Wrapper<Vue>['vm'] & Record<string, any> & { $refs: Record<string, any> };
};

type WrapperOrRender<TWrapper extends Wrapper<Vue>> = TWrapper | RenderResult;

const routerLink = defineComponent({
    setup() {
        return { navigate: vi.fn() };
    },
    render: compileToFunctions('<div><slot href="/" :navigate="navigate" /></div>').render,
});

export function createDefaultLocalVue() {
    const localVue = createLocalVue();

    localVue.mixin({
        created() {
            // when the root element is created we set a global app reference
            if (!(this as Vue).$parent) {
                window.app = this as Vue & ComponentCustomProperties;
            }
        },
    });

    localVue.use(BootstrapVue);
    localVue.use(Modal);
    localVue.use(PiniaVuePlugin);
    localVue.use(Vuex);
    localVue.use(VueI18n);
    localVue.use(VueMask);
    localVue.use(VueFormulate);
    localVue.use(FiltersPlugin);
    localVue.use(TypingHelpers);

    localVue.directive('safe-html', safeHtml);

    localVue.component('router-link', routerLink);

    registerDirectives(localVue);

    return localVue;
}
/**
 * Use this setup function to create your testcomponent factory. It will help with:
 * - destroying the wrapper after the test (to prevent memory leaks)
 * - creating the localVue instance
 *
 * @param factory a function that takes the localvue and any custom arguments and returns a wrapper or render result
 * @returns a function that takes the custom arguments and returns a wrapper or render result. Use this function to create your components
 */
interface ISetupTest {
    <TProperties extends unknown[], TRenderResult extends Promise<RenderResult>>(
        factory: (localVue: VueConstructor, ...args: TProperties) => TRenderResult
    ): (...args: TProperties) => TRenderResult;
    <TProperties extends unknown[], TRenderResult extends RenderResult>(
        factory: (localVue: VueConstructor, ...args: TProperties) => TRenderResult
    ): (...args: TProperties) => TRenderResult;
    <TProperties extends unknown[], TComponent extends Vue, TWrapper extends Wrapper<TComponent>>(
        factory: (localVue: VueConstructor, ...args: TProperties) => TWrapper
    ): (...args: TProperties) => DecoratedWrapper<TWrapper> & UntypedWrapper;
    <TProperties extends unknown[], TComponent extends Vue, TWrapper extends Promise<Wrapper<TComponent>>>(
        factory: (localVue: VueConstructor, ...args: TProperties) => TWrapper
    ): (...args: TProperties) => Promise<DecoratedWrapper<Awaited<TWrapper>> & UntypedWrapper>;
}

let localVue: VueConstructor;
export const setupTest: ISetupTest = (factoryFunction: Parameters<ISetupTest>[0]) => {
    if (localVue) {
        throw new Error('Only one setup allowed per test file');
    }

    localVue = createDefaultLocalVue();
    let wrapperOrRender: WrapperOrRender<Awaited<ReturnType<typeof factoryFunction>>> | null;

    afterEach(() => {
        if (wrapperOrRender) {
            if (isRenderResult(wrapperOrRender)) {
                wrapperOrRender.unmount();
            } else {
                wrapperOrRender.destroy();
            }
        }
        wrapperOrRender = null;
    });

    afterEach(() => {
        vi.clearAllMocks();
    });

    return <TProperties extends unknown[]>(...args: TProperties) => {
        if (wrapperOrRender)
            throw new Error('Cannot create multiple wrappers in a single test case, try to use `it.each` instead');

        const newWrapperOrRender = factoryFunction(localVue, ...args);

        if (newWrapperOrRender instanceof Promise) {
            return newWrapperOrRender.then((resolvedWrapper) => {
                wrapperOrRender = resolvedWrapper;
                return isRenderResult(wrapperOrRender) ? wrapperOrRender : decorateWrapper(wrapperOrRender);
            });
        }

        wrapperOrRender = newWrapperOrRender;
        return isRenderResult(wrapperOrRender) ? wrapperOrRender : decorateWrapper(wrapperOrRender);
    };
};

function isRenderResult<TWrapper extends Wrapper<Vue>>(result: WrapperOrRender<TWrapper>): result is RenderResult {
    return !!(result as RenderResult)?.isUnmounted;
}

/**
 * Decorator for faker.
 * @returns faker, decorated with custom helpers.
 */
export const fakerjs = {
    ...faker,
    custom: {
        arrayOfUuids: (min = 1, max = 10) =>
            faker.helpers.uniqueArray(faker.string.uuid, faker.number.int({ min, max })),
        typedArray: <Type>(el: Type, min = 1, max = 10) => [...Array(faker.number.int({ min, max }))].map(() => el),
    },
};

/**
 * Creates a container in the document body.
 * @param {string} [tag=div] - The tag of the container
 * @returns {HTMLElement} The newly created container
 */
export const createContainer = (tag = 'div') => {
    const container = document.createElement(tag);
    document.body.appendChild(container);
    return container;
};

export function waitForElements(wrapper: Wrapper<Vue>, selector: string, timeout = 500): Promise<WrapperArray<Vue>> {
    return new Promise((resolve, reject) => {
        const start = Date.now();
        const interval = setInterval(() => {
            const elements = wrapper.findAll(selector);
            if (!elements.length) {
                if (Date.now() - start > timeout) {
                    clearInterval(interval);
                    reject(`Timeout of ${timeout}ms reached while looking for element ${selector}`);
                }

                return;
            }

            clearInterval(interval);
            resolve(elements);
        }, 10);
    });
}
