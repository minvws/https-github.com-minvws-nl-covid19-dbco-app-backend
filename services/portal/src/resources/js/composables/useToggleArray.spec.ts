import { fakerjs } from '@/utils/test';
import { useToggleArray } from './useToggleArray';

describe('useToggleArray', () => {
    it('should be empty by default', () => {
        const { toggledItems } = useToggleArray();
        expect(toggledItems.value).toStrictEqual([]);
    });

    it('should add item to open list when toggled', () => {
        const { toggledItems, isToggled, toggleItem } = useToggleArray();
        const fakeId = fakerjs.string.uuid();

        toggleItem(fakeId);

        expect(toggledItems.value.includes(fakeId)).toBe(true);
        expect(isToggled(fakeId)).toBe(true);
    });

    it('should remove item from list when already in the list', () => {
        const { toggledItems, isToggled, toggleItem } = useToggleArray();
        const fakeId = fakerjs.string.uuid();
        toggledItems.value = [fakeId];

        toggleItem(fakeId);

        expect(toggledItems.value.includes(fakeId)).toBe(false);
        expect(isToggled(fakeId)).toBe(false);
    });

    it('should add multiple items to open list when toggled', () => {
        const { toggledItems, isToggled, toggleItem } = useToggleArray();
        const fakeIds = [fakerjs.string.uuid(), fakerjs.string.uuid(), fakerjs.string.uuid()];

        fakeIds.forEach((fakeId) => toggleItem(fakeId));

        expect(toggledItems.value.length).toBe(fakeIds.length);

        toggledItems.value.forEach((id) => {
            expect(toggledItems.value.includes(id)).toBe(true);
            expect(isToggled(id)).toBe(true);
        });
    });
});
