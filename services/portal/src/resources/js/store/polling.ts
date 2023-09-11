export type Poll = {
    polling: ReturnType<typeof setInterval> | null;
    pollInterval: number;
    pollStartedAt?: Date;
};
