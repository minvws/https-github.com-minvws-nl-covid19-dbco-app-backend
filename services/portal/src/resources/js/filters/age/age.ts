import { calculateAge } from '@/utils/date';

export function age(value?: string | null) {
    if (!value) return '';

    const ageString = `${calculateAge(new Date(value))}`;

    return ageString === 'NaN' ? '' : ageString;
}
