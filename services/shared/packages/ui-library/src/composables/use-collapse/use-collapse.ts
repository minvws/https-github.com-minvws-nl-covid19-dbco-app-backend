import type { EasingOptions } from 'animejs';
import anime from 'animejs';
import type { WatchSource } from 'vue';
import { onBeforeUnmount, onMounted, ref, watch } from 'vue';

const EASING: EasingOptions = 'easeInOutQuad';
const MAX_ANIMATION_TIME = 600;

export type CollapseConfig = { collapsedSize?: number; isOpen: WatchSource<boolean> };

export const useCollapse = (config: CollapseConfig) => {
    const collapsedSize = config.collapsedSize || 0;
    const { isOpen } = config;

    const collapseRef = ref<HTMLElement | null>(null);
    const mutationObserver = ref<MutationObserver | null>(null);
    const contentFitsInsideCollapsedSize = ref(false);

    function getDuration() {
        if (!collapseRef.value) return 0;
        return Math.min(
            Math.round(300 + Math.max((collapseRef.value.scrollHeight - collapsedSize) / 4, 0)),
            MAX_ANIMATION_TIME
        );
    }

    function onOpenAnimationComplete() {
        if (!collapseRef.value) return;
        collapseRef.value.style.maxHeight = ``;
    }

    function animateOpen() {
        if (!collapseRef.value) return;

        anime({
            targets: collapseRef.value,
            maxHeight: collapseRef.value.scrollHeight,
            easing: EASING,
            duration: getDuration(),
            complete: onOpenAnimationComplete,
        });
    }

    function animateClose() {
        if (!collapseRef.value) return;

        if (!collapseRef.value.style.maxHeight) {
            collapseRef.value.style.maxHeight = `${collapseRef.value.scrollHeight}px`;
        }

        anime({
            targets: collapseRef.value,
            maxHeight: collapsedSize,
            easing: EASING,
            duration: getDuration(),
        });
    }

    watch(isOpen, (newOpen) => {
        newOpen ? animateOpen() : animateClose();
    });

    function checkContentFit() {
        setTimeout(() => {
            if (!collapseRef.value) return;
            contentFitsInsideCollapsedSize.value = collapseRef.value.scrollHeight <= collapsedSize;
        });
    }

    onMounted(() => {
        if (!collapseRef.value) return;

        collapseRef.value.style.overflow = `hidden`;

        const isInitiallyOpen = typeof isOpen === 'function' ? isOpen() : isOpen.value;
        if (!isInitiallyOpen) {
            collapseRef.value.style.maxHeight = `${collapsedSize}px`;
        }

        if (collapsedSize) {
            mutationObserver.value = new MutationObserver(checkContentFit);
            mutationObserver.value.observe(collapseRef.value, { childList: true, subtree: true });
            checkContentFit();
        }
    });

    onBeforeUnmount(() => {
        anime.remove(collapseRef.value);
        mutationObserver.value?.disconnect();
    });

    return {
        collapseRef,
        contentFitsInsideCollapsedSize,
    };
};
