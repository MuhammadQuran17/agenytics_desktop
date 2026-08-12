<?php

use App\Models\Feedback;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

describe('FeedBackController', function () {
    beforeEach(function () {
        // Create test data - only in specific tests that need it
    });

    describe('filterData method', function () {
        it('returns all feedbacks when no filters are applied', function () {
            // Create test data for this specific test
            User::factory()->create();
            Feedback::factory()->count(10)->create(['status' => 'planned']);

            $response = $this->getJson('/feedback/filter');

            $response->assertSuccessful();
            $response->assertJsonStructure([
                'data' => [
                    '*' => [
                        'id',
                        'title',
                        'description',
                        'created_at',
                        'status',
                        'upvotes_count',
                        'comments_count',
                        'author',
                        'tags',
                    ],
                ],
            ]);

            expect(count($response->json('data')))->toBe(10);
        });

        it('filters feedbacks by status', function () {
            // Create feedback with specific status
            Feedback::factory()->create(['status' => 'planned']);
            Feedback::factory()->create(['status' => 'completed']);

            $response = $this->getJson('/feedback/filter?status=planned');

            $response->assertSuccessful();
            $data = $response->json('data');

            // All returned feedbacks should have status 'planned'
            foreach ($data as $feedback) {
                expect($feedback['status'])->toBe('planned');
            }
        });

        it('filters feedbacks by tag', function () {
            // Create feedback with specific tags
            Feedback::factory()->create(['tags' => ['bug', 'urgent']]);
            Feedback::factory()->create(['tags' => ['feature']]);
            Feedback::factory()->create(['tags' => ['bug', 'documentation']]);

            $response = $this->getJson('/feedback/filter?tag=bug');

            $response->assertSuccessful();
            $data = $response->json('data');

            // All returned feedbacks should contain 'bug' tag
            foreach ($data as $feedback) {
                expect($feedback['tags'])->toContain('bug');
            }
        });

        it('filters feedbacks by search term in title', function () {
            Feedback::factory()->create(['title' => 'Bug in login system']);
            Feedback::factory()->create(['title' => 'Feature request for dashboard']);
            Feedback::factory()->create(['title' => 'Documentation improvement']);

            $response = $this->getJson('/feedback/filter?search=bug');

            $response->assertSuccessful();
            $data = $response->json('data');

            // All returned feedbacks should contain 'bug' in title
            foreach ($data as $feedback) {
                expect(strtolower($feedback['title']))->toContain('bug');
            }
        });

        it('filters feedbacks created today', function () {
            $today = now()->startOfDay();
            Feedback::factory()->create(['created_at' => $today]);
            Feedback::factory()->create(['created_at' => $today->copy()->addHours(2)]);
            Feedback::factory()->create(['created_at' => $today->copy()->subDay()]); // Yesterday

            $response = $this->getJson('/feedback/filter?time=today');

            $response->assertSuccessful();
            $data = $response->json('data');

            // All returned feedbacks should be from today
            foreach ($data as $feedback) {
                $createdAt = \Carbon\Carbon::parse($feedback['created_at']);
                expect($createdAt->isToday())->toBeTrue();
            }
        });

        it('filters feedbacks created this week', function () {
            $thisWeek = now()->startOfWeek();
            Feedback::factory()->create(['created_at' => $thisWeek]);
            Feedback::factory()->create(['created_at' => $thisWeek->copy()->addDays(3)]);
            Feedback::factory()->create(['created_at' => $thisWeek->copy()->addDays(5)]);
            Feedback::factory()->create(['created_at' => now()]); // Today
            Feedback::factory()->create(['created_at' => $thisWeek->copy()->subWeek()]); // Last week

            $response = $this->getJson('/feedback/filter?time=this_week');

            $response->assertSuccessful();
            $data = $response->json('data');

            // All returned feedbacks should be from this week
            foreach ($data as $feedback) {
                $createdAt = \Carbon\Carbon::parse($feedback['created_at']);
                expect($createdAt->isCurrentWeek())->toBeTrue();
            }

            // Explicitly verify today and yesterday are included
            $todayIncluded = false;
            foreach ($data as $feedback) {
                $createdAt = \Carbon\Carbon::parse($feedback['created_at']);
                if ($createdAt->isToday()) {
                    $todayIncluded = true;
                }
            }
            expect($todayIncluded)->toBeTrue();
        });

        it('filters feedbacks created this month', function () {
            $thisMonth = now()->startOfMonth();
            Feedback::factory()->create(['created_at' => $thisMonth]);
            Feedback::factory()->create(['created_at' => now()]); // Today
            Feedback::factory()->create(['created_at' => now()->subDay()]); // Yesterday
            Feedback::factory()->create(['created_at' => $thisMonth->copy()->addDays(15)]);
            Feedback::factory()->create(['created_at' => $thisMonth->copy()->subMonth()]); // Last month

            $response = $this->getJson('/feedback/filter?time=this_month');

            $response->assertSuccessful();
            $data = $response->json('data');

            // All returned feedbacks should be from this month
            foreach ($data as $feedback) {
                $createdAt = \Carbon\Carbon::parse($feedback['created_at']);
                expect($createdAt->isCurrentMonth())->toBeTrue();
            }
        });

        it('filters feedbacks created this year', function () {
            $thisYear = now()->startOfYear();
            Feedback::factory()->create(['created_at' => $thisYear]);
            Feedback::factory()->create(['created_at' => now()]); // Today
            Feedback::factory()->create(['created_at' => now()->subDay()]); // Yesterday
            Feedback::factory()->create(['created_at' => $thisYear->copy()->addMonths(6)]);
            Feedback::factory()->create(['created_at' => $thisYear->copy()->subYear()]); // Last year

            $response = $this->getJson('/feedback/filter?time=this_year');

            $response->assertSuccessful();
            $data = $response->json('data');

            // All returned feedbacks should be from this year
            foreach ($data as $feedback) {
                $createdAt = \Carbon\Carbon::parse($feedback['created_at']);
                expect($createdAt->isCurrentYear())->toBeTrue();
            }
        });

        it('combines multiple filters correctly', function () {
            // Create feedbacks with different combinations
            Feedback::factory()->create([
                'title' => 'Bug in login',
                'status' => 'planned',
                'tags' => ['bug'],
                'created_at' => now(),
            ]);
            Feedback::factory()->create([
                'title' => 'Feature request',
                'status' => 'planned',
                'tags' => ['feature'],
                'created_at' => now(),
            ]);
            Feedback::factory()->create([
                'title' => 'Another bug',
                'status' => 'completed',
                'tags' => ['bug'],
                'created_at' => now(),
            ]);

            $response = $this->getJson('/feedback/filter?status=planned&tag=bug&search=login&time=today');

            $response->assertSuccessful();
            $data = $response->json('data');

            // Should return only the first feedback that matches all criteria
            expect(count($data))->toBe(1);
            $feedback = $data[0];
            expect($feedback['status'])->toBe('planned');
            expect($feedback['tags'])->toContain('bug');
            expect(strtolower($feedback['title']))->toContain('login');
            expect(\Carbon\Carbon::parse($feedback['created_at'])->isToday())->toBeTrue();
        });

        it('returns empty result when no feedbacks match filters', function () {
            // Create only completed feedbacks, then filter for planned ones
            Feedback::factory()->count(3)->create(['status' => 'completed']);

            $response = $this->getJson('/feedback/filter?status=planned');

            $response->assertSuccessful();
            $data = $response->json('data');
            expect(count($data))->toBe(0);
        });

        it('rejects invalid time filter with validation error', function () {
            // Create test data for this specific test
            User::factory()->create();
            Feedback::factory()->count(5)->create(['status' => 'planned']);

            $response = $this->getJson('/feedback/filter?time=invalid_time');

            $response->assertStatus(422);
            $response->assertJsonValidationErrors(['time']);
        });
    });

    describe('updateStatus method', function () {
        it('allows admin user to update feedback status', function () {
            $adminUser = User::factory()->create(['email' => explode(',', config('feedback.admin_emails'))[0]]);
            $feedback = Feedback::factory()->create(['status' => 'planned']);

            $this->actingAs($adminUser);

            $response = $this->patchJson("/feedback/{$feedback->id}/status", [
                'status' => 'completed',
            ]);

            $response->assertSuccessful();
            $response->assertJson(['status' => 'completed']);

            $feedback->refresh();
            expect($feedback->status)->toBe('completed');
        });

        it('prevents non-admin user from updating feedback status', function () {
            $regularUser = User::factory()->create(['email' => 'user@example.com']);
            $feedback = Feedback::factory()->create(['status' => 'planned']);

            $this->actingAs($regularUser);

            $response = $this->patchJson("/feedback/{$feedback->id}/status", [
                'status' => 'completed',
            ]);

            $response->assertStatus(403);
        });

        it('validates status field', function () {
            $adminUser = User::factory()->create(['email' => explode(',', config('feedback.admin_emails'))[0]]);
            $feedback = Feedback::factory()->create(['status' => 'planned']);

            $this->actingAs($adminUser);

            $response = $this->patchJson("/feedback/{$feedback->id}/status", [
                'status' => 'invalid_status',
            ]);

            $response->assertStatus(422);
            $response->assertJsonValidationErrors(['status']);
        });
    });

    describe('show method', function () {
        it('passes canManageFeedback true for admin users', function () {
            $adminUser = User::factory()->create(['email' => explode(',', config('feedback.admin_emails'))[0]]);
            $feedback = Feedback::factory()->create();

            $this->actingAs($adminUser);

            $response = $this->get("/feedback/{$feedback->id}");

            $response->assertInertia(function ($page) {
                $page->has('canManageFeedback')
                    ->where('canManageFeedback', true);
            });
        });

        it('passes canManageFeedback false for non-admin users', function () {
            $regularUser = User::factory()->create(['email' => 'user@example.com']);
            $feedback = Feedback::factory()->create();

            $this->actingAs($regularUser);

            $response = $this->get("/feedback/{$feedback->id}");

            $response->assertInertia(function ($page) {
                $page->has('canManageFeedback')
                    ->where('canManageFeedback', false);
            });
        });

        it('passes canManageFeedback false for unauthenticated users', function () {
            $feedback = Feedback::factory()->create();

            $response = $this->get("/feedback/{$feedback->id}");

            $response->assertInertia(function ($page) {
                $page->has('canManageFeedback')
                    ->where('canManageFeedback', false);
            });
        });
    });
});
