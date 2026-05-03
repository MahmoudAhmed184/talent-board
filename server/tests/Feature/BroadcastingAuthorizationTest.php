<?php

use App\Events\ApplicationStatusBroadcast;
use App\Events\ApplicationStatusChanged;
use App\Listeners\BroadcastApplicationStatusChanged;
use App\Models\Application;
use App\Models\User;
use Illuminate\Broadcasting\BroadcastManager;
use Illuminate\Contracts\Events\ShouldDispatchAfterCommit;
use Illuminate\Events\Dispatcher;

function broadcastAuthPayload(string $channel): array
{
    return [
        'socket_id' => '123.456',
        'channel_name' => $channel,
    ];
}

function useBroadcastingAuthDriver(): void
{
    config([
        'broadcasting.default' => 'reverb',
        'broadcasting.connections.reverb.key' => 'test-key',
        'broadcasting.connections.reverb.secret' => 'test-secret',
        'broadcasting.connections.reverb.app_id' => 'test-app',
    ]);

    app(BroadcastManager::class)->forgetDrivers();
    require base_path('routes/channels.php');
}

test('candidate can subscribe only to own application status channel', function () {
    useBroadcastingAuthDriver();
    $candidate = actingAsCandidate();
    $otherCandidate = User::factory()->candidate()->create();

    $this->postJson(
        '/broadcasting/auth',
        broadcastAuthPayload("private-application-status.candidate.{$candidate->id}"),
    )->assertOk();

    $this->postJson(
        '/broadcasting/auth',
        broadcastAuthPayload("private-application-status.candidate.{$otherCandidate->id}"),
    )->assertForbidden();
});

test('employer can subscribe only to own application status channel', function () {
    useBroadcastingAuthDriver();
    $employer = actingAsEmployer();
    $otherEmployer = User::factory()->employer()->create();

    $this->postJson(
        '/broadcasting/auth',
        broadcastAuthPayload("private-application-status.employer.{$employer->id}"),
    )->assertOk();

    $this->postJson(
        '/broadcasting/auth',
        broadcastAuthPayload("private-application-status.employer.{$otherEmployer->id}"),
    )->assertForbidden();
});

test('application status broadcast event exposes candidate and employer private channels', function () {
    $application = Application::factory()->create();
    $event = ApplicationStatusChanged::fromApplication($application);
    $broadcast = new ApplicationStatusBroadcast($event->broadcastPayload());
    $listeners = app(Dispatcher::class)->getListeners(ApplicationStatusChanged::class);
    $channelNames = array_map(
        fn ($channel): string => $channel->name,
        $broadcast->broadcastOn(),
    );

    expect($event)->toBeInstanceOf(ShouldDispatchAfterCommit::class)
        ->and($listeners)->not->toBeEmpty()
        ->and(app(BroadcastApplicationStatusChanged::class))->toBeInstanceOf(BroadcastApplicationStatusChanged::class)
        ->and($broadcast->broadcastAs())->toBe('ApplicationStatusChanged')
        ->and($event->broadcastPayload())->toMatchArray([
            'application_id' => $application->id,
            'candidate_id' => $application->candidate_id,
            'employer_id' => $application->employer_id,
            'status' => $application->status->value,
        ])
        ->and($channelNames)->toContain(
            "private-application-status.candidate.{$application->candidate_id}",
            "private-application-status.employer.{$application->employer_id}",
        );
});
