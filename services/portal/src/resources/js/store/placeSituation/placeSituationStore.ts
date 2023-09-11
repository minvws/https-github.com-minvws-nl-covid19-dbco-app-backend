import type { PlaceSituation } from '@dbco/portal-api/place.dto';
import { defineStore } from 'pinia';

export const usePlaceSituationStore = defineStore('placeSituation', {
    state: () => ({
        situationNumbers: [] as Array<Omit<PlaceSituation, 'uuid'>>,
    }),
    actions: {
        updateSituations(situationNumbers: PlaceSituation[]) {
            const situationsArray = situationNumbers.map((s) => ({
                name: s.name,
                value: s.value,
            }));
            this.situationNumbers = situationsArray;
        },
    },
});
