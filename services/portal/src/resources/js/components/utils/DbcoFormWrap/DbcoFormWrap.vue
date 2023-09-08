<template>
    <div class="container pl-5 pr-5">
        <div class="row">
            <div class="col">
                <slot></slot>
            </div>
        </div>
    </div>
</template>

<script lang="ts">
import { defineComponent } from 'vue';

export default defineComponent({
    name: 'DbcoFormWrap',
});
</script>
