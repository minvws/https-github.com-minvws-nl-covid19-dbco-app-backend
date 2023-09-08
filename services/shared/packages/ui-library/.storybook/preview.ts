import '../src/base.css';
import './storybook.css';

import { Preview } from '@storybook/vue';

import Vue from 'vue';
import { registerDirectives } from '../src';

registerDirectives(Vue);

interface StoryIndexEntry {
    id: string;
    name: string;
    title: string;
    importPath: string;
    tags?: string[];
    type: 'story' | 'docs';
}

type StorySort = (entryA: StoryIndexEntry, entryB: StoryIndexEntry) => number;

type PreviewExtraTypes = {
    parameters?: {
        options?: {
            storySort?: StorySort;
        };
    };
};

const preview: Preview & PreviewExtraTypes = {
    parameters: {
        options: {
            /**
             * @see: https://storybook.js.org/docs/react/writing-stories/naming-components-and-hierarchy#sorting-stories
             * NOTE: TypeScript is not supported here, so we have to use plain JavaScript.
             * Also references outside of this function are not supported.
             * This function is probably executed in a sandbox.
             */
            storySort: ({ title: titleA, name: nameA }, { title: titleB, name: nameB }) => {
                function findIndex(array, predicate) {
                    for (let index = 0; index < array.length; index++) {
                        if (predicate(array[index])) return index;
                    }
                    return -1;
                }

                function compare(a, b, patterns) {
                    const indexA = findIndex(patterns, (x) => x.test(a));
                    const indexB = findIndex(patterns, (x) => x.test(b));
                    const depthA = a.split('/').length;
                    const depthB = b.split('/').length;

                    if (indexA === indexB) {
                        if (depthA === depthB) return a.localeCompare(b);
                        return depthA - depthB;
                    }

                    if (indexA === -1) return 1;
                    if (indexB === -1) return -1;
                    return indexA - indexB;
                }

                if (titleA === titleB) {
                    return compare(nameA, nameB, [/Docs/, /Default/]);
                }

                return compare(titleA, titleB, [
                    /^Introduction.*/,
                    /^Fundamentals.*/,
                    /^Composables.*/,
                    /^Directives.*/,
                    /^Components.*/,
                    /^JsonForms\/Introduction.*/,
                ]);
            },
        },
    },
};

export default preview;
