import { emits as jsonFormsChildEmits } from '../JsonFormsChild/emits';
const { change, formLink } = jsonFormsChildEmits;

export const emits = {
    change,
    formLink,
};
