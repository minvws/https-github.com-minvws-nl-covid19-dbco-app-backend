const routerMock = {
    push: vi.fn(),
    back: vi.fn(),
};

const routeMock = {
    path: '',
    hash: '',
    query: {},
    params: {},
    fullPath: '',
    matched: [],
};

export const useRouter = vi.fn(() => routerMock);
export const useRoute = vi.fn(() => routeMock);
