import client from './client';

export interface TeamMember {
    id: string;
    firstName: string;
    lastName: string;
    position: string | null;
    phone: string | null;
    photoUrl: string | null;
}

export async function fetchTeamMembers(): Promise<TeamMember[]> {
    return client.request<TeamMember[]>('/team_members', {
        headers: { Accept: 'application/json' },
    });
}
