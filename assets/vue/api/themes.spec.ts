import { beforeEach, describe, expect, it, vi } from 'vitest';
import client from './client';
import { fetchContentThemes } from './themes';

vi.mock('./client', () => ({
    default: { request: vi.fn() },
}));

const mockedRequest = vi.mocked(client.request);

describe('fetchContentThemes', () => {
    beforeEach(() => {
        mockedRequest.mockReset();
    });

    it('loads themes from the API as id/name/icon objects', async () => {
        const themes = [
            { id: 1, name: 'Pastorale', icon: 'mdi-hands-pray' },
            { id: 2, name: 'APEL', icon: 'mdi-account-group' },
        ];
        mockedRequest.mockResolvedValue(themes);

        await expect(fetchContentThemes()).resolves.toEqual(themes);
        expect(mockedRequest).toHaveBeenCalledWith('/content_themes', {
            headers: { Accept: 'application/json' },
        });
    });
});
