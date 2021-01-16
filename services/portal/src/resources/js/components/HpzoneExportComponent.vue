<template>
    <div v-if="loaded">
        <!-- clipboard buffer -->
        <textarea id="clipboard" class="clipboard-offscreen" aria-hidden="true" ></textarea>

        <!-- Section: gegevens index -->
        <b-card class="w-100 mb-3">
            <b-card-body>
                <h3>
                    Gegevens case
                    <div class="float-right">
                        <b-button @click="ctrlC(caseexport.copydata.case, $event)" variant="outline-primary" size="sm">Kopieer deze gegevens</b-button>
                    </div>
                </h3>
                <div class="container mt-4">
                    <div class="row copyable" @click="ctrlCF(caseexport.covidCase.name, 'name', null, $event)">
                        <div class="col col-4 mb-1">Naam</div>
                        <div class="col">
                            {{ caseexport.covidCase.name }}
                            <div class="float-right">
                                <span class="row-action copy">Kopieer</span>
                                <span class="row-status">
                                    <span v-if="isCopied('name')">&check;</span>
                                </span>
                            </div>

                        </div>
                    </div>
                    <div class="row copyable" @click="ctrlCF(caseexport.covidCase.caseId, 'caseid', null, $event)">
                        <div class="col col-4 mb-1">HPZone casenummer</div>
                        <div class="col">
                            {{ caseexport.covidCase.caseId }}
                            <div class="float-right">
                                <span class="row-action copy">Kopieer</span>
                                <span class="row-status">
                                    <span v-if="isCopied('caseid')">&check;</span>
                                </span>
                            </div>
                        </div>
                    </div>
                </div>
            </b-card-body>
        </b-card>

        <!-- Section: Informatie BCO-medewerker -->
        <b-card class="w-100 mb-3">
            <b-card-body>
                <h3>
                    Informatie BCO-medewerker
                    <div class="float-right">
                        <b-button @click="ctrlC(caseexport.copydata.user, $event)" variant="outline-primary" size="sm">Kopieer deze gegevens</b-button>
                     </div>
                </h3>
                <div class="container mt-4">
                    <div class="row copyable" @click="ctrlCF(caseexport.user.name, 'username', null, $event)">
                        <div class="col col-4 mb-1">Naam (volledig)</div>
                        <div class="col">
                            {{ caseexport.user.name }}
                            <div class="float-right">
                                <span class="row-action copy">Kopieer</span>
                                <span class="row-status">
                                    <span v-if="isCopied('username')">&check;</span>
                                </span>
                            </div>
                        </div>
                    </div>
                    <div class="row copyable" @click="ctrlCF(caseexport.covidCase.createdAt|dateFormatYmd, 'casecreated', null, $event)">
                        <div class="col col-4 mb-1">Datum contactonderzoek</div>
                        <div class="col">
                            {{ caseexport.covidCase.createdAt | dateFormatLong }}
                            <div class="float-right">
                                <span class="row-action copy">Kopieer</span>
                                <span class="row-status">
                                    <span v-if="isCopied('casecreated')">&check;</span>
                                </span>
                            </div>
                        </div>
                    </div>
                </div>
            </b-card-body>
        </b-card>
        <!-- end of section -->

        <!-- Section: Informatie over index zelf -->
        <b-card class="w-100 mb-3">
            <b-card-body>
                <h3>
                    Informatie over de index zelf
                    <div class="float-right">
                        <b-button @click="ctrlC(caseexport.copydata.index, $event)" variant="outline-primary" size="sm">Kopieer deze gegevens</b-button>
                    </div>
                </h3>
                <div class="container mt-4">
                    <div class="row copyable" @click="ctrlCF(caseexport.covidCase.dateOfSymptomOnset | dateFormatYmd, 'dateofsymptomonset', null, $event)">
                        <div class="col col-4 mb-1">Datum eerste ziektedag (EZD)</div>
                        <div class="col">
                            {{ caseexport.covidCase.dateOfSymptomOnset | dateFormatLong }}
                            <div class="float-right">
                                <span class="row-action copy">Kopieer</span>
                                <span class="row-status">
                                    <span v-if="isCopied('dateofsymptomonset')">&check;</span>
                                </span>
                            </div>
                        </div>
                    </div>
                    <div class="row copyable" @click="ctrlCF(caseexport.covidCase.contagiousPeriodStart | dateFormatYmd, 'contagiousperiodstart', null, $event)">
                        <div class="col col-4 mb-1">Datum start besmettelijke periode</div>
                        <div class="col">
                            {{ caseexport.covidCase.contagiousPeriodStart | dateFormatLong }}
                            <div class="float-right">
                                <span class="row-action copy">Kopieer</span>
                                <span class="row-status">
                                    <span v-if="isCopied('contagiousperiodstart')">&check;</span>
                                </span>
                            </div>
                        </div>
                    </div>
                </div>
            </b-card-body>
        </b-card>

        <!-- end of section -->
        <!-- Section: Contactonderzoek -->
        <b-card v-for="(taskgroup, category) in caseexport.taskcategories" :key="category" class="w-100 mb-3">
            <b-card-body>
                <h3>
                    {{ groupTitles[category].title }}
                    <div class="float-right">
                        <b-button @click="ctrlC(caseexport.copydata.contacts[category], $event)" variant="outline-primary" size="sm">Kopieer deze gegevens</b-button>
                    </div>
                </h3>
                <div class="container mt-4">
                    <div v-for="task in taskgroup" class="case-task pb-4">
                        <div v-for="(field, key) in task.data" class="row copyable" @click="ctrlCF(field.copyValue, key, task, $event)">
                            <div class="col-4 mb-1" data-field="field.field">
                                {{ field.fieldLabel }} <span v-if="postfixes.includes(field.name)">{{ groupTitles[category].postfix }}</span>
                            </div>
                            <div class="col">
                                {{ field.displayValue ? field.displayValue : '-' }}
                                <div class="float-right">
                                    <button v-if="field.isUpdated" class="btn btn-outline-secondary btn-sm py-0 new-data">Nieuwe gegevens</button>
                                    <span class="row-action copy">Kopieer</span>
                                    <span class="row-status">
                                        <span v-if="task.copiedFields.includes(field.field)">&check;</span>
                                    </span>
                                </div>

                            </div>
                        </div>
                        <div v-if="task.needsExport" class="invisible">
                            <input type="text" size="10" :id="'remote_' + task.uuid"
                                   :value="task.exportId"/>
                            <input type="checkbox" class="chk-upload-completed"
                                   :id="'upload_' + task.uuid"/>
                        </div>
                        <div v-else>
                            {{ task.exportId }}
                        </div>
                    </div>
                </div>
            </b-card-body>
        </b-card>
        <div class="invisible">
            <input type="text" size="10" :id="'remote_' + caseUuid"
                   :value="caseexport.covidCase.exportId"/>
            <input type="checkbox" class="chk-case-upload-completed"
                   :id="'upload_' + caseUuid "/>
        </div>
    </div>
