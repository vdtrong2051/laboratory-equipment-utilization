from __future__ import annotations

from datetime import datetime
from pathlib import Path
from xml.sax.saxutils import escape

from reportlab.lib import colors
from reportlab.lib.enums import TA_CENTER, TA_LEFT
from reportlab.lib.pagesizes import A4
from reportlab.lib.styles import ParagraphStyle, getSampleStyleSheet
from reportlab.lib.units import cm
from reportlab.pdfbase import pdfmetrics
from reportlab.pdfbase.ttfonts import TTFont
from reportlab.platypus import (
    PageBreak,
    Paragraph,
    SimpleDocTemplate,
    Spacer,
    Table,
    TableStyle,
)


ROOT = Path(__file__).resolve().parents[1]
OUTPUT_DIR = ROOT / "output" / "pdf"
OUTPUT_PATH = OUTPUT_DIR / "laboratory-equipment-utilization-project-journal.pdf"


def register_fonts() -> tuple[str, str]:
    regular = Path(r"C:\Windows\Fonts\arial.ttf")
    bold = Path(r"C:\Windows\Fonts\arialbd.ttf")

    if regular.exists():
        pdfmetrics.registerFont(TTFont("ArialVN", str(regular)))
    else:
        pdfmetrics.registerFont(TTFont("ArialVN", "Helvetica"))

    if bold.exists():
        pdfmetrics.registerFont(TTFont("ArialVNBold", str(bold)))
    else:
        pdfmetrics.registerFont(TTFont("ArialVNBold", "Helvetica-Bold"))

    return "ArialVN", "ArialVNBold"


FONT, FONT_BOLD = register_fonts()


def styles():
    base = getSampleStyleSheet()
    return {
        "title": ParagraphStyle(
            "TitleVN",
            parent=base["Title"],
            fontName=FONT_BOLD,
            fontSize=21,
            leading=27,
            alignment=TA_CENTER,
            textColor=colors.HexColor("#102333"),
            spaceAfter=10,
        ),
        "subtitle": ParagraphStyle(
            "SubtitleVN",
            parent=base["Normal"],
            fontName=FONT,
            fontSize=10,
            leading=15,
            alignment=TA_CENTER,
            textColor=colors.HexColor("#475569"),
            spaceAfter=18,
        ),
        "h1": ParagraphStyle(
            "Heading1VN",
            parent=base["Heading1"],
            fontName=FONT_BOLD,
            fontSize=15,
            leading=20,
            textColor=colors.HexColor("#0f4f6b"),
            spaceBefore=14,
            spaceAfter=8,
        ),
        "h2": ParagraphStyle(
            "Heading2VN",
            parent=base["Heading2"],
            fontName=FONT_BOLD,
            fontSize=12,
            leading=16,
            textColor=colors.HexColor("#17212f"),
            spaceBefore=10,
            spaceAfter=6,
        ),
        "p": ParagraphStyle(
            "ParagraphVN",
            parent=base["BodyText"],
            fontName=FONT,
            fontSize=9.4,
            leading=14,
            textColor=colors.HexColor("#17212f"),
            alignment=TA_LEFT,
            spaceAfter=5,
        ),
        "bullet": ParagraphStyle(
            "BulletVN",
            parent=base["BodyText"],
            fontName=FONT,
            fontSize=9.2,
            leading=13,
            leftIndent=12,
            bulletIndent=0,
            spaceAfter=3,
        ),
        "small": ParagraphStyle(
            "SmallVN",
            parent=base["BodyText"],
            fontName=FONT,
            fontSize=8.2,
            leading=11,
            textColor=colors.HexColor("#475569"),
        ),
        "code": ParagraphStyle(
            "CodeVN",
            parent=base["Code"],
            fontName=FONT,
            fontSize=8.4,
            leading=11,
            backColor=colors.HexColor("#f1f5f9"),
            borderPadding=6,
            textColor=colors.HexColor("#0f172a"),
            spaceBefore=4,
            spaceAfter=8,
        ),
    }


S = styles()


def p(text: str, style: str = "p") -> Paragraph:
    return Paragraph(escape(text).replace("\n", "<br/>"), S[style])


