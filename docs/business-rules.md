# Current Business Rules

## Data Meaning

- Booking là lịch dự kiến.
- UsageSession là quá trình sử dụng thực tế.
- PowerEvent là sự kiện thay đổi trạng thái nguồn điện.
- ActivitySignal là phép đo hoạt động đầu vào.
- UsageMetric là dữ liệu tổng hợp theo kỳ.
- AbnormalPattern là kết quả của rule evaluation.
- Equipment chỉ giữ snapshot trạng thái hiện tại.

## Rule Ownership

Backend sở hữu toàn bộ rule phân loại và tính toán.

Simulator hoặc gateway không được quyết định trạng thái nghiệp vụ cuối cùng.

## Current Operational Status

- OFF
- POWERED_IDLE
- ACTIVE

## Current Abnormal Rules

- Idle while powered.
- No-show.
- Overdue.
- Underutilized.
- Capacity pressure.

## Current Abnormal Workflow

OPEN
→ REVIEWED
→ RESOLVED

## Current Limitations

- Chưa có UNKNOWN operational status.
- Chưa có data health status.
- Một số threshold đang hard-code.
- Simulator hiện còn tham gia xác định activity.
- Một số transaction chưa chống được request đồng thời.