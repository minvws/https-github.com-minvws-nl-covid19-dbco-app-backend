const regexNumCharDashReplace = /[^A-Za-z0-9-]/g;
const regexNumChar = /[^A-Za-z0-9]/g;

export const lcFirst = (str: string) => str.charAt(0).toLowerCase() + str.substr(1);

export const removeSpecialCharacters = (str: string) => {
    // Beware that this functions doesn't remove dashes! "-"
    return str.replace(regexNumCharDashReplace, '');
};

export const removeAllExceptAlphanumeric = (str: string) => {
    return str.replace(regexNumChar, '');
};
