# Laboratory Equipment Utilization System

**Laboratory Equipment Utilization System** là hệ thống theo dõi và phân tích hiệu suất khai thác thiết bị phòng thí nghiệm.

Hệ thống hỗ trợ:

- Theo dõi lịch sử đặt và sử dụng thiết bị.
- Phân biệt thời gian bật máy với thời gian hoạt động thực tế.
- Phát hiện các hành vi sử dụng bất thường.
- Tính toán chỉ số khai thác thiết bị.
- Cung cấp không gian làm việc riêng cho từng vai trò.
- Hỗ trợ người quản lý đánh giá nhu cầu mua thêm thiết bị.

> Hiện tại hệ thống sử dụng dữ liệu mô phỏng và chưa kết nối trực tiếp với thiết bị IoT thật.

---

## Mục tiêu dự án

Dự án giúp phòng thí nghiệm trả lời các câu hỏi quản lý quan trọng:

- Thiết bị nào đang được sử dụng hiệu quả?
- Thiết bị nào ít được sử dụng hoặc bị bỏ quên?
- Thiết bị nào có dấu hiệu quá tải?
- Thiết bị nào được bật lâu nhưng không có hoạt động thực tế?
- Có thiết bị nào bị giữ hoặc sử dụng quá thời gian cho phép?
- Phòng thí nghiệm có thực sự cần mua thêm thiết bị không?
- Có thể tối ưu lịch sử đặt và phân phối thiết bị hiện tại hay không?

---

## Tech Stack

| Thành phần | Công nghệ |
|---|---|
| Backend | Laravel 13 |
| Ngôn ngữ | PHP OOP |
| Database | MySQL 8 |
| ORM | Eloquent ORM |
| Giao diện | Blade, JavaScript |
| Gửi request bất đồng bộ | Fetch API, Ajax |
| Kiến trúc | MVC, Service Layer, Rule Object |
| Mô phỏng dữ liệu | Laravel Seeder, PHP Script |
| Kiểm thử | Pest, PHPUnit |
| Chuẩn hóa code | Laravel Pint |

---

## Vai trò người dùng

Hệ thống hiện sử dụng cơ chế **demo login theo vai trò**. Auth production đầy đủ chưa được triển khai.

### Admin

Quản lý:

- Danh mục thiết bị.
- Dữ liệu nền.
- Người dùng demo.
- Cấu hình hệ thống.
- Lịch sử phân tích.
- Trạng thái lưu trữ hoặc ngừng sử dụng thiết bị.

### Manager

Theo dõi:

- Hiệu suất khai thác thiết bị.
- Thiết bị cần chú ý.
- Bất thường đang mở.
- Lịch sử phân tích.
- Chỉ số sử dụng và áp lực công suất.
- Quá trình tiếp nhận và hoàn tất xử lý bất thường.

### Lab Staff

Xử lý vận hành hằng ngày:

- Lịch đặt thiết bị.
- Check-in.
- Phiên sử dụng đang chạy.
- Trạng thái bật, tắt hoặc không hoạt động.
- Hoàn tất phiên sử dụng.
- Dữ liệu telemetry mô phỏng.

### Researcher / Student

Thực hiện:

- Tìm kiếm thiết bị.
- Tạo lịch đặt.
- Theo dõi lịch cá nhân.
- Xem các phiên sử dụng của mình.
- Hủy lịch đặt khi cần.

---

## Pipeline nghiệp vụ

```text
Equipment
    ↓
Booking / Check-in
    ↓
Usage Session
    ↓
Activity Signal
    ↓
Usage Metrics
    ↓
Rule Evaluation
    ↓
Abnormal Pattern
    ↓
Utilization Analysis
    ↓
Manager Dashboard
```

Pipeline thể hiện quá trình từ dữ liệu thiết bị ban đầu đến kết luận hỗ trợ quản lý.

---

## Các nhóm trạng thái

Hệ thống không sử dụng một trường trạng thái duy nhất cho mọi nghiệp vụ. Trạng thái được chia thành nhiều nhóm độc lập.

### Trạng thái vận hành

| Trạng thái | Ý nghĩa |
|---|---|
| `OFF` | Thiết bị đang tắt |
| `POWERED_IDLE` | Thiết bị đang bật nhưng không có hoạt động |
| `ACTIVE` | Thiết bị đang được sử dụng |

### Trạng thái đặt và sử dụng

