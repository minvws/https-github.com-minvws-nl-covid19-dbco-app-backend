import { getModulePath, isEditCaseModulePath } from '../url';

describe('getModulePath', () => {
    it('Should return "editcase" when first string in pathname is "editcase"', () => {
        Object.defineProperty(window, 'location', {
            value: {
                pathname: '/editcase/8b1820fc-1ccd-4450-bc5e-ee9a11fd7513',
            },
        });

        expect(getModulePath()).toBe('editcase');
    });
});

describe('isEditCaseModulePath', () => {
    it('Should return true if getModulePath() returns "editcase"', () => {
        Object.defineProperty(window, 'location', {
            value: {
                pathname: '/editcase/8b1820fc-1ccd-4450-bc5e-ee9a11fd7513',
            },
        });

        expect(isEditCaseModulePath()).toBe(true);
    });

    it('Should return false if getModulePath() returns something else than "editcase"', () => {
        Object.defineProperty(window, 'location', {
            value: {
                pathname: '/briefing/8b1820fc-1ccd-4450-bc5e-ee9a11fd7513',
            },
        });

        expect(isEditCaseModulePath()).toBe(false);
    });
});
