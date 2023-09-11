<template>
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-12 col-lg-10 col-xl-7">
                <div class="form-chapter mt-2 py-2 d-flex justify-content-between">
                    <div class="d-flex align-items-center">
                        <Link @click="clearSelection">Modellen</Link>
                        <span v-for="(breadcrumb, index) in breadcrumbs" :key="index">
                            >
                            <Link @click="selectBreadcrumb(index)">
                                {{ breadcrumb.name }}
                            </Link>
                        </span>
                    </div>
                    <div>
                        <BDropdown :text="purposeTranslations[$as.defined(selectedPurpose)] || 'Selecteer Doelbinding'">
                            <BDropdownItem @click="updatedPurpose('')"> Alle Doelbindingen</BDropdownItem>
                            <BDropdownItem v-for="el in purposes" @click="updatedPurpose(el)" :key="el">
                                {{ purposeTranslations[el] }}
                            </BDropdownItem>
                        </BDropdown>
                    </div>
                </div>
                <div class="form-chapter mt-2">
                    <div v-if="selectedElement">
                        <CatalogElementDetail
                            :element="selectedElement"
                            @select="selectElement"
                            @selectVersion="selectElementVersion"
                            @diffToVersion="diffElementToVersion"
                        />
                    </div>
                    <div v-else>
                        <h2 class="mt-4 mb-0">Modellen</h2>
                        <hr />
                        <div class="text-muted" v-if="elements.length === 0">
                            <p v-if="selectedPurpose !== null">Er zijn geen modellen gevonden voor deze purpose.</p>
                            <p v-else>Er zijn geen modellen gevonden.</p>
                        </div>
                        <div v-for="(model, index) in elements" :key="index" class="pb-3 model">
                            <div class="name">
                                <Link @click="selectElement(model.name, model.class, model.version)">
                                    {{ model.name }}
                                    <BBadge variant="light-grey" class="ml-2">V {{ model.version }}</BBadge>
                                </Link>
                            </div>
                            <div class="label">{{ model.label }}</div>
                            <div class="description">{{ model.shortDescription }}</div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>

<script lang="ts">
import { defineComponent } from 'vue';
import type { CatalogDetailResponse, CatalogElement } from '@dbco/portal-api/catalog.dto';
import { CatalogCategory, CatalogPurposes, CatalogPurposeTranslations, Filters } from '@dbco/portal-api/catalog.dto';
import CatalogElementDetail from '@/components/catalog/CatalogElementDetail/CatalogElementDetail.vue';
import { catalogApi } from '@dbco/portal-api';
import { Link } from '@dbco/ui-library';

