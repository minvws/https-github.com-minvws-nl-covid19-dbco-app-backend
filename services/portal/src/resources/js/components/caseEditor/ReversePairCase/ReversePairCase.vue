<template>
    <div>
        <h3 class="mb-4">GGD Contact</h3>

        <div v-if="meta == null || meta.bcoStatus == null || isPairingAllowed == null">
            Controleren koppel status...
        </div>
        <div v-else-if="!isPairingAllowed">
            <i class="icon icon--error-grey icon--m0 mr-1"></i> De periode om te koppelen met de GGD Contact-app is
            verlopen
        </div>
        <div v-else>
            <BFormCheckbox
                v-model="showPairingCodeForm"
                :disabled="isPairingActive || !userCanEdit"
                data-testid="show-pairing-checkbox"
                class="font-weight-bold"
                >Index heeft zelf het contactonderzoek voorbereid via de GGD Contact-app</BFormCheckbox
            >

            <div v-if="showPairingCodeForm">
                <div class="line-break my-4">
                    <h3 class="h6 ml-1">Vul de koppelcode van de index in</h3>
                    <BRow>
                        <BCol cols="6" class="d-flex ml-1">
                            <span class="d-flex align-items-center" v-for="i in [0, 1, 2, 3, 4, 5]" :key="i">
                                <BFormInput
                                    v-if="isPairingActive && !isRetry"
                                    size="lg"
                                    ref="digits"
                                    class="mr-2 digit rounded"
                                    value="*"
                                    readonly
                                />
                                <BFormInput
                                    v-else
                                    size="lg"
                                    ref="digits"
                                    class="mr-2 digit rounded"
                                    v-model="pairingCodeDigits[i]"
                                    @input="nextInput(i, $event)"
                                    @focus.native="focus(i)"
                                    @keydown.native="keydown(i, $event)"
                                />
                                <span class="mr-2" v-if="i === 2">-</span>
                            </span>
                            <BButton
                                class="ml-2 px-4"
                                variant="primary"
                                :disabled="pairingCode.length != 6 || isBusy || (isPairingActive && !isRetry)"
                                ref="submit"
                                @keydown="keydown(pairingCode.length - 1, $event)"
                                @click="performPairing"
                                >Koppelen</BButton
                            >
                        </BCol>
                    </BRow>

                    <BRow class="mt-3 pb-3" v-if="isInvalidCode">
                        <BCol cols="6" class="invalid">
                            <i class="icon icon--error ml-1"></i>De koppelcode is ongeldig
                        </BCol>
                    </BRow>
                    <BRow class="mt-3 pb-3" v-if="isPairingSuccessNoData || isPairingSuccessDataReceived">
                        <BCol cols="12">
                            Er is gekoppeld met de GGD Contact app van de index.
                            <span v-if="!isRetry"
                                >Is het delen van (nieuwe) gegevens niet gelukt?
                                <Link @click="isRetry = true">Koppel opnieuw</Link></span
                            >
                            <span class="d-block pt-1 text-muted" v-if="isPairingSuccessNoData"
                                >De index heeft nog geen gegevens gedeeld</span
                            >
                        </BCol>
                    </BRow>

                    <BRow class="bg-grey mt-3 ml-1">
                        <BCol cols="12" xl="6" class="p-4">
                            <h4>Instructies voor index</h4>
                            <ol class="list">
                                <li><span>Zet je telefoon op luidspreker.</span></li>
                                <li><span>Open de GGD Contact-app.</span></li>
                                <li>
                                    <span
                                        >Rond het toevoegen van contacten af als je dit nog niet hebt gedaan. De lijst
                                        met contacten hoeft nog niet compleet te zijn.</span
                                    >
                                </li>
                                <li><span>Druk op ‘Ik ben klaar’ en daarna op ‘Ja, gegevens delen’.</span></li>
                                <li>
                                    <span
                                        >Geef de code in beeld door en wacht tot de GGD-medewerker gekoppeld
                                        heeft.</span
                                    >
                                </li>
                                <li>
                                    <span
                                        >Druk na bevestiging van de koppeling op ‘Volgende’ én ‘Deel gegevens met de
                                        GGD’ om direct gegevens te delen met de GGD.</span
                                    >
                                </li>
                            </ol>
                            <p>
                                De index kan na de koppeling op ieder moment gegevens met de GGD delen door op ‘Deel
                                gegevens met GGD’ te drukken.
                            </p>
                        </BCol>
                        <BCol cols="6" xl="3" class="text-center">
                            <img class="img-fluid screenshot" :src="pairingLeftPng" alt="screenshot pairing" />
                        </BCol>
                        <BCol cols="6" xl="3" class="d-flex align-items-end text-center">
                            <img class="img-fluid screenshot" :src="pairingRightPng" alt="screenshot pairing" />
                        </BCol>
                    </BRow>
                </div>

                <BModal
                    ref="status-modal"
                    centered
                    okOnly
                    :hideFooter="isPairingSuccessNoData"
                    :title="modalTitle"
                    @ok="modalConfirm"
                    @show="onModalShow"
                    @hide="onModalHide"
                >
                    <div v-if="isPairingSuccessNoData">
                        <p>
                            Zorg dat de index op 'Volgende' drukt om zo direct gegevens uit de GGD Contact-app te delen.
                        </p>

                        <div class="bg-grey text-center">
                            <img class="img-fluid screenshot mb-1" :src="pairingpModalPng" alt="screenshot" />
                        </div>

                        <div class="mt-4 text-center">
                            <BSpinner variant="primary" />

                            <p class="mt-1">Wachten op index</p>
                        </div>
                    </div>
                    <p v-else-if="isPairingSuccessDataReceived">De gegevens van de index zijn opgehaald.</p>
                </BModal>
            </div>
        </div>
    </div>
