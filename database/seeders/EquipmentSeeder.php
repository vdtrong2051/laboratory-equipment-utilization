<?php

namespace Database\Seeders;

use App\Enums\AnalysisStatus;
use App\Enums\OperationalStatus;
use App\Enums\UsageMode;
use App\Models\Equipment;
use Illuminate\Database\Seeder;

class EquipmentSeeder extends Seeder
{
    public function run(): void
    {
        $equipments = [
            ['MICRO-001', 'Kính hiển vi quang học', 'Microscope', 'Lab A', 'tại chỗ', 120],
            ['MICRO-002', 'Kính hiển vi quang học', 'Microscope', 'Lab A', 'tại chỗ', 120],
            ['MICRO-003', 'Kính hiển vi soi nổi', 'Stereo Microscope', 'Lab A', 'tại chỗ', 90],
            ['CEN-001', 'Máy ly tâm', 'Centrifuge', 'Lab A', 'tại chỗ', 90],
            ['CEN-002', 'Máy ly tâm', 'Centrifuge', 'Lab A', 'tại chỗ', 90],
            ['CEN-003', 'Máy vi ly tâm', 'Microcentrifuge', 'Lab A', 'tại chỗ', 60],
            ['SHAKE-001', 'Máy lắc mẫu', 'Laboratory Shaker', 'Lab A', 'tại chỗ', 180],
            ['SHAKE-002', 'Máy lắc mẫu', 'Laboratory Shaker', 'Lab A', 'tại chỗ', 180],
            ['VORTEX-001', 'Máy trộn Vortex', 'Vortex Mixer', 'Lab A', 'tại chỗ', 60],
            ['SONIC-001', 'Bể rửa siêu âm', 'Ultrasonic Cleaner', 'Lab A', 'tại chỗ', 120],
            ['PCR-001', 'Máy PCR', 'PCR Machine', 'Lab B', 'tại chỗ', 180],
            ['PCR-002', 'Máy PCR', 'PCR Machine', 'Lab B', 'tại chỗ', 180],
            ['QPCR-001', 'Máy PCR thời gian thực', 'Real-Time PCR Machine', 'Lab B', 'tại chỗ', 240],
            ['ELEC-001', 'Bộ điện di', 'Electrophoresis System', 'Lab B', 'tại chỗ', 120],
            ['ELEC-002', 'Bộ điện di', 'Electrophoresis System', 'Lab B', 'tại chỗ', 120],
            ['GEL-001', 'Máy chụp ảnh gel', 'Gel Documentation System', 'Lab B', 'tại chỗ', 90],
            ['SPEC-001', 'Máy quang phổ', 'Spectrometer', 'Lab B', 'tại chỗ', 120],
            ['SPEC-002', 'Máy quang phổ', 'Spectrometer', 'Lab B', 'tại chỗ', 120],
            ['UVVIS-001', 'Máy quang phổ UV-Vis', 'UV-Vis Spectrophotometer', 'Lab B', 'tại chỗ', 120],
            ['FLUOR-001', 'Máy đo huỳnh quang', 'Fluorometer', 'Lab B', 'tại chỗ', 120],
            ['BAL-001', 'Cân phân tích', 'Analytical Balance', 'Lab C', 'tại chỗ', 60],
            ['BAL-002', 'Cân phân tích', 'Analytical Balance', 'Lab C', 'tại chỗ', 60],
            ['BAL-003', 'Cân kỹ thuật', 'Precision Balance', 'Lab C', 'tại chỗ', 45],
            ['PH-001', 'Máy đo pH', 'pH Meter', 'Lab C', 'di động', 60],
            ['PH-002', 'Máy đo pH', 'pH Meter', 'Lab C', 'di động', 60],
            ['COND-001', 'Máy đo độ dẫn điện', 'Conductivity Meter', 'Lab C', 'di động', 60],
            ['DO-001', 'Máy đo oxy hòa tan', 'Dissolved Oxygen Meter', 'Lab C', 'di động', 90],
            ['TURB-001', 'Máy đo độ đục', 'Turbidity Meter', 'Lab C', 'di động', 60],
            ['MOIST-001', 'Máy đo độ ẩm', 'Moisture Analyzer', 'Lab C', 'tại chỗ', 90],
            ['REFRA-001', 'Khúc xạ kế', 'Refractometer', 'Lab C', 'di động', 45],
            ['OVEN-001', 'Tủ sấy', 'Drying Oven', 'Lab D', 'tại chỗ', 360],
            ['OVEN-002', 'Tủ sấy', 'Drying Oven', 'Lab D', 'tại chỗ', 360],
            ['FURN-001', 'Lò nung', 'Muffle Furnace', 'Lab D', 'tại chỗ', 480],
            ['INCUB-001', 'Tủ ấm', 'Incubator', 'Lab D', 'tại chỗ', 720],
            ['INCUB-002', 'Tủ ấm', 'Incubator', 'Lab D', 'tại chỗ', 720],
            ['AUTO-001', 'Nồi hấp tiệt trùng', 'Autoclave', 'Lab D', 'tại chỗ', 180],
            ['AUTO-002', 'Nồi hấp tiệt trùng', 'Autoclave', 'Lab D', 'tại chỗ', 180],
            ['WATER-001', 'Bể ổn nhiệt', 'Water Bath', 'Lab D', 'tại chỗ', 240],
            ['HOT-001', 'Bếp gia nhiệt', 'Hot Plate', 'Lab D', 'di động', 120],
            ['STIR-001', 'Máy khuấy từ', 'Magnetic Stirrer', 'Lab D', 'di động', 120],
            ['OSC-001', 'Máy hiện sóng', 'Oscilloscope', 'Lab E', 'tại chỗ', 120],
            ['OSC-002', 'Máy hiện sóng', 'Oscilloscope', 'Lab E', 'tại chỗ', 120],
            ['PSU-001', 'Bộ nguồn DC', 'DC Power Supply', 'Lab E', 'di động', 180],
            ['PSU-002', 'Bộ nguồn DC', 'DC Power Supply', 'Lab E', 'di động', 180],
            ['MULTI-001', 'Đồng hồ vạn năng', 'Digital Multimeter', 'Lab E', 'di động', 90],
            ['MULTI-002', 'Đồng hồ vạn năng', 'Digital Multimeter', 'Lab E', 'di động', 90],
            ['SIGNAL-001', 'Máy phát tín hiệu', 'Signal Generator', 'Lab E', 'tại chỗ', 120],
            ['LOGIC-001', 'Máy phân tích logic', 'Logic Analyzer', 'Lab E', 'di động', 120],
            ['THERM-001', 'Camera nhiệt', 'Thermal Camera', 'Lab E', 'di động', 90],
            ['SOLDER-001', 'Trạm hàn điện tử', 'Soldering Station', 'Lab E', 'tại chỗ', 180],
        ];

        foreach ($equipments as [$code, $name, $type, $laboratory, $usageMode, $duration]) {
            Equipment::query()->updateOrCreate(
                ['equipment_code' => $code],
                [
                    'name' => $name,
                    'type' => $type,
                    'laboratory' => $laboratory,
                    'usage_mode' => $usageMode === 'di động' ? UsageMode::Loan : UsageMode::OnSite,
                    'allowed_usage_duration_minutes' => $duration,
                    'current_operational_status' => OperationalStatus::Off,
                    'current_analysis_status' => AnalysisStatus::Normal,
                    'last_used_at' => null,
                    'utilization_rate' => 0,
                ],
            );
        }
    }
}
