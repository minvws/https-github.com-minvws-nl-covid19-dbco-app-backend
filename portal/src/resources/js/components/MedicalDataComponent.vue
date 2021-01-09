<template>
    <dbco-form-wrap title="Medische gegevens">
        <b-card>
            <b-card-body>
                <!-- Start of question title component -->
                <div class="align-items-end  mb-3">
                    <h3 class="mb-0"><div class="question-nr">1</div> Hoe heet de index?</h3>
                    <p class="mt-2 mb-0  ml-auto">Deze naam is bedoeld om de case makkelijker terug te vinden in het portaal. De naam wordt niet naar HPZone verstuurd.</p>
                </div>
                <!-- End of question title component -->
                <b-form-input @change="$emit('persist')" type="text" maxlength="255" placeholder="Naam van de index" v-model="value.name" />
                <!-- Start of question title component -->
                <div class="align-items-end  mb-3 mt-5">
                    <h3 class="mb-0"><div class="question-nr">2</div> Heb je een case nummer als referentie?</h3>
                    <p class="mt-2 mb-0  ml-auto">Bijvoorbeeld een case id uit HPZone, zodat je later makkelijk kunt zien bij wie deze gegevens horen.</p>
                </div>
                <!-- End of question title component -->
                <b-form-input @change="$emit('persist')" type="text" maxlength="255" placeholder="Case nummer" v-model="value.caseId" />

                <!-- Question: discuss app download and pairing with index -->
                <div class="align-items-end  mb-3 mt-5">
                    <h3 class="mb-0"><div class="question-nr">3</div> Heeft de index klachten?</h3>
                    <b-form-radio-group
                        class="mt-3"
                        id="hassymptoms"
                        v-model="hasSymptoms"
                        :options="symptomOptions"
                        button-variant="outline-primary"
                        name="radio-btn-symptoms"
                        buttons
                    />
                </div>
                <!-- End of app and pairing question -->

                <!-- Start of question title component -->
                <div class="align-items-end  mb-3 mt-5">
                    <h3 class="mb-0"><div class="question-nr">4</div> Wat is de eerste ziektedag van de index?</h3>
                    <p class="mt-2 mb-0  ml-auto">De besmettelijke periode is twee dagen voor de eerste ziektedag tot en met vandaag.</p>
                </div>
                <!-- End of question title component -->
                <div>
                    <dbco-datepicker @select="$emit('persist')" v-model="value.dateOfSymptomOnset" id="dateOfSymptomOnset" />
                </div>
           </b-card-body>
       </b-card>
   </dbco-form-wrap>


</template>

<script>
import DbcoDatepicker from "./DbcoDatepicker";
import DbcoFormWrap from "./DbcoFormWrap";
export default {
    name: "MedicalDataComponent",
    components: {DbcoDatepicker, DbcoFormWrap},
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