def bullet(text: str) -> Paragraph:
    return Paragraph(escape(text), S["bullet"], bulletText="-")


def code(text: str) -> Paragraph:
    return p(text, "code")


def table(data: list[list[str]], widths: list[float] | None = None) -> Table:
    escaped = [[p(str(cell), "small") for cell in row] for row in data]
    tbl = Table(escaped, colWidths=widths, hAlign="LEFT")
    tbl.setStyle(
        TableStyle(
            [
                ("BACKGROUND", (0, 0), (-1, 0), colors.HexColor("#eaf4f8")),
                ("TEXTCOLOR", (0, 0), (-1, 0), colors.HexColor("#102333")),
                ("FONTNAME", (0, 0), (-1, 0), FONT_BOLD),
                ("GRID", (0, 0), (-1, -1), 0.3, colors.HexColor("#cbd5e1")),
                ("VALIGN", (0, 0), (-1, -1), "TOP"),
                ("LEFTPADDING", (0, 0), (-1, -1), 6),
                ("RIGHTPADDING", (0, 0), (-1, -1), 6),
                ("TOPPADDING", (0, 0), (-1, -1), 5),
                ("BOTTOMPADDING", (0, 0), (-1, -1), 5),
            ]
        )
    )
    return tbl


def add_header_footer(canvas, doc):
    canvas.saveState()
    canvas.setFont(FONT, 8)
    canvas.setFillColor(colors.HexColor("#64748b"))
    canvas.drawString(1.6 * cm, 1.1 * cm, "Laboratory Equipment Utilization System - Project Journal")
    canvas.drawRightString(A4[0] - 1.6 * cm, 1.1 * cm, f"Trang {doc.page}")
    canvas.restoreState()


