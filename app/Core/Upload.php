<?php
declare(strict_types=1);

namespace App\Core;

use RuntimeException;

/**
 * Güvenli görsel yükleme. Dosya içeriği finfo + getimagesize ile doğrulanır,
 * rastgele bir adla public/uploads altına kaydedilir.
 */
final class Upload
{
    private const MIME_EXT = [
        'image/jpeg' => 'jpg',
        'image/png'  => 'png',
        'image/webp' => 'webp',
        'image/gif'  => 'gif',
    ];

    public const MAX_SIZE = 5 * 1024 * 1024;

    /**
     * @return string Kaydedilen dosyanın public klasörüne göre yolu (ör: uploads/products/abc.jpg)
     */
    public static function image(array $file, string $folder): string
    {
        if (($file['error'] ?? UPLOAD_ERR_NO_FILE) !== UPLOAD_ERR_OK) {
            throw new RuntimeException(self::errorMessage((int) ($file['error'] ?? UPLOAD_ERR_NO_FILE)));
        }
        if ($file['size'] > self::MAX_SIZE) {
            throw new RuntimeException('Görsel boyutu en fazla 5 MB olabilir.');
        }
        if (!is_uploaded_file($file['tmp_name'])) {
            throw new RuntimeException('Geçersiz dosya yüklemesi.');
        }

        $mime = (new \finfo(FILEINFO_MIME_TYPE))->file($file['tmp_name']);
        if (!isset(self::MIME_EXT[$mime]) || @getimagesize($file['tmp_name']) === false) {
            throw new RuntimeException('Yalnızca JPG, PNG, WEBP veya GIF görselleri yükleyebilirsiniz.');
        }

        $folder = trim($folder, '/');
        $dir = PUBLIC_PATH . '/uploads/' . $folder;
        if (!is_dir($dir) && !mkdir($dir, 0775, true) && !is_dir($dir)) {
            throw new RuntimeException('Yükleme klasörü oluşturulamadı.');
        }

        $name = date('Ymd') . '-' . bin2hex(random_bytes(8)) . '.' . self::MIME_EXT[$mime];
        if (!move_uploaded_file($file['tmp_name'], $dir . '/' . $name)) {
            throw new RuntimeException('Dosya kaydedilemedi.');
        }
        return 'uploads/' . $folder . '/' . $name;
    }

    /** Çoklu dosya girişini ($_FILES['images']) tekil dizilere dönüştürür */
    public static function normalize(?array $files): array
    {
        if (!$files || !isset($files['name'])) {
            return [];
        }
        if (!is_array($files['name'])) {
            return ($files['error'] ?? UPLOAD_ERR_NO_FILE) === UPLOAD_ERR_NO_FILE ? [] : [$files];
        }
        $result = [];
        foreach ($files['name'] as $i => $name) {
            if (($files['error'][$i] ?? UPLOAD_ERR_NO_FILE) === UPLOAD_ERR_NO_FILE) {
                continue;
            }
            $result[] = [
                'name'     => $name,
                'type'     => $files['type'][$i],
                'tmp_name' => $files['tmp_name'][$i],
                'error'    => $files['error'][$i],
                'size'     => $files['size'][$i],
            ];
        }
        return $result;
    }

    public static function delete(?string $path): void
    {
        if (!$path || !str_starts_with($path, 'uploads/') || str_contains($path, '..')) {
            return;
        }
        $full = PUBLIC_PATH . '/' . $path;
        if (is_file($full)) {
            @unlink($full);
        }
    }

    private static function errorMessage(int $code): string
    {
        return match ($code) {
            UPLOAD_ERR_INI_SIZE, UPLOAD_ERR_FORM_SIZE => 'Dosya boyutu sunucu limitini aşıyor.',
            UPLOAD_ERR_PARTIAL => 'Dosya kısmen yüklendi, lütfen tekrar deneyin.',
            UPLOAD_ERR_NO_FILE => 'Dosya seçilmedi.',
            default => 'Dosya yüklenirken bir hata oluştu.',
        };
    }
}
