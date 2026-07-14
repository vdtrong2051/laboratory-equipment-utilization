<?php

use App\Models\AbnormalPattern;
use App\Models\ActivitySignal;
use App\Models\Booking;
use App\Models\Equipment;
use App\Models\PowerEvent;
use App\Models\UsageMetric;
use App\Models\UsageSession;
use App\Models\User;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

test('equipment exposes core utilization relationships', function () {
    $equipment = new Equipment;

    expect($equipment->bookings())->toBeInstanceOf(HasMany::class)
        ->and($equipment->usageSessions())->toBeInstanceOf(HasMany::class)
        ->and($equipment->powerEvents())->toBeInstanceOf(HasMany::class)
        ->and($equipment->activitySignals())->toBeInstanceOf(HasMany::class)
        ->and($equipment->usageMetrics())->toBeInstanceOf(HasMany::class)
        ->and($equipment->abnormalPatterns())->toBeInstanceOf(HasMany::class);
});

test('booking links user equipment and usage session', function () {
    $booking = new Booking;

    expect($booking->equipment())->toBeInstanceOf(BelongsTo::class)
        ->and($booking->user())->toBeInstanceOf(BelongsTo::class)
        ->and($booking->creator())->toBeInstanceOf(BelongsTo::class)
        ->and($booking->usageSession())->toBeInstanceOf(HasOne::class);
});

test('usage session links operational context', function () {
    $usageSession = new UsageSession;

    expect($usageSession->booking())->toBeInstanceOf(BelongsTo::class)
        ->and($usageSession->equipment())->toBeInstanceOf(BelongsTo::class)
        ->and($usageSession->user())->toBeInstanceOf(BelongsTo::class)
        ->and($usageSession->checkedInBy())->toBeInstanceOf(BelongsTo::class)
        ->and($usageSession->completedBy())->toBeInstanceOf(BelongsTo::class)
        ->and($usageSession->activitySignals())->toBeInstanceOf(HasMany::class)
        ->and($usageSession->abnormalPatterns())->toBeInstanceOf(HasMany::class);
});

test('signal metric and abnormal models link back to equipment', function () {
    expect((new PowerEvent)->equipment())->toBeInstanceOf(BelongsTo::class)
        ->and((new PowerEvent)->recordedBy())->toBeInstanceOf(BelongsTo::class)
        ->and((new ActivitySignal)->equipment())->toBeInstanceOf(BelongsTo::class)
        ->and((new ActivitySignal)->usageSession())->toBeInstanceOf(BelongsTo::class)
        ->and((new ActivitySignal)->recordedBy())->toBeInstanceOf(BelongsTo::class)
        ->and((new UsageMetric)->equipment())->toBeInstanceOf(BelongsTo::class)
        ->and((new AbnormalPattern)->equipment())->toBeInstanceOf(BelongsTo::class)
        ->and((new AbnormalPattern)->usageSession())->toBeInstanceOf(BelongsTo::class)
        ->and((new AbnormalPattern)->reviewedBy())->toBeInstanceOf(BelongsTo::class)
        ->and((new AbnormalPattern)->resolvedBy())->toBeInstanceOf(BelongsTo::class);
});

test('user exposes role based activity relationships', function () {
    $user = new User;

    expect($user->bookings())->toBeInstanceOf(HasMany::class)
        ->and($user->createdBookings())->toBeInstanceOf(HasMany::class)
        ->and($user->usageSessions())->toBeInstanceOf(HasMany::class)
        ->and($user->checkedInSessions())->toBeInstanceOf(HasMany::class)
        ->and($user->completedSessions())->toBeInstanceOf(HasMany::class);
});
