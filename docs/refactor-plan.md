# System Refactor Plan

## Baseline

- Branch: `refactor/system-rebuild`
- Tag: `pre-refactor-v1`
- Created at: 2026-07-14
- Database migration: Pending verification
- Seeder: Pending verification
- Tests: Pending verification
- Pint: Pending verification
- Frontend build: Pending verification

## Baseline Objective

Giữ một phiên bản có thể quay lại trước khi thay đổi:

- Usage metric calculation.
- Operational status.
- Data health.
- Booking transaction.
- Check-in transaction.
- Abnormal workflow.
- Asset lifecycle.
- Authentication và authorization.
- UI structure.
- IoT ingest.

## Phase 0 Rules

- Không thay đổi nghiệp vụ.
- Không thay đổi rule.
- Không thay đổi database schema.
- Không thêm tính năng.
- Chỉ kiểm tra baseline và bổ sung tài liệu.

## Completion Conditions

- Database sạch migrate thành công.
- Seeder chạy ổn định.
- Test cũ pass.
- Pint pass.
- Frontend build pass.
- Có baseline tag.
- Có branch cải tổ.