import client from './client';

export interface Grade {
    id: number;
    name: string;
}

export interface SchoolClass {
    id: number;
    name: string;
}

interface CollectionPayload<T> {
    member?: T[];
    'hydra:member'?: T[];
}

function unwrapCollection<T>(data: T[] | CollectionPayload<T>): T[] {
    if (Array.isArray(data)) {
        return data;
    }

    return data.member ?? data['hydra:member'] ?? [];
}

export async function fetchSchoolClasses(): Promise<SchoolClass[]> {
    const data = await client.request<SchoolClass[] | CollectionPayload<SchoolClass>>('/school_classes', {
        headers: { Accept: 'application/json' },
    });

    return unwrapCollection(data).map((schoolClass) => ({
        id: schoolClass.id,
        name: schoolClass.name,
    }));
}

