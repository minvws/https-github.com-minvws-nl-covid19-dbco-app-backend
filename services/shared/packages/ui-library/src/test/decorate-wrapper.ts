import type { Wrapper, WrapperArray } from '@vue/test-utils';

function findByText(wrapper: Wrapper<any>, selector: string, text: RegExp | string) {
    const elements = wrapper.findAll(selector);
    const filter =
        typeof text === 'string'
            ? (element: Wrapper<any>) => element.text() === text
            : (element: Wrapper<any>) => element.text().match(text);
    return elements.filter(filter).at(0);
}

export type DecoratedWrapper<TWrapper extends Wrapper<Vue>> = TWrapper & {
    findByAriaLabel<el extends Element>(ariaLabel: string): Wrapper<Vue, el>;
    findByTestId<el extends Element>(testId: string): Wrapper<Vue, el>;
    findAllByTestId(testId: string): WrapperArray<Vue>;
    findByText(selector: string, text: RegExp | string): Wrapper<Vue>;
};

/**
 * This method adds some useful methods to the Vue test wrapper.
 * @see: https://test-utils.vuejs.org/api/#wrapper-methods
 */
export function decorateWrapper<TWrapper extends Wrapper<Vue>>(wrapper: TWrapper) {
    const decoratedWrapper: DecoratedWrapper<TWrapper> = wrapper as DecoratedWrapper<TWrapper>;
    decoratedWrapper.findByAriaLabel = (ariaLabel) => wrapper.find(`[aria-label="${ariaLabel}"]`);
    decoratedWrapper.findByTestId = (testId) => wrapper.find(`[data-testid="${testId}"]`);
    decoratedWrapper.findAllByTestId = (testId) => wrapper.findAll(`[data-testid="${testId}"]`);
    decoratedWrapper.findByText = (selector, text) => findByText(wrapper, selector, text);
    return decoratedWrapper;
}

export function decorateWrappers<TWrapper extends Wrapper<Vue>>(wrappers: TWrapper[]) {
    return wrappers.map((x) => decorateWrapper(x));
}
