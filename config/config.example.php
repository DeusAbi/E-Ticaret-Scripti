<?php
/**
 * E-Ticaret Scripti yapılandırma dosyası.
 *
 * Bu dosyayı "config.php" adıyla kopyalayıp kendi bilgilerinizi girin.
 * config.php bulunamazsa bu örnek dosyadaki varsayılanlar kullanılır.
 */
return [
    'app' => [
        'name'     => 'Vitrin',
        // Sitenin tam adresi (sonunda / olmadan). Ör: https://www.magazam.com
        'url'      => 'http://localhost:8000',
        // Canlı ortamda mutlaka false yapın.
        'debug'    => true,
        'timezone' => 'Europe/Istanbul',
        'locale'   => 'tr_TR',
    ],

    'db' => [
        // "sqlite" (kurulum gerektirmez) veya "mysql"
        'driver'   => 'sqlite',
        'path'     => __DIR__ . '/../storage/database.sqlite',

        // MySQL / MariaDB ayarları
        'host'     => '127.0.0.1',
        'port'     => 3306,
        'database' => 'eticaret',
        'username' => 'root',
        'password' => '',
        'charset'  => 'utf8mb4',
    ],

    'mail' => [
        // "log" (storage/logs/mail.log dosyasına yazar), "mail" (PHP mail()) veya "smtp"
        'driver'     => 'log',
        'from'       => 'no-reply@example.com',
        'from_name'  => 'Vitrin',
        'host'       => 'smtp.example.com',
        'port'       => 587,
        'username'   => '',
        'password'   => '',
        'encryption' => 'tls', // tls | ssl | none
    ],

    'payment' => [
        // "demo" sanal POS: gerçek tahsilat yapmaz. Canlıya geçerken
        // App\Payment\PaymentGatewayInterface'i uygulayan bir sağlayıcı ekleyin (iyzico, PayTR vb.)
        'gateway' => 'demo',
    ],

    // Kurulumda örnek ürün, kategori, sipariş vb. verileri oluşturulsun mu?
    'seed_demo_data' => true,
];
