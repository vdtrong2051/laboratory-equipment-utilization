<?php

namespace App\Http\Controllers;

use App\Enums\BookingStatus;
use App\Http\Requests\StoreBookingRequest;
use App\Models\Booking;
use App\Models\Equipment;
use App\Models\User;
use App\Services\BookingService;
use App\Services\CheckInService;
use App\Services\Flow\BookingFlowPresenter;
use App\Services\UserContextService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\Response;

class BookingController extends Controller
{
    public function __construct(
        private readonly BookingService $bookingService,
        private readonly CheckInService $checkInService,
        private readonly UserContextService $userContext,
        private readonly BookingFlowPresenter $bookingFlowPresenter,
    ) {}

    public function index(): View
    {
        $currentUser = $this->userContext->current();
        $filters = request()->only(['status', 'equipment_id', 'user_id', 'date']);

        if ($currentUser->isResearcher()) {
            $filters['user_id'] = $currentUser->id;
        }

        return view('bookings.index', [
            'bookings' => $this->bookingService->paginatedList($filters),
            'equipments' => Equipment::query()->orderBy('equipment_code')->get(),
            'users' => $this->bookingUsers($currentUser),
            'statuses' => BookingStatus::cases(),
            'currentUser' => $currentUser,
        ]);
    }

    public function create(): View
    {
        $currentUser = $this->userContext->current();

        return view('bookings.create', [
            'booking' => new Booking([
                'equipment_id' => request('equipment_id'),
                'start_time' => now()->addHour()->startOfHour(),
                'end_time' => now()->addHours(3)->startOfHour(),
            ]),
            'equipments' => Equipment::query()->orderBy('equipment_code')->get(),
            'users' => $this->bookingUsers($currentUser),
            'currentUser' => $currentUser,
        ]);
    }

    public function store(StoreBookingRequest $request): RedirectResponse|JsonResponse
    {
        $currentUser = $this->userContext->current();
        $data = $request->validated();

        if ($currentUser->isResearcher()) {
            $data['user_id'] = $currentUser->id;
        }

        $booking = $this->bookingService->create($data, $currentUser);

        if ($request->expectsJson()) {
            return response()->json([
                'message' => 'Đã tạo lịch đặt thiết bị.',
                'data' => ['booking_id' => $booking->id],
                'refresh' => ['target' => '#bookings-table'],
            ]);
        }

        return redirect()
            ->route('bookings.show', $booking)
            ->with('status', 'Đã tạo lịch đặt thiết bị.');
    }

    public function show(Booking $booking): View
    {
        $this->ensureCanViewBooking($booking);

        return view('bookings.show', [
            'booking' => $booking->load(['equipment', 'user', 'creator', 'usageSession']),
            'flow' => $this->bookingFlowPresenter->present($booking),
        ]);
    }

    public function checkIn(Request $request, Booking $booking): RedirectResponse|JsonResponse
    {
        $usageSession = $this->checkInService->checkIn($booking, $this->userContext->current());

        if ($request->expectsJson()) {
            return response()->json([
                'message' => 'Check-in thành công.',
                'data' => ['usage_session_id' => $usageSession->id],
                'refresh' => ['target' => '#today-bookings'],
            ]);
        }

        return redirect()
            ->route('usage-sessions.show', $usageSession)
            ->with('status', 'Đã check-in và tạo phiên sử dụng.');
    }

    public function destroy(Request $request, Booking $booking): RedirectResponse|JsonResponse
    {
        $this->ensureCanViewBooking($booking);

        $this->bookingService->cancel($booking);

        if ($request->expectsJson()) {
            return response()->json([
                'message' => 'Đã huỷ lịch đặt thiết bị.',
                'data' => ['booking_id' => $booking->id],
                'refresh' => ['target' => '#bookings-table'],
            ]);
        }

        return redirect()
            ->route('bookings.index')
            ->with('status', 'Đã huỷ lịch đặt thiết bị.');
    }

    private function bookingUsers(User $currentUser)
    {
        if ($currentUser->isResearcher()) {
            return User::query()
                ->whereKey($currentUser->id)
                ->orderBy('name')
                ->get();
        }

        return User::query()
            ->where('is_active', true)
            ->orderBy('name')
            ->get();
    }

    private function ensureCanViewBooking(Booking $booking): void
    {
        $currentUser = $this->userContext->current();

        if ($currentUser->canOperateLab() || $currentUser->canManageSystem()) {
            return;
        }

        if ($currentUser->isResearcher() && (int) $booking->user_id === (int) $currentUser->id) {
            return;
        }

        abort(Response::HTTP_FORBIDDEN, 'Vai trò demo hiện tại không thể truy cập booking này.');
    }
}
