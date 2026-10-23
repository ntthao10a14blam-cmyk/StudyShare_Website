# QUY CHUẨN THIẾT KẾ GIAO DIỆN (UI/UX GUIDELINES) — STUDYHUB

Tài liệu này dùng để **thống nhất toàn bộ thiết kế** cho tất cả thành viên trong nhóm (TV1 → TV6), đảm bảo website đồng bộ về font chữ, bảng màu, phong cách Header - Footer và định dạng web cộng đồng gọn mắt.

---

## 1. Định hướng thiết kế: Web Cộng đồng Basic & Gọn mắt

> **Tiêu chí cốt lõi:** Đơn giản, thoáng đãng, dễ đọc, trực quan. Tránh rườm rà, tránh gradient gắt hoặc bóng đổ quá đậm. Tập trung tối đa vào nội dung tài liệu và thảo luận của sinh viên.

- **Bố cục (Layout):** Tận dụng dạng danh sách phẳng (List feed) hoặc thẻ phẳng (Flat Card) với đường viền mỏng (`1px solid #e2e8f0`).
- **Khoảng cách (Spacing):** Đồng đều (margin/padding theo chuẩn `0.75rem`, `1rem`, `1.5rem`), giữ cho trang có khoảng thở.
- **Thao tác nhanh:** Nút bấm, tag môn học, lượt tải/bình luận hiển thị rõ ràng, súc tích.

---

## 2. Thống nhất Font chữ (Typography)

Tất cả các trang và phân hệ **bắt buộc dùng chung font `Inter`** đã nhúng tại `app/views/layouts/header.php`.

```css
font-family: 'Inter', -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, sans-serif;
```

### Phân cấp cỡ chữ (Hierarchy):
| Thành phần | Cỡ chữ | Độ đậm (font-weight) | Mục đích |
|------------|--------|----------------------|----------|
| **Tiêu đề trang (Page title)** | `1.375rem` (22px) | `700` (Bold) | Tiêu đề chính đầu trang |
| **Tiêu đề khối / Feed item** | `1.05rem` (16.8px) | `600` (Semi-bold) | Tiêu đề tài liệu, câu hỏi |
| **Văn bản chuẩn (Body)** | `0.9375rem` (15px) | `400` (Regular) | Nội dung bài viết, mô tả |
| **Meta text, chú thích** | `0.8125rem` (13px) | `400` / `500` | Ngày đăng, tác giả, dung lượng |
| **Badge, Tag môn học** | `0.75rem` (12px) | `500` (Medium) | Tag môn, trạng thái |

---

## 3. Thống nhất Bảng màu (Color Palette)

Không dùng mã màu tự do hoặc style inline. Hãy sử dụng các biến CSS đã định nghĩa trong `public/assets/css/main.css`:

| Tên biến | Mã màu Hex | Ý nghĩa sử dụng |
|----------|------------|-----------------|
| `--color-primary` | `#0b766e` | Màu chủ đạo: Nút chính, link, icon thương hiệu, active state (Xanh ngọc) |
| `--color-primary-dark` | `#075e57` | Trạng thái hover của nút bấm và liên kết |
| `--color-primary-light` | `#d7efeb` | Nền nhạt cho tag môn học, pill, highlight nhẹ nhàng |
| `--color-primary-border`| `#8bcbc1` | Viền nhạt cho tag và khối thông tin nổi bật |
| `--color-accent` | `#f5d77a` | Màu nhấn: Vàng pastel cho điểm nhấn, icon, nút phụ |
| `--color-accent-dark` | `#e9c45f` | Hiệu ứng hover nút vàng nhấn |
| `--color-accent-light` | `#fff3c4` | Vàng kem nhạt: nền badge, nhãn danh mục nổi bật |
| `--color-cream` | `#fffcf3` | Trắng kem: nền các khu vực phụ |
| `--color-bg` | `#f8faf7` | Nền toàn trang (dịu mắt, không gây mỏi) |
| `--color-surface` | `#ffffff` | Nền thẻ Card, Header, Footer |
| `--color-border` | `#d4e5e1` | Đường viền 1px phân tách các khối |
| `--color-border-subtle` | `#eaf3f0` | Đường viền phụ nhẹ nhàng |
| `--color-text` | `#203b38` | Chữ chính: tiêu đề, nội dung |
| `--color-text-muted` | `#607d78` | Chữ phụ, meta thông tin, tác giả |
| `--color-text-light` | `#829c96` | Chữ ít quan trọng: chú thích, placeholder |
| `--color-success` | `#27865b` | Trạng thái duyệt thành công, đã trả lời |
| `--color-danger` | `#d65b5b` | Trạng thái từ chối, xóa, lỗi |
| `--color-warning` | `#d99a35` | Trạng thái chờ duyệt (Pending) |
| `--color-info` | `#287f9c` | Trạng thái thông báo, hướng dẫn |

