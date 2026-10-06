<?php
/**
 * Komut satırından kurulum:
 *   php database/install.php            -> Tablolar yoksa kurar (örnek verilerle)
 *   php database/install.php --fresh    -> TÜM tabloları silip yeniden kurar
 *   php database/install.php --no-demo  -> Örnek veri olmadan kurar
 */
declare(strict_types=1);

if (PHP_SAPI !== 'cli') {
    exit('Bu betik yalnızca komut satırından çalıştırılabilir.');
}

require dirname(__DIR__) . '/app/bootstrap.php';

use App\Core\Database;
use App\Core\Installer;

$args = array_slice($argv, 1);
$fresh = in_array('--fresh', $args, true);
$demo = !in_array('--no-demo', $args, true);

try {
    if ($fresh) {
        echo "Mevcut tablolar siliniyor...\n";
        Installer::dropAll();
        foreach (glob(PUBLIC_PATH . '/uploads/{products,sliders}/demo-*.svg', GLOB_BRACE) ?: [] as $f) {
            @unlink($f);
        }
    } elseif (Database::tableExists('settings')) {
        echo "Veritabanı zaten kurulu. Sıfırdan kurmak için --fresh parametresini kullanın.\n";
        exit(0);
    }

    echo 'Veritabanı kuruluyor (' . Database::driver() . ")...\n";
    Installer::install($demo);
    echo "✔ Kurulum tamamlandı.\n\n";
    echo "Yönetici girişi : admin@example.com / admin123\n";
    if ($demo) {
        echo "Demo müşteri    : demo@example.com / demo123\n";
    }
    echo "\nGüvenliğiniz için yönetici şifresini ilk girişte değiştirin.\n";
} catch (Throwable $e) {
    fwrite(STDERR, 'Kurulum hatası: ' . $e->getMessage() . "\n");
    exit(1);
}
