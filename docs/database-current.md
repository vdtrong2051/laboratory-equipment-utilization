# Current Database

## Core Tables

- users
- equipments
- bookings
- usage_sessions
- power_events
- activity_signals
- usage_metrics
- abnormal_patterns
- analysis_runs

## Data Ownership

### Metadata

Dữ liệu được người dùng hoặc Admin nhập:

- Equipment code
- Equipment name
- Equipment type
- Laboratory
- Usage mode
- Allowed usage duration
- Booking time
- Booking purpose
- Resolution note

### Raw Events

Dữ liệu đầu vào từ simulator hoặc gateway:

- PowerEvent
- ActivitySignal
- recorded_at
- gateway_id
- raw_payload

### Derived Data

Dữ liệu do backend tính:

- current_operational_status
- current_analysis_status
- last_used_at
- utilization_rate
- usage_metrics
- abnormal_patterns

## Equipment Snapshot

Bảng `equipments` giữ snapshot hiện tại để đọc nhanh:

- Trạng thái vận hành hiện tại.
- Trạng thái phân tích hiện tại.
- Tỷ lệ khai thác gần nhất.
- Thời điểm sử dụng gần nhất.

Lịch sử chi tiết nằm ở các bảng sự kiện, phiên sử dụng, metric và bất thường.

## Current Limitations

- Laboratory và equipment type vẫn là text.
- Chưa có asset status riêng.
- Chưa có data health status.
- Chưa có audit log toàn hệ thống.
- Usage metric chưa liên kết rõ với analysis run.
- Chưa có bảng gateway và telemetry ingest batch.