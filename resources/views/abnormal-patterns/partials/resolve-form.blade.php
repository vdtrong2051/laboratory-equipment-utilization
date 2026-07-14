<p>Hoàn tất xử lý bất thường <strong>#{{ $pattern->id }}</strong> trên thiết bị <strong>{{ $pattern->equipment?->equipment_code }}</strong>.</p>
<p class="muted">Ghi rõ kết quả kiểm tra để người quản lý khác có thể audit lại quyết định.</p>

<div class="field" style="margin-top: 12px;">
    <label for="resolution_note_{{ $pattern->id }}">Ghi chú kết quả</label>
    <textarea id="resolution_note_{{ $pattern->id }}" name="resolution_note" rows="4" required placeholder="Ví dụ: Đã kiểm tra dữ liệu mô phỏng, tạo lại phiên sử dụng và chạy phân tích lại."></textarea>
    <span class="error" data-error-for="resolution_note"></span>
</div>
