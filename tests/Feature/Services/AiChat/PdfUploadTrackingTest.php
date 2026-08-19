<?php

use App\Jobs\ProcessAiChatMessage;
use App\Models\PdfUpload;
use App\Services\AiChat\PdfTextExtractor;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Bus;

require_once __DIR__.'/helpers.php';

uses(RefreshDatabase::class);

it('records a successful pdf_uploads row for a PDF that parses correctly', function () {
    Bus::fake();

    $user = makeUserWithPrompts(5);
    $sessionId = \Illuminate\Support\Str::uuid()->toString();
    createUserChat($sessionId, $user->id);

    $this->mock(PdfTextExtractor::class, function ($mock) {
        $mock->shouldReceive('extract')->once()->andReturn('Extracted PDF text');
    });

    $this->actingAs($user)->post(route('chat.send'), [
        'message' => 'Summarize this',
        'sessionId' => $sessionId,
        'pdfs' => [UploadedFile::fake()->create('report.pdf', 10, 'application/pdf')],
    ])->assertSuccessful();

    expect(PdfUpload::count())->toBe(1);

    $upload = PdfUpload::first();
    expect($upload->user_id)->toBe($user->id)
        ->and($upload->filename)->toBe('report.pdf')
        ->and($upload->status)->toBe('success')
        ->and($upload->error)->toBeNull();

    Bus::assertDispatched(ProcessAiChatMessage::class);
});

it('records a failed pdf_uploads row when the PDF cannot be parsed, without aborting the request', function () {
    Bus::fake();

    $user = makeUserWithPrompts(5);
    $sessionId = \Illuminate\Support\Str::uuid()->toString();
    createUserChat($sessionId, $user->id);

    $this->mock(PdfTextExtractor::class, function ($mock) {
        $mock->shouldReceive('extract')->once()->andThrow(new \Exception('Corrupt PDF structure'));
    });

    $response = $this->actingAs($user)->post(route('chat.send'), [
        'message' => 'Summarize this',
        'sessionId' => $sessionId,
        'pdfs' => [UploadedFile::fake()->create('broken.pdf', 10, 'application/pdf')],
    ]);

    $response->assertSuccessful();

    $upload = PdfUpload::first();
    expect($upload->filename)->toBe('broken.pdf')
        ->and($upload->status)->toBe('failed')
        ->and($upload->error)->toBe('Corrupt PDF structure');

    Bus::assertDispatched(ProcessAiChatMessage::class);
});

it('records one row per PDF when several are attached, mixing success and failure', function () {
    Bus::fake();

    $user = makeUserWithPrompts(5);
    $sessionId = \Illuminate\Support\Str::uuid()->toString();
    createUserChat($sessionId, $user->id);

    $this->mock(PdfTextExtractor::class, function ($mock) {
        $mock->shouldReceive('extract')->twice()
            ->andReturnUsing(function (UploadedFile $file) {
                if ($file->getClientOriginalName() === 'bad.pdf') {
                    throw new \Exception('Unreadable');
                }

                return 'Some text';
            });
    });

    $this->actingAs($user)->post(route('chat.send'), [
        'message' => 'Process these',
        'sessionId' => $sessionId,
        'pdfs' => [
            UploadedFile::fake()->create('good.pdf', 10, 'application/pdf'),
            UploadedFile::fake()->create('bad.pdf', 10, 'application/pdf'),
        ],
    ])->assertSuccessful();

    expect(PdfUpload::count())->toBe(2);
    expect(PdfUpload::where('filename', 'good.pdf')->value('status'))->toBe('success');
    expect(PdfUpload::where('filename', 'bad.pdf')->value('status'))->toBe('failed');
});
