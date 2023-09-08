import type { DispatchOptions, CommitOptions } from 'vuex';
import {
    mapGetters as mapGettersVuex,
    mapState as mapStateVuex,
    mapActions as mapActionsVuex,
    mapMutations as mapMutationsVuex,
} from 'vuex';
import type { StoreModules } from '../store';
import type store from '../store';

type DropFirstArg<T extends unknown[]> = T extends [any, ...infer U] ? U : never; // eslint-disable-line @typescript-eslint/no-explicit-any
type AddPrefix<TKey, TPrefix extends string> = TKey extends string ? `${TPrefix}${TKey}` : never;
type Unpacked<T> = T extends (infer U)[] ? U : T;

type Computed<T> = () => T;
type StringKeys<T> = Extract<keyof T, string>;

type NameSpace = keyof StoreModules;

type State<N extends NameSpace> = StoreModules[N]['state'];
type StateKeys<N extends NameSpace> = StringKeys<State<N>>;

type GetterReturnType<G> = G extends (...args: any) => any ? ReturnType<G> : never; // eslint-disable-line @typescript-eslint/no-explicit-any
type Getters<N extends NameSpace> = StoreModules[N]['getters'];
type GettersKeys<N extends NameSpace> = keyof Getters<N>;

type Actions<N extends NameSpace> = StoreModules[N]['actions'];
type ActionsKeys<N extends NameSpace> = keyof Actions<N>;

type Action = (context: any, ...args: any[]) => any; // eslint-disable-line @typescript-eslint/no-explicit-any
type ActionMethod<T> = T extends Action ? (...args: DropFirstArg<Parameters<T>>) => ReturnType<T> : never;

type Mutations<N extends NameSpace> = StoreModules[N]['mutations'];
type MutationsKeys<N extends NameSpace> = keyof Mutations<N>;

type Mutation = (state: any, ...args: any[]) => any; // eslint-disable-line @typescript-eslint/no-explicit-any
type MutationMethod<T> = T extends Mutation ? (...args: DropFirstArg<Parameters<T>>) => ReturnType<T> : never;

type ExtractRootGetters<N extends NameSpace, G = Getters<N>> = {
    [K in keyof G as AddPrefix<K, `${N}/`>]: GetterReturnType<G[K]>;
};

type RootGetters = ExtractRootGetters<'context'> &
    ExtractRootGetters<'index'> &
    ExtractRootGetters<'organisation'> &
    ExtractRootGetters<'place'> &
    ExtractRootGetters<'task'> &
    ExtractRootGetters<'userInfo'> &
    ExtractRootGetters<'supervision'>;
type RootGettersKeys = keyof RootGetters;

type ExtractRootActions<N extends NameSpace, A = Actions<N>> = {
    [K in keyof A as AddPrefix<K, `${N}/`>]: ActionMethod<A[K]>;
};

type RootActions = ExtractRootActions<'context'> &
    ExtractRootActions<'index'> &
    ExtractRootActions<'organisation'> &
    ExtractRootActions<'place'> &
    ExtractRootActions<'task'> &
    ExtractRootActions<'userInfo'> &
    ExtractRootActions<'supervision'>;

type ExtractRootMutations<N extends NameSpace, A = Mutations<N>> = {
    [K in keyof A as AddPrefix<K, `${N}/`>]: MutationMethod<A[K]>;
};

type RootMutations = ExtractRootMutations<'context'> &
    ExtractRootMutations<'index'> &
    ExtractRootMutations<'organisation'> &
    ExtractRootMutations<'place'> &
    ExtractRootMutations<'task'> &
    ExtractRootMutations<'userInfo'> &
    ExtractRootMutations<'supervision'>;

/**
 *  --- STATE ---
 */

type ComputedStateMap<
    N extends NameSpace,
    Map extends StateKeys<N>[] | Record<string, StateKeys<N>>,
> = Map extends StateKeys<N>[]
    ? {
          [K in Unpacked<Map>]: Computed<State<N>[K]>;
      }
    : Map extends Record<string, StateKeys<N>>
    ? { [K in keyof Map]: Computed<State<N>[Map[K]]> }
    : never;