</template>

<script lang="ts">
import { caseApi } from '@dbco/portal-api';
import { defineComponent } from 'vue';
import type { BModal } from 'bootstrap-vue';
import type { IndexStoreState } from '@/store/index/indexStore';
import type { CovidCaseUnionDTO } from '@dbco/schema/unions';
import { userCanEdit } from '@/utils/interfaceState';
import { Link } from '@dbco/ui-library';

import pairingLeftPng from '@images/ggd-contact-pairing-left.png';
import pairingRightPng from '@images/ggd-contact-pairing-right.png';
import pairingpModalPng from '@images/ggd-contact-pairing-modal.png';
import axios from 'axios';

export default defineComponent({
    name: 'ReversePairCase',
    components: { Link },
    data() {
        return {
            // When submit request is being made
            isBusy: false,
            // Code invalid after submit (not reflected in BCOStatus/IndexStatus)
            isInvalidCode: false,
            isRetry: false,

            pairingCodeDigits: [] as string[],
            pollingInterval: null as number | null,

            showPairingCodeForm: false,

            pairingLeftPng,
            pairingRightPng,
            pairingpModalPng,
        };
    },
    computed: {
        userCanEdit,
        digits() {
            return this.$refs.digits as HTMLInputElement[];
        },
        meta() {
            return this.$store.getters['index/meta'] as IndexStoreState['meta'];
        },
        isPairingActive() {
            // Pair code has been entered
            const { indexStatus } = this.meta;

            // True IF NOT one of the following values
            return !!indexStatus && ['initial', 'timeout', 'expired'].indexOf(indexStatus) === -1;
        },
        isPairingAllowed() {
            const fragments: CovidCaseUnionDTO = this.$store.getters['index/fragments'];
            return fragments.general.isPairingAllowed;
        },
        isPairingSuccessNoData() {
            // Pair code has been accepted, but no data has been received
            const { indexStatus } = this.meta;

            // True IF one of these values
            return !!indexStatus && ['pairing_request_accepted', 'paired'].indexOf(indexStatus) !== -1;
        },
        isPairingSuccessDataReceived() {
            // Pair code has been accepted, data has been received
            const { indexStatus } = this.meta;

            return !!indexStatus && indexStatus === 'delivered';
        },
        modalTitle() {
            if (this.isPairingSuccessNoData) {
                return 'Koppeling met de GGD Contact-app gelukt';
            } else if (this.isPairingSuccessDataReceived) {
                return 'Delen van gegevens gelukt';
            }
        },
        pairingCode() {
            return this.pairingCodeDigits.join('');
        },
        uuid() {
            return this.$store.getters['index/uuid'];
        },
    },
    beforeDestroy() {
        // Always make sure the polling interval is cleared before destroying this component
        this.stopPolling();
    },
    methods: {
        async setMeta(meta: IndexStoreState['meta']) {
            await this.$store.dispatch('index/CHANGE', { path: 'meta', values: meta });
        },
        focus(index: number) {
            if (Array.isArray(this.$refs.digits)) {
                // select the current element so we can replace it
                this.digits[index].select();
            }
        },
        keydown(index: number, event: KeyboardEvent) {
            // Move to previous item on backspace
            if (event.key == 'Backspace') {
                if (index > 0) {
                    event.preventDefault();
                    this.$set(this.pairingCodeDigits, index, '');
                    this.digits[index - 1].focus();
                    this.digits[index - 1].select();
                }
            } else if (isNaN(parseInt(event.key))) {
                // Only allow numbers
                event.preventDefault();
            }
        },
        modalConfirm() {
            if (!this.isInvalidCode && this.isPairingSuccessDataReceived) {
                window.location.reload();
                return false;
            }
        },
        nextInput(index: number, value: string) {
            if (value.length > 1) {
                this.pairingCodeDigits[index] = value.substring(0, 1);
            } else if (value.length > 0) {
                if (index < 5) {
                    this.digits[index + 1].focus();
                    this.digits[index + 1].select();
                }
            } else if (value.length == 0) {
                // backspaced
                if (index > 0) {
                    this.digits[index - 1].focus();
                    this.digits[index - 1].select();
                }
            }
        },
        onModalShow() {
            if (!this.isPairingSuccessNoData) return;

            // Start polling if we are showing the modal and we're waiting for data
            this.startPolling();
        },
        onModalHide() {
            // Always stop polling when closing the modal
            this.stopPolling();

            // If pairing has succeeded and you click the modal away without button press, refresh as well
            this.modalConfirm();
        },
        async performPairing() {
            this.isInvalidCode = false;
            this.isBusy = true;

            try {
                const data = await caseApi.reversePair(this.uuid, this.pairingCode);
                await this.setMeta({
                    ...this.meta,
                    bcoStatus: data.case.bcoStatus,
                    indexStatus: data.case.indexStatus,
                });

                (this.$refs['status-modal'] as BModal).show();
            } catch (error) {
                if (axios.isAxiosError(error) && error.response?.status === 404) {
                    this.isInvalidCode = true;
                    this.$modal.show({
                        title: 'Koppeling met de GGD Contact-app is niet gelukt',
                        text: 'Er is nog niet gekoppeld met de index. Er zijn daarom nog geen gegevens gesynchroniseerd. Probeer het opnieuw.',
                        okOnly: true,
                        centered: true,
                    });
                } else {
                    this.$modal.show({
                        title: 'Foutmelding',
                        text: 'Er ging iets fout bij controleren van de koppelcode.',
                        okOnly: true,
                    });
                }
            }
            this.isBusy = false;
        },
        startPolling() {
            this.pollingInterval = window.setInterval(async () => {
                const data = await caseApi.getMeta(this.uuid);
                await this.setMeta(data.case);

                // Stop polling when we are not waiting for data anymore
                if (!this.isPairingSuccessNoData) {
                    this.stopPolling();
                }
            }, 5000);
        },
        stopPolling() {
            if (this.pollingInterval) {
                clearInterval(this.pollingInterval);
            }
        },
    },
    watch: {
        isPairingActive: {
            handler(active) {
                if (active) {
                    // If pairing is active, make sure the form is visible
                    this.showPairingCodeForm = true;
                }
            },
            immediate: true,
        },
    },
});
</script>

<style scoped>
.digit {
    font-size: 1.125rem;
    font-weight: bold;
    width: 44px;
    height: 44px;
}

.invalid {
    color: red;
}

.screenshot {
    width: 240px;
}

ol {
    font-weight: bold;
}
ol li span {
    font-weight: normal;
}
</style>
