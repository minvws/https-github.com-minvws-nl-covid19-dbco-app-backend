export const getModulePath = (): string => (window as Window).location.pathname.split('/')[1];
export const isEditCaseModulePath = (): boolean => getModulePath() === 'editcase';
