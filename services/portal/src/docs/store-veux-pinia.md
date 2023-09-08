# The Store

For our state management we currently use a mix of [Vuex V3](https://v3.vuex.vuejs.org/) and [Pinia](https://pinia.vuejs.org/).

<br/>

# Vuex V3

We are in the process of moving all the Vuex stores over to pinia stores. However as a lot of components still rely on Vuex data we added a bunch of helpers to get proper types when working with the veux store data.

<br/>

## Option API Components

<br>

> Note: <br/>
> If you have the opportunity to update an `option api` component into a `composition api` component. That would of course always be the preference.

<br>

When working in option api components and `vuex` data. Please always use the helpers provided by the `utils/vuex` file. It contains all the [component binding helpers](https://v3.vuex.vuejs.org/api/#component-binding-helpers) ( `mapState` / `mapGetters` / `mapActions` / `mapMutations` ) that you would normally use from `vuex`. The only difference it that these method are strongly typed.

Currently the direct mapping of root actions and mutations are not available through these util methods. But if you require access to multiple modules you can simply use the helper once for each module you need to access.

```typescript
import { mapActions } from '@/utils/vuex';
    ...
    methods: {
        mapActions({
            answerQuestion: 'supervision/ANSWER_QUESTION',
        }), // TS Error: this signature is currently not typed in the utils.

        mapActions('supervision', {
            answerQuestion: SupervisionActions.ANSWER_QUESTION,
        }) // This works and is recommended
```

The exception here being the getters as there is a `mapRootGetters` method available. (Which will still uses the same vuex `mapGetters` method behind the scenes.)

```typescript
import { mapRootGetters } from '@/utils/vuex';
    ...
    computed: {
        ...mapRootGetters({
            storeTasks: `${StoreType.INDEX}/tasks`,
            selectedTaskUuid: `${StoreType.TASK}/selectedTaskUuid`,
        }),
```

<br/>

## Composition API Components

When working with `composition api` components you can use the `useStore` hook from the `utils/vuex` to retrieve a strong typed store reference. And even though we are still on vuex v3, you can use this store in the same manner as is documented on [Vuex V4 documentation / composition API](https://vuex.vuejs.org/guide/composition-api.html).

### Accessing State and Getters

In order to access state and getters, you will want to create `computed` references to retain reactivity. This is the equivalent of creating computed properties using the Option API.

```typescript
import { computed } from 'vue';
import { useStore } from '@/utils/vuex';

export default {
    setup() {
        const store = useStore();

        return {
            // access a state in computed function
            count: computed(() => store.state.count),

            // access a getter in computed function
            double: computed(() => store.getters.double),
        };
    },
};
```

### Accessing Mutations and Actions

When accessing mutations and actions, you can simply provide the `commit` and `dispatch` method inside the setup hook.

```typescript
import { useStore } from '@/utils/vuex';

export default {
    setup() {
        const store = useStore();

        return {
            // access a mutation
            increment: () => store.commit('increment'),

            // access an action
            asyncIncrement: () => store.dispatch('asyncIncrement'),
        };
    },
};
```

For more information and examples see the [official documentation](https://vuex.vuejs.org/guide/composition-api.html).

<br/>

# Pinia

We prefer pinia for our stores as its API is much simpler to understand and provides much better type support out of the box. Their documentation is also great so if you would like to [learn more about Pinia please visit the official docs](https://pinia.vuejs.org/core-concepts/).

example store

```typescript
export const useCounterStore = defineStore('counter', {
    state: () => ({
        count: 0,
        name: 'Eduardo',
    }),
    getters: {
        doubleCount: (state) => state.count * 2,
    },
    actions: {
        increment() {
            this.count++;
        },
    },
});
```

example usage

```typescript
import { storeToRefs } from 'pinia';

export default defineComponent({
    setup() {
        const store = useCounterStore();
        // `name` and `doubleCount` are reactive refs
        // This will also create refs for properties added by plugins
        // but skip any action or non reactive (non ref/reactive) property
        const { name, doubleCount } = storeToRefs(store);
        // the increment action can be just extracted
        const { increment } = store;

        return {
            name,
            doubleCount,
            increment,
        };
    },
});
```
