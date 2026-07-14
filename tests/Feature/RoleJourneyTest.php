<?php

use App\Enums\AnalysisStatus;
use App\Enums\BookingStatus;
use App\Enums\OperationalStatus;
use App\Enums\UsageMode;
use App\Enums\UserRole;
use App\Models\AbnormalPattern;
use App\Models\Booking;
use App\Models\Equipment;
use App\Models\UsageSession;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

function useRoleJourneyUser(UserRole $role, string $suffix): User
{
    $user = User::factory()->create([
        'email' => $role->value.'.role-journey-'.$suffix.'@lab.local',
        'role' => $role,
        'is_active' => true,
    ]);

    config(['demo.user_email' => $user->email]);

    return $user;
}

function roleJourneyEquipmentPayload(array $overrides = []): array
{
    return [
        'equipment_code' => 'RJ-EQ-'.fake()->unique()->numberBetween(1000, 9999),
        'name' => 'Thiết bị kiểm thử hành trình',
        'type' => 'Journey Type',
        'laboratory' => 'Lab Journey',
        'usage_mode' => UsageMode::OnSite->value,
        'allowed_usage_duration_minutes' => 120,
        ...$overrides,
    ];
}

test('admin journey covers dashboard equipment modal and system views', function () {
    useRoleJourneyUser(UserRole::Admin, 'admin');

    $this->get(route('dashboard.admin'))
        ->assertOk()
        ->assertSee('Tổng quan quản trị')
        ->assertSee('create-equipment-modal')
        ->assertSee('Tình trạng hệ thống');

    $payload = roleJourneyEquipmentPayload([
        'equipment_code' => 'RJ-ADMIN-001',
        'name' => 'Thiết bị admin tạo nhanh',
    ]);

    $this->postJson(route('equipments.store'), $payload)
        ->assertOk()
        ->assertJsonStructure(['message', 'data' => ['equipment_id'], 'refresh' => ['target']]);

    $equipment = Equipment::query()->where('equipment_code', 'RJ-ADMIN-001')->firstOrFail();

    $this->get(route('equipments.index'))
        ->assertOk()
        ->assertSee('RJ-ADMIN-001')
        ->assertSee('Thêm thiết bị')
        ->assertSee('data-modal-open="edit-equipment-'.$equipment->id.'"', false);

    $this->putJson(route('equipments.update', $equipment), [
        ...$payload,
        'name' => 'Thiết bị admin đã cập nhật',
        'usage_mode' => UsageMode::Loan->value,
    ])
        ->assertOk()
        ->assertJsonStructure(['message', 'data' => ['equipment_id'], 'refresh' => ['target']]);

    $this->assertDatabaseHas('equipments', [
        'id' => $equipment->id,
        'name' => 'Thiết bị admin đã cập nhật',
        'usage_mode' => UsageMode::Loan->value,
    ]);
});

test('manager journey covers insight critical equipment analysis and review', function () {
    $manager = useRoleJourneyUser(UserRole::Manager, 'manager');
    $equipment = Equipment::factory()->create([
        'equipment_code' => 'RJ-MANAGER-001',
        'current_analysis_status' => AnalysisStatus::CapacityPressure,
    ]);
    $pattern = AbnormalPattern::factory()->create([
        'equipment_id' => $equipment->id,
        'usage_session_id' => null,
        'rule_name' => 'CapacityPressureRule',
        'status' => 'open',
    ]);

    $this->get(route('dashboard.manager'))
        ->assertOk()
        ->assertSee('Tổng quan khai thác')
        ->assertSee('Nhận định')
        ->assertSee('Việc cần xử lý')
        ->assertSee('RJ-MANAGER-001');

    $this->get(route('equipments.show', $equipment))
        ->assertOk()
        ->assertSee('Tình trạng');

    $this->postJson(route('dashboard.manager.run-analysis'))
        ->assertOk()
        ->assertJsonStructure(['message', 'data', 'refresh' => ['target']]);

    $this->postJson(route('abnormal-patterns.review', $pattern))
        ->assertOk()
        ->assertJsonPath('message', 'Đã review bất thường.');

    expect($pattern->refresh()->reviewed_by)->toBe($manager->id)
        ->and($pattern->status)->toBe('reviewed');
});

test('lab staff journey covers today bookings check in running session and completion', function () {
    $labStaff = useRoleJourneyUser(UserRole::LabStaff, 'lab');
    $researcher = User::factory()->create(['role' => UserRole::Researcher, 'is_active' => true]);
    $equipment = Equipment::factory()->create(['equipment_code' => 'RJ-LAB-001']);
    $booking = Booking::factory()->create([
        'equipment_id' => $equipment->id,
        'user_id' => $researcher->id,
        'created_by' => $researcher->id,
        'start_time' => now()->subMinutes(10),
        'end_time' => now()->addHour(),
        'status' => BookingStatus::Booked,
        'purpose' => 'Kiểm thử check-in theo hành trình',
    ]);

    $this->get(route('dashboard.lab-staff'))
        ->assertOk()
        ->assertSee('Lab Staff Workspace')
        ->assertSee('Booking cần theo dõi')
        ->assertSee('RJ-LAB-001');

    $this->postJson(route('bookings.check-in', $booking))
        ->assertOk()
        ->assertJsonStructure(['message', 'data' => ['usage_session_id'], 'refresh' => ['target']]);

    $usageSession = UsageSession::query()->where('booking_id', $booking->id)->firstOrFail();

    $this->get(route('usage-sessions.show', $usageSession))
        ->assertOk()
        ->assertSee('Luồng nghiệp vụ')
        ->assertSee('RJ-LAB-001');

    $this->postJson(route('usage-sessions.complete', $usageSession))
        ->assertOk()
        ->assertJsonStructure(['message', 'data' => ['usage_session_id'], 'refresh' => ['target']]);

    expect($usageSession->refresh()->status)->toBe(BookingStatus::Completed)
        ->and($usageSession->completed_by)->toBe($labStaff->id);
});

test('researcher journey covers search booking personal schedule and cancellation', function () {
    $researcher = useRoleJourneyUser(UserRole::Researcher, 'researcher');
    $equipment = Equipment::factory()->create([
        'equipment_code' => 'RJ-RESEARCH-001',
        'name' => 'Máy tìm kiếm hành trình',
    ]);

    $this->get(route('equipments.index', ['search' => 'RJ-RESEARCH-001']))
        ->assertOk()
        ->assertSee('RJ-RESEARCH-001')
        ->assertSee('Máy tìm kiếm hành trình');

    $this->postJson(route('bookings.store'), [
        'equipment_id' => $equipment->id,
        'user_id' => $researcher->id,
        'start_time' => now()->addHours(2)->toDateTimeString(),
        'end_time' => now()->addHours(3)->toDateTimeString(),
        'purpose' => 'Kiểm thử đặt lịch cá nhân',
    ])
        ->assertOk()
        ->assertJsonStructure(['message', 'data' => ['booking_id'], 'refresh' => ['target']]);

    $booking = Booking::query()->where('equipment_id', $equipment->id)->firstOrFail();

    $this->get(route('bookings.index'))
        ->assertOk()
        ->assertSee('RJ-RESEARCH-001')
        ->assertSee('Kiểm thử đặt lịch cá nhân');

    $this->deleteJson(route('bookings.destroy', $booking))
        ->assertOk()
        ->assertJsonStructure(['message', 'data' => ['booking_id'], 'refresh' => ['target']]);

    expect($booking->refresh()->status)->toBe(BookingStatus::Cancelled);
});
