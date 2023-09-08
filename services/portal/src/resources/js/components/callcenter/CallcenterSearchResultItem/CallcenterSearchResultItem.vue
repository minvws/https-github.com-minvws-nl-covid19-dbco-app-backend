<template>
    <BCard>
        <div class="cardheader">
            <h4>{{ item.caseType === 'index' ? 'Indexdossier' : 'Contactdossier' }}</h4>
            <span class="cardheader-label">
                <template v-if="item.caseType === 'index'">
                    Testdatum:
                    <span class="cardheader-value" data-testid="test-date">
                        {{ item.testDate ? $filters.dateFnsFormat(item.testDate, 'd MMMM yyyy') : 'Niet ingevuld' }}
                    </span>
                </template>
                <template v-else>
                    Laatste contactdatum:
                    <span class="cardheader-value" data-testid="test-date">
                        {{
                            item.dateOfLastExposure
                                ? $filters.dateFnsFormat(item.dateOfLastExposure, 'd MMMM yyyy')
                                : 'Niet ingevuld'
                        }}
                    </span>
                </template>
            </span>
        </div>
        <ul v-if="item.personalDetails.length > 0" class="table-list">
            <li v-for="detail in item.personalDetails" :key="detail.key">
                <span class="table-list-label">{{ fieldTitles[detail.key] }}</span>
                <span v-if="detail.isMatch" class="table-list-value">
                    <img :src="iconSuccessSvg" class="tw-inline-block tw-mr-2" alt="success icon" />
                    <template v-if="detail.key === 'dateOfBirth'">
                        {{ $filters.dateFnsFormat(detail.value) }}
                    </template>
                    <template v-else>{{ detail.value }}</template>
                </span>
                <span v-else class="table-list-value">
                    <img :src="iconSupportSvg" class="tw-inline-block tw-mr-2" alt="success icon" />
                    Komt niet overeen of staat niet in het dossier
                </span>
            </li>
        </ul>
        <div v-if="isNoteToggled">
            <FormulateForm class="form-container" @submit="addNote(item)">
                <FormulateInput
                    type="textarea"
                    name="note"
                    label="Notitie"
                    v-model="note"
                    placeholder="Deel hier geen persoonlijke gegevens"
                    class="w100"
                    maxlength="5000"
                    validation="required|max:5000,length"
                    :validation-messages="{ required: 'Er mist nog een notitie' }"
                />
                <footer class="cardfooter note-open">
                    <BButton @click="toggleNote" variant="secondary" data-testid="cancel-note-button">
                        Annuleren
                    </BButton>
                    <BButton variant="primary" type="submit" :disabled="isSaving" data-testid="add-note-button">
                        Plaatsen
                    </BButton>
                </footer>
            </FormulateForm>
        </div>
        <div v-else-if="isTaskToggled">
            <CreateCallToAction
                :caseUuid="item.uuid"
                :token="item.token"
                data-testid="call-to-action"
                @cancel="toggleTask"
                @created="toggleTask"
            />
        </div>
        <footer v-else class="cardfooter">
            <BButton
                v-if="hasCaseEditPermission"
                @click="openCase(item.uuid, item.token)"
                data-testid="view-case-button"
            >
                <Icon name="external-link" class="tw-mr-2 tw-text-gray-950" />
                Dossier bekijken
            </BButton>
            <div class="ml-auto">
                <BButton variant="secondary" @click="toggleTask" data-testid="show-add-task-button">
                    Taak aanmaken
                </BButton>
                <BButton variant="secondary" @click="toggleNote" class="ml-2" data-testid="show-add-note-button">
                    Notitie plaatsen
                </BButton>
            </div>
        </footer>
    </BCard>
</template>

<script lang="ts" setup>
import CreateCallToAction from '@/components/callToAction/CreateCallToAction/CreateCallToAction.vue';
import { useAssignmentStore } from '@/store/assignment/assignmentStore';
import { useCallcenterStore } from '@/store/callcenter/callcenterStore';
import { StoreType } from '@/store/storeType';
import showToast from '@/utils/showToast';
import { useStore } from '@/utils/vuex';
import { CaseNoteTypeV1, PermissionV1 } from '@dbco/enum';
import type { CallcenterSearchResult } from '@dbco/portal-api/callcenter.dto';
import { Icon } from '@dbco/ui-library';
import iconSuccessSvg from '@images/icon-success.svg';
import iconSupportSvg from '@images/icon-support.svg';
import { computed, ref } from 'vue';

defineProps({ item: { type: Object as () => CallcenterSearchResult, required: true } });

const fieldTitles = {
    dateOfBirth: 'Geboortedatum',
    lastThreeBsnDigits: 'Laatste 3 cijfers BSN',
    address: 'Adres',
    lastname: 'Achternaam',
    phone: 'Telefoonnummer',
};

const hasCaseEditPermission = computed(() =>
    useStore().getters[`${StoreType.USERINFO}/hasPermission`](PermissionV1.VALUE_caseEditViaSearchCase)
);

// Note logic
const note = ref('');
const isNoteToggled = ref(false);
const toggleNote = () => (isNoteToggled.value = !isNoteToggled.value);
const isSaving = ref(false);
const callcenterStore = useCallcenterStore();

async function addNote(item: CallcenterSearchResult) {
    isSaving.value = true;
    try {
        await callcenterStore.addNote({
            uuid: item.uuid,
            note: note.value,
            type:
                item.caseType === 'index'
                    ? CaseNoteTypeV1.VALUE_case_note_index_by_search
                    : CaseNoteTypeV1.VALUE_case_note_contact_by_search,
            token: item.token,
        });
        showToast('Notitie is geplaatst', 'callcenter-add-note-toast');
        toggleNote();
    } catch (error) {
        showToast(`Er ging iets mis. Probeer het opnieuw.`, 'callcenter-add-note-toast', true);
    } finally {
        isSaving.value = false;
    }
}

// Task logic
const isTaskToggled = ref(false);
const toggleTask = () => (isTaskToggled.value = !isTaskToggled.value);

// Open logic
const assignmentStore = useAssignmentStore();
async function openCase(uuid: string, token: string) {
    try {
        await assignmentStore.getAccessToCase({ uuid, token });
        window.open(`/editcase/${uuid}`, '_blank');
    } catch (error) {
        showToast(`Er ging iets mis. Probeer het opnieuw.`, 'callcenter-open-case-toast', true);
    }
}
</script>

<style lang="scss" scoped>
@import './resources/scss/variables.scss';

.card {
    margin-bottom: $padding-md;

    .card-body {
        padding: $padding-md;
    }

    .cardheader,
    .cardfooter {
        display: flex;
        justify-content: space-between;

        &-value {
            font-weight: 500;
        }

        &.note-open {
            justify-content: flex-end;
            gap: 1rem;
        }

        .btn {
            display: inline-block;
        }
    }

    ul.table-list {
        list-style: none;
        padding: $padding-sm 0;

        li {
            padding: $padding-sm 0;
            box-shadow: inset 0 -1px 0 $lightest-grey;
            display: flex;
            justify-content: space-evenly;

            &:last-child {
                box-shadow: none;
            }

            .table-list-label {
                width: 50%;
            }

            .table-list-value {
                font-weight: 500;
                width: 50%;
            }
        }
    }
}
</style>