| Trạng thái | Ý nghĩa |
|---|---|
| `BOOKED` | Thiết bị đã được đặt |
| `CHECKED_IN` | Người dùng đã nhận hoặc bắt đầu sử dụng thiết bị |
| `COMPLETED` | Phiên sử dụng đã hoàn thành |
| `NO_SHOW` | Người dùng đã đặt nhưng không đến sử dụng |
| `OVERDUE` | Thiết bị bị giữ hoặc sử dụng quá thời gian cho phép |

### Trạng thái phân tích

| Trạng thái | Ý nghĩa |
|---|---|
| `NORMAL` | Mức khai thác bình thường |
| `UNDERUTILIZED` | Thiết bị không được sử dụng trong thời gian dài |
| `CAPACITY_PRESSURE` | Thiết bị có tỷ lệ khai thác cao |
| `IDLE_WHILE_POWERED` | Thiết bị bật nhưng không hoạt động quá lâu |

---

## Các rule nghiệp vụ chính

### Idle While Powered

```text
Thiết bị đang bật
+
Không có activity quá 30 phút
→ IDLE_WHILE_POWERED
```

### No-show

```text
Booking đã bắt đầu
+
Không có check-in trong thời gian cho phép
→ NO_SHOW
```

### Overdue

```text
Thiết bị đã được check-in
+
Chưa hoàn tất trước thời hạn
→ OVERDUE
```

### Underutilized

```text
Không có phiên sử dụng trong hơn 60 ngày
→ UNDERUTILIZED
```

### Capacity Pressure

```text
Tỷ lệ khai thác lớn hơn 85%
→ CAPACITY_PRESSURE
```

---

## Kiến trúc dự án

Dự án được tổ chức theo các nguyên tắc:

- MVC.
- Service Layer.
- Rule Object.
- Role-based Workspace.
- Blade Components.
- Ajax Modal Actions.
- Seeder-based Simulation.
- Eloquent Relationships.
- Authorization theo vai trò.
- Tách dữ liệu nhập tay khỏi dữ liệu do hệ thống tính toán.

### Các nhóm module chính

- Equipment Management.
- Booking Management.
- Usage Session Management.
- Telemetry Simulation.
- Activity Signal Processing.
- Usage Metric Calculation.
- Rule Evaluation.
- Abnormal Pattern Management.
- Role-based Dashboard.
- Demo Login và Role Context.
- Analysis Run History.

---

## Chức năng hiện có

- Demo login theo vai trò.
- Dashboard riêng cho Admin, Manager, Lab Staff và Researcher.
- Navigation và sidebar riêng theo từng vai trò.
- CRUD thiết bị bằng modal.
- Tạo booking.
- Hủy booking.
- Check-in booking.
- Hoàn tất usage session.
- Sinh dữ liệu mô phỏng.
- Ghi nhận power event và activity signal.
- Tính usage metrics.
- Chạy rule evaluation.
- Phát hiện abnormal pattern.
- Tiếp nhận xử lý bất thường.
- Hoàn tất xử lý bất thường.
- Ghi chú khi hoàn tất xử lý.
- Soft delete thiết bị bằng hành động **Ngừng sử dụng**.
- Dashboard Manager theo hướng exception-first.
- Insight bằng ngôn ngữ tự nhiên dựa trên rule.
- Giao diện responsive với sidebar có thể thu gọn.

---

## Cài đặt dự án

### 1. Clone repository

```bash
git clone https://github.com/vdtrong2051/laboratory-equipment-utilization.git
cd laboratory-equipment-utilization
```

### 2. Cài dependency PHP

```bash
composer install
```

### 3. Tạo file môi trường

Trên Linux hoặc macOS:

```bash
cp .env.example .env
```

Trên Windows PowerShell:

```powershell
Copy-Item .env.example .env
```

### 4. Tạo application key

```bash
php artisan key:generate
```

### 5. Cấu hình database

Tạo database MySQL:

```sql
CREATE DATABASE laboratory_equipment_utilization
CHARACTER SET utf8mb4
COLLATE utf8mb4_unicode_ci;
```

Cập nhật file `.env`:

```env
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=laboratory_equipment_utilization
DB_USERNAME=root
DB_PASSWORD=your_password
```

### 6. Chạy migration

```bash
php artisan migrate
```

### 7. Chạy seeder

```bash
php artisan db:seed
```

Hoặc tạo lại toàn bộ database và dữ liệu mẫu:

```bash
php artisan migrate:fresh --seed
```

