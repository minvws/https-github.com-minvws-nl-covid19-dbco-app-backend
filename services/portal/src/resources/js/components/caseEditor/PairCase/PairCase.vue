<template>
    <div v-if="!covidCaseBcoStatus">Controleren koppel status...</div>
    <div v-else-if="!isPairingAllowed">
        <i class="icon icon--error-grey icon--m0 mr-1"></i> De periode om te koppelen met de GGD Contact-app is verlopen
    </div>
    <div v-else-if="covidCaseBcoStatus">
        <BRow>
            <BCol>
                <p>
                    Gaat de index gegevens aanvullen via de app? Deel dan de koppelcode hieronder. De index moet zelf de
                    koppelcode invullen in de app (dit kan nadat index op tweede scherm op ‘Ja, ik heb een code’ drukt).
                </p>
            </BCol>
        </BRow>
        <BRow>
            <BCol class="font-weight-bold"> Koppelcode </BCol>
        </BRow>
        <BRow class="mb-1">
            <BCol class="pairing-digits">
                <div v-for="(part, partIndex) in pairingCodeParts" :key="partIndex" class="m-0">
                    <span
                        v-for="(character, index) in part"
                        :key="index"
                        class="bg-grey border rounded p-3 m-1 digit"
                        >{{ character }}</span
                    >
                    <span v-if="partIndex != pairingCodeParts.length - 1" class="m-1 digit-separator">-</span>
                </div>
                <BButton
                    @click="retrievePairingCode(uuid)"
                    variant="primary"
                    class="ml-2"
                    :disabled="!userCanEdit"
                    data-testid="show-pairing-code-button"
                    >{{ covidCaseBcoStatus === 'draft' ? 'Toon koppelcode' : 'Maak nieuwe code' }}
                </BButton>
            </BCol>
        </BRow>
        <BRow v-if="error">
            <div class="error-block form-font p-2">
                <WarningIcon class="warning-icon pl-2" />
                <span class="pl-2">{{ error }}</span>
            </div>
        </BRow>
        <BRow>
            <BCol>
                <div v-if="covidCaseIndexStatus === 'timeout'">De code is verlopen</div>
                <div v-else-if="!pairingCode && hasSharedPairingCode" class="mb-0">
                    <span>Koppelcode gedeeld</span>
                </div>
                <p v-else-if="covidCaseIndexStatus === 'pairing_request_accepted'" class="mb-0">Zelf BCO gestart</p>
                <p v-else-if="covidCaseBcoStatus === 'unknown'" class="mb-0">Status onbekend</p>

                <p
                    v-if="displayRemainingTime()"
                    v-b-tooltip.hover.leftbottom
                    title="We controleren iedere 10 seconden of de index gekoppeld is"
                >
                    <span>Koppelcode actief: Nog {{ remainingPairingTime }} geldig</span>
                    <BSpinner v-if="displayPairingCaseCheckSpinner" small label="Small Spinner" />
                </p>

                <p v-if="covidCaseBcoStatus === 'open' && covidCaseIndexStatus === 'expired'">
                    Gekoppeld met index maar gesloten<br />
                </p>
                <p v-else-if="hasPaired">
                    <span>Er is gekoppeld met de GGD Contact app van de index</span><br />
                    <span v-if="indexSubmittedAt" class="index-submitted-msg"
                        >De index heeft voor het laatst gegevens gedeeld op
                        {{ $filters.dateTimeFormatLong(indexSubmittedAt) }}</span
                    >
                </p>
            </BCol>
        </BRow>
    </div>
</template>

<script lang="ts">
import { caseApi } from '@dbco/portal-api';
import { defineComponent } from 'vue';
import type { IndexStoreState } from '@/store/index/indexStore';
import { userCanEdit } from '@/utils/interfaceState';
import WarningIcon from '@icons/warning.svg?vue';
interface Data {
    error: string | null;
    pairingCode: string | null;
    remainingPairingTime: string | null;
    pairingCaseCheckIntervalId: number | null;
    remainingPairingTimeIntervalId: number | null;
    displayPairingCaseCheckSpinner: boolean;
}

