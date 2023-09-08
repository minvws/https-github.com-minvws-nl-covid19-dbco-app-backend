const showToast = (message: string, toastId: string, isError = false, toaster = 'b-toaster-top-center'): void => {
    if (typeof window === 'undefined') {
        return;
    }
    const { app } = window;
    const created = app.$createElement;
    // Create the message
    const vNodesMsg = created('div', { class: ['d-flex', 'align-items-center', 'justify-content-sm-between'] }, [
        created('p', { class: ['text-left', 'mb-0'] }, message),
        created(
            'button',
            {
                attrs: {
                    ariaLabel: 'Close',
                },
                class: 'close',
                on: { click: () => app.$bvToast.hide(toastId) },
            },
            '×'
        ),
    ]);

    app.$bvToast.toast([vNodesMsg], {
        autoHideDelay: 3000,
        bodyClass: isError ? 'error' : '',
        id: toastId,
        isStatus: true,
        noCloseButton: true,
        solid: true,
        toaster: toaster,
    });
};

export default showToast;
