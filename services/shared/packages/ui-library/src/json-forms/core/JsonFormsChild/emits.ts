import { emits as jsonFormBaseEmit } from '../JsonFormsBase/emits';

const { formLink, change, childFormChange } = jsonFormBaseEmit;

export const emits = { formLink, change, childFormChange };
