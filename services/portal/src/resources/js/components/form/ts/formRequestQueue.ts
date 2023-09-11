import type { AxiosInstance } from 'axios';

// Inspired by (copied from) https://github.com/bernawil/axios-concurrency / https://www.npmjs.com/package/axios-concurrency

const formRequestQueue = (axios: AxiosInstance, MAX_CONCURRENT = 10) => {
    if (MAX_CONCURRENT < 1) throw 'Concurrency Manager Error: minimun concurrent requests is 1';
    const instance = {
        queue: [] as any[],
        running: [] as any[],
        shiftInitial: () => {
            setTimeout(() => {
                if (instance.running.length < MAX_CONCURRENT) {
                    instance.shift();
                }
            }, 0);
        },
        push: (reqHandler: any) => {
            const reqIdentifier = reqHandler.request.url + Object.keys(reqHandler.request.data).join(',');
            reqHandler.identifier = reqIdentifier;

            instance.queue = instance.queue.filter((handler) => handler.identifier !== reqIdentifier);

            instance.queue.push(reqHandler);
            instance.shiftInitial();
        },
        shift: () => {
            if (instance.queue.length) {
                const queued = instance.queue.shift();
                queued.resolver(queued.request);
                instance.running.push(queued);
            }
        },
        // Use as interceptor. Queue outgoing requests
        requestHandler: (req: any): Promise<any> => {
            return new Promise((resolve) => {
                instance.push({ request: req, resolver: resolve });
            });
        },
        // Use as interceptor. Execute queued request upon receiving a response
        responseHandler: (res: any): Promise<any> => {
            // Removes this request from running queue
            const thisHandler = instance.running.shift();

            // If a similar request is in the queue, add a signal that this response should be ignored
            const isDuplicated = instance.queue.some((handler) => {
                return thisHandler.identifier === handler.identifier;
            });

            if (res.isAxiosError) {
                res.response.isDuplicated = isDuplicated;
            } else {
                res.isDuplicated = isDuplicated;
            }

            // Gets next request
            instance.shift();

            return res;
        },
        responseErrorHandler: (res: any) => {
            return Promise.reject(instance.responseHandler(res));
        },
        interceptors: {
            request: 0,
            response: 0,
        },
        detach: () => {
            axios.interceptors.request.eject(instance.interceptors.request);
            axios.interceptors.response.eject(instance.interceptors.response);
        },
    };
    // queue concurrent requests
    instance.interceptors.request = axios.interceptors.request.use(instance.requestHandler);
    instance.interceptors.response = axios.interceptors.response.use(
        instance.responseHandler,
        instance.responseErrorHandler
    );
    return instance;
};

export default formRequestQueue;
