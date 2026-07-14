<?php

use App\Enums\OperationalStatus;
use App\Enums\PowerEventType;
use App\Models\ActivitySignal;
use App\Models\Equipment;
use App\Models\PowerEvent;
use App\Rules\Equipment\OperationalStatusRule;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

test('operational status is off when equipment has no power event', function () {
    $equipment = Equipment::factory()->create();

    $decision = app(OperationalStatusRule::class)->classify($equipment);

    expect($decision->status)->toBe(OperationalStatus::Off);
});

test('operational status is off when latest power event is power off', function () {
    $equipment = Equipment::factory()->create();

    PowerEvent::factory()->create([
        'equipment_id' => $equipment->id,
        'event_type' => PowerEventType::PowerOn,
        'recorded_at' => '2026-07-11 09:00:00',
    ]);

    PowerEvent::factory()->create([
        'equipment_id' => $equipment->id,
        'event_type' => PowerEventType::PowerOff,
        'recorded_at' => '2026-07-11 10:00:00',
    ]);

    $decision = app(OperationalStatusRule::class)->classify($equipment);

    expect($decision->status)->toBe(OperationalStatus::Off);
});

test('operational status is powered idle when power is on without later signal', function () {
    $equipment = Equipment::factory()->create();

    PowerEvent::factory()->create([
        'equipment_id' => $equipment->id,
        'event_type' => PowerEventType::PowerOn,
        'recorded_at' => '2026-07-11 09:00:00',
    ]);

    $decision = app(OperationalStatusRule::class)->classify($equipment);

    expect($decision->status)->toBe(OperationalStatus::PoweredIdle);
});

test('operational status is active when latest signal after power on is active', function () {
    $equipment = Equipment::factory()->create();

    PowerEvent::factory()->create([
        'equipment_id' => $equipment->id,
        'event_type' => PowerEventType::PowerOn,
        'recorded_at' => '2026-07-11 09:00:00',
    ]);

    ActivitySignal::factory()->create([
        'equipment_id' => $equipment->id,
        'usage_session_id' => null,
        'is_active' => true,
        'recorded_at' => '2026-07-11 09:05:00',
    ]);

    $decision = app(OperationalStatusRule::class)->classify($equipment);

    expect($decision->status)->toBe(OperationalStatus::Active);
});

test('operational status is powered idle when latest signal after power on is not active', function () {
    $equipment = Equipment::factory()->create();

    PowerEvent::factory()->create([
        'equipment_id' => $equipment->id,
        'event_type' => PowerEventType::PowerOn,
        'recorded_at' => '2026-07-11 09:00:00',
    ]);

    ActivitySignal::factory()->create([
        'equipment_id' => $equipment->id,
        'usage_session_id' => null,
        'is_active' => true,
        'recorded_at' => '2026-07-11 09:05:00',
    ]);

    ActivitySignal::factory()->create([
        'equipment_id' => $equipment->id,
        'usage_session_id' => null,
        'is_active' => false,
        'recorded_at' => '2026-07-11 09:10:00',
    ]);

    $decision = app(OperationalStatusRule::class)->classify($equipment);

    expect($decision->status)->toBe(OperationalStatus::PoweredIdle);
});
