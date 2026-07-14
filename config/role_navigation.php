<?php

use App\Services\Insights\AdminInsightService;
use App\Services\Insights\LabStaffInsightService;
use App\Services\Insights\ManagerInsightService;
use App\Services\Insights\ResearcherInsightService;

return [
    'workspaces' => [
        'admin' => [
            'title' => 'Tổng quan quản trị',
            'subtitle' => 'Quản lý dữ liệu nền, thiết bị và hoạt động hệ thống.',
            'navigation' => [
                [
                    'title' => 'Quản trị',
                    'items' => [
                        ['label' => 'Tổng quan', 'route' => 'dashboard.admin', 'icon' => 'QT'],
                        ['label' => 'Thiết bị', 'route' => 'equipments.index', 'icon' => 'TB'],
                        ['label' => 'Lịch sử phân tích', 'route' => 'analysis-runs.index', 'icon' => 'PT'],
                    ],
                ],
            ],
            'quick_actions' => [
                ['label' => 'Thêm thiết bị', 'route' => 'equipments.create', 'variant' => 'primary', 'modal_id' => 'create-equipment-modal'],
            ],
            'dashboard_sections' => ['kpis', 'system_insight', 'next_actions', 'system_tables'],
            'insight_service' => AdminInsightService::class,
        ],
        'manager' => [
            'title' => 'Tổng quan khai thác',
            'subtitle' => 'Theo dõi mức sử dụng, thiết bị cần chú ý và các bất thường cần xử lý.',
            'navigation' => [
                [
                    'title' => 'Khai thác thiết bị',
                    'items' => [
                        ['label' => 'Tổng quan khai thác', 'route' => 'dashboard.manager', 'icon' => 'MG'],
                        ['label' => 'Thiết bị', 'route' => 'equipments.index', 'icon' => 'TB'],
                        ['label' => 'Bất thường', 'route' => 'abnormal-patterns.index', 'icon' => 'AB'],
                        ['label' => 'Lịch sử phân tích', 'route' => 'analysis-runs.index', 'icon' => 'PT'],
                    ],
                ],
            ],
            'quick_actions' => [
                ['label' => 'Chạy phân tích', 'route' => 'dashboard.manager.run-analysis', 'method' => 'POST', 'variant' => 'primary', 'modal_id' => 'run-batch-analysis-modal'],
            ],
            'dashboard_sections' => ['context_summary', 'natural_language_insight', 'next_actions', 'critical_data_tables'],
            'insight_service' => ManagerInsightService::class,
        ],
        'lab_staff' => [
            'title' => 'Lab Staff Workspace',
            'subtitle' => 'Ưu tiên ca trực hôm nay: check-in, phiên đang chạy và thiết bị bật nhưng rảnh.',
            'navigation' => [
                [
                    'title' => 'Vận hành lab',
                    'items' => [
                        ['label' => 'Tổng quan ca trực', 'route' => 'dashboard.lab-staff', 'icon' => 'LS'],
                        ['label' => 'Lịch đặt', 'route' => 'bookings.index', 'icon' => 'BK'],
                        ['label' => 'Phiên sử dụng', 'route' => 'usage-sessions.index', 'icon' => 'US'],
                        ['label' => 'Thiết bị', 'route' => 'equipments.index', 'icon' => 'TB'],
                    ],
                ],
            ],
            'quick_actions' => [
                ['label' => 'Mở lịch đặt', 'route' => 'bookings.index', 'variant' => 'primary'],
                ['label' => 'Phiên đang chạy', 'route' => 'usage-sessions.index'],
                ['label' => 'Kiểm tra thiết bị', 'route' => 'equipments.index'],
            ],
            'dashboard_sections' => ['context_summary', 'natural_language_insight', 'next_actions', 'operational_tables'],
            'insight_service' => LabStaffInsightService::class,
        ],
        'researcher' => [
            'title' => 'Researcher Workspace',
            'subtitle' => 'Tập trung vào lịch đặt cá nhân, phiên sắp tới và thiết bị có thể dùng.',
            'navigation' => [
                [
                    'title' => 'Sử dụng thiết bị',
                    'items' => [
                        ['label' => 'Tổng quan cá nhân', 'route' => 'dashboard.researcher', 'icon' => 'RS'],
                        ['label' => 'Tạo lịch đặt', 'route' => 'bookings.create', 'icon' => '+'],
                        ['label' => 'Lịch đặt của tôi', 'route' => 'bookings.index', 'icon' => 'BK'],
                        ['label' => 'Thiết bị', 'route' => 'equipments.index', 'icon' => 'TB'],
                    ],
                ],
            ],
            'quick_actions' => [
                ['label' => 'Tạo lịch đặt', 'route' => 'bookings.create', 'variant' => 'primary', 'modal_id' => 'create-booking-modal'],
                ['label' => 'Lịch đặt của tôi', 'route' => 'bookings.index'],
                ['label' => 'Tìm thiết bị', 'route' => 'equipments.index'],
            ],
            'dashboard_sections' => ['context_summary', 'natural_language_insight', 'next_actions', 'personal_tables'],
            'insight_service' => ResearcherInsightService::class,
        ],
    ],
];