---

## 4. Header & Footer Đồng bộ

Header và Footer tạo thành khung nhận diện thương hiệu thống nhất từ đầu đến chân trang:

### Quy chuẩn chung:
1. **Màu nền:** Cùng sử dụng nền trắng phẳng (`#ffffff` / `var(--color-surface)`).
2. **Đường viền:** Header có `border-bottom: 1px solid var(--color-border)`; Footer có `border-top: 1px solid var(--color-border)`.
3. **Thương hiệu:** Cùng sử dụng icon sách `<i class="bi bi-book-half text-primary"></i>` và chữ `StudyHub` màu chữ chính.
4. **Màu chữ & Link:** Chữ phụ dùng `var(--color-text-muted)`, khi rê chuột (hover) đổi sang `var(--color-primary)`.
5. **Đóng mở thẻ HTML chuẩn:**
   - `header.php` mở thẻ `<main class="main-content flex-grow-1">`.
   - `footer.php` đóng thẻ `</main>`.
   - **Tuyệt đối không** tự ý viết lại thẻ `<html>`, `<head>`, `<body>` hoặc tự đóng `</main>` trong file view của từng module.

---

## 5. Quy chuẩn Component cho từng Module

Khi thành viên làm việc trên các view của mình (`documents`, `community`, `questions`, `collections`, `admin`), hãy áp dụng các mẫu chuẩn sau:

### 5.1. Khung trang chuẩn (Page Template)
```php
<?php
$pageTitle = 'Tên chức năng';
require_once BASE_PATH . '/app/views/layouts/header.php';
?>

<div class="container py-4">
    <!-- Tiêu đề trang chuẩn -->
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h1 class="page-title mb-0">Tên chức năng</h1>
        <a href="..." class="btn btn-primary btn-sm">
            <i class="bi bi-plus-lg me-1"></i> Thao tác chính
        </a>
    </div>

    <!-- Nội dung phân hệ -->
    ...
</div>

<?php require_once BASE_PATH . '/app/views/layouts/footer.php'; ?>
```

### 5.2. Thẻ hiển thị danh sách (Feed Item)
```html
<div class="feed-item">
    <div class="d-flex justify-content-between align-items-start gap-2">
        <a href="..." class="feed-title text-decoration-none">
            <i class="bi bi-file-pdf text-danger me-1"></i> Tiêu đề tài liệu
        </a>
        <span class="badge-tag">Môn học</span>
    </div>
    <div class="feed-meta mt-2">
        <span><i class="bi bi-person me-1"></i> Tác giả</span>
        <span><i class="bi bi-clock me-1"></i> 2 giờ trước</span>
        <span><i class="bi bi-download me-1"></i> 45 lượt tải</span>
    </div>
</div>
```

### 5.3. Thẻ Card thống kê & Form
```html
<div class="card community-card p-3">
    <h6 class="fw-bold text-dark mb-2">Tiêu đề Card</h6>
    <!-- Nội dung gọn mắt -->
</div>
```

### 5.4. Nút bấm (Button)
- Thao tác chính (Submit, Tải lên, Đăng bài): `class="btn btn-primary btn-sm"`
- Thao tác phụ (Xem thêm, Lọc): `class="btn btn-outline-primary btn-sm"`
- Thao tác trung tính (Hủy, Quay lại): `class="btn btn-light-subtle btn-sm"`
- Thao tác nguy hiểm (Xóa): `class="btn btn-danger btn-sm"`
