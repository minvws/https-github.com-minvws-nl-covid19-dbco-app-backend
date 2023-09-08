import type { FormField } from '@/components/form/ts/formTypes';

export class ElementGenerator {
    constructor(private config: FormField = {}) {}

    appendConfig(config: Partial<FormField>) {
        this.config = {
            ...this.config,
            ...config,
        };

        return this;
    }

    toConfig(): FormField {
        return this.config;
    }
}
