const rootSchemaStub = {
    tabs: [
        {
            type: 'tab',
            id: 'about',
            title: 'Over de index',
            schema: () => vi.fn(),
        },
        {
            type: 'tab',
            id: 'medical',
            title: 'Medisch',
            schema: () => vi.fn(),
        },
    ],
    sidebar: vi.fn(() => []),
    rules: {
        index: [],
    },
};

export default rootSchemaStub;
