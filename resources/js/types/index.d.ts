export interface User {
    id: number;
    name: string;
    username: string;
    email: string;
    role: 'user' | 'admin';
    is_active: boolean;
    email_verified_at?: string;
    created_at?: string;
    updated_at?: string;
}

export type PageProps<
    T extends Record<string, unknown> = Record<string, unknown>,
> = T & {
    auth: {
        user: User;
    };
};
