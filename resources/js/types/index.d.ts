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
    status?: 'not_started' | 'in_progress' | 'completed';
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
    has_passed?: boolean;
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
    status?: 'not_started' | 'in_progress' | 'completed';
    is_completed?: boolean;
}

export interface Question {
    id: number;
    title: string;
    body: string;
    is_resolved: boolean;
    answers_count?: number;
    created_at: string;
    created_at_human?: string;
    is_owner?: boolean;
    user: {
        id?: number;
        name: string;
        username: string;
    };
}

export interface Answer {
    id: number;
    body: string;
    is_best_answer: boolean;
    votes_count: number;
    has_voted: boolean;
    is_own_answer: boolean;
    created_at: string;
    created_at_human?: string;
    user: {
        id?: number;
        name: string;
        username: string;
    };
}

export interface AnswerVote {
    id: number;
    answer_id: number;
    user_id: number;
    created_at: string;
}

export type PageProps<
    T extends Record<string, unknown> = Record<string, unknown>,
> = T & {
    auth: {
        user: User;
    };
    flash?: {
        success?: string;
        error?: string;
    };
};


