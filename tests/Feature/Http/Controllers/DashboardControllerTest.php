<?php

use App\Models\PdfUpload;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

describe('Dashboard - index', function () {
    it('renders the dashboard with pdf stats scoped to the current user', function () {
        $user = User::factory()->create();
        $otherUser = User::factory()->create();

        PdfUpload::create(['user_id' => $user->id, 'filename' => 'mine.pdf', 'status' => 'success']);
        PdfUpload::create(['user_id' => $otherUser->id, 'filename' => 'not-mine.pdf', 'status' => 'success']);

        $response = $this->actingAs($user)->get(route('dashboard'));

        $response->assertInertia(function ($page) {
            $page->component('Dashboard')
                ->where('period', 'day')
                ->has('pdfStats.labels')
                ->has('pdfStats.success')
                ->has('pdfStats.failed')
                ->has('pdfStats.uploads', 1)
                ->where('pdfStats.uploads.0.filename', 'mine.pdf');
        });
    });

    it('requires authentication', function () {
        $this->get(route('dashboard'))->assertRedirect(route('login'));
    });
});

describe('Dashboard - pdf stats endpoint', function () {
    it('returns success and failed counts for the requested period', function () {
        $user = User::factory()->create();

        PdfUpload::create(['user_id' => $user->id, 'filename' => 'a.pdf', 'status' => 'success']);
        PdfUpload::create(['user_id' => $user->id, 'filename' => 'b.pdf', 'status' => 'success']);
        PdfUpload::create(['user_id' => $user->id, 'filename' => 'c.pdf', 'status' => 'failed', 'error' => 'bad file']);

        $response = $this->actingAs($user)->getJson(route('dashboard.pdf-stats', ['period' => 'day']));

        $response->assertSuccessful();

        $json = $response->json();

        expect(array_sum($json['success']))->toBe(2)
            ->and(array_sum($json['failed']))->toBe(1)
            ->and(count($json['uploads']))->toBe(3);
    });

    it('rejects an invalid period', function () {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->getJson(route('dashboard.pdf-stats', ['period' => 'century']));

        $response->assertUnprocessable();
    });

    it('does not include another user\'s PDFs', function () {
        $user = User::factory()->create();
        $otherUser = User::factory()->create();

        PdfUpload::create(['user_id' => $otherUser->id, 'filename' => 'secret.pdf', 'status' => 'success']);

        $response = $this->actingAs($user)->getJson(route('dashboard.pdf-stats', ['period' => 'day']));

        expect($response->json('uploads'))->toBe([]);
    });
});
