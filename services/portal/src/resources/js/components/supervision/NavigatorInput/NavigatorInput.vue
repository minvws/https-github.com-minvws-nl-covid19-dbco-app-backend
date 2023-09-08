<template>
    <b-form id="navigator-input" @submit.prevent="enoughInput && findQuestionByCaseId($as.defined(caseId))">
        <b-input-group size="sm" id="medical-supervisor-search-input-group">
            <b-form-input
                v-model="caseId"
                type="text"
                id="medical-supervisor-search-input"
                :formatter="formatter"
                placeholder="Zoeken op casenummer"
            ></b-form-input>
            <b-input-group-append id="search-button-wrapper">
                <b-button
                    id="search-button"
                    variant="outline-secondary"
                    :disabled="!enoughInput"
                    :aria-label="$t('components.navigatorInput.search_casenumber')"
                    type="submit"
                >
                    <SearchIcon id="search-icon" />
                </b-button>
            </b-input-group-append>
        </b-input-group>
        <p id="search-hint" v-if="caseId && !enoughInput">
            <i class="icon icon--error-warning"></i>
            {{ $t('components.navigatorInput.not_enough_input') }}
        </p>
    </b-form>
</template>

<script lang="ts">
import { defineComponent } from 'vue';
import { removeSpecialCharacters, removeAllExceptAlphanumeric } from '@/utils/string';
import SearchIcon from '@icons/search.svg?vue';

export default defineComponent({
    name: 'NavigatorInput',
    components: { SearchIcon },
    data() {
        return {
            caseId: null as null | string,
        };
    },
    computed: {
        enoughInput() {
            return !!this.caseId && this.caseId.toString().length >= 7;
        },
    },
    methods: {
        addDashes(str: string) {
            return `${str.substr(0, 3)}-${str.substr(3, 3)}-${str.substr(6, 3)}`;
        },
        formatter(value: string) {
            // First remove all special characters (except for dashes)
            value = removeSpecialCharacters(value);
            const valueWithoutDashes = removeAllExceptAlphanumeric(value);
            if (valueWithoutDashes.length > 9) {
                value = this.addDashes(valueWithoutDashes);
            }
            const maxLength = 9;
            if (valueWithoutDashes.length == maxLength && !value.includes('-')) {
                value = this.addDashes(value);
            }
            return value;
        },
        findQuestionByCaseId(caseId: string) {
            void this.$store.dispatch('supervision/FIND_QUESTION_BY_CASE_ID', caseId);
        },
    },
});
</script>

<style lang="scss" scoped>
@import '@/../scss/variables';

#navigator-input {
    #medical-supervisor-search-input-group {
        flex-wrap: nowrap;
        width: 320px;
        display: flex;
        flex-direction: row;
    }

    #medical-supervisor-search-input {
        border-top-left-radius: 2px;
        border-bottom-left-radius: 2px;
        border-color: $lightest-grey;
        border-right-color: transparent;
        padding: 1.5rem 1rem;
        &::placeholder {
            color: $lighter-grey;
        }
    }

    #search-button-wrapper {
        background: $white;
        border: 1px solid $lightest-grey;
        border-left-color: transparent;
        border-top-right-radius: 2px;
        border-bottom-right-radius: 2px;
    }

    #search-button {
        background: $white;
        border: none;
        padding: $padding-xs $padding-sm;
    }

    #search-hint {
        margin: $padding-xs;
    }

    #search-icon {
        width: 20px;
    }
}
</style>