export default defineComponent({
    name: 'PairCase',
    components: {
        WarningIcon,
    },
    data: function () {
        return {
            error: null,
            pairingCode: null,
            remainingPairingTime: null,
            pairingCaseCheckIntervalId: null,
            remainingPairingTimeIntervalId: null,
            displayPairingCaseCheckSpinner: false,
        } as Data;
    },
    created() {
        if (this.hasActivePairingCode()) {
            this.setRemainingPairingTime();
            this.setPairingCaseCheckInterval();
        }
    },
    computed: {
        contacts() {
            return this.$store.getters['index/tasks'].contact;
        },
        covidCaseBcoStatus() {
            return this.meta.bcoStatus;
        },
        covidCaseIndexStatus() {
            return this.meta.indexStatus;
        },
        hasPaired() {
            return (
                this.covidCaseIndexStatus === 'paired' ||
                this.covidCaseIndexStatus === 'delivered' ||
                this.covidCaseBcoStatus === 'completed'
            );
        },
        userCanEdit,
        hasSharedPairingCode() {
            return this.covidCaseBcoStatus === 'open' && this.covidCaseIndexStatus === 'initial';
        },
        indexSubmittedAt() {
            return this.meta.indexSubmittedAt;
        },
        isPairingAllowed() {
            return this.$store.getters['index/fragments'].general.isPairingAllowed;
        },
        meta: {
            get() {
                return this.$store.getters['index/meta'];
            },
            set(meta: IndexStoreState['meta']) {
                void this.$store.dispatch('index/CHANGE', { path: 'meta', values: meta });
            },
        },
        pairingCodeParts() {
            if (this.pairingCode) {
                return this.pairingCode.split('-');
            } else if (!this.pairingCode && this.covidCaseBcoStatus !== 'draft') {
                return ['****', '****', '****'];
            } else {
                return ['    ', '    ', '    '];
            }
        },
        pairingExpiresAt() {
            return this.meta.pairingExpiresAt;
        },
        uuid() {
            return this.$store.getters['index/uuid'];
        },
    },
    methods: {
        async retrievePairingCode(caseUuid: string) {
            if (caseUuid) {
                if (
                    this.contacts &&
                    this.contacts.filter((c: any) => c.uuid).some((c: any) => !c.dateOfLastExposure || !c.category)
                ) {
                    this.error =
                        "Kan koppelcode niet tonen. Vul bij alle contacten de velden 'categorie' en 'contactdatum' in.";
                } else {
                    this.error = null;
                    const data = await caseApi.pair(caseUuid);
                    this.clearIntervals();
                    this.pairingCode = data.pairingCode;
                    this.meta = {
                        ...this.meta,
                        bcoStatus: data.case.bcoStatus,
                        indexStatus: data.case.indexStatus,
                        pairingExpiresAt: data.case.pairingExpiresAt,
                    };

                    if (this.hasActivePairingCode()) {
                        this.setRemainingPairingTime();
                        this.setPairingCaseCheckInterval();
                    }
                }
            }
        },
        setRemainingPairingTime() {
            if (!this.pairingExpiresAt) return;

            const pairingExpiresAt = new Date(this.pairingExpiresAt);
            const now = new Date();

            //We check every 10 seconds, so the remaining time might already be expired a few seconds.
            if (pairingExpiresAt < now) {
                this.remainingPairingTime = '0s';
                return;
            }
            // get total seconds between the times
            let delta = Math.abs(pairingExpiresAt.getTime() - now.getTime()) / 1000;

            // calculate (and subtract) whole minutes
            const minutes = Math.floor(delta / 60) % 60;
            delta -= minutes * 60;

            // what's left is seconds
            const seconds = Math.floor(delta % 60);

            this.remainingPairingTime = minutes === 0 ? seconds + 's' : minutes + 'm ' + seconds + 's';
        },
        setPairingCaseCheckInterval() {
            this.pairingCaseCheckIntervalId = window.setInterval(async () => {
                this.displayPairingCaseCheckSpinner = true;

                const data = await caseApi.getMeta(this.uuid);
                this.meta = data.case;

                setTimeout(() => {
                    this.displayPairingCaseCheckSpinner = false;
                }, 2000);

                if (!this.hasActivePairingCode() || this.covidCaseIndexStatus === 'timeout') {
                    this.clearIntervals();
                    this.pairingCode = null;
                    this.remainingPairingTime = null;
                }
            }, 10000);

            this.remainingPairingTimeIntervalId = window.setInterval(() => {
                this.setRemainingPairingTime();

                if (!this.hasActivePairingCode() || this.covidCaseIndexStatus === 'timeout') {
                    this.clearIntervals();
                    this.pairingCode = null;
                    this.remainingPairingTime = null;
                }
            }, 1000);
        },
        clearIntervals() {
            if (this.pairingCaseCheckIntervalId) {
                clearInterval(this.pairingCaseCheckIntervalId);
            }
            if (this.remainingPairingTimeIntervalId) {
                clearInterval(this.remainingPairingTimeIntervalId);
            }
        },
        hasActivePairingCode() {
            if (!this.pairingExpiresAt) {
                return false;
            }

            const pairingExpiresAt = new Date(this.pairingExpiresAt);
            const now = new Date();
            return pairingExpiresAt > now;
        },
        displayRemainingTime() {
            return (
                this.hasActivePairingCode() &&
                this.covidCaseBcoStatus === 'open' &&
                this.covidCaseIndexStatus != 'timeout' &&
                this.covidCaseIndexStatus != 'pairing_request_accepted'
            );
        },
    },
});
</script>

<style lang="scss" scoped>
@import './resources/scss/_variables.scss';

.pairing-digits {
    div,
    span {
        display: inline-block;
    }

    .digit {
        width: 52px;
        height: 52px;
        text-align: center;
        vertical-align: middle;
        color: '#001E49';
    }

    .digit-separator {
        text-align: center;
        vertical-align: middle;
        color: '#001E49';
    }
}

.error-block {
    display: flex;
    align-items: center;
    font-size: 14px;
    font-weight: 400;
    line-height: 20px;
    border-radius: $border-radius-small;
    color: $bco-red;

    .warning-icon {
        height: 16px;
    }
}

.index-submitted-msg {
    color: #a3aebf;
    margin-top: 5px;
}
</style>
