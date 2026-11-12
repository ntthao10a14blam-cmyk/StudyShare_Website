# 👤 TV3: Phân hệ Cộng đồng, Kho tài liệu công khai & Tương tác

* **Phụ trách Mục 3:** Kho tài liệu mở, Xem trước, Tải về, Bình luận, Báo cáo vi phạm.

## Nhiệm vụ cụ thể

### 1. Xây dựng tính năng Tải xuống thực tế (`download`)
* Viết action `download()` trong `CommunityController.php`: kiểm tra tài liệu có `approved` không, đọc file PDF từ `storage/documents/` gửi về trình duyệt với header `Content-Disposition: attachment`.
* Tự động thực hiện câu lệnh:
  ```sql
  UPDATE documents SET download_count = download_count + 1 WHERE id = :id;
  ```

### 2. Khung Xem trước PDF (Preview) tại `views/show.php`
* Nhúng thẻ `<iframe src="index.php?module=serve&file=<?= $doc['file_path'] ?>" width="100%" height="650px">` để người xem đọc thử trực tiếp trên web trước khi bấm tải.
* Hiển thị gallery ảnh minh họa đính kèm (`document_images`) bấm xem kích thước lớn.

### 3. Hệ thống Bình luận tài liệu
* Cho phép User bình luận, hiển thị form comment. Tác giả bình luận có quyền sửa hoặc xóa bình luận của chính mình.

### 4. Chức năng Báo cáo vi phạm (Report)
* Thêm nút cờ báo cáo **[Báo cáo vi phạm]** ở góc tài liệu và mỗi bình luận. 
* Bấm vào hiện modal chọn lý do (spam, sai chuyên môn, nội dung nhạy cảm...) rồi lưu vào bảng `reports` với `target_type = 'document'` hoặc `'comment'`.