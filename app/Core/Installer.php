<?php
declare(strict_types=1);

namespace App\Core;

use App\Services\DemoSeeder;
use RuntimeException;

/**
 * İlk çalıştırmada veritabanı tablolarını ve varsayılan verileri oluşturur.
 */
final class Installer
{
    public static function ensureInstalled(): void
    {
        if (Database::tableExists('settings')) {
            return;
        }
        $lock = fopen(STORAGE_PATH . '/install.lock', 'c');
        if ($lock && flock($lock, LOCK_EX)) {
            try {
                if (!Database::tableExists('settings')) {
                    self::install((bool) config('seed_demo_data', true));
                }
            } finally {
                flock($lock, LOCK_UN);
                fclose($lock);
            }
        }
    }

    public static function install(bool $withDemo = true): void
    {
        self::runSchema();
        self::seedDefaults();
        if ($withDemo) {
            DemoSeeder::run();
        }
    }

    /** Tüm tabloları siler (yalnızca CLI'dan "--fresh" ile kullanılır) */
    public static function dropAll(): void
    {
        $tables = ['order_history', 'order_items', 'orders', 'wishlists', 'reviews', 'product_images', 'products',
            'categories', 'addresses', 'password_resets', 'login_attempts', 'coupons', 'pages', 'sliders', 'messages',
            'newsletter_subscribers', 'users', 'settings'];
        if (Database::driver() === 'mysql') {
            Database::pdo()->exec('SET FOREIGN_KEY_CHECKS = 0');
        } else {
            Database::pdo()->exec('PRAGMA foreign_keys = OFF');
        }
        foreach ($tables as $t) {
            Database::pdo()->exec("DROP TABLE IF EXISTS $t");
        }
        if (Database::driver() === 'mysql') {
            Database::pdo()->exec('SET FOREIGN_KEY_CHECKS = 1');
        } else {
            Database::pdo()->exec('PRAGMA foreign_keys = ON');
        }
    }

    private static function runSchema(): void
    {
        $file = BASE_PATH . '/database/schema.' . Database::driver() . '.sql';
        $sql = file_get_contents($file);
        if ($sql === false) {
            throw new RuntimeException("Şema dosyası okunamadı: $file");
        }
        foreach (array_filter(array_map('trim', explode(';', $sql))) as $statement) {
            Database::pdo()->exec($statement);
        }
    }

    public static function defaultSettings(): array
    {
        return [
            'store_name'              => (string) config('app.name', 'Vitrin'),
            'store_tagline'           => 'Alışverişin en şık hali',
            'store_email'             => 'destek@example.com',
            'store_phone'             => '0850 123 45 67',
            'store_whatsapp'          => '905321234567',
            'store_address'           => 'Maslak Mah. Büyükdere Cad. No: 1, Sarıyer / İstanbul',
            'store_company'           => 'Vitrin E-Ticaret A.Ş.',
            'store_tax_info'          => 'Maslak V.D. 1234567890',
            'store_mersis'            => '0123456789000015',
            'footer_about'            => 'Vitrin; elektronikten modaya, ev yaşamından spora binlerce ürünü güvenli ödeme ve hızlı teslimat avantajıyla sunan yeni nesil alışveriş platformudur.',
            'meta_description'        => 'Elektronik, moda, ev & yaşam ve daha fazlasında en yeni ürünler uygun fiyatlarla. Hızlı kargo, güvenli ödeme ve kolay iade.',
            'primary_color'           => '#ef5a24',
            'currency_symbol'         => '₺',
            'top_bar_text'            => '750 ₺ ve üzeri siparişlerde kargo bedava!',
            'shipping_fee'            => '49.90',
            'free_shipping_threshold' => '750',
            'shipping_days'           => '1-3 iş günü',
            'cargo_companies'         => 'Yurtiçi Kargo, Aras Kargo, MNG Kargo, PTT Kargo, Sürat Kargo',
            'card_enabled'            => '1',
            'transfer_enabled'        => '1',
            'cod_enabled'             => '1',
            'cod_fee'                 => '29.90',
            'bank_accounts'           => "Ziraat Bankası | Vitrin E-Ticaret A.Ş. | TR00 0001 0000 0000 0000 0000 01\nGaranti BBVA | Vitrin E-Ticaret A.Ş. | TR00 0006 2000 0000 0000 0000 02",
            'review_auto_approve'     => '0',
            'products_per_page'       => '12',
            'social_instagram'        => 'https://instagram.com',
            'social_facebook'         => 'https://facebook.com',
            'social_twitter'          => 'https://x.com',
            'social_youtube'          => 'https://youtube.com',
        ];
    }