export default defineComponent({
    name: 'CatalogPage',
    components: { CatalogElementDetail, Link },
    data() {
        return {
            activeTab: 0,
            breadcrumbs: [] as { name: string; class: string; version: number; diffToVersion?: number }[],
            elements: [] as CatalogElement[],
            purposes: CatalogPurposes,
            purposeTranslations: CatalogPurposeTranslations as Record<string, string>,
            selectedElement: undefined as CatalogDetailResponse | undefined,
            selectedElementClass: '',
            selectedPurpose: null as string | null,
        };
    },
    async created() {
        await this.setElements();
        await this.loadHash();
        window.addEventListener('hashchange', this.loadHash);
    },
    destroyed() {
        window.removeEventListener('hashchange', this.loadHash);
    },
    methods: {
        clearSelection() {
            this.breadcrumbs = [];
            this.selectedElement = undefined;
            this.selectedElementClass = '';
            this.selectedPurpose = '';
            this.setHash();
            void this.setElements();
        },
        selectBreadcrumb(index: number) {
            const selectedbreadcrumb = this.breadcrumbs[index];
            if (!selectedbreadcrumb) return;

            this.breadcrumbs = this.breadcrumbs.slice(0, index);
            void this.selectElement(
                selectedbreadcrumb.name,
                selectedbreadcrumb.class,
                selectedbreadcrumb.version,
                selectedbreadcrumb.diffToVersion
            );
        },
        setHash() {
            const params = this.getQueryParams();

            if (this.selectedPurpose) {
                params.set('purpose', this.selectedPurpose);
            } else {
                params.delete('purpose');
            }

            this.breadcrumbs.length
                ? params.set(
                      'b',
                      this.breadcrumbs
                          .map((b) => {
                              // create breadcrumb URL path string for every entry, format: class-version or class-version-diffToVersion
                              if (!b.diffToVersion) return [b.class, b.version].join('-');

                              return [b.class, b.version, b.diffToVersion].join('-');
                          })
                          // join all breadcrumb parts into one param
                          .join(',')
                  )
                : params.delete('b');
            window.location.hash = params.toString();
        },
        selectElementVersion(version: number) {
            if (!this.selectedElement) return;
            // remove the last breadcrumb so it can be replaced by the other version when selected
            this.breadcrumbs.pop();
            void this.selectElement(this.selectedElement.name, this.selectedElement.class, version);
        },
        diffElementToVersion(version: number) {
            if (!this.selectedElement) return;

            // remove the last breadcrumb so it can be replaced by the other version when selected
            this.breadcrumbs.pop();
            return this.selectElement(
                this.selectedElement.name,
                this.selectedElement.class,
                this.selectedElement.version,
                version
            );
        },
        getQueryParams() {
            return new URLSearchParams(window.location.hash.slice(1));
        },
        async loadHash() {
            const params = this.getQueryParams();
            this.selectedPurpose = params.get('purpose') || null;

            if (!params.get('b')) {
                this.breadcrumbs = [];
                this.selectedElement = undefined;
            }

            await this.setElements();

            try {
                const breadcrumbs = (params.get('b') || '')
                    // breadcrumb parts are joined by comma
                    .split(',')
                    .map((e: string) => {
                        // parse every breadcrumb string, format: class-version or class-version-diffToVersion
                        const [elementClass, elementVersion, diffToVersion] = e.split('-');
                        const element = this.elements.find((el) => el.class === elementClass);

                        if (!element) throw 'Could not find element in catalog';
                        return {
                            class: element.class,
                            name: element.name,
                            version: parseInt(elementVersion, 10),
                            diffToVersion: diffToVersion ? parseInt(diffToVersion, 10) : undefined,
                        };
                    });

                // take everything except the last item
                this.breadcrumbs = breadcrumbs.slice(0, -1);
                // the last item is the one that should be loaded initially (and will be pushed to the breadcrumbs again)
                const selectedbreadcrumb = breadcrumbs.slice(-1)[0];
                await this.selectElement(
                    selectedbreadcrumb.name,
                    selectedbreadcrumb.class,
                    selectedbreadcrumb.version,
                    selectedbreadcrumb.diffToVersion
                );
                //log the current location and hash
            } catch (e) {
                if (this.selectedElement) {
                    this.selectedElement.fields = [];
                }
            }
        },
        async selectElement(name: string, elementClass: string, version: number, diffToVersion?: number) {
            this.breadcrumbs.push({ name, class: elementClass, version, diffToVersion });
            this.selectedElement = await catalogApi.show(elementClass, version, this.selectedPurpose, diffToVersion);
            this.selectedElementClass = elementClass;
            this.setHash();
        },
        async setElements() {
            let purpose = this.selectedPurpose;
            const params = this.getQueryParams();
            let categories: CatalogCategory[] = [CatalogCategory.MODEL];
            let filter = Filters.main;

            if (this.selectedElement !== undefined || params.get('b')) {
                categories = [];
                filter = Filters.all;
            }

            const { elements } = await catalogApi.index(purpose, categories, filter);
            this.elements = elements;
        },
        updatedPurpose(purpose: string) {
            this.selectedPurpose = purpose;
            this.setHash();
        },
    },
});
</script>

<style lang="scss" scoped>
.model {
    cursor: pointer;

    .name {
        font-weight: 500;

        &::v-deep .badge {
            font-size: 0.6rem;
            line-height: 0.7rem;
        }
    }

    .label {
        font-weight: 500;
    }
}
</style>
