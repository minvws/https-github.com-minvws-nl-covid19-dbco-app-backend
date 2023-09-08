import type { FormChangeEvent } from '../../types';
import type { FormEvents } from './provide/event-bus';

export const emits = {
    formLink: (data: FormEvents['formLink']) => undefined, // eslint-disable-line @typescript-eslint/no-unused-vars
    change: (event: FormChangeEvent) => undefined, // eslint-disable-line @typescript-eslint/no-unused-vars
    childFormChange: (event: FormEvents['childFormChange']) => undefined, // eslint-disable-line @typescript-eslint/no-unused-vars
};