### 8. Khởi động server local

```bash
php artisan serve
```

Mở trình duyệt tại:

```text
http://127.0.0.1:8000
```

---

## Tài khoản demo

Truy cập trang đăng nhập:

```text
/login
```

Chọn:

```text
Trải nghiệm bằng tài khoản mẫu
```

Hoặc truy cập trực tiếp:

```text
/demo-login
```

Các nhóm tài khoản demo:

- Admin.
- Manager.
- Lab Staff.
- Researcher / Student.

Demo login lưu người dùng hiện tại bằng session và chuyển hướng đến workspace tương ứng.

---

## Kiểm thử

### Chạy toàn bộ test

```bash
php artisan test
```

### Chạy một file test cụ thể

```bash
php artisan test tests/Feature/ExampleTest.php
```

### Chuẩn hóa code

Trên Linux hoặc macOS:

```bash
./vendor/bin/pint
```

Trên Windows PowerShell:

```powershell
vendor\bin\pint
```

### Kiểm tra thay đổi mà không tự động sửa

```bash
./vendor/bin/pint --test
```

---

## Dữ liệu mô phỏng

Dữ liệu hiện tại được tạo từ:

- Laravel Seeder.
- Factory.
- PHP Script.
- Các hành động mô phỏng trong giao diện.

Hệ thống chưa nhận dữ liệu trực tiếp từ cảm biến hoặc thiết bị IoT thật.

Thiết kế hiện tại tách riêng:

```text
Simulation
    ↓
Application Service
    ↓
Domain Data
```

Trong tương lai có thể thay nguồn mô phỏng bằng:

```text
IoT Device
    ↓
Gateway
    ↓
MQTT / REST API
    ↓
Laravel Ingestion Endpoint
    ↓
Activity Event
```

Phần phân tích nghiệp vụ phía Laravel vẫn có thể được giữ nguyên.

---

## Nguyên tắc dữ liệu

Các dữ liệu sau được nhập hoặc quản lý bởi Admin:

- Mã thiết bị.
- Tên thiết bị.
- Loại thiết bị.
- Phòng lab.
- Chế độ sử dụng.
- Thời lượng sử dụng cho phép.
- Trạng thái tài sản.

Các dữ liệu sau phải do hệ thống tự tính hoặc suy ra:

- Trạng thái vận hành.
- Trạng thái phân tích.
- Tỷ lệ khai thác.
- Lần sử dụng gần nhất.
- Thời gian hoạt động.
- Thời gian bật nhưng không hoạt động.
- Abnormal pattern.

Admin không chỉnh sửa trực tiếp các dữ liệu suy diễn này.

---

## Định hướng phát triển

- Triển khai auth production đầy đủ.
- Bổ sung Laravel Policy và Gate chi tiết hơn.
- Tách riêng asset status của thiết bị.
- Xây dựng danh mục laboratory, equipment type và manufacturer.
- Kết nối IoT gateway thật.
- Nhận dữ liệu qua MQTT hoặc REST API.
- Bổ sung lịch bảo trì và hiệu chuẩn.
- Xây dựng biểu đồ xu hướng khai thác.
- Export báo cáo cho Manager.
- Bổ sung audit log.
- Bổ sung notification cho bất thường nghiêm trọng.
- Tự động chạy phân tích định kỳ bằng Laravel Scheduler.
- Bổ sung queue cho các tác vụ phân tích dài.
- Cải thiện kiểm thử rule engine và authorization.

---

## Phạm vi hiện tại

Phiên bản hiện tại tập trung vào:

- Thiết kế domain.
- Xử lý trạng thái.
- Mô hình lịch sử sử dụng.
- Phân tích hiệu suất khai thác.
- Phát hiện bất thường.
- Phân quyền workspace theo vai trò.
- Mô phỏng dữ liệu trước khi kết nối thiết bị thật.

Dự án không chỉ là CRUD thiết bị. Phần cốt lõi nằm ở:

```text
Usage Session
+
Activity History
+
Usage Metrics
+
Rule Evaluation
+
Management Insight
```

---

## License

Dự án được xây dựng phục vụ mục đích:

- Học tập.
- Nghiên cứu.
- Demo portfolio.
- Thử nghiệm kiến trúc hệ thống quản lý thiết bị phòng thí nghiệm.

Không sử dụng cho môi trường production khi chưa hoàn thiện bảo mật, xác thực và kết nối dữ liệu thiết bị thật.
