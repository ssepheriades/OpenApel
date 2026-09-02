const API_URL = import.meta.env.VITE_API_URL || '/api';

export class ApiError extends Error {
    constructor(public readonly status: number, message: string) {
        super(message);
    }
}

async function request<T>(path: string, init?: RequestInit): Promise<T> {
    const { headers: initHeaders, ...rest } = init ?? {};
    const response = await fetch(`${API_URL}${path}`, {
        ...rest,
        headers: {
            Accept: 'application/ld+json',
            ...initHeaders,
        },
    });

    if (!response.ok) {
        throw new ApiError(response.status, `Request to ${path} failed`);
    }

    return response.json() as Promise<T>;
}

export default { request };
