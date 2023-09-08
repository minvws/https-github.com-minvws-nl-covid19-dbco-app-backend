export function truncate(value?: string | null, limit = 100) {
    if (!value) return '';

    if (value.length > limit) {
        return value.substring(0, limit - 3) + '...';
    }

    return value;
}
