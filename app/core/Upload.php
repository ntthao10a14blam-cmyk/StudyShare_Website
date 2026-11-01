<?php
/**
 * UPLOAD.PHP — Xử lý file upload dùng chung
 *
 * Tất cả thao tác upload file (PDF, ảnh, avatar) phải dùng lớp này.
 * Không tự xử lý $_FILES trong controller riêng lẻ.
 *
 * Cách dùng — Upload PDF:
 *   $result = Upload::pdf($_FILES['document_file'], STORAGE_DOCUMENTS);
 *   if ($result['success']) {
 *       $filePath = $result['path'];       // đường dẫn lưu vào DB
 *       $fileName = $result['original'];   // tên gốc
 *       $fileSize = $result['size'];
 *   } else {
 *       $error = $result['error'];
 *   }
 *
 * Cách dùng — Upload ảnh:
 *   $result = Upload::image($_FILES['image'], STORAGE_DOC_IMAGES);
 *
 * Cách dùng — Upload nhiều ảnh:
 *   $files = Upload::normalizeMultiple($_FILES['images']);
 *   foreach ($files as $file) {
 *       $result = Upload::image($file, STORAGE_DOC_IMAGES);
 *   }
 *
 * Cách dùng — Upload avatar:
 *   $result = Upload::avatar($_FILES['avatar'], STORAGE_AVATARS);
 *
 * Xóa file:
 *   Upload::delete($storagePath);
 */

class Upload
{
    // ============================================================
    // PUBLIC — UPLOAD CÁC LOẠI FILE
    // ============================================================

    /**
     * Upload file PDF.
     */
    public static function pdf(array $file, string $targetDir): array
    {
        return self::handle($file, $targetDir, ALLOWED_PDF_TYPES, MAX_PDF_SIZE, 'pdf_');
    }

    /**
     * Upload ảnh minh hoạ (tài liệu / câu hỏi).
     */
    public static function image(array $file, string $targetDir): array
    {
        return self::handle($file, $targetDir, ALLOWED_IMAGE_TYPES, MAX_IMAGE_SIZE, 'img_');
    }

    /**
     * Upload ảnh đại diện.
     */
    public static function avatar(array $file, string $targetDir): array
    {
        return self::handle($file, $targetDir, ALLOWED_IMAGE_TYPES, MAX_AVATAR_SIZE, 'av_');
    }

    /**
     * Xóa file theo đường dẫn tương đối (đã lưu trong DB).
     * Ví dụ: $storagePath = 'documents/pdf_abc123.pdf'
     */
    public static function delete(string $storagePath): bool
    {
        if (empty($storagePath)) return false;
        $fullPath = STORAGE_PATH . '/' . ltrim($storagePath, '/');
        if (file_exists($fullPath)) {
            return unlink($fullPath);
        }
        return false;
    }

    /**
     * Chuẩn hóa mảng $_FILES khi upload nhiều ảnh.
     * PHP trả về mảng lồng khi name="images[]"; hàm này chuyển thành
     * mảng các $_FILES đơn lẻ để upload từng cái.
     *
     * @return array[] Mảng các file đơn lẻ, mỗi phần tử tương tự $_FILES['x']
     */
    public static function normalizeMultiple(array $filesField): array
    {
        $normalized = [];
        if (!isset($filesField['name']) || !is_array($filesField['name'])) {
            return $normalized;
        }
        $count = count($filesField['name']);
        for ($i = 0; $i < $count; $i++) {
            if ($filesField['error'][$i] === UPLOAD_ERR_NO_FILE) continue;
            $normalized[] = [
                'name'     => $filesField['name'][$i],
                'type'     => $filesField['type'][$i],
                'tmp_name' => $filesField['tmp_name'][$i],
                'error'    => $filesField['error'][$i],
                'size'     => $filesField['size'][$i],
            ];
        }
        return $normalized;
    }

    // ============================================================
    // PRIVATE — XỬ LÝ UPLOAD
    // ============================================================

    /**
     * Hàm xử lý upload chung.
     *
     * @return array{success:bool, path?:string, original?:string, size?:int, error?:string}
     */
    private static function handle(
        array  $file,
        string $targetDir,
        array  $allowedMimes,
        int    $maxSize,
        string $prefix
    ): array {
        // 1. Kiểm tra lỗi upload từ PHP
        if ($file['error'] !== UPLOAD_ERR_OK) {
            return ['success' => false, 'error' => self::uploadErrorMessage($file['error'])];
        }

        // 2. Kiểm tra kích thước
        if ($file['size'] > $maxSize) {
            $maxMb = round($maxSize / 1024 / 1024, 1);
            return ['success' => false, 'error' => "File không được vượt quá {$maxMb} MB."];
        }

        // 3. Kiểm tra MIME type thực tế (không tin vào $_FILES['type'])
        $finfo    = new finfo(FILEINFO_MIME_TYPE);
        $mimeType = $finfo->file($file['tmp_name']);
        if (!in_array($mimeType, $allowedMimes, true)) {
            return ['success' => false, 'error' => 'Loại file không được hỗ trợ.'];
        }

        // 4. Tạo tên file duy nhất
        $ext      = self::extensionFromMime($mimeType);
        $newName  = $prefix . bin2hex(random_bytes(12)) . '.' . $ext;
        $destPath = rtrim($targetDir, '/') . '/' . $newName;

        // 5. Tạo thư mục nếu chưa có
        if (!is_dir($targetDir)) {
            mkdir($targetDir, 0755, true);
        }

        // 6. Di chuyển file
        if (!move_uploaded_file($file['tmp_name'], $destPath)) {
            return ['success' => false, 'error' => 'Không thể lưu file. Kiểm tra quyền ghi thư mục storage/.'];
        }

        // 7. Đường dẫn lưu vào DB (tương đối từ storage/)
        $relativePath = str_replace(STORAGE_PATH . '/', '', $destPath);

        return [
            'success'  => true,
            'path'     => $relativePath,           // lưu vào DB
            'original' => $file['name'],           // tên gốc của người dùng
            'size'     => $file['size'],
        ];
    }

    /**
     * Ánh xạ MIME type sang extension.
     */
    private static function extensionFromMime(string $mime): string
    {
        return match ($mime) {
            'application/pdf' => 'pdf',
            'image/jpeg'      => 'jpg',
            'image/png'       => 'png',
            'image/webp'      => 'webp',
            'image/gif'       => 'gif',
            default           => 'bin',
        };
    }

    /**
     * Chuyển mã lỗi upload PHP thành thông báo tiếng Việt.
     */
    private static function uploadErrorMessage(int $code): string
    {
        return match ($code) {
            UPLOAD_ERR_INI_SIZE, UPLOAD_ERR_FORM_SIZE => 'File vượt quá kích thước cho phép.',
            UPLOAD_ERR_PARTIAL   => 'File chỉ được tải lên một phần. Thử lại.',
            UPLOAD_ERR_NO_FILE   => 'Chưa chọn file.',
            UPLOAD_ERR_NO_TMP_DIR, UPLOAD_ERR_CANT_WRITE => 'Lỗi máy chủ khi xử lý file.',
            default              => 'Đã xảy ra lỗi không xác định khi upload file.',
        };
    }
}
