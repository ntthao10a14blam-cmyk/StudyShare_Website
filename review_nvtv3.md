# Review nhiệm vụ TV3

## Phạm vi

Đối chiếu các yêu cầu trong [nvtv3.md](./nvtv3.md) với code hiện có ở `app/modules/community/`, `public/serve.php` và `database/schema.sql`.

## Tóm tắt

Phần lớn chức năng cộng đồng đã có: xem tài liệu, tải file, bình luận, sửa/xóa bình luận và gửi báo cáo. Tuy nhiên, có một số điểm chưa khớp yêu cầu cụ thể: chưa có action `download()` trong `CommunityController`, tải qua `serve.php` chưa giới hạn trạng thái `approved` cho mọi trường hợp, và báo cáo bình luận dùng hộp thoại `prompt` thay vì modal chọn lý do.

## Review theo nhiệm vụ

### 1. Tải tài liệu

**Trạng thái: Đã có chức năng, nhưng triển khai khác yêu cầu và cần kiểm tra điều kiện duyệt.**

- Nút tải trong `app/modules/community/views/show.php` gọi `public/serve.php?file=...&download=1`.
- `public/serve.php` kiểm tra đường dẫn và quyền truy cập, đặt `Content-Disposition: attachment`, rồi tăng `documents.download_count`.
- Không tìm thấy action `download()` trong `CommunityController.php` như yêu cầu.
- Với tài liệu riêng tư hoặc chưa duyệt, `public/serve.php` vẫn cho chủ sở hữu hoặc admin tải. Nếu nhiệm vụ yêu cầu chỉ tải tài liệu `approved`, hành vi này chưa khớp.

**Đề xuất:** thống nhất yêu cầu quyền tải với quy tắc duyệt của cộng đồng; nếu chỉ cho tải tài liệu đã duyệt, cần chặn cả chủ sở hữu/admin qua đường dẫn tải cộng đồng, hoặc tách rõ luồng tải dành cho chủ sở hữu.

### 2. Xem trước PDF và ảnh minh họa

**Trạng thái: Đã triển khai, có giới hạn quyền xem.**

- `show.php` nhúng PDF bằng `<iframe>` và cung cấp liên kết mở toàn màn hình.
- Ảnh từ `document_images` được hiển thị thành gallery; bấm ảnh sẽ mở file kích thước đầy đủ ở tab mới.
- Khách chưa đăng nhập không thấy bản xem trước; thay vào đó họ được nhắc đăng nhập. Đây là giới hạn do view và `public/serve.php` áp dụng.
- URL xem trước hiện dùng `serve.php`, không dùng mẫu `index.php?module=serve` trong yêu cầu. Router trong `public/index.php` đang khai báo module `serve` trỏ tới `app/modules/documents/ServeController.php`, nhưng file đó không có trong workspace. Vì vậy không nên đổi sang URL module này nếu chưa bổ sung controller.

### 3. Bình luận

**Trạng thái: Đã triển khai.**

- Người dùng đăng nhập có thể gửi bình luận; danh sách chỉ hiển thị bình luận còn hiệu lực.
- Tác giả có thể sửa/xóa bình luận của mình. Admin cũng được phép thao tác.
- Các thao tác ghi yêu cầu CSRF token; xóa bình luận là xóa mềm.

### 4. Báo cáo vi phạm

**Trạng thái: Đã triển khai một phần; giao diện báo cáo bình luận chưa đúng yêu cầu modal.**

- Báo cáo tài liệu có modal chọn lý do và ô chi tiết; báo cáo được lưu qua `CommunityModel::createReport()`.
- Báo cáo bình luận hiện dùng `prompt()` để nhập lý do, không có modal chọn lý do/ô chi tiết như mô tả.
- Nút báo cáo tài liệu hiển thị “Báo cáo”, còn nút báo cáo bình luận ghi “Báo cáo vi phạm”.
- `nvtv3.md` mô tả `target_type = 'document'/'comment'`, nhưng schema hiện tại không có cột `target_type`. Thay vào đó, bảng `reports` lưu mục tiêu qua `document_id` hoặc `comment_id`, kèm ràng buộc chỉ được chọn một mục tiêu. Model đang ghi theo thiết kế schema hiện tại.
- Form báo cáo yêu cầu đăng nhập và có CSRF token.

## Kết luận

TV3 đã hoàn thành phần cốt lõi của cả bốn nhóm chức năng. Các mục nên xử lý/ xác nhận trước khi nghiệm thu:

1. Xác nhận chính sách tải tài liệu chưa duyệt; hiện chủ sở hữu/admin vẫn tải được qua `serve.php`.
2. Báo cáo bình luận cần chuyển từ `prompt()` sang modal chọn lý do nếu phải bám sát yêu cầu giao diện.
3. Chốt tài liệu có cần xem trước cho khách hay chỉ người đăng nhập; code hiện yêu cầu đăng nhập để đọc PDF.
4. Ghi nhận `document_id`/`comment_id` là cách lưu loại đối tượng báo cáo theo schema hiện tại, thay cho `target_type`.
