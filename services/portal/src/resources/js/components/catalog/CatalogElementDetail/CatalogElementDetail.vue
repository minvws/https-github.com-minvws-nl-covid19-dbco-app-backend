<template>
    <div>
        <CatalogElementMeta
            :element="element"
            @selectVersion="(version) => $emit('selectVersion', version)"
            @diffToVersion="(version) => $emit('diffToVersion', version)"
        />
        <div v-if="!element.fields || element.fields.length === 0">
            <p class="text-muted mt-3">Er zijn geen velden gevonden.</p>
        </div>
        <div v-else>
            <h2 class="mt-4 mb-0">Velden</h2>
            <hr />
            <div v-for="(field, $index) in element.fields" :key="$index" class="mb-2">
                <div
                    class="d-flex justify-content-start align-items-center mono-font field-meta"
                    :class="field.diffResult ? `field-meta--${field.diffResult}` : ''"
                >
                    <div class="field-type">{{ field.type.type }}</div>
                    <div>
                        <span class="field-name">{{ field.name }}</span>
                        <span v-if="field.type.name || field.type.type === 'array'">:</span>
                        <span v-if="field.type.name" class="field-class">
                            <Link @click="selectType(field.type)">
                                {{ field.type.name }}
                            </Link>
                            <Link @click="selectType(field.type)">
                                <BBadge variant="light-grey" class="ml-2">V {{ field.type.version }}</BBadge>
                            </Link>
                        </span>
                        <span
                            v-if="field.type.type === 'array' && field.type.elementType && field.type.elementType.name"
                            class="field-class"
                        >
                            <Link @click="field.type.elementType && selectType(field.type.elementType)">
                                {{ field.type.elementType.name }}[]
                            </Link>
                            <Link @click="field.type.elementType && selectType(field.type.elementType)">
                                <BBadge variant="light-grey" class="ml-2">
                                    V {{ field.type.elementType.version }}
                                </BBadge>
                            </Link>
                        </span>
                        <span
                            v-if="field.type.type === 'array' && field.type.elementType && !field.type.elementType.name"
                            class="field-element-type"
                        >
                            {{ field.type.elementType.type }}[]
                        </span>
                    </div>
                </div>
                <div class="field-descriptions">
                    <div class="field-label">{{ field.label }}</div>
                    <div class="field-description">{{ field.description }}</div>
                    <div v-if="field.purposeSpecification.remark" class="field-purposes">
                        <div class="field-purposes-title">Privacy attentie</div>
                        <ul>
                            <li>{{ field.purposeSpecification.remark }}</li>
                        </ul>
                    </div>
                    <div v-if="field.condition && field.condition.all" class="field-condition">
                        Alleen beschikbaar indien: {{ field.condition.all }}
                    </div>
                    <div class="field-purposes" v-if="field.purposeSpecification.purposes.length > 0">
                        <div class="field-purposes-title">Doelbindingen</div>
                        <ul>
                            <li v-for="(purposeDetail, $index) in field.purposeSpecification.purposes" :key="$index">
                                {{ purposeDetail.purpose.label }} ({{ purposeDetail.subPurpose.label }})
                            </li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>
        <div v-if="element.options && element.options.length">
            <h2 class="mt-4 mb-0">Opties</h2>
            <hr />
            <div v-for="(option, $index) in element.options" :key="$index" class="mb-2">
                <div class="d-flex justify-content-start field-meta">
                    <div class="field-type field-type--wide mono-font">{{ option.value }}</div>
                    <div>
                        <span class="field-name">{{ option.label }}</span>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>

<script lang="ts">
import type { PropType } from 'vue';
import { defineComponent } from 'vue';
import type { CatalogDetailResponse, CatalogFieldType } from '@dbco/portal-api/catalog.dto';
import CatalogElementMeta from '../CatalogElementMeta/CatalogElementMeta.vue';
import { Link } from '@dbco/ui-library';

export default defineComponent({
    name: 'CatalogElementDetail',
    components: { CatalogElementMeta, Link },
    props: {
        element: {
            type: Object as PropType<CatalogDetailResponse>,
            required: true,
        },
    },
    methods: {
        selectType(type: CatalogFieldType) {
            if (!type?.class) return;

            this.$emit('select', type.name, type.class, type.version);
        },
    },
});
</script>

<style lang="scss" scoped>
@import './resources/scss/_variables.scss';

.field-meta {
    line-height: 1.5625rem;

    &--added {
        & .field-type {
            color: $white;
        }

        & .badge {
            color: $black;
        }

        background-color: $bco-green;
    }

    &--modified {
        & .field-type {
            color: $white;
        }

        & .badge {
            color: $black;
        }

        background-color: $bco-orange;
    }

    &--removed {
        &,
        & .field-type,
        & .field-class,
        & .field-class a,
        & .badge {
            color: $white;
        }

        background-color: $bco-red;
        text-decoration: line-through;
    }
}

.field-type {
    color: $lighter-grey;
    width: 5rem;
    text-align: right;
    padding-right: 0.5rem;

    &--wide {
        width: 10rem;
    }
}

.field-name {
    font-weight: 500;
}

.field-descriptions {
    margin-left: 5rem;

    .field-label {
        font-weight: 500;
    }

    .field-description {
        color: $grey;
        font-size: 0.75rem;
    }

    .field-condition {
        margin-top: 0.1rem;
        color: $grey;
        font-style: italic;
        font-size: 0.75rem;
    }

    .field-purposes {
        margin-top: 0.2rem;
        font-size: 0.7rem;
    }

    .field-purposes-title {
        font-weight: 500;
    }
}

.field-class {
    color: $grey;
    cursor: pointer;

    &::v-deep .badge {
        font-size: 0.6rem;
        line-height: 0.7rem;
    }
}

.field-element-type {
    color: $lighter-grey;
}
</style>
