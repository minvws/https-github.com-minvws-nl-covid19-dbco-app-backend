<template>
    <b-dropdown variant="link" text="small" class="dbco-select">
        <template #button-content>
            {{ currentOption }}
            <span v-show="!currentOption">Kies</span>
        </template>
        <b-dropdown-item v-for="(option, index) in options" :key="index" @click="select(option)">
            <strong>{{ option.title }}</strong>
            <p>{{ option.subtitle }}</p>
        </b-dropdown-item>
        <b-dropdown-divider />
        <b-dropdown-form>
            <b-form-group label="" label-for="dropdown-form-add-on" @submit.stop.prevent>
                <b-form-text>Optioneel: wat is er tijdens het contact gebeurd</b-form-text>
                <b-form-textarea rows="2" placeholder="Beschrijf situatie" />
            </b-form-group>
        </b-dropdown-form>
        <slot></slot>
    </b-dropdown>
</template>

<script>
export default {
    name: "DbcoCategorySelect",
    props: {
        value: String
    },
    data() {
        return {
            options: [
                { value:  '1', title:  '1 - Huisgenoot',     subtitle: 'Contacnten waarmee de index in één huis woont' },
                { value: '2a', title: '2A - Nauw contact',   subtitle: 'Meer dan 15 minuten bij elkaar op minder dan 1,5 meter' },
                { value: '2b', title: '2B - Nauw contact',   subtitle: 'Minder dan 15 minuten, wel intensief contact (bijv. knuffelen of zoenen)' },
                { value:  '3', title:  '3 - Overig contact', subtitle: 'Langer dan 15 minuten in dezelfde ruimte' }
            ]
        }
    },
    created() {

    },
    computed: {
        currentOption: function() {
            for (var i = 0; i < this.options.length; i++) {
                if (this.options[i].value == this.value) {
                    return this.options[i].title;
                }
            }
        }
    },
    methods: {
        select(selectedOption) {
            this.$emit('input', selectedOption.value)
        }
    }
}
</script>

<style scoped>

</style>
