import { beforeEach, describe, expect, it, vi } from 'vitest';
import client from './client';
import { submitContactMessage } from './contact';

vi.mock('./client', () => ({
    default: { request: vi.fn() },
}));

const mockedRequest = vi.mocked(client.request);

describe('submitContactMessage', () => {
    beforeEach(() => {
        mockedRequest.mockReset();
    });

    it('posts the payload as JSON to /contact_messages', async () => {
        const payload = {
            name: 'Marie Dupont',
            email: 'marie@example.org',
            phone: '0601020304',
            subject: 'Question cantine',
            message: 'Comment ça se passe ?',
            schoolClassId: 3,
        };
        mockedRequest.mockResolvedValue({ ok: true });

        await expect(submitContactMessage(payload)).resolves.toEqual({ ok: true });
        expect(mockedRequest).toHaveBeenCalledWith('/contact_messages', {
            method: 'POST',
            headers: {
                Accept: 'application/json',
                'Content-Type': 'application/json',
            },
            body: JSON.stringify(payload),
        });
    });
});
