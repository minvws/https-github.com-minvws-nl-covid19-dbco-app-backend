import store from '@/store';
import { readdirSync } from 'fs';
import { resolve } from 'path';
import {
    getRootSchema,
    placeSchema,
    placeCreateSuggestedSchema,
    caseSchema,
    complianceSearchByCaseSchema,
    complianceSearchByNameSchema,
    getSchema,
} from '../formSchema';
import type { Schema } from '../schemaType';

vi.useFakeTimers();
vi.setSystemTime(new Date(2021, 0, 1));

const schemaNameFormat = 'formSchemaV';
const findVersions = (searchDir: string) =>
    readdirSync(searchDir, { withFileTypes: true })
        .filter((dir) => dir.isDirectory() && dir.name.startsWith(schemaNameFormat))
        .map((dir) => dir.name);
const schemaStoragePath = resolve(__dirname, '..');
const versions = findVersions(schemaStoragePath);

for (const version of versions) {
    // eslint-disable-next-line @typescript-eslint/no-var-requires
    const tsSchema = await vi.importActual<any>(resolve(schemaStoragePath, version));
    const schema: Schema = tsSchema.default;

    const tabs = schema.tabs.map((tab) => tab.id);
    const sections: string[] = Object.values(tsSchema.SectionTypes);

    describe.skip(version, () => {
        const versionNumber = parseInt(version.replace(schemaNameFormat, ''));

        it(`should return the version ${versionNumber} for ${version}`, () => {
            const rootSchema = getRootSchema();

            expect(rootSchema?.version).toBe(versionNumber);
        });

        beforeEach(async () => {
            const changeIndex = store.dispatch('index/CHANGE', { path: 'uuid', values: 'abcde-12345' });
            const fillIndex = store.dispatch('index/FILL', {
                errors: {},
                fragments: {
                    test: {
                        dateOfTest: '01-01-20202',
                    },
                },
                meta: { schemaVersion: versionNumber },
            });
            const updateTaskFragments = store.dispatch('task/UPDATE_FRAGMENTS', {
                general: {
                    dateOfLastExposure: '01-01-2020',
                    closeContactDuringQuarantine: false,
                    dateOfTest: '02-01-2020',
                },
                immunity: {
                    isImmune: true,
                },
            });

            await Promise.all([changeIndex, fillIndex, updateTaskFragments]);
        });

        tabs.forEach((tab, index) => {
            it.skip(`should match the snapshot of ${version} tab: ${tab}`, () => {
                const rootSchema = getRootSchema();
                const schema = rootSchema?.tabs[index].schema();

                expect(schema).toMatchSnapshot();
            });
        });

        sections.forEach((section) => {
            it.skip(`should match the snapshot of ${version} section: ${section}`, () => {
                const schema = getSchema(section);
                expect(schema).toMatchSnapshot();
            });
        });
    });
}

describe('formSchema', () => {
    it.skip('should match the snapshot of caseSchema', () => {
        expect(caseSchema()).toMatchSnapshot();
    });

    it.skip('should match the snapshot of caseSchema deletable', () => {
        expect(caseSchema({ isDeletable: true } as any)).toMatchSnapshot();
    });

    it.skip('should match the snapshot of caseSchema with bsn', () => {
        expect(caseSchema({ isDeletable: false } as any, true)).toMatchSnapshot();
    });

    it.skip('should match the snapshot of caseSchema deletable with bsn', () => {
        expect(caseSchema({ isDeletable: true } as any, true)).toMatchSnapshot();
    });

    it.skip('should match the snapshot of complianceSearchByNameSchema', () => {
        expect(complianceSearchByNameSchema()).toMatchSnapshot();
    });

    it.skip('should match the snapshot of complianceSearchByCaseSchema', () => {
        expect(complianceSearchByCaseSchema()).toMatchSnapshot();
    });

    it.skip('should match the snapshot of placeSchema', () => {
        expect(placeSchema()).toMatchSnapshot();
    });

    it.skip('should match the snapshot of placeCreateSuggestedSchema', () => {
        expect(placeCreateSuggestedSchema()).toMatchSnapshot();
    });

    it.skip('should load the rootSchema based on the store (existing)', async () => {
        await store.dispatch('index/CHANGE', { path: 'meta', values: { schemaVersion: 1 } });
        expect(getRootSchema()).toBeDefined();
        expect(getRootSchema()?.version).toBe(1);
    });

    it.skip('should load the rootSchema based on the store (no version given)', async () => {
        await store.dispatch('index/CHANGE', { path: 'meta', values: {} });
        expect(getRootSchema()).toBe(null);
        await store.dispatch('index/CHANGE', { path: 'meta', values: null });
        expect(getRootSchema()).toBe(null);
    });

    it.skip('should load the rootSchema based on the store (non-existing)', async () => {
        await store.dispatch('index/CHANGE', { path: 'meta', values: { schemaVersion: Number.MAX_VALUE } });
        expect(() => getRootSchema()).toThrow('Schema does not exist');
    });

    it.skip('should load the requested schema based on the store (existing)', async () => {
        await store.dispatch('index/CHANGE', { path: 'meta', values: { schemaVersion: 1 } });
        const schema = getSchema('contact-modal-contagious');
        expect(schema).toEqual(expect.any(Array));
        expect(schema?.length).toBeGreaterThan(0);
    });

    it.skip('should load the requested schema based on the store (no version given)', async () => {
        await store.dispatch('index/CHANGE', { path: 'meta', values: {} });
        expect(getSchema('contact-modal-contagious')).toBe(null);

        await store.dispatch('index/CHANGE', { path: 'meta', values: null });
        expect(getSchema('contact-modal-contagious')).toBe(null);
    });

    it.skip('should load the requested schema based on the store (non-existing schema name)', async () => {
        await store.dispatch('index/CHANGE', { path: 'meta', values: { schemaVersion: 1 } });
        expect(getSchema('abcdef')).toBe(null);
    });

    it.skip('should load the requested schema based on the store (non-existing rootSchema)', async () => {
        const mockConsoleError = vi.spyOn(console, 'error').mockImplementation(() => {});
        await store.dispatch('index/CHANGE', { path: 'meta', values: { schemaVersion: Number.MAX_VALUE } });
        expect(() => getSchema('contact-modal-contagious')).toThrow('Schema does not exist or an error occured');
        mockConsoleError.mockRestore();
    });
});
