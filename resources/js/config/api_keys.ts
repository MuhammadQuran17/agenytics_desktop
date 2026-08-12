import type { ApiKeyConfig } from '@/types/api.keys'

export const apiKeyConfigs: ApiKeyConfig[] = [
    {
        key: 'openai',
        label: 'OpenAI API Key',
        placeholder: 'sk-...',
    },
/*     {
        key: 'gemini_key',
        label: 'Google Gemini API Key',
        placeholder: 'AIza...',
    },
    {
        key: 'perplexity_key',
        label: 'Perplexity API Key',
        placeholder: 'pplx-...',
    }, */
];