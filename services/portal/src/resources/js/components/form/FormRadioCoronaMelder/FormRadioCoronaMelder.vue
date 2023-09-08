<template>
    <div>
        <BRow align-v="center">
            <div class="col-auto px-0">
                <img :src="coronaMelderSvg" alt="CoronaMelder" class="coronamelder-logo rounded-0" />
            </div>
            <BCol>
                <FormulateInput v-bind="fieldAttributes" type="formNumberBoolean" />
            </BCol>
        </BRow>
        <BRow>
            <BCol class="info">
                <hr />
                <p>
                    <strong>Gebruikt de index CoronaMelder?</strong> <br />
                    Waarschuw contacten van de index die ook CoronaMelder gebruiken.
                </p>
                <a
                    href="https://coronamelder-portal.nl/"
                    class="btn btn-outline-primary"
                    target="_blank"
                    rel="noopener noreferrer"
                    >Open CoronaMelder Portaal</a
                >
            </BCol>
        </BRow>
    </div>
</template>

<script>
import coronaMelderSvg from '@images/coronamelder.svg';

export default {
    name: 'FormRadioCoronaMelder',
    props: {
        context: {
            type: Object,
            required: true,
        },
        fieldAttributes: {
            type: Object,
            required: true,
        },
    },
    data() {
        return { coronaMelderSvg };
    },
};
</script>

<style lang="scss" scoped>
.coronamelder-logo {
    margin-left: 1rem;
    width: 2.25rem;
}

.info {
    padding: 0 1rem;
}

.formulate-input::v-deep {
    .formulate-input-label {
        margin-bottom: 0;
    }

    .formulate-input {
        padding: 0;
    }

    .radio-button-wrapper {
        display: flex;
        justify-content: space-between;
        align-items: center;

        .row {
            width: 50%;
        }
    }
}
</style>
