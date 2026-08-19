export interface PdfUploadRecord {
    filename: string;
    status: 'success' | 'failed';
    created_at: string;
}

export interface PdfStats {
    labels: string[];
    success: number[];
    failed: number[];
    uploads: PdfUploadRecord[];
}

export type StatsPeriod = 'day' | 'week' | 'month' | 'year';
