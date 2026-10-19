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

export interface Module {
    id: number;
    title: string;
    slug: string;
    description: string;
    order: number;
    is_published?: boolean;
    lessons_count?: number;
    completion_percentage?: number;
    lessons?: Lesson[];
}

export interface Exercise {
    id: number;
    lesson_id?: number;
    title: string;
    instruction: string;
    validation_type: 'system_state' | 'output' | 'combined';
    validation_config?: Record<string, any>;
    points: number;
    order: number;
    hints?: Hint[];
}

export interface Hint {
    id: number;
    exercise_id: number;
    content: string;
    order: number;
}

export interface ExerciseAttempt {
    id: number;
    user_id: number;
    exercise_id: number;
    terminal_session_id?: number | null;
    is_correct: boolean;
    score: number;
    feedback: string;
    created_at: string;
}

export interface Lesson {
    id: number;
    module_id?: number;
    title: string;
    slug: string;
    content?: string;
    example_commands?: string | null;
    cheat_sheet?: string | null;
    order: number;
    exercises?: Exercise[];
}

export type PageProps<
    T extends Record<string, unknown> = Record<string, unknown>,
> = T & {
    auth: {
        user: User;
    };
};


