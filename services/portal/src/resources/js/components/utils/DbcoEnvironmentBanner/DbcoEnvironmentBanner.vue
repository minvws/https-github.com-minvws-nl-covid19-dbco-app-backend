<template>
    <div class="banner" :class="bannerClass" v-if="environment !== Environment.PRODUCTION">
        <p>
            Let op: dit is een {{ environmentName[environment] }}. Je mag hier geen gegevens van echte indexen en
            contacten invullen.
        </p>
    </div>
</template>

<script lang="ts">
import type { PropType } from 'vue';
import { defineComponent } from 'vue';

export enum Environment {
    TRAINING = 'training',
    TEST = 'test',
    ACCEPTANCE = 'acceptance',
    DEVELOPMENT = 'development',
    PRODUCTION = 'production',
}

export default defineComponent({
    name: 'DbcoEnvironmentBanner',
    props: {
        environment: {
            type: String as PropType<`${Environment}`>,
            required: true,
        },
    },
    data() {
        return {
            environmentName: {
                training: 'trainingsomgeving',
                test: 'testomgeving',
                acceptance: 'acceptatieomgeving',
                development: 'ontwikkelomgeving',
            },
            Environment,
        };
    },
    computed: {
        bannerClass: function () {
            switch (this.environment) {
                case Environment.TRAINING:
                    return 'banner--yellow';
                case Environment.TEST:
                    return 'banner--purple';
                case Environment.ACCEPTANCE:
                    return 'banner--green';
                case Environment.DEVELOPMENT:
                    return 'banner--blue';
                default:
                    return '';
            }
        },
    },
});
</script>
