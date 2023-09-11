<template>
    <BModal
        v-if="data.bsnInfo.guid"
        id="bsn-modal"
        title="We hebben een persoon gevonden"
        :okTitle="data.caseUuid ? 'Wijzigingen toepassen' : 'Case aanmaken'"
        cancelTitle="Gegevens aanpassen"
        cancelVariant="outline-primary"
        v-on="$listeners"
        @ok="$emit('continue', data.bsnInfo)"
        visible
    >
        Op basis van ingevoerde gegevens hebben we een persoon gevonden.<br />
        <h4 class="mt-3">Persoonsgegevens</h4>
        <p>
            {{ data.firstname }} {{ data.lastname }}<br />
            {{ $filters.age(data.dateOfBirth) }} jaar ({{ $filters.dateFormatMonth(data.dateOfBirth) }})<br />
            {{ data.bsnInfo.censoredBsn }}<br />
        </p>
        <h4 class="mt-3">Adres</h4>
        <p>
            {{ data.address.street }} {{ data.address.houseNumber }} {{ data.address.houseNumberSuffix }} <br />
            {{ data.address.postalCode }} {{ data.address.town }} <br />
        </p>
    </BModal>
    <BModal
        v-else-if="!data.bsnInfo || data.bsnInfo.error === BsnLookupError.SERVICE_UNAVAILABLE"
        id="bsn-modal"
        title="Het portaal kan op dit moment niet de identiteit van de index controleren"
        okTitle="Toch doorgaan"
        cancelTitle="Annuleren "
        cancelVariant="outline-primary"
        v-on="$listeners"
        @ok="$emit('continue')"
        visible
    >
        <p>
            Dit is een technisch probleem. Je hoeft dus niet opnieuw de noodzakelijke gegevens zoals postcode en
            huisnummer in te vullen. Kloppen de gegevens? Klik dan op ‘Toch doorgaan’.
        </p>
    </BModal>
    <BModal
        v-else
        id="bsn-modal"
        title="Geen resultaten voor combinatie van BSN, postcode, huisnummer en geboortedatum"
        okTitle="Gegevens controleren"
        :cancelTitle="data.caseUuid ? 'Case toch wijzigen' : 'Case toch aanmaken'"
        cancelVariant="outline-primary"
        v-on="$listeners"
        @cancel="$emit('continue')"
        visible
    >
        <p>
            Je kunt nog een keer de gegevens controleren of besluiten de case aan te maken. Als je doorgaat, gebruiken
            we het ingevoerde BSN niet.
        </p>
    </BModal>
</template>

<script>
import { BsnLookupError } from '@dbco/portal-api/bsn.dto';

export default {
    name: 'BsnModal',
    props: {
        data: {
            type: Object,
            required: true,
        },
    },
    data() {
        return {
            BsnLookupError,
        };
    },
};
</script>
