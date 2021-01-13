<template>
    <b-dropdown variant="link" text="small" class="dbco-select">
        <template #button-content>
            {{ currentOption }}
        </template>
        <b-dropdown-form>
            <b-form-group label="" label-for="dropdown-form-search" @submit.stop.prevent>
                <b-form-input
                    id="dropdown-form-search"
                    v-model="filterString"
                    size="sm"
                    placeholder="Zoeken..."
                ></b-form-input>
            </b-form-group>
        </b-dropdown-form>
        <b-dropdown-item v-for="(option, index) in filteredOptions" :key="index" @click="select(option)">
            {{ option[labelAttribute] }}
        </b-dropdown-item>
        <b-dropdown-item v-if="filteredOptions.length == 0" disabled>
            Niets gevonden...
        </b-dropdown-item>
    </b-dropdown>
</template>

<script>
export default {
    name: "DbcoFilterableSelect",
    props: {
        options: Array,
        labelAttribute: String,
        valueAttribute: String,
        value: String
    },
    data() {
        return {
            filterString: '',
        }
    },
    created() {

    },
    computed: {
       filteredOptions: function() {
           return this.options.filter(function(option) {
               return option[this.labelAttribute].toLowerCase().includes(this.filterString.toLowerCase())
           }, this);
       },
       currentOption: function() {
           for (var i = 0; i < this.options.length; i++) {
               if (this.options[i][this.valueAttribute] == this.value) {
                   return this.options[i][this.labelAttribute];
               }
           }
       }
    },
    methods: {
        select(selectedOption) {
            this.$emit('input', selectedOption[this.valueAttribute])
        }
    }
}
</script>

<style scoped>

</style>
