<?php

use App\Enums\AnalysisStatus;
use App\Enums\BookingStatus;
use App\Enums\UserRole;
use App\Models\ActivitySignal;
use App\Models\AnalysisRun;
use App\Models\Booking;
use App\Models\Equipment;
use App\Models\UsageMetric;
use App\Models\UsageSession;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

function useFlowStepperUser(UserRole $role): User
{
    $user = User::factory()->create([
        'email' => $role->value.'.flow-stepper@lab.local',
        'role' => $role,
        'is_active' => true,
    ]);

    config(['demo.user_email' => $user->email]);

    return $user;
}

test('booking detail explains booking flow', function () {
    $researcher = useFlowStepperUser(UserRole::Researcher);
    $equipment = Equipment::factory()->create(['equipment_code' => 'FLOW-BOOK-001']);
    $booking = Booking::factory()->create([
        'equipment_id' => $equipment->id,
        'user_id' => $researcher->id,
        'created_by' => $researcher->id,
        'status' => BookingStatus::Booked,
        'purpose' => 'Kiểm tra luồng booking',
    ]);

    $this->get(route('bookings.show', $booking))
        ->assertOk()
        ->assertSee('Luồng nghiệp vụ')
        ->assertSee('Select Equipment')
        ->assertSee('Select Time')
        ->assertSee('Add Purpose')
        ->assertSee('Confirm Booking');
});

test('usage session detail explains telemetry usage flow', function () {
    $labStaff = useFlowStepperUser(UserRole::LabStaff);
    $researcher = User::factory()->create(['role' => UserRole::Researcher]);
    $equipment = Equipment::factory()->create();
    $booking = Booking::factory()->create([
        'equipment_id' => $equipment->id,
        'user_id' => $researcher->id,
        'created_by' => $labStaff->id,
        'status' => BookingStatus::CheckedIn,
    ]);
    $session = UsageSession::factory()->create([
        'booking_id' => $booking->id,
        'equipment_id' => $equipment->id,
        'user_id' => $researcher->id,
        'checked_in_by' => $labStaff->id,
        'status' => BookingStatus::CheckedIn,
    ]);
    ActivitySignal::factory()->create(['usage_session_id' => $session->id, 'equipment_id' => $equipment->id]);

    $this->get(route('usage-sessions.show', $session))
        ->assertOk()
        ->assertSee('Luồng nghiệp vụ')
        ->assertSee('Checked In')
        ->assertSee('Session Running')
        ->assertSee('Telemetry Received')
        ->assertSee('Completed');
});

test('analysis run detail explains analysis pipeline', function () {
    $manager = useFlowStepperUser(UserRole::Manager);
    $run = AnalysisRun::factory()->create([
        'triggered_by' => $manager->id,
        'status' => 'completed',
        'processed_equipment' => 4,
        'usage_metrics_calculated' => 4,
        'rule_sets_evaluated' => 4,
        'matched_rules' => 2,
    ]);

    $this->get(route('analysis-runs.show', $run))
        ->assertOk()
        ->assertSee('Usage Data')
        ->assertSee('Metrics Calculated')
        ->assertSee('Rules Evaluated')
        ->assertSee('Pattern Detected')
        ->assertSee('Management Insight');
});

test('equipment detail summarizes operational context without full stepper', function () {
    useFlowStepperUser(UserRole::Manager);
    $equipment = Equipment::factory()->create([
        'equipment_code' => 'FLOW-EQ-001',
        'current_analysis_status' => AnalysisStatus::CapacityPressure,
    ]);
    UsageSession::factory()->create(['equipment_id' => $equipment->id]);
    UsageMetric::factory()->create(['equipment_id' => $equipment->id]);

    $this->get(route('equipments.show', $equipment))
        ->assertOk()
        ->assertSee('Tình trạng')
        ->assertSee('Dữ liệu liên quan')
        ->assertSee('Chỉ số khai thác')
        ->assertSee('Bất thường đang mở')
        ->assertDontSee('Usage Data');
});
