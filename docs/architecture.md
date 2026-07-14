# Current Architecture

## Tech Stack

- Backend: Laravel 13
- Language: PHP 8.3+
- Database: MySQL 8
- ORM: Eloquent ORM
- Frontend: Blade, JavaScript, Fetch API
- Testing: Pest
- Asset Build: Vite

## Current Architecture

Hệ thống sử dụng kiến trúc MVC kết hợp Service Layer và Rule Object.

- Controller tiếp nhận HTTP request và gọi service.
- Service xử lý luồng nghiệp vụ và transaction.
- Rule Object đánh giá trạng thái và bất thường.
- Model ánh xạ dữ liệu và quan hệ Eloquent.
- Blade hiển thị dữ liệu đã được backend chuẩn bị.
- JavaScript xử lý modal và Ajax form.

## Data Pipeline

Equipment
→ Booking
→ Check-in
→ Usage Session
→ Power Event / Activity Signal
→ Usage Metric
→ Rule Evaluation
→ Abnormal Pattern
→ Role Dashboard

## Current Modules

- Equipment
- Booking
- Usage Session
- Telemetry Simulator
- Usage Metrics
- Rule Evaluation
- Abnormal Pattern
- Analysis Run
- Role Dashboard

## Architecture Boundary

Gateway hoặc simulator chỉ nên cung cấp dữ liệu đầu vào.

Backend là nơi sở hữu:

- Phân loại trạng thái vận hành.
- Tính usage metrics.
- Đánh giá rule.
- Sinh abnormal pattern.
- Cập nhật snapshot hiện tại của thiết bị.

## Current Limitations

- Authentication hiện vẫn có chế độ demo.
- Telemetry hiện là dữ liệu mô phỏng.
- Analysis result chưa truy vết đầy đủ tới từng analysis run.
- UI assets chưa được tách hoàn toàn thành các module riêng.