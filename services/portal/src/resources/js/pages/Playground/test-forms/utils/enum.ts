// method to map genderV1Options to array with objects containing const/title
export const enumMap = (enumObject: Record<string, string>) =>
    Object.entries(enumObject).map(([key, value]) => ({
        const: key,
        title: value,
    }));
