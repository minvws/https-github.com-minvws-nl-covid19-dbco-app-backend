<template>
    <router-link :to="to" custom v-slot="{ navigate, isExactActive: isActive }">
        <Tab @click="navigate" :isActive="isActive" v-on="$listeners">
            <slot />
        </Tab>
    </router-link>
</template>

<script lang="ts">
import { defineComponent } from 'vue';
import Tab from './Tab.vue';

export default defineComponent({
    components: {
        Tab,
    },
    props: {
        to: {
            type: String,
            required: true,
        },
    },
});
</script>
