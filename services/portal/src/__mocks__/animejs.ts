const mockedAnime = vi.fn();
(mockedAnime as any).remove = vi.fn();
export default mockedAnime;