export const mapState = <N extends NameSpace, Map extends StateKeys<N>[] | Record<string, StateKeys<N>>>(
    namespace: N,
    map: Map
) => mapStateVuex(namespace, map as any) as ComputedStateMap<N, Map>; // eslint-disable-line @typescript-eslint/no-explicit-any

/**
 *  --- GETTERS ---
 */

type ComputedGetterMap<
    N extends NameSpace,
    Map extends GettersKeys<N>[] | Record<string, GettersKeys<N>>,
> = Map extends GettersKeys<N>[]
    ? {
          [K in Unpacked<Map>]: Computed<GetterReturnType<Getters<N>[K]>>;
      }
    : Map extends Record<string, GettersKeys<N>>
    ? { [K in keyof Map]: Computed<GetterReturnType<Getters<N>[Map[K]]>> }
    : never;

export const mapGetters = <N extends NameSpace, Map extends GettersKeys<N>[] | Record<string, GettersKeys<N>>>(
    namespace: N,
    map: Map
) => mapGettersVuex(namespace, map as any) as ComputedGetterMap<N, Map>; // eslint-disable-line @typescript-eslint/no-explicit-any

/**
 *  --- ROOT GETTERS ---
 */

type ComputedRootGetterMap<Map extends Record<string, RootGettersKeys>> = {
    [K in keyof Map]: Computed<RootGetters[Map[K]]>;
};

export const mapRootGetters = <Map extends Record<string, RootGettersKeys>>(map: Map) =>
    mapGettersVuex(map) as ComputedRootGetterMap<Map>;

/**
 *  --- ACTIONS ---
 */

type ActionsMap<N extends NameSpace, Map extends Record<string, ActionsKeys<N>>> = {
    [K in keyof Map]: Actions<N>[Map[K]] extends Action ? ActionMethod<Actions<N>[Map[K]]> : never;
};

export const mapActions = <N extends NameSpace, Map extends Record<string, ActionsKeys<N>>>(namespace: N, map: Map) =>
    mapActionsVuex(namespace, map as any) as unknown as ActionsMap<N, Map>; // eslint-disable-line @typescript-eslint/no-explicit-any

/**
 *  --- MUTATIONS ---
 */

type MutationsMap<N extends NameSpace, Map extends Record<string, MutationsKeys<N>>> = {
    [K in keyof Map]: Mutations<N>[Map[K]] extends Mutation ? MutationMethod<Mutations<N>[Map[K]]> : never;
};

export const mapMutations = <N extends NameSpace, Map extends Record<string, MutationsKeys<N>>>(
    namespace: N,
    map: Map
) => mapMutationsVuex(namespace, map as any) as unknown as MutationsMap<N, Map>; // eslint-disable-line @typescript-eslint/no-explicit-any

/**
 *  --- Composition API ---
 */

type Store = typeof store;

type Dispatch = <T extends keyof RootActions>(
    type: T,
    ...payload: Parameters<RootActions[T]>[0] extends undefined
        ? [undefined?, DispatchOptions?]
        : [Parameters<RootActions[T]>[0], DispatchOptions?]
) => Promise<ReturnType<RootActions[T]>>;

type Commit = <T extends keyof RootMutations>(
    type: T,
    ...payload: Parameters<RootMutations[T]>[0] extends undefined
        ? [undefined?, CommitOptions?]
        : [Parameters<RootMutations[T]>[0], CommitOptions?]
) => void;

export type TypedStore = {
    state: Store['state'];
    getters: RootGetters;
    dispatch: Dispatch;
    commit: Commit;
};

/**
 * @deprecated Vuex is deprecated, implementations need to move to Pinia
 * @see: https://pinia.vuejs.org/
 */
export function useStore() {
    if (!window.app.$store) {
        throw Error('Store not available');
    }
    // Return the globally registered vuex store, as this property is mocked during the unit tests.
    return window.app.$store as TypedStore;
}
