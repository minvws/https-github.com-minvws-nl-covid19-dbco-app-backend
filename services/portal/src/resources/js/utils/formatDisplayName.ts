/**
 * Concatenates firstname, initials and lastname into full display name.
 * Handles null/undefined/empty values.
 * Adds spaces, open brackets and casing when needed.
 */
export const formatDisplayName = (firstname?: string | null, initials?: string | null, lastname?: string | null) => {
    let formattedInitials = initials?.toUpperCase();
    if (firstname && initials) formattedInitials = `(${formattedInitials})`;
    return [firstname, formattedInitials, lastname].filter(Boolean).join(' ');
};
