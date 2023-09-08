import Vue from 'vue';
import BaseModal from '../modals/BaseModal/BaseModal.vue';

export interface BaseModalMethods {
    show(params: any): void;
    hide(): void;
}

const Modal = {
    EventBus: new Vue(),
    install(Vue: Vue.VueConstructor) {
        Vue.component('base-modal', BaseModal);
        Vue.prototype.$modal = {
            show(params: any) {
                Modal.EventBus.$emit('show', params);
            },
            hide() {
                Modal.EventBus.$emit('hide');
            },
        };
    },
};

export default Modal;
