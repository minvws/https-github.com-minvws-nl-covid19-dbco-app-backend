<template>
    <div v-if="isOffline" class="timeout-alert-wrapper">
        <div class="timeout-alert form-font" data-testid="offline-error-alert">
            <ErrorOutlineIcon class="svg-icon" />
            <span class="pl-2">
                Geen verbinding. Controleer je internet-instellingen of VPN-instellingen.
                <button type="button" data-testid="offline-error-alert-more-info" @click="modalVisible = true">
                    Meer informatie
                </button>
            </span>
        </div>

        <BModal
            data-testid="offline-error-modal"
            title="Verbindingsproblemen"
            okTitle="Annuleren"
            okVariant="outline-primary"
            @ok="modalVisible = false"
            ok-only
            :visible="modalVisible"
        >
            <FormInfo
                text="Het lijkt erop dat je geen of slechte internetverbinding hebt. Je kunt nu het volgende doen:"
                infoType="info"
            />
            <ul class="pt-3 pl-4">
                <li>Controleer of je internetverbinding hebt. Kun je bijvoorbeeld een andere website bezoeken?</li>
                <li>Werk je vanuit huis? Check of de GGD VPN-verbinding actief is.</li>
            </ul>
            <p>Kom je er niet uit? Neem contact op met een key-user of ambassadeur van je eigen organisatie.</p>
        </BModal>
    </div>
</template>
<script lang="ts">
import type { CancelTokenSource } from 'axios';
import axios from 'axios';
import { storeToRefs } from 'pinia';
import { useAppStore } from '@/store/app/appStore';
import { defineComponent, ref, watch, onUnmounted, onMounted } from 'vue';
import FormInfo from '@/components/form/FormInfo/FormInfo.vue';
import ErrorOutlineIcon from '@icons/error-outline.svg?vue';

export default defineComponent({
    name: 'DbcoTimeoutAlert',
    components: {
        FormInfo,
        ErrorOutlineIcon,
    },
    setup() {
        let timeout = -1;
        let pingRequest: CancelTokenSource | null;
        const modalVisible = ref(false);

        const appStore = useAppStore();
        const { setOffline } = appStore;
        const { isOffline } = storeToRefs(appStore);

        const handleOffline = () => setOffline(true);
        const handleOnline = () => setOffline(false);

        const ping = async () => {
            pingRequest = axios.CancelToken.source();

            try {
                const { status, data } = await axios.get('/ping', {
                    cancelToken: pingRequest.token,
                    withCredentials: false,
                });

                if (status === 200 && data === 'PONG') {
                    handleOnline();
                } else {
                    timeout = window.setTimeout(ping, 3000);
                }
            } catch (error) {
                if (!axios.isCancel(error)) throw error;
            }

            pingRequest = null;
        };

        watch(isOffline, (newHasTimeout) => {
            if (newHasTimeout) {
                timeout = window.setTimeout(ping, 3000);
            } else {
                clearTimeout(timeout);
            }
        });

        onMounted(() => {
            window.addEventListener('offline', handleOffline);
            window.addEventListener('online', handleOnline);
        });

        onUnmounted(() => {
            if (pingRequest) pingRequest.cancel('cancelled due to unmount');
            clearTimeout(timeout);
            window.removeEventListener('offline', handleOffline);
            window.removeEventListener('online', handleOnline);
        });

        return {
            isOffline,
            modalVisible,
        };
    },
});
</script>

<style lang="scss" scoped>
@import './resources/scss/_variables.scss';

.timeout-alert-wrapper {
    display: flex;
    justify-content: center;
    align-items: flex-start;
    position: fixed;
    width: 100%;
    height: 100%;
    top: 0;
    left: 0;
    right: 0;
    bottom: 0;
    background-color: rgba(0, 0, 0, 0.25);
    z-index: 1040;

    .timeout-alert {
        align-items: center;
        width: 825px;
        font-size: 14px;
        font-weight: 400;
        line-height: 20px;
        border-radius: $border-radius-small;
        background: #fee6ea;
        color: $bco-red;
        padding: 0.875rem 0.875rem 0.875rem 1.375rem;
        margin-top: 35px;

        span {
            margin-left: 0.75rem;

            button {
                border: none;
                background: none;
                color: $bco-red;
                text-decoration: underline;
                cursor: pointer;
            }
        }

        .svg-icon {
            height: 16px;
            color: $bco-red;
            flex-shrink: 0;
        }
    }
}
</style>
