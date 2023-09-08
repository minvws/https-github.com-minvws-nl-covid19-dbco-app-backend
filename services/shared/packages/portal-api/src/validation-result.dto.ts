export type ValidationResultType = {
    errors: Record<string, string[]>;
    failed: Record<string, { [key: string]: string[] }>;
};

export type ValidationResult = {
    notice?: ValidationResultType;
    warning?: ValidationResultType;
    fatal?: ValidationResultType;
};
