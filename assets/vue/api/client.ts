const API_URL = import.meta.env.VITE_API_URL;

export class ApiError extends Error {
    constructor(public readonly status: number, message: string) {
        super(message);
    }
}

async function request<T>(path: string, init?: RequestInit): Promise<T> {
    const response = await fetch(`${API_URL}${path}`, {
        headers: { Accept: 'application/ld+json', ...init?.headers },
        ...init,
    });

    if (!response.ok) {
        throw new ApiError(response.status, `Request to ${path} failed`);
    }

    return response.json() as Promise<T>;
}

export default { request };
