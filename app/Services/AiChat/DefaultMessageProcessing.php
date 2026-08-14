<?php

namespace App\Services\AiChat;

use App\Exceptions\AiChatJobLimitExceededException;
use App\Http\Requests\Api\AiAgent\AiAgentSendMessageRequest;
use App\Jobs\ProcessAiChatMessage;
use App\Services\AiChat\Contracts\MessageProcessingContract;
use App\Services\AiChat\History\AiChatHistory;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Bus;
use Illuminate\Support\Str;

class DefaultMessageProcessing extends MessageProcessingContract
{
    public function __construct(
        private AiChatHistory $aiChatHistory,
        private UserJobLimiter $userJobLimiter,
        private PdfTextExtractor $pdfTextExtractor,
    ) {}

    public function processMessage(AiAgentSendMessageRequest $request): array
    {
        $userId = Auth::user()->id;

        if (! $this->userJobLimiter->canDispatchJob($userId)) {
            throw new AiChatJobLimitExceededException;
        }

        $jobId = (string) Str::uuid();

        $messageForAgent = $this->appendPdfContents($request->message, $request->file('pdfs') ?? []);

        Bus::dispatch(new ProcessAiChatMessage(
            ['message' => $messageForAgent, 'sessionId' => $request->sessionId],
            $userId,
            $jobId,
        ));

        $this->aiChatHistory->saveUserInput($request->message, $request->sessionId, $jobId);

        return [
            'jobId' => $jobId,
            'sessionId' => $request->sessionId,
        ];
    }

    /**
     * @param  UploadedFile[]  $pdfs
     */
    private function appendPdfContents(string $message, array $pdfs): string
    {
        if ($pdfs === []) {
            return $message;
        }

        $sections = [$message];

        foreach ($pdfs as $index => $pdf) {
            $number = $index + 1;
            $sections[] = "--- Attached PDF {$number} ({$pdf->getClientOriginalName()}) ---\n".$this->pdfTextExtractor->extract($pdf);
        }

        return implode("\n\n", $sections);
    }
}