    private static function seedDefaults(): void
    {
        foreach (self::defaultSettings() as $key => $value) {
            Database::insert('settings', ['setting_key' => $key, 'setting_value' => $value]);
        }

        Database::insert('users', [
            'name'       => 'Mağaza Yöneticisi',
            'email'      => 'admin@example.com',
            'phone'      => '05320000000',
            'password'   => password_hash('admin123', PASSWORD_DEFAULT),
            'role'       => 'admin',
            'is_active'  => 1,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        foreach (self::defaultPages() as $i => [$title, $slug, $content]) {
            Database::insert('pages', [
                'title'          => $title,
                'slug'           => $slug,
                'content'        => $content,
                'show_in_footer' => 1,
                'is_active'      => 1,
                'sort_order'     => $i,
                'created_at'     => now(),
                'updated_at'     => now(),
            ]);
        }
    }

    private static function defaultPages(): array
    {
        return [
            ['Hakkımızda', 'hakkimizda', <<<TXT
## Biz Kimiz?
Vitrin, kaliteli ürünleri uygun fiyatlarla ve kusursuz bir alışveriş deneyimiyle müşterilerine ulaştırmak amacıyla kurulmuş bir e-ticaret platformudur.

## Misyonumuz
Müşterilerimize **güvenli ödeme**, **hızlı teslimat** ve **kolay iade** imkânı sunarak alışverişi keyifli hale getirmek.

## Neden Vitrin?
- Orijinal ve faturalı ürünler
- 256-bit SSL ile güvenli ödeme altyapısı
- 14 gün içinde koşulsuz iade
- Uzman müşteri hizmetleri ekibi
TXT],
            ['Teslimat ve İade', 'teslimat-ve-iade', <<<TXT
## Teslimat Koşulları
Siparişleriniz, ödeme onayının ardından 1-3 iş günü içinde kargoya teslim edilir. Kargo takip numaranız e-posta ile tarafınıza iletilir ve "Hesabım > Siparişlerim" sayfasından takip edilebilir.

## İade Koşulları
- Ürünü teslim aldığınız tarihten itibaren **14 gün** içinde iade edebilirsiniz.
- İade edilecek ürünlerin kullanılmamış, ambalajının açılmamış ve yeniden satılabilir durumda olması gerekir.
- Kozmetik ve kişisel bakım ürünlerinde ambalajı açılmış ürünlerin iadesi hijyen nedeniyle kabul edilmemektedir.

## Para İadesi
İade ürün depomuza ulaşıp kontrol edildikten sonra ücret, ödeme yaptığınız yönteme 3-10 iş günü içinde iade edilir.
TXT],
            ['Mesafeli Satış Sözleşmesi', 'mesafeli-satis-sozlesmesi', <<<TXT
## 1. Taraflar
Bu sözleşme, bir tarafta SATICI ile diğer tarafta internet sitesi üzerinden sipariş veren ALICI arasında, 6502 sayılı Tüketicinin Korunması Hakkında Kanun ve Mesafeli Sözleşmeler Yönetmeliği hükümleri uyarınca düzenlenmiştir.

## 2. Konu
İşbu sözleşmenin konusu, ALICI'nın SATICI'ya ait internet sitesi üzerinden elektronik ortamda siparişini verdiği ürünün satışı ve teslimi ile ilgili olarak tarafların hak ve yükümlülüklerinin belirlenmesidir.

## 3. Cayma Hakkı
ALICI, ürünü teslim aldığı tarihten itibaren 14 (on dört) gün içinde herhangi bir gerekçe göstermeksizin ve cezai şart ödemeksizin sözleşmeden cayma hakkına sahiptir.

## 4. Yetkili Mahkeme
İşbu sözleşmeden doğan uyuşmazlıklarda Tüketici Hakem Heyetleri ve Tüketici Mahkemeleri yetkilidir.

*Not: Bu metin örnek amaçlıdır; yayına almadan önce hukuk danışmanınıza kontrol ettiriniz.*
TXT],
            ['KVKK Aydınlatma Metni', 'kvkk-aydinlatma-metni', <<<TXT
## Veri Sorumlusu
6698 sayılı Kişisel Verilerin Korunması Kanunu ("KVKK") uyarınca kişisel verileriniz, veri sorumlusu sıfatıyla şirketimiz tarafından aşağıda açıklanan kapsamda işlenebilecektir.

## İşlenen Kişisel Veriler
- Kimlik bilgileri (ad, soyad)
- İletişim bilgileri (e-posta, telefon, adres)
- Müşteri işlem bilgileri (sipariş, fatura bilgileri)

## İşleme Amaçları
Kişisel verileriniz; siparişlerinizin alınması ve teslimi, faturalandırma, müşteri hizmetlerinin yürütülmesi ve yasal yükümlülüklerin yerine getirilmesi amaçlarıyla işlenmektedir.

## Haklarınız
KVKK'nın 11. maddesi kapsamındaki haklarınızı kullanmak için bizimle iletişime geçebilirsiniz.
TXT],
            ['Gizlilik ve Çerez Politikası', 'gizlilik-ve-cerez-politikasi', <<<TXT
## Gizlilik
Kişisel bilgileriniz yalnızca hizmet sunumu amacıyla kullanılır ve izniniz olmadan üçüncü kişilerle paylaşılmaz. Kart bilgileriniz sistemimizde **saklanmaz**.

## Çerezler
Sitemiz; oturum yönetimi, sepet işlemleri ve güvenlik amacıyla zorunlu çerezler kullanmaktadır. Tarayıcı ayarlarınızdan çerez tercihlerinizi yönetebilirsiniz.
TXT],
            ['Sıkça Sorulan Sorular', 'sikca-sorulan-sorular', <<<TXT
## Siparişim ne zaman kargoya verilir?
Siparişleriniz ödeme onayının ardından 1-3 iş günü içinde kargoya teslim edilir.

## Hangi ödeme yöntemlerini kullanabilirim?
Kredi kartı / banka kartı, havale / EFT ve kapıda ödeme seçeneklerini kullanabilirsiniz.

## Siparişimi nasıl takip ederim?
"Hesabım > Siparişlerim" sayfasından veya "Sipariş Takibi" sayfasından sipariş numaranız ve e-posta adresinizle takip edebilirsiniz.

## Siparişimi iptal edebilir miyim?
Kargoya verilmemiş siparişlerinizi "Hesabım > Siparişlerim" sayfasından iptal edebilirsiniz.
TXT],
        ];
    }
}
