# Current Role Permissions

## Admin

- Quản lý metadata thiết bị.
- Quản lý user.
- Xem dữ liệu hệ thống.
- Xem analysis run.

## Manager

- Xem hiệu suất khai thác.
- Xem thiết bị ưu tiên.
- Xem abnormal pattern.
- Review abnormal pattern.
- Resolve abnormal pattern.
- Chạy và xem analysis run.

## Lab Staff

- Xem booking cần vận hành.
- Check-in booking.
- Theo dõi usage session.
- Hoàn tất usage session.
- Sinh telemetry mô phỏng trong môi trường demo.

## Researcher

- Xem thiết bị.
- Tạo booking.
- Xem booking của mình.
- Hủy booking của mình.
- Xem usage session của mình.

## Current Limitations

- Quyền hiện tại còn dựa vào demo session và capability middleware.
- Chưa sử dụng Laravel Policy đầy đủ.
- Một số kiểm tra ownership còn nằm trong controller.