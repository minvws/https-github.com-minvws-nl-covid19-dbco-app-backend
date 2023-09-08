declare module '*.svg' {
    const url: string;
    export default url;
}
declare module '*.svg?raw' {
    const rawSvgString: string;
    export default rawSvgString;
}

declare module '*.svg?vue' {
    import type { VueConstructor } from 'vue';
    import type Vue from 'vue';
    const content: VueConstructor<Vue>;
    export default content;
}

declare module '*.png' {
    const url: string;
    export default url;
}

declare module '*.jpeg' {
    const url: string;
    export default url;
}
