<template>
    <div>
        <div class="col ml-5 mr-5">
            <h4 class="mt-4 mb-2">Indexdossier</h4>
            <div class="card">
                <BCardBody v-if="!indexCase" class="px-4 p-4">
                    <div class="row justify-content-center">
                        <div class="col-auto">
                            <i class="icon icon--m0 icon--spinner"></i>
                        </div>
                    </div>
                </BCardBody>
                <BCardBody v-else class="px-4 p-4">
                    <BsnPersonalDetails
                        :firstname="indexCase.index.firstname"
                        :initials="indexCase.index.initials"
                        :lastname="indexCase.index.lastname"
                        :address="indexCase.index.address"
                        :dateOfBirth="indexCase.index.dateOfBirth"
                    />
                    <FormInfo
                        v-if="indexCase.index.bsnCensored"
                        class="my-3 info-block--lg"
                        text="Gecontroleerd in Basisregistratie Personen. Index is geïdentificeerd."
                        infoType="success"
                    />
                    <FormInfo
                        v-else
                        class="my-3 info-block--lg"
                        text="De gegevens van deze index zijn niet gevonden in de gemeentelijke basisadministratie."
                        infoType="warning"
                    />
                    <table class="table table-rounded table-hover table-ggd">
                        <colgroup>
                            <col class="w-20" />
                            <col class="w-20" />
                            <col class="w-60" />
                        </colgroup>
                        <thead>
                            <tr>
                                <th scope="col">Eerste ziektedag</th>
                                <th scope="col">Index</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr>
                                <td>{{ $filters.dateFormat(indexCase.test.dateOfSymptomOnset) }}</td>
                                <td>{{ indexCase.general.reference }}</td>
                                <td>
                                    <div v-if="indexCase.general.deletedAt" class="d-flex justify-content-end">
                                        <span class="deleted-time"
                                            >Verwijderd, nog te herstellen tot
                                            {{ $filters.dateTimeFormatLong(permanentlyDeletedAt) }}</span
                                        >
                                        <BButton
                                            v-if="indexCase.general.deletedAt"
                                            @click="onRestore"
                                            variant="outline-primary"
                                            class="ml-2"
                                            :disabled="restoring"
                                        >
                                            Herstel
                                        </BButton>
                                    </div>
                                    <div v-else class="d-flex justify-content-end">
                                        <BDropdown variant="outline-primary" :disabled="downloading">
                                            <template #button-content>
                                                <i v-if="downloading" class="icon icon--m0 icon--spinner"></i>
                                                <span v-else>Download</span>
                                            </template>
                                            <BDropdownItem @click="download('pdf')">PDF bestand</BDropdownItem>
                                            <BDropdownItem @click="download('html')">HTML bestand</BDropdownItem>
                                        </BDropdown>
                                        <BButton
                                            variant="outline-danger"
                                            class="ml-2"
                                            :disabled="deleting"
                                            @click="openCovidCaseDeleteModal"
                                        >
                                            Verwijder
                                        </BButton>
                                    </div>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </BCardBody>
            </div>
        </div>
        <CovidCaseDeleteModal
            ref="covidCaseDeleteModal"
            :text="`${$t(`shared.case_delete_warning.compliance`)}`"
            @confirm="onDelete"
        />
    </div>
</template>

<script>
import { accessRequestApi } from '@dbco/portal-api';
import BsnPersonalDetails from '@/components/caseEditor/BsnPersonalDetails/BsnPersonalDetails.vue';
import FormInfo from '@/components/form/FormInfo/FormInfo.vue';
import CovidCaseDeleteModal from '@/components/modals/CovidCaseDeleteModal/CovidCaseDeleteModal.vue';
import addDays from 'date-fns/addDays';

export default {
    components: { BsnPersonalDetails, FormInfo, CovidCaseDeleteModal },
    name: 'ComplianceSearchIndex',
    props: {
        uuid: {
            type: String,
            required: true,
        },
    },
    data() {
        return {
            indexCase: null,
            downloading: false,
            deleting: false,
            restoring: false,
            downloadCheckInterval: null,
        };
    },
    async created() {
        const { data } = await accessRequestApi.getCaseFragments(this.uuid);
        this.indexCase = data;
    },
    computed: {
        permanentlyDeletedAt() {
            if (!this.indexCase || !this.indexCase.general || !this.indexCase.general.deletedAt) return '';

            return addDays(new Date(this.indexCase.general.deletedAt), 7).toString();
        },
    },
    methods: {
        resetDownload() {
            if (this.downloadCheckInterval) clearInterval(this.downloadCheckInterval);
            this.downloadCheckInterval = null;
            this.downloading = false;
        },
        async download(type) {
            // Clear any previously initiated downloads
            this.resetDownload();

            // Show download button spinner for this case
            this.downloading = true;

            // In case the blur doesn't trigger, remove the spinner after 10s
            const token = `${this.uuid}-${Date.now()}`;
            this.downloadCheckInterval = setInterval(() => {
                // check if the cookie was set, or fallback after 10s
                if (document.cookie.includes(`downloadCompleteToken=${token}`) || Date.now() > token + 10000) {
                    this.resetDownload();
                }
            }, 500);

            // Start the download
            const data = await accessRequestApi.downloadCase(this.uuid, token, type);

            const link = document.createElement('a');
            link.href = window.URL.createObjectURL(data.file);
            link.download = data.fileName;
            link.click();
        },
        openCovidCaseDeleteModal() {
            this.$refs.covidCaseDeleteModal.show(this.uuid, this.indexCase.general.reference);
        },
        async onDelete() {
            this.deleting = true;
            const data = await accessRequestApi.deleteCase(this.uuid);
            this.indexCase.general.deletedAt = data.deleted_at;
            this.deleting = false;
        },
        onRestore() {
            this.$modal.show({
                title: 'Weet je zeker dat je dit dossier wilt herstellen?',
                text: 'Het dossier wordt weer beschikbaar gemaakt.',
                okTitle: 'Herstellen',
                onConfirm: async () => {
                    this.restoring = true;
                    await accessRequestApi.restoreCase(this.uuid);
                    this.indexCase.general.deletedAt = null;
                    this.restoring = false;
                },
            });
        },
    },
};
</script>

<style lang="scss" scoped>
@import '@/../scss/_variables.scss';

.deleted-time {
    line-height: 2.3rem;
    color: $light-grey;
}
</style>
