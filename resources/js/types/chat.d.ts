// used for shared inertia property, UserChats, which accessible in all pages
export interface UserChat {
    id: number;
    session_id: string;
    created_at: string;
}

export interface Message {
    // Object for response, string for user input
    content: object | string;
    role: 'user' | 'assistant';
    created_at: string;
    // The tool-call log recorded while the assistant produced this message
    steps?: { message: string; status: 'in_progress' | 'done' }[];
    // Identifies the turn (question + answer share one) - lets the UI target
    // a specific message for edit/retry/rate instead of just its array index
    jobId?: string;
    // Assistant messages only: the user's thumbs up/down on this answer
    rating?: 'good' | 'bad' | null;
    // Assistant messages only: whether this turn's job ultimately failed, and
    // why - loaded from the database so the failure survives a page reload
    // or app restart, unlike the in-memory "Retry" banner shown while live.
    jobStatus?: 'processing' | 'completed' | 'failed';
    error?: string | null;
}
