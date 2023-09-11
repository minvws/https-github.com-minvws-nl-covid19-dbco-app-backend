# Typescript

[Typescript](https://www.typescriptlang.org/) has been introduced to this project some time ago. Even though there are still some JS files, the aim is to have everything written in Typescript.

The type checking the Vue templates is done with [Volar](https://github.com/johnsoncodehk/volar). Even though this works pretty well there are still some issues and [things you need to take into account](https://vuejs.org/guide/typescript/overview.html#general-usage-notes). We will list some of the most important ones below.

<br>

> **No take over mode?** <br>
> Even though Volar also offers a [take over mode](https://vuejs.org/guide/typescript/overview.html#volar-takeover-mode) which would save some resources as it makes a seperate Typescript compiler redundant. We currently can not use this yet as the typings of `vue-test-utils` are not compatible [due to this issue](https://github.com/vuejs/vue-test-utils/issues/1993).
> For now, `.vue` files are type checked by Volar (`vue-tsc`). And `.ts` files are still type checked using the Typescript (`tsc`) compiler.

<br>

## defineComponent

**All** components need to use the `defineComponent` in order to enable proper type checking. You can read more about the usage in the [Vue documentation](https://vuejs.org/api/general.html#definecomponent).

> Even template only components still need to container a script containing `export default defineComponent({})` or it **will break the type checking** on any component that imports it.

_example_

```typescript
<script lang="ts">
import { defineComponent } from 'vue';

export default defineComponent({
    setup() {
       ...
    },
});
</script>
```

<br>

## Template Escape hatches

When running into typing issues in the template you might be tempted to case certain values using Typescript. Unfortunately _Typescript syntax will not compile_. So try to move event handlers or values to the script where you can propery type them before exposing them to the template.

There are however escape hatches available to avoid proper type checking **only if absolutely neccessary**. These have been used extensively during the upgrade to the new type checking system as not everything could be refactored or fixed in one go. These are:

```typescript
$as.any(value:any) // value => value as any
$as.defined(value:T) // value => value as NonNullable<T>
```

These helpers are an absolute **last resort and should not be used** unless there is a very good reason to do so.

_example_

```html
<template>
    <Component :some-value="$as.any(wrongTypedValue)">
</template>
```

<br>

## `Property '...' does not exist on type 'CreateComponentPublicInstance<...`

Because of the circular nature of Vue’s declaration files, TypeScript may have difficulties inferring the types of certain methods. For this reason, you may need to annotate the return type on methods like render and those in computed.

This issue only occurs on `Option API` typed components. For example when one computed value references another computed value.

For more info see the [Vue documentation here](https://v2.vuejs.org/v2/guide/typescript.html#Annotating-Return-Types) and [here](https://vuejs.github.io/vetur/guide/FAQ.html#property-xxx-does-not-exist-on-type-combinedvueinstance)

_example_

```typescript
defineComponent({
    ...
    computed: {
        test() {
            return '';
        },
        error() {
            return this.test + ''; // Error: Property '...' does not exist on type 'CreateComponentPublicInstance<...
        },
        ok(): string { // <-- added return type
            return this.test + '';
        },
    ...
```

<br>

## i18n

Because the return type of the default `t` method to retrieve the translations is typed as `string | LocaleMessages`. It will complain anywhere where it expects a `string` value. This will be solved when we move to version 9. For now you can simply use `tc` for most use cases insetead. As this will always return a `string`.

[Also see this issue](https://github.com/kazupon/vue-i18n/issues/410)

_example_

```html
<template>
    <Tooltip :hint="$t(`something.hint`)" /> // Error: Type 'TranslateResult' is not assignable to type 'string'

    <Tooltip :hint="$tc(`something.hint`)" /> // ok
</template>
```

<br>

## `JSX elements cannot have multiple attributes with the same name.`

In one instance we encountered an issue where both `v-model` and `value` were defined on an component. Per Vue3 this is not allowed as it both relates to the same property. This was temporarily fixed by using a combination of `v-bind` and an as any cast (`$as.any`).

<!-- prettier-ignore -->
```html
<BFormCheckbox
    v-model="selected"
    :id="field.id"
    :name="field.id"
    :value="field.id" // JSX elements cannot have multiple attributes with the same name.
/>

<BFormCheckbox
    v-model="selected"
    :id="field.id"
    :name="field.id"
    v-bind="$as.any({ value: field.id })"
/>
```
