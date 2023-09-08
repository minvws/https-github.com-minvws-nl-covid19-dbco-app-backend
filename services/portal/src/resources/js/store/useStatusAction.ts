import { computed, ref } from 'vue';

export enum Status {
    idle = 'idle',
    pending = 'pending',
    resolved = 'resolved',
    rejected = 'rejected',
}

export type Resolved<T> = {
    status: Status.resolved;
    result: T;
};

export type Rejected = {
    status: Status.rejected;
    error: Error;
};

export type Idle = {
    status: Status.idle;
};

export type Pending = {
    status: Status.pending;
};

export type StatusState<T> = Resolved<T> | Rejected | Idle | Pending;

export default function useStatusAction<TParams extends unknown[], TOutput>(
    func: (...params: TParams) => Promise<TOutput>
) {
    const status = ref<StatusState<TOutput>>({
        status: Status.idle,
    });
    const actionIsPending = computed(() => isPending(status.value));
    const actionIsIdle = computed(() => isIdle(status.value));
    const actionIsResolved = computed(() => isResolved(status.value));
    const actionIsRejected = computed(() => isRejected(status.value));
    const actionResult = computed(() => (isResolved(status.value) ? status.value.result : undefined));

    const action = async (...params: TParams) => {
        if (status.value.status == Status.pending) return;

        /**
         * Using Object.assign is not type safe, so be aware
         * It is not possible to replace the whole object, that breaks reactivity of the Ref<>
         */

        Object.assign(status.value, {
            status: Status.pending,
        });

        try {
            const result = await func(...params);
            Object.assign(status.value, {
                status: Status.resolved,
                result: result,
            });
            return result;
        } catch (error) {
            Object.assign(status.value, {
                status: Status.rejected,
                error: error instanceof Error ? error : new Error(),
            });
        }
    };

    return {
        status,
        action,
        isPending: actionIsPending,
        isIdle: actionIsIdle,
        isResolved: actionIsResolved,
        isRejected: actionIsRejected,
        result: actionResult,
    };
}

export const isResolved = <T>(status: StatusState<T>): status is Resolved<T> => {
    return status.status === Status.resolved;
};

export const isRejected = <T>(status: StatusState<T>): status is Rejected => {
    return status.status === Status.rejected;
};

export const isPending = <T>(status: StatusState<T>): status is Pending => {
    return status.status === Status.pending;
};

export const isIdle = <T>(status: StatusState<T>): status is Idle => {
    return status.status === Status.idle;
};
