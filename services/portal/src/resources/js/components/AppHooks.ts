const useFormulate = () => {
    if (!window.app.$formulate) {
        throw Error('Formulate not available');
    }

    return window.app.$formulate;
};

const useModal = () => {
    if (!window.app.$modal) {
        throw Error('Modal not available');
    }

    return window.app.$modal;
};

export { useFormulate, useModal };
