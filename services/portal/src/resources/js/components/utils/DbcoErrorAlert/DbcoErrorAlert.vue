<template>
    <div>
        <BModal
            data-testid="error-modal"
            title="Er gaat iets mis"
            okTitle="Annuleren"
            okVariant="outline-primary"
            @hide="dismissError"
            ok-only
            :visible="hasError"
        >
            <p>
                Probeer de pagina te vernieuwen.<br />
                <strong>Let op:</strong> je werk kan hierdoor verloren gaan.
            </p>

            <p>
                Blijft dit probleem terugkomen?<br />
                Neem contact op met een key-user of ambassadeur van je eigen organisatie.
            </p>
        </BModal>
        <BModal
            data-testid="permission-error-modal"
            title="Je hebt geen toegang (meer) tot deze case of pagina"
            okTitle="OK"
            okVariant="outline-primary"
            @hide="dismissPermissionError"
            ok-only
            :visible="hasPermissionError"
        >
            <p>
                Het kan zijn dat je deze pagina eerst wel kon bekijken. Mogelijk zijn je rechten veranderd of is de case
                aan iemand anders toegewezen.
            </p>
        </BModal>
    </div>
</template>

<script lang="ts">
import { useAppStore } from '@/store/app/appStore';
import { defineComponent } from 'vue';
import { storeToRefs } from 'pinia';

export default defineComponent({
    name: 'DbcoErrorAlert',
    setup() {
        const appStore = useAppStore();
        const { setHasError, setHasPermissionError } = appStore;
        const { hasError, hasPermissionError } = storeToRefs(appStore);

        const dismissError = () => setHasError(false);
        const dismissPermissionError = () => {
            window.location.replace('/');
            setHasPermissionError(false);
        };

        return {
            hasError,
            hasPermissionError,
            dismissError,
            dismissPermissionError,
        };
    },
});
</script>

<style lang="scss" scoped></style>
