import type { DirectiveOptions, VNode } from 'vue';
import { extendTailwindMerge } from 'tailwind-merge';

const twMerge = extendTailwindMerge({
    prefix: 'tw-',
});

export type NodeWithClassname = Node & {
    className: string;
};

export type NodeWithSVGAnimatedString = Node & {
    className: SVGAnimatedString;
};

function isNodeWithClassName(node: Node): node is NodeWithClassname {
    return typeof (node as NodeWithClassname).className === 'string';
}

function isNodeWithSVGAnimatedString(node: Node): node is NodeWithSVGAnimatedString {
    return (node as NodeWithSVGAnimatedString).className instanceof SVGAnimatedString;
}

function getClasses(vnode: VNode, classes: string[] = []): string[] {
    const { data, parent } = vnode;

    if (data) {
        const { staticClass, class: classBinding } = data;

        if (staticClass) {
            classes.push(staticClass);
        }

        if (classBinding) {
            if (typeof classBinding === 'string') {
                classes.push(classBinding);
            } else if (Array.isArray(classBinding)) {
                classes.push(...classBinding);
            } else {
                classes.push(
                    ...Object.entries(classBinding)
                        .filter(([key, value]) => !!value)
                        .map(([key]) => key)
                );
            }
        }
    }

    return parent ? getClasses(parent, classes) : classes;
}

function mergeElementClasses(vnode: VNode) {
    /**
     * We don't attempt to merge duplicate classes if there is no parent reference.
     * Duplicate classes in the component definition itself are considerd a programmer's error.
     * Skipping all the components with no new parent implementations will save some overhead.
     */
    if (!vnode.elm || !vnode.parent) return;

    const classes = getClasses(vnode);

    if (isNodeWithClassName(vnode.elm)) {
        vnode.elm.className = twMerge(classes);
    } else if (isNodeWithSVGAnimatedString(vnode.elm)) {
        vnode.elm.className.baseVal = twMerge(classes);
    }
}

export const tailwindMergeDirective: DirectiveOptions = {
    bind(element, binding, vnode) {
        mergeElementClasses(vnode);
    },
    update(element, binding, vnode) {
        mergeElementClasses(vnode);
    },
};
