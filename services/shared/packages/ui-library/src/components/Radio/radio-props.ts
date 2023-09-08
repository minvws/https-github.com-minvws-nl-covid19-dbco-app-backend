export type RadioVariant = 'plain' | 'button' | 'switch';

export const props = {
    ariaLabel: { type: String },
    ariaErrormessage: { type: String },
    checked: { type: Boolean },
    disabled: { type: Boolean },
    name: { type: String },
    required: { type: Boolean },
    readonly: { type: Boolean },
    autoFocus: { type: Boolean },
    invalid: { type: Boolean },
    value: { type: String, required: true },
} as const;
