<template>
    <datalist id="medicine-list">
        <option v-for="(medicine, index) in medicineList" :key="index" :value="medicine.value"></option>
    </datalist>
</template>

<script>
import medicineList from '@/i18n/nl/medicineList.json';

export default {
    name: 'FormMedicineDatalist',
    data() {
        return {
            medicineList,
        };
    },
};
</script>

<style></style>
