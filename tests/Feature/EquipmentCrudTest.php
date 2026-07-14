<?php

use App\Enums\UsageMode;
use App\Enums\UserRole;
use App\Models\Equipment;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

function useAdminForEquipmentCrudTests(): User
{
    $admin = User::factory()->create([
        'email' => 'admin.equipment-crud@lab.local',
        'role' => UserRole::Admin,
        'is_active' => true,
    ]);

    config(['demo.user_email' => $admin->email]);

    return $admin;
}

function useResearcherForEquipmentCrudTests(): User
{
    $researcher = User::factory()->create([
        'email' => 'researcher.equipment-crud@lab.local',
        'role' => UserRole::Researcher,
        'is_active' => true,
    ]);

    config(['demo.user_email' => $researcher->email]);

    return $researcher;
}

test('equipment index renders seeded equipment list', function () {
    useResearcherForEquipmentCrudTests();

    $equipment = Equipment::factory()->create([
        'equipment_code' => 'TEST-INDEX-001',
        'name' => 'Thiết bị kiểm tra danh sách',
        'laboratory' => 'Lab Test',
    ]);

    $this->get(route('equipments.index', ['search' => 'TEST-INDEX-001']))
        ->assertOk()
        ->assertSee($equipment->equipment_code)
        ->assertSee($equipment->name);
});

test('equipment detail renders linked data counters', function () {
    useResearcherForEquipmentCrudTests();

    $equipment = Equipment::factory()->create([
        'equipment_code' => 'TEST-SHOW-001',
        'name' => 'Thiết bị kiểm tra chi tiết',
    ]);

    $this->get(route('equipments.show', $equipment))
        ->assertOk()
        ->assertSee('Tổng quan')
        ->assertSee($equipment->equipment_code)
        ->assertSee('Dữ liệu liên quan');
});

test('equipment can be created from form request', function () {
    useAdminForEquipmentCrudTests();

    $payload = [
        'equipment_code' => 'TEST-CREATE-001',
        'name' => 'Thiết bị tạo từ test',
        'type' => 'Demo Type',
        'laboratory' => 'Lab Test',
        'usage_mode' => UsageMode::OnSite->value,
        'allowed_usage_duration_minutes' => 120,
    ];

    $this->post(route('equipments.store'), $payload)
        ->assertRedirect();

    $this->assertDatabaseHas('equipments', [
        'equipment_code' => 'TEST-CREATE-001',
        'name' => 'Thiết bị tạo từ test',
    ]);
});

test('equipment can be updated from form request', function () {
    useAdminForEquipmentCrudTests();

    $equipment = Equipment::factory()->create([
        'equipment_code' => 'TEST-UPDATE-001',
        'name' => 'Tên trước cập nhật',
    ]);

    $this->put(route('equipments.update', $equipment), [
        'equipment_code' => 'TEST-UPDATE-001',
        'name' => 'Tên sau cập nhật',
        'type' => 'Updated Type',
        'laboratory' => 'Lab Update',
        'usage_mode' => UsageMode::Loan->value,
        'allowed_usage_duration_minutes' => 90,
    ])->assertRedirect(route('equipments.show', $equipment));

    $this->assertDatabaseHas('equipments', [
        'id' => $equipment->id,
        'name' => 'Tên sau cập nhật',
        'usage_mode' => UsageMode::Loan->value,
    ]);
});

test('equipment update ignores derived fields owned by system services', function () {
    useAdminForEquipmentCrudTests();

    $equipment = Equipment::factory()->create([
        'equipment_code' => 'TEST-DERIVED-001',
        'utilization_rate' => 8,
    ]);

    $this->put(route('equipments.update', $equipment), [
        'equipment_code' => 'TEST-DERIVED-001',
        'name' => 'Tên sau cập nhật metadata',
        'type' => 'Updated Type',
        'laboratory' => 'Lab Metadata',
        'usage_mode' => UsageMode::Loan->value,
        'allowed_usage_duration_minutes' => 90,
        'utilization_rate' => 99,
        'last_used_at' => now()->toDateTimeString(),
    ])->assertRedirect(route('equipments.show', $equipment));

    expect((float) $equipment->refresh()->utilization_rate)->toBe(8.0)
        ->and($equipment->last_used_at)->toBeNull();
});
