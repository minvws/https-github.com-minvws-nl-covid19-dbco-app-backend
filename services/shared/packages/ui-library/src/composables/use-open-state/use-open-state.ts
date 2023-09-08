import { ref } from 'vue';

interface Config {
    onClose?: () => void;
}

export const useOpenState = ({ onClose }: Config = {}) => {
    const isOpen = ref(false);
    const close = () => {
        if (typeof onClose === 'function') {
            onClose();
        }
        isOpen.value = false;
    };
    const open = () => {
        isOpen.value = true;
    };
    return { isOpen, close, open };
};
