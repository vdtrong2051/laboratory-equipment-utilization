<?php

use App\Enums\UserRole;
use App\Models\AbnormalPattern;
use App\Models\Equipment;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;

uses(RefreshDatabase::class);

function createDemoManagerForAbnormalPatternTests(): User
{
    $manager = User::factory()->create([
        'name' => 'Abnormal Manager',
        'email' => 'abnormal.manager@lab.local',
        'role' => UserRole::Manager,
        'password' => Hash::make('password'),
        'is_active' => true,
    ]);

    config(['demo.user_email' => $manager->email]);

    return $manager;
}

test('abnormal pattern index renders patterns and filters', function () {
    createDemoManagerForAbnormalPatternTests();
    $equipment = Equipment::factory()->create([
        'equipment_code' => 'ABN-INDEX-001',
        'name' => 'Thiết bị bất thường',
    ]);

    AbnormalPattern::factory()->create([
        'equipment_id' => $equipment->id,
        'usage_session_id' => null,
        'rule_name' => 'DemoRule',
        'severity' => 'critical',
        'status' => 'open',
        'message' => 'Cần kiểm tra ngay',
    ]);

    $this->get(route('abnormal-patterns.index', ['status' => 'open', 'severity' => 'critical']))
        ->assertOk()
        ->assertSee('Bất thường thiết bị')
        ->assertSee('ABN-INDEX-001')
        ->assertSee('Cần kiểm tra ngay')
        ->assertSee('Tiếp nhận xử lý')
        ->assertDontSee('Hoàn tất xử lý');
});

test('abnormal pattern detail renders evidence and actions', function () {
    createDemoManagerForAbnormalPatternTests();
    $equipment = Equipment::factory()->create(['equipment_code' => 'ABN-SHOW-001']);
    $pattern = AbnormalPattern::factory()->create([
        'equipment_id' => $equipment->id,
        'usage_session_id' => null,
        'rule_name' => 'DemoEvidenceRule',
        'status' => 'open',
        'evidence' => ['idle_minutes' => 45],
    ]);

    $this->get(route('abnormal-patterns.show', $pattern))
        ->assertOk()
        ->assertSee('DemoEvidenceRule')
        ->assertSee('Dữ liệu làm căn cứ')
        ->assertSee('DemoEvidenceRule')
        ->assertSee('idle_minutes')
        ->assertSee('Tiếp nhận xử lý')
        ->assertDontSee('Hoàn tất xử lý');
});

test('open abnormal pattern can be reviewed', function () {
    $manager = createDemoManagerForAbnormalPatternTests();
    $pattern = AbnormalPattern::factory()->create([
        'usage_session_id' => null,
        'status' => 'open',
        'reviewed_by' => null,
        'reviewed_at' => null,
    ]);

    $this->post(route('abnormal-patterns.review', $pattern))
        ->assertRedirect(route('abnormal-patterns.show', $pattern));

    expect($pattern->refresh()->status)->toBe('reviewed')
        ->and($pattern->reviewed_by)->toBe($manager->id)
        ->and($pattern->reviewed_at)->not->toBeNull();
});

test('abnormal pattern can be resolved and gets reviewed if needed', function () {
    $manager = createDemoManagerForAbnormalPatternTests();
    $pattern = AbnormalPattern::factory()->create([
        'usage_session_id' => null,
        'status' => 'reviewed',
        'reviewed_by' => $manager->id,
        'reviewed_at' => now(),
        'resolved_by' => null,
        'resolved_at' => null,
    ]);

    $this->post(route('abnormal-patterns.resolve', $pattern), [
        'resolution_note' => 'Đã kiểm tra dữ liệu và hoàn tất xử lý bất thường.',
    ])
        ->assertRedirect(route('abnormal-patterns.show', $pattern));

    expect($pattern->refresh()->status)->toBe('resolved')
        ->and($pattern->reviewed_by)->toBe($manager->id)
        ->and($pattern->reviewed_at)->not->toBeNull()
        ->and($pattern->resolved_by)->toBe($manager->id)
        ->and($pattern->resolved_at)->not->toBeNull()
        ->and($pattern->resolution_note)->toBe('Đã kiểm tra dữ liệu và hoàn tất xử lý bất thường.');
});
