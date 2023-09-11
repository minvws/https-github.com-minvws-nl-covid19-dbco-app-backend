import { fakeSituation } from '@/utils/__fakes__/situation';
import { setActivePinia, createPinia } from 'pinia';
import { usePlaceSituationStore } from './placeSituationStore';

describe('PlaceSituationStore Store', () => {
    beforeEach(() => {
        setActivePinia(createPinia());
    });

    it('should call updateSituations with situations', () => {
        const placeSituationStore = usePlaceSituationStore();
        const situationNumbers = [fakeSituation(), fakeSituation()];

        placeSituationStore.updateSituations(situationNumbers);

        const expectedSituations = [
            { name: situationNumbers[0].name, value: situationNumbers[0].value },
            { name: situationNumbers[1].name, value: situationNumbers[1].value },
        ];
        expect(placeSituationStore.situationNumbers).toStrictEqual(expectedSituations);
    });
});
