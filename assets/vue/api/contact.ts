import client from './client';

export interface ContactMessagePayload {
    name: string;
    email: string;
    phone: string | null;
    subject: string;
    message: string;
    schoolClassId: number | null;
    hp?: string;
}

export interface ContactMessageResponse {
    ok: boolean;
}

export async function submitContactMessage(payload: ContactMessagePayload): Promise<ContactMessageResponse> {
    return client.request<ContactMessageResponse>('/contact_messages', {
        method: 'POST',
        headers: {
            Accept: 'application/json',
            'Content-Type': 'application/json',
        },
        body: JSON.stringify(payload),
    });
}
