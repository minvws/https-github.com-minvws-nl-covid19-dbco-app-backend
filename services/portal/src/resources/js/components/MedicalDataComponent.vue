<template>
    <div class="questionform">
        <b-form autocomplete="off">
            <div class="row bg-grey">
                <div class="col ml-4 mb-3 mt-4">
                    <h3><strong>Medische gegevens</strong></h3>
                </div>
            </div>
            <div class="row bg-grey">
                <div class="col ml-5 mr-5 mb-5 bg-white">
                    <div class="row ml-2 mr-2">
                        <div class="col">
                        <!-- Start of question title component -->
                        <div class="align-items-end  mb-3 mt-5">
                            <h6 class="mb-0"><div class="question-nr">1</div> Hoe heet de index?</h6>
                            <p class="mt-2 mb-0  ml-auto">Deze naam is bedoeld om de case makkelijker terug te vinden in het portaal. De naam wordt niet naar HPZone verstuurd.</p>
                        </div>
                        <!-- End of question title component -->
                        <b-form-input @change="$emit('persist')" type="text" maxlength="255" placeholder="Naam van de index" v-model="value.name" />
                        <!-- Start of question title component -->
                        <div class="align-items-end  mb-3 mt-5">
                            <h6 class="mb-0"><div class="question-nr">2</div> Heb je een case nummer als referentie?</h6>
                            <p class="mt-2 mb-0  ml-auto">Bijvoorbeeld een case id uit HPZone, zodat je later makkelijk kunt zien bij wie deze gegevens horen.</p>
                        </div>
                        <!-- End of question title component -->
                        <b-form-input @change="$emit('persist')" type="text" maxlength="255" placeholder="Case nummer" v-model="value.caseId" />

                        <!-- Question: discuss app download and pairing with index -->
                        <div class="align-items-end  mb-3 mt-5">
                            <h6 class="mb-0"><div class="question-nr">3</div> Heeft de index klachten?</h6>
                            <b-form-radio-group
                                class="mt-3"
                                id="hassymptoms"
                                v-model="hasSymptoms"
                                :options="symptomOptions"
                                button-variant="outline-primary"
                                name="radio-btn-symptoms"
                                buttons
                            ></b-form-radio-group>

                        </div>
                        <!-- End of app and pairing question -->

                        <!-- Start of question title component -->
                        <div class="align-items-end  mb-3 mt-5">
                            <h6 class="mb-0"><div class="question-nr">4</div> Wat is de eerste ziektedag van de index?</h6>
                            <p class="mt-2 mb-0  ml-auto">De besmettelijke periode is twee dagen voor de eerste ziektedag tot en met vandaag.</p>
                        </div>
                        <!-- End of question title component -->
                        <div>
                            <dbco-datepicker @select="$emit('persist')" v-model="value.dateOfSymptomOnset" id="dateOfSymptomOnset" />
                        </div>
                        </div>
                    </div>

                    <p></p>
                </div>
            </div>

        </b-form>

    </div>
</template>

<script>
import DbcoDatepicker from "./DbcoDatepicker";
export default {
    name: "MedicalDataComponent",
    components: {DbcoDatepicker},
    props: {
        value: {
            type: Object,
            required: true
        }
    },
    data() {
        return {
            hasSymptoms: true,
            symptomOptions: [
                { text: 'Ja', value: true },
                { text: 'Nee', value: false }
            ]
        }
    }
}
</script>

<style scoped>

</style>
