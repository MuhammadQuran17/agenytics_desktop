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
}
