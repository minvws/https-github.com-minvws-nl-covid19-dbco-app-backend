import type { Meta, StoryFn } from '@storybook/vue';
import type { AxiosResponse } from 'axios';
import axios from 'axios';
import { onUnmounted, ref } from 'vue';
import { Button, HStack } from '../../components';
import { usePolling } from './use-polling';

const story: Meta = {
    title: 'Composables/use-polling',
    parameters: {
        docs: {
            description: {
                component: `\`use-polling\` can be used to poll a certain endpoint and keep track of the status.<br/><br/>
In this example we are polling a \`/todos\` endpoint. If the polling is stopped during a request, the request is aborted.
                `,
            },
        },
    },
    args: {
        interval: 1000,
        timeout: 3500,
    },
    argTypes: {
        interval: {
            control: { type: 'number', min: 1 },
        },
        timeout: {
            control: { type: 'number', min: 1 },
        },
    },
};

interface StoryProps {
    interval: number;
    timeout: number;
}

export const Default: StoryFn<StoryProps> = ({ interval, timeout }) => {
    return {
        components: { Button, HStack },
        setup() {
            const pollingCount = ref(0);
            const pollingTimedOut = ref(false);

            const { startPolling, stopPolling, isPolling } = usePolling({
                request: (signal) => {
                    pollingCount.value++;
                    return axios.get('https://jsonplaceholder.typicode.com/todos', { signal });
                },
                continuePolling: (response: AxiosResponse) => {
                    // figure out if we need to keep polling based on the response
                    return true;
                },
                interval,
                timeout,
                onTimeout: () => {
                    pollingTimedOut.value = true;
                },
            });

            const handleStartClick = () => {
                pollingCount.value = 0;
                pollingTimedOut.value = false;
                startPolling();
            };

            onUnmounted(() => {
                // Don't forget to alway stop the polling when the component is unmounted.
                stopPolling();
            });

            return { handleStartClick, stopPolling, isPolling, pollingCount, pollingTimedOut };
        },
        template: `
            <div>
                <div class="tw-body-md">
                    Current poll count: {{pollingCount}} 
                    <span v-if="pollingTimedOut">...polling timed out...</span>
                </div>
                <HStack class="tw-mt-4">
                    <Button @click="handleStartClick" :loading="isPolling" loadingText="Polling...">
                        Start polling
                    </Button>

                    <Button @click="stopPolling" :disabled="!isPolling" variant="outline">
                        Stop polling
                    </Button>
                </HStack>
            </div>
            `,
    };
};

export default story;