def build_story() -> list:
    story = []

    story.append(p("Laboratory Equipment Utilization System", "title"))
    story.append(p("Nhật ký dự án, quyết định kiến trúc, các phase triển khai, các lần chỉnh sửa UI/UX và kế hoạch tiếp theo", "subtitle"))
    story.append(p(f"Ngày xuất tài liệu: {datetime.now().strftime('%d/%m/%Y %H:%M')}", "small"))
    story.append(Spacer(1, 12))

    story.append(p("1. Tổng quan dự án", "h1"))
    story.append(p("Dự án xây dựng hệ thống phân tích hiệu suất khai thác thiết bị phòng thí nghiệm. Hệ thống giúp theo dõi lịch đặt, phiên sử dụng, tín hiệu hoạt động, thời gian bật máy, chỉ số khai thác và các bất thường để hỗ trợ quản lý trước khi quyết định mua thêm thiết bị."))
    for item in [
        "Tên dự án: Laboratory Equipment Utilization System.",
        "Backend: Laravel 13, PHP OOP, MySQL 8, Eloquent ORM.",
        "Frontend: Blade, JavaScript, Fetch API/Ajax.",
        "Kiến trúc: MVC, Service Layer, Rule Object, role-based workspace.",
        "Dữ liệu hiện tại: mô phỏng bằng Laravel Seeder/PHP Script, chưa nhận dữ liệu IoT thật.",
    ]:
        story.append(bullet(item))

    story.append(p("2. Pipeline nghiệp vụ đã chốt", "h1"))
    story.append(code("Equipment\nBooking / Check-in\nUsage Session\nActivity Signal\nUsage Metrics\nRule Evaluation\nAbnormal Pattern\nUtilization Analysis\nManager Dashboard"))
    story.append(p("Quyết định quan trọng: backend sở hữu rule phân loại trạng thái. Gateway hoặc simulator chỉ gửi event/tín hiệu thô. Điều này giúp thay đổi rule tập trung, dễ audit, lưu lịch sử tốt hơn và tránh coupling với nhiều gateway."))

    story.append(p("3. Các role và mục tiêu sử dụng", "h1"))
    story.append(
        table(
            [
                ["Role", "Mục tiêu chính", "Không gian làm việc"],
                ["Admin", "Quản lý dữ liệu nền, thiết bị, user và cấu hình hệ thống.", "Tổng quan quản trị, thiết bị, lịch sử phân tích."],
                ["Manager", "Đọc hiệu suất khai thác, xử lý bất thường, ra quyết định quản lý.", "Tổng quan khai thác, thiết bị, bất thường, lịch sử phân tích."],
                ["Lab Staff", "Vận hành hằng ngày, check-in, hoàn tất phiên, theo dõi thiết bị bật/idle.", "Tổng quan ca trực, lịch đặt, phiên sử dụng, thiết bị."],
                ["Researcher / Student", "Tìm thiết bị, đặt lịch, xem lịch cá nhân.", "Tổng quan cá nhân, tạo lịch đặt, lịch đặt của tôi, thiết bị."],
            ],
            [3.0 * cm, 6.2 * cm, 7.1 * cm],
        )
    )

    story.append(p("4. Nhật ký triển khai theo phase", "h1"))
    phase_rows = [
        ["Phase", "Nội dung", "Kết quả"],
        ["0", "Cài môi trường, MySQL, Beekeeper, migration.", "Kết nối MySQL, sửa lỗi FK equipments/equipment, chạy migration thành công."],
        ["1", "Seeder thiết bị và user demo.", "Danh sách 50 thiết bị theo Lab A-E, role demo cơ bản."],
        ["2-6", "Booking, check-in, usage session, telemetry simulation, metrics và rule evaluation.", "Luồng dữ liệu mô phỏng chạy được từ booking đến abnormal pattern."],
        ["7-10", "Flow stepper, modal CRUD, ajax response chuẩn, dashboard role.", "Các action nhỏ thao tác trong modal, có fallback form thường."],
        ["11-14", "Refine role workspace, dashboard theo role, insight tự nhiên.", "Admin, Manager, Lab Staff, Researcher có không gian riêng."],
        ["UI đợt 1", "Sidebar/header, role navigation, natural-language insight.", "Ứng dụng dễ test hơn, giảm cảm giác bảng thô."],
        ["UI đợt 2", "Auth nhẹ qua demo login, modal CRUD, next actions, flow wizard.", "Login/demo-login rõ ràng, role vào đúng workspace."],
        ["Admin refine", "Tách metadata khỏi dữ liệu suy diễn, soft delete thiết bị, detail thiết bị gọn.", "Admin không còn sửa tay trạng thái/chỉ số do hệ thống tính."],
        ["Manager refine", "Exception-first dashboard, bất thường nghiệp vụ, resolution note.", "Manager tập trung vào vấn đề mở, thiết bị ưu tiên và hành động xử lý."],
    ]
    story.append(table(phase_rows, [2.5 * cm, 6.3 * cm, 7.5 * cm]))

    story.append(PageBreak())

    story.append(p("5. Các quyết định kiến trúc quan trọng", "h1"))
    for item in [
        "Backend là nơi sở hữu rule phân loại OFF, POWERED_IDLE, ACTIVE.",
        "Telemetry/simulator chỉ tạo PowerEvent và ActivitySignal, không quyết định trạng thái cuối.",
        "Admin chỉ sửa metadata thiết bị. Trạng thái vận hành, trạng thái phân tích, tỷ lệ khai thác và lần dùng gần nhất do service/rule tính.",
        "Thiết bị ngừng sử dụng dùng soft delete để giữ lịch sử booking, session, metrics và abnormal patterns.",
        "Manager dashboard theo hướng exception-first, không hiển thị toàn bộ pipeline kỹ thuật.",
        "Abnormal pattern có state machine đơn giản: open -> reviewed -> resolved.",
        "Resolve bất thường bắt buộc có resolution_note để audit quyết định.",
        "Demo login tách khỏi login chính: /login là cửa chính, /demo-login là lối vào trải nghiệm role.",
    ]:
        story.append(bullet(item))

    story.append(p("6. Các module đã hình thành", "h1"))
    story.append(
        table(
            [
                ["Module", "Trách nhiệm"],
                ["Equipment", "Quản lý danh mục thiết bị, metadata, trạng thái hiện tại, soft delete."],
                ["Booking", "Tạo lịch đặt, kiểm tra trùng lịch, hủy lịch."],
                ["Usage Session", "Check-in, hoàn tất phiên, liên kết booking với hoạt động thực tế."],
                ["Telemetry Simulator", "Sinh dữ liệu mô phỏng PowerEvent và ActivitySignal."],
                ["Usage Metrics", "Tính thời gian đặt, bật máy, hoạt động thực, idle và tỷ lệ khai thác."],
                ["Rule Evaluation", "Đánh giá rule bất thường và cập nhật trạng thái phân tích."],
                ["Abnormal Pattern", "Theo dõi, tiếp nhận xử lý, hoàn tất xử lý, lưu resolution note."],
                ["Dashboard", "Không gian riêng theo role, insight và next action phù hợp."],
                ["UI Shell", "Sidebar/header theo role, modal, ajax form, compact pagination."],
            ],
            [4.2 * cm, 11.9 * cm],
        )
    )

    story.append(p("7. Những lỗi và vấn đề đã xử lý", "h1"))
    for item in [
        "Chưa có PHP/Composer trong PATH: tạm dừng scaffold, sau đó kiểm tra lại môi trường.",
        "MySQL/Beekeeper connection fail: hướng dẫn cài MySQL Server, tạo database, kiểm tra port 3306.",
        "Migration fail do FK tham chiếu sai bảng equipment thay vì equipments: sửa migration và chạy lại.",
        "Beekeeper không hiện bảng: cần chọn đúng database và refresh sau migration.",
        "Dashboard ban đầu quá chung chung: tách workspace theo role.",
        "Admin dashboard quá card-heavy: rút gọn thành workspace quản trị.",
        "Form thiết bị cho sửa dữ liệu suy diễn: đã bỏ khỏi request và form.",
        "Link analysis-runs hard-code về manager: đổi sang dashboard theo role hiện tại.",
        "Manager dashboard quá kỹ thuật: rút gọn theo exception-first.",
        "Abnormal detail phô raw JSON/class: chuyển sang ngôn ngữ nghiệp vụ và thu gọn technical detail.",
    ]:
        story.append(bullet(item))

    story.append(p("8. Trạng thái hiện tại", "h1"))
    for item in [
        "Laravel project đã scaffold và có cấu trúc hợp lệ.",
        "Database MySQL đã chạy migration và seeder.",
        "Demo login theo role đã hoạt động.",
        "Các dashboard role đã có UX riêng.",
        "Manager có flow xử lý bất thường với review/resolve và ghi chú kết quả.",
        "Admin có modal sửa metadata thiết bị và soft delete bằng 'Ngừng sử dụng'.",
        "Test suite gần nhất: 104 tests passed, 518 assertions.",
    ]:
        story.append(bullet(item))

    story.append(p("9. Điểm yếu còn lại", "h1"))
    weaknesses = [
        ["Nhóm", "Điểm yếu", "Gợi ý xử lý"],
        ["Auth", "Hiện mới là demo login nhẹ, chưa phải auth production.", "Triển khai Laravel auth, policy/gate chi tiết."],
        ["Data model", "Chưa có asset_status riêng, lab/type/manufacturer vẫn là text.", "Thêm bảng danh mục và asset status độc lập với operational status."],
        ["Telemetry", "Chưa nhận dữ liệu IoT thật.", "Thiết kế endpoint ingest hoặc gateway adapter."],
        ["Audit", "Chưa có audit log đầy đủ cho mọi hành động.", "Thêm bảng audit_logs cho create/update/delete/review/resolve."],
        ["Reporting", "Chưa có export báo cáo.", "Thêm export CSV/PDF theo kỳ cho manager."],
        ["Visualization", "Biểu đồ xu hướng còn hạn chế.", "Thêm trend utilization, idle rate, capacity pressure theo thời gian."],
        ["UX mobile", "Đã responsive cơ bản nhưng chưa QA sâu trên mobile/tablet.", "Test lại hành trình chính theo từng viewport."],
    ]
    story.append(table(weaknesses, [3.0 * cm, 6.3 * cm, 6.8 * cm]))

    story.append(PageBreak())

    story.append(p("10. Kế hoạch phát triển tiếp theo", "h1"))
    plan_rows = [
        ["Ưu tiên", "Việc cần làm", "Mục tiêu"],
        ["P1", "Triển khai auth thật và policy/gate.", "Tách demo khỏi production, kiểm soát quyền rõ ràng."],
        ["P1", "Thêm asset_status cho thiết bị.", "Phân biệt tài sản đang hoạt động/bảo trì/ngừng sử dụng với trạng thái vận hành."],
        ["P1", "Chuẩn hóa abnormal state machine.", "OPEN -> REVIEWED -> RESOLVED, có note và timeline."],
        ["P2", "Danh mục lab, equipment type, manufacturer.", "Giảm text tự do, dữ liệu nhất quán hơn."],
        ["P2", "Audit log toàn hệ thống.", "Truy vết ai làm gì, khi nào, trước/sau ra sao."],
        ["P2", "IoT ingest API.", "Nhận PowerEvent/ActivitySignal từ gateway thật."],
        ["P3", "Biểu đồ xu hướng và export báo cáo.", "Hỗ trợ manager ra quyết định dài hạn."],
        ["P3", "QA UX toàn bộ role.", "Đảm bảo luồng dễ hiểu từ login đến xử lý công việc."],
    ]
    story.append(table(plan_rows, [2.2 * cm, 6.5 * cm, 7.4 * cm]))

    story.append(p("11. Hướng dẫn clone tay và chạy lại", "h1"))
    story.append(code("git clone https://github.com/vdtrong2051/laboratory-equipment-utilization.git\ncd laboratory-equipment-utilization\ncomposer install\ncp .env.example .env\nphp artisan key:generate"))
    story.append(p("Cấu hình MySQL trong .env:"))
    story.append(code("DB_CONNECTION=mysql\nDB_HOST=127.0.0.1\nDB_PORT=3306\nDB_DATABASE=laboratory_equipment_utilization\nDB_USERNAME=root\nDB_PASSWORD=your_password"))
    story.append(code("php artisan migrate\nphp artisan db:seed\nphp artisan serve"))
    story.append(p("Mở trình duyệt tại http://127.0.0.1:8000, vào /login rồi chọn trải nghiệm bằng tài khoản mẫu hoặc vào trực tiếp /demo-login."))

    story.append(p("12. Checklist trước khi demo hoặc nộp portfolio", "h1"))
    for item in [
        "Chạy php artisan migrate:fresh --seed trên database sạch để kiểm tra setup.",
        "Chạy php artisan test và đảm bảo toàn bộ test pass.",
        "Kiểm tra /login, /demo-login, dashboard từng role.",
        "Manager: kiểm tra dashboard, thiết bị ưu tiên, bất thường, tiếp nhận xử lý và hoàn tất xử lý.",
        "Admin: kiểm tra thêm/sửa/ngừng sử dụng thiết bị.",
        "Lab Staff: kiểm tra lịch đặt, check-in, phiên đang chạy, hoàn tất phiên.",
        "Researcher: kiểm tra tạo booking và hủy booking cá nhân.",
        "Đọc lại README để đảm bảo đúng repo và đúng database config.",
    ]:
        story.append(bullet(item))

    story.append(p("13. Kết luận", "h1"))
    story.append(p("Dự án đã đi từ thư mục trống đến một Laravel application có kiến trúc rõ: role workspace, service layer, rule object, dữ liệu mô phỏng, dashboard theo vai trò và test suite. Điểm quan trọng nhất đã được chốt là ranh giới dữ liệu: người dùng nhập metadata và hành động nghiệp vụ, còn trạng thái/chỉ số/bất thường do backend service và rule engine tính toán."))

    return story


def main() -> None:
    OUTPUT_DIR.mkdir(parents=True, exist_ok=True)
    doc = SimpleDocTemplate(
        str(OUTPUT_PATH),
        pagesize=A4,
        rightMargin=1.6 * cm,
        leftMargin=1.6 * cm,
        topMargin=1.6 * cm,
        bottomMargin=1.8 * cm,
        title="Laboratory Equipment Utilization System - Project Journal",
        author="Codex",
    )
    doc.build(build_story(), onFirstPage=add_header_footer, onLaterPages=add_header_footer)
    print(OUTPUT_PATH)


if __name__ == "__main__":
    main()
