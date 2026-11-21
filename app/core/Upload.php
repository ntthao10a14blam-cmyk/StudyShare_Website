<?php

class Upload
{

    public static function pdf(array $file, string $targetDir): array
    {
        return self::handle($file, $targetDir, ALLOWED_PDF_TYPES, MAX_PDF_SIZE, 'pdf_');
    }

    public static function image(array $file, string $targetDir): array
    {
        return self::handle($file, $targetDir, ALLOWED_IMAGE_TYPES, MAX_IMAGE_SIZE, 'img_');
    }

    public static function avatar(array $file, string $targetDir): array
    {
        return self::handle($file, $targetDir, ALLOWED_IMAGE_TYPES, MAX_AVATAR_SIZE, 'av_');
    }

    public static function delete(string $storagePath): bool
    {
        if (empty($storagePath)) return false;
        $fullPath = STORAGE_PATH . '/' . ltrim($storagePath, '/');
        if (file_exists($fullPath)) {
            return unlink($fullPath);
        }
        return false;
    }

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

    private static function handle(
        array  $file,
        string $targetDir,
        array  $allowedMimes,
        int    $maxSize,
        string $prefix
    ): array {

        if ($file['error'] !== UPLOAD_ERR_OK) {
            return ['success' => false, 'error' => self::uploadErrorMessage($file['error'])];
        }

        if ($file['size'] > $maxSize) {
            $maxMb = round($maxSize / 1024 / 1024, 1);
            return ['success' => false, 'error' => "File không được vượt quá {$maxMb} MB."];
        }

        $finfo    = new finfo(FILEINFO_MIME_TYPE);
        $mimeType = $finfo->file($file['tmp_name']);
        if (!in_array($mimeType, $allowedMimes, true)) {
            return ['success' => false, 'error' => 'Loại file không được hỗ trợ.'];
        }

        $ext      = self::extensionFromMime($mimeType);
        $newName  = $prefix . bin2hex(random_bytes(12)) . '.' . $ext;
        $destPath = rtrim($targetDir, '/') . '/' . $newName;

        if (!is_dir($targetDir)) {
            mkdir($targetDir, 0755, true);
        }

        if (!move_uploaded_file($file['tmp_name'], $destPath)) {
            return ['success' => false, 'error' => 'Không thể lưu file. Kiểm tra quyền ghi thư mục storage/.'];
        }

        $relativePath = str_replace(STORAGE_PATH . '/', '', $destPath);

        return [
            'success'  => true,
            'path'     => $relativePath,
            'original' => $file['name'],
            'size'     => $file['size'],
        ];
    }

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
