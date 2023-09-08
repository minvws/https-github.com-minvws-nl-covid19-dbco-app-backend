<template>
    <div class="row mb-3">
        <div class="col">
            <h4>Persoonsgegevens</h4>
            <p class="mb-0" v-if="displayName.length" data-testid="display-name">{{ displayName }}</p>
            <p class="mb-0" v-if="dateOfBirth" data-testid="date-of-birth">
                {{ $filters.age(dateOfBirth) }} jaar ({{ $filters.dateFormatMonth(dateOfBirth) }})
                <span
                    v-if="meta.schemaVersion >= 8 && bsnCensored"
                    class="tw-text-green-600"
                    data-testid="bsn-verified"
                >
                    <Icon name="check-mark" class="tw-ml-1" /> Matcht met BSN
                </span>
            </p>
            <p class="mb-0" v-if="bsnCensored" data-testid="bsn-censored">BSN {{ bsnCensored }}</p>
        </div>
        <div class="col" v-if="address" data-testid="address">
            <div class="tw-flex">
                <h4 class="tw-mr-1">Adres</h4>
                <template v-if="meta.schemaVersion >= 8 && bsnCensored">
                    <span v-if="addressVerified" class="tw-text-green-600" data-testid="address-verified">
                        <Icon name="check-mark" /> Matcht met BSN
                    </span>
                    <span v-else-if="!editing" data-testid="edit-address-link">
                        <Link iconLeft="pencil" @click="editDetails()">Adres bewerken</Link>
                    </span>
                </template>
            </div>
            <p class="mb-0">{{ address.street }} {{ address.houseNumber }} {{ address.houseNumberSuffix }}</p>
            <p class="mb-0">{{ address.postalCode }} {{ address.town }}</p>
        </div>
    </div>
</template>

<script lang="ts" setup>
import { formatDisplayName } from '@/utils/formatDisplayName';
import { useStore } from '@/utils/vuex';
import type { AddressCommonDTO } from '@dbco/schema/shared/address/addressCommon';
import { Icon, Link } from '@dbco/ui-library';
import type { PropType } from 'vue';
import { computed, ref } from 'vue';

const emit = defineEmits(['edit']);
const props = defineProps({
    firstname: { type: String as PropType<string | null>, required: false },
    initials: { type: String as PropType<string | null>, required: false },
    lastname: { type: String as PropType<string | null>, required: false },
    dateOfBirth: { type: String as PropType<string | null>, required: false },
    bsnCensored: { type: String as PropType<string | null>, required: false },
    address: { type: Object as PropType<AddressCommonDTO | null>, required: false },
    addressVerified: { type: Boolean as PropType<boolean>, required: false, default: false },
});
const editing = ref(false);
const meta = computed(() => useStore().getters['index/meta']);
const displayName = computed(() => formatDisplayName(props.firstname, props.initials, props.lastname));

function editDetails() {
    editing.value = true;
    emit('edit');
}
</script>
