<template>
    <div>
        <EditDossier v-if="dossierId || testFormId" :dossierId="dossierId" :testFormId="testFormId" />
        <DiseaseAdmin v-else />
    </div>
</template>

<script lang="ts">
import { defineComponent } from 'vue';
import DiseaseAdmin from './DiseaseAdmin/DiseaseAdmin.vue';
import EditDossier from './EditDossier/EditDossier.vue';

export default defineComponent({
    name: 'PlaygroundPage',
    components: {
        EditDossier,
        DiseaseAdmin,
    },
    setup() {
        const urlParams = new URLSearchParams(window.location.search);
        const dossierId = parseInt(urlParams.get('dossierId') || '');
        const testFormId = urlParams.get('testFormId') || '';

        return {
            dossierId,
            testFormId,
        };
    },
});
</script>

<style lang="scss">
header {
    .banner {
        display: none !important;
    }
}

label,
legend {
    font-weight: 500 !important;
}
fieldset {
    box-shadow: none !important;
}
.group {
    box-shadow: none;
    overflow-x: hidden;
}
.group-item {
    margin-bottom: 1rem;

    &:last-of-type {
        margin-bottom: 0 !important;
    }

    > .horizontal-layout > .horizontal-layout-item > .group,
    > .group {
        border: none !important;
        padding: 0 !important;
        margin: 0 !important;
        position: relative;

        &:after {
            left: auto;
            right: -999px;
        }

        .group-item {
            margin-top: 1rem !important;
            margin-bottom: 0 !important;
        }
    }
}
.array-item--content {
    label,
    legend {
        font-weight: 300 !important;
    }
    .description {
        display: none;
    }
    .horizontal-layout-item {
        .control {
            display: block !important;
        }
    }
}
.array-list-add {
    margin-top: 0.5rem;
}
.horizontal-layout-item {
    .control {
        flex-direction: column;
    }
}
.form-link {
    justify-content: center;
    height: 100%;

    label,
    .description {
        display: none;
    }

    .wrapper {
        flex-grow: 0 !important;
        position: relative;
        top: 2px;
    }
}
</style>
