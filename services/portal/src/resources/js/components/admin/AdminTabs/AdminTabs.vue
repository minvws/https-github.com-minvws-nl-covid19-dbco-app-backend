<template>
    <div class="tw-bg-white tw-pt-6 tw-border-b tw-border-black">
        <Container>
            <h2>Beheren</h2>
            <TabList class="tw-bg-white tw-sticky tw-top-0 after:tw-h-0">
                <RouterTab to="/beheren/beleidsversies">Beleidsversies</RouterTab>
            </TabList>
        </Container>
    </div>
</template>

<script lang="ts" setup>
import { Container, RouterTab, TabList } from '@dbco/ui-library';
</script>
