import Badge from './Badge.vue';
import { setupStory } from '../../../docs/utils/story';
import { iconNames } from '../Icon/icons';

const colors = ['gray', 'violet', 'blue', 'green', 'yellow', 'red', 'seaGreen'];

export default {
    title: 'Components/Badge',
    component: Badge,
    parameters: {
        design: {
            type: 'figma',
            url: 'https://www.figma.com/file/vvlnyJCd5qtSuIT7OKRaO5/%F0%9F%8E%A8-Components?type=design&node-id=370-869&t=Ppw1pSPAHA7Zotr9-4',
        },
    },
    args: {
        content: 'Label',
    },
    argTypes: {
        color: { control: { type: 'select' }, options: colors },
        iconLeft: { control: { type: 'select' }, options: iconNames },
    },
} as const;

export const Default = setupStory({
    components: { Badge },
    template: `<Badge v-bind="args">{{args.content}}</Badge>`,
});
