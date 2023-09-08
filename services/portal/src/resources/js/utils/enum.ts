export const isEnum =
    <T extends AnyObject>(e: T) =>
    (input: unknown): input is T[keyof T] =>
        Object.values(e).includes(input as T[keyof T]);
