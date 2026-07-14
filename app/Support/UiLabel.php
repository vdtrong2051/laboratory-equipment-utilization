<?php

namespace App\Support;

use App\Enums\AnalysisStatus;
use App\Enums\OperationalStatus;

class UiLabel
{
    public static function analysisStatus(?string $status): string
    {
        return match ($status) {
            AnalysisStatus::Normal->value => 'Khai thác bình thường',
            AnalysisStatus::Underutilized->value => 'Ít được sử dụng',
            AnalysisStatus::CapacityPressure->value => 'Áp lực công suất',
            AnalysisStatus::IdleWhilePowered->value => 'Bật không hoạt động',
            default => $status ?? '-',
        };
    }

    public static function operationalStatus(?string $status): string
    {
        return match ($status) {
            OperationalStatus::Off->value => 'Đang tắt',
            OperationalStatus::PoweredIdle->value => 'Bật không hoạt động',
            OperationalStatus::Active->value => 'Đang sử dụng',
            default => $status ?? '-',
        };
    }

    public static function abnormalRule(?string $ruleName): string
    {
        return match (class_basename($ruleName ?? '')) {
            'DemoDataQualityRule' => 'Chất lượng dữ liệu không hợp lệ',
            'IdleWhilePoweredRule' => 'Bật nhưng không hoạt động',
            'UnderutilizedRule' => 'Không sử dụng dài ngày',
            'CapacityPressureRule' => 'Áp lực công suất',
            'NoShowRule' => 'Không đến sử dụng',
            'OverdueUsageRule' => 'Quá hạn sử dụng',
            default => class_basename($ruleName ?? '-') ?: '-',
        };
    }

    public static function abnormalSeverity(?string $severity): string
    {
        return match ($severity) {
            'info' => 'Thông tin',
            'warning' => 'Cảnh báo',
            'critical' => 'Nghiêm trọng',
            default => $severity ?? '-',
        };
    }

    public static function abnormalStatus(?string $status): string
    {
        return match ($status) {
            'open' => 'Đang mở',
            'reviewed' => 'Đang xử lý',
            'resolved' => 'Đã hoàn tất',
            default => $status ?? '-',
        };
    }

    public static function abnormalMessage(?string $ruleName, ?string $message): string
    {
        if (class_basename($ruleName ?? '') === 'DemoDataQualityRule') {
            return 'Thiết bị có bất thường nhưng chưa có dữ liệu sử dụng tương ứng. Hãy kiểm tra dữ liệu mô phỏng hoặc lịch sử phân tích.';
        }

        return $message ?: '-';
    }
}
