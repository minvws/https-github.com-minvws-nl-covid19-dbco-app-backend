<template>
    <div class="questionform">
        <b-form autocomplete="off">
            <div class="row bg-grey">
                <div class="col ml-4 mb-3 mt-4">
                    <h2>{{ title }}</h2>
                </div>
            </div>
            <div class="row bg-grey">
                <div class="col ml-3 mr-3">
                    <slot></slot>
                </div>
            </div>
        </b-form>

    </div>
</template>

<script>
export default {
    name: "DbcoFormWrap",
    props: {
        title: {
            type: String,
            required: true
        }
    }
}
</script>

<style scoped>

</style>
