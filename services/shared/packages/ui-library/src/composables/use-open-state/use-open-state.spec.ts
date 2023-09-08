import { useOpenState } from './use-open-state';

describe('useOpenState', () => {
    it('renders with isOpen initially set to false', () => {
        const { isOpen } = useOpenState();
        expect(isOpen.value).toBe(false);
    });

    it('toggles isOpen when "open" and "close" methods are triggered', () => {
        const { isOpen, close, open } = useOpenState();
        open();
        expect(isOpen.value).toBe(true);
        close();
        expect(isOpen.value).toBe(false);
    });

    it('triggers given "onClose" callback function when "close" method is triggered', () => {
        const onCloseCallbackMock = vi.fn();
        const { close } = useOpenState({ onClose: () => onCloseCallbackMock() });
        close();
        expect(onCloseCallbackMock).toHaveBeenCalledTimes(1);
    });
});
