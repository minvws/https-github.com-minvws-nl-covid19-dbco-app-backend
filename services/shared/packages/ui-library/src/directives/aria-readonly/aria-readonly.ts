import type { ObjectDirective } from 'vue';

function handleMouseEvents(event: Event) {
    event.preventDefault();
    if (event.target instanceof HTMLElement) {
        event.target.focus();
    }
}

function handleKeyDown(event: KeyboardEvent) {
    switch (event.key) {
        case 'ArrowUp':
        case 'ArrowDown':
        case ' ':
            event.preventDefault();
            break;
        default:
        // nothing
    }
}

function enableReadOnly(element: HTMLElement) {
    if (!element) return;

    element.setAttribute('aria-readonly', 'true');
    element.addEventListener('click', handleMouseEvents);
    element.addEventListener('mousedown', handleMouseEvents);
    element.addEventListener('keydown', handleKeyDown);
}

function disableReadOnly(element: HTMLElement) {
    if (!element) return;

    element.removeAttribute('aria-readonly');
    element.removeEventListener('click', handleMouseEvents);
    element.removeEventListener('mousedown', handleMouseEvents);
    element.removeEventListener('keydown', handleKeyDown);
}

export const ariaReadonlyDirective: ObjectDirective<HTMLElement> = {
    bind(element, binding) {
        if (binding.value) {
            enableReadOnly(element);
        }
    },
    update(element, binding) {
        if (binding.value === binding.oldValue) return;

        if (binding.value) {
            enableReadOnly(element);
        } else {
            disableReadOnly(element);
        }
    },
    unbind(element) {
        disableReadOnly(element);
    },
};
