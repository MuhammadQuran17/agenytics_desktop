export interface ApiKeys {
    openai?: string;
    gemini?: string;
}

export interface ApiKeyConfig {
    key: keyof ApiKeys;
    label: string;
    placeholder: string;
};
