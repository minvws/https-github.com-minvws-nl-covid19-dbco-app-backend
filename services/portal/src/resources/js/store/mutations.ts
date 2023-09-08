export enum SharedMutations {
    CHANGE = 'CHANGE',
    CLEAR = 'CLEAR',
    FILL = 'FILL',
    LOAD = 'LOAD',
    LOAD_MESSAGES = 'LOAD_MESSAGES',
    SET_MESSAGES = 'SET_MESSAGES',
    UPDATE_FORM_ERRORS = 'UPDATE_FORM_ERRORS',
    UPDATE_FORM_VALUE = 'UPDATE_FORM_VALUE',
    UPDATE_FRAGMENTS = 'UPDATE_FRAGMENTS',
}

// eslint-disable-next-line @typescript-eslint/ban-types
export const sharedMutations = (fnDefaultState: Function) => {
    return {
        [SharedMutations.CHANGE](state: any, { path, values }: { path: string; values: any }) {
            state[path] = values;
        },
        [SharedMutations.CLEAR](state: any) {
            Object.assign(state, fnDefaultState());
        },
        [SharedMutations.FILL](
            state: any,
            data: { errors: AnyObject; fragments: AnyObject; meta: AnyObject; computedData?: AnyObject }
        ) {
            state.errors = formatErrors(data.errors);
            state.fragments = data.fragments;
            state.meta = data.meta;

            if (data.computedData?.calendarData) {
                state.calendarData = data.computedData.calendarData.map(function (item: AnyObject) {
                    item.startDate = new Date(item.startDate);
                    item.endDate = new Date(item.endDate);
                    return item;
                });
                state.calendarViews = data.computedData.calendarViews;
            }
            state.loaded = true;
        },
        [SharedMutations.SET_MESSAGES]: (state: any, payload: any) => {
            state.messages = payload;
        },
        [SharedMutations.UPDATE_FRAGMENTS]: (state: any, payload: any) => {
            state.fragments = { ...state.fragments, ...payload };
        },
        [SharedMutations.UPDATE_FORM_ERRORS](state: any, payload: { [key: string]: any }) {
            const errors = formatErrors(payload);
            state.errors = { ...state.errors, ...errors };
        },
    };
};

type Errors = { [fieldName: string]: string };
type Fragments = {
    [fragment: string]: { errors: Errors } | null;
};
type FormattedErrors = { [errorField: string]: null | { [errorField: string]: string } };

const formatErrors = (payload: Fragments): FormattedErrors => {
    return Object.entries(payload).reduce((acc, [fragment, value]) => {
        if (value === null || !value.errors) return { ...acc, [fragment]: null };

        const errorFields = {} as { [key: string]: { [key: string]: string } };
        Object.keys(value.errors).forEach((key) => {
            errorFields[fragment] = errorFields[fragment] || {};
            errorFields[fragment][key] = value.errors[key];
        });

        return { ...acc, ...errorFields };
    }, {});
};
