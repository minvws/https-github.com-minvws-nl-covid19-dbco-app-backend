type GenericObject = { [key: string]: any };

interface InputEvent<T extends HTMLInputElement | HTMLTextAreaElement> extends InputEvent {
    target: T;
}

interface BlurEvent<T extends HTMLInputElement | HTMLTextAreaElement | HTMLSelectElement> extends Event {
    target: T;
}

interface ChangeEvent<T extends HTMLInputElement | HTMLTextAreaElement | HTMLSelectElement> extends Event {
    target: T;
}

interface FocusEvent<T extends HTMLInputElement | HTMLTextAreaElement | HTMLSelectElement> extends FocusEvent {
    target: T;
}
