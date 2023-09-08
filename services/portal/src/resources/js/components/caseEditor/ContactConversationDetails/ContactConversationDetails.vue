<template>
    <div class="pb-2">
        {{ $as.defined(fragments.general).firstname }} {{ $as.defined(fragments.general).lastname }}
        <PhoneIcon class="icon icon--fill mr-0" v-if="$as.defined(fragments.general).phone" />
        {{ $as.defined(fragments.general).phone }}
    </div>
</template>

<script lang="ts">
import type { TaskStoreState } from '@/store/task/taskStore';
import { defineComponent } from 'vue';
import PhoneIcon from '@icons/phone.svg?vue';

export default defineComponent({
    name: 'ContactConversationDetails',
    components: {
        PhoneIcon,
    },
    computed: {
        fragments() {
            return this.$store.getters['task/fragments'] as TaskStoreState['fragments'];
        },
    },
});
</script>