</template>

<script>
export default {
    name: "HpzoneExportComponent",
    props: {
        caseUuid: {
            type: String,
            required: true
        }
    },
    data() {
        return {
            caseexport: {
                copiedFields: [],
                covidCase: {},
                copydata: {},
                user: {}
            },
            postfixes: [ 'lastname', 'label' ],
            groupTitles: {
                '1': {title: '1 - Huisgenoten', postfix: 'van de huisgenoot'},
                '2a': {title: '2a - Nauwe contacten', postfix: 'van het nauwe contact'},
                '2b': {title: '2b - Nauwe contacten', postfix: 'van het nauwe contact'},
                '3': {title: '3 - Overige contacten', postfix: 'van het overig contact'}
            },
            loaded: false,
        }
    },
    mounted() {
        if (this.caseUuid) {
            this.loadExport(this.caseUuid)
        }
    },
    methods: {
        loadExport(caseUuid) {
            axios.get('/api/cases/' + caseUuid +'/dump').then(response => {
                this.caseexport = response.data
                this.loaded = true
            })
        },
        isCopied(field) {
            return this.caseexport.copiedFields.includes(field)
        },
        copyToClipboard(value) {
            $('#clipboard').val(value).select();
            document.execCommand('Copy');
        },
        // card copy
        ctrlC(value, event) {
            this.copyToClipboard(value)
            let btn = event.target
            console.log('button', btn)
            let org = btn.innerHTML
            btn.innerHTML = '&check; Gekopieerd'
            setTimeout(() => {
                btn.innerHTML = org
            }, 3000)
        },
        // field copy
        ctrlCF(value, copyField, task, event) {
            this.copyToClipboard(value)

            if (copyField) {
                this.markAsCopied(this.caseUuid, task ? task.uuid : null, copyField);
                task.copiedFields.push(copyField);
            }

            // legacy jquery stuff. should be ported to more reactive approach
            // if the copy/paste feature is in here for the long run

            /** The following is legacy from jquery. It doesn't work like this in the vue variant
             * this means we currently don't have the fancy 'Copied!' marker for a few seconds.
            let statusLabel = element.find('.row-status').first();
            statusLabel.html('Gekopieerd');
            setTimeout(() => {
                statusLabel.html('&check;');
            }, 2000);
            statusLabel.css('display', 'block');
            */

        },
        markAsCopied(caseId, taskId, fieldName) {
            axios.post('/api/markascopied/', {
                'caseId': caseId,
                'taskId': taskId,
                'fieldName': fieldName
            }).then(response => {

            }).catch(error => {
               alert('Er ging iets mis bij het markeren als gekopieerd')
            })

        }
    }
}
</script>

<style scoped>

</style>
