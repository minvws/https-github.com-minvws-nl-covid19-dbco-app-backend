import { ref } from 'vue';

export function useToggleArray() {
    const toggledItems = ref<string[]>([]);

    /**
     * Determines whether the item in the list is open, returning true or false as appropriate.
     * @param {string} id
     */
    function isToggled(id: string) {
        return toggledItems.value.includes(id);
    }

    /**
     * Toggles the open state of an item in the list
     * @param {string} id
     */
    function toggleItem(id: string) {
        if (toggledItems.value.includes(id)) {
            toggledItems.value = toggledItems.value.filter((item) => item !== id);
        } else {
            toggledItems.value = [...toggledItems.value, id];
        }
    }

    return { toggledItems, isToggled, toggleItem };
}
