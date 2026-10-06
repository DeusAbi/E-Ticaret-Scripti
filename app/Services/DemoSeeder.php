<?php
declare(strict_types=1);

namespace App\Services;

use App\Core\Database;

/**
 * Mağazayı ilk açılışta dolu göstermek için örnek veriler oluşturur.
 */
final class DemoSeeder
{
    private const PALETTES = [
        'elektronik' => ['#e8efff', '#d4e1ff', '#2a4bd7'],
        'moda'       => ['#ffeee6', '#ffd9c7', '#c2410c'],
        'ev'         => ['#eaf7ef', '#d3efdd', '#15803d'],
        'spor'       => ['#e6f6fb', '#cdeef7', '#0e7490'],
        'kozmetik'   => ['#fdeef6', '#f9d8ea', '#be185d'],
        'oyun'       => ['#f1ecff', '#e0d6ff', '#6d28d9'],
    ];

    public static function run(): void
    {
        $dir = PUBLIC_PATH . '/uploads/products';
        if (!is_dir($dir)) {
            mkdir($dir, 0775, true);
        }

        $catIds = self::categories();
        $productIds = self::products($catIds);
        $customers = self::customers();
        self::reviews($productIds, $customers);
        self::coupons();
        self::sliders();
        self::orders($productIds, $customers);
    }

    private static function categories(): array
    {
        $tree = [
            ['Elektronik', 'smartphone', 'elektronik', 'Telefon, bilgisayar, ses sistemleri ve akıllı cihazlar.', [
                ['Telefon & Tablet', 'smartphone'],
                ['Bilgisayar', 'laptop'],
                ['Ses & Kulaklık', 'headphones'],
                ['Giyilebilir Teknoloji', 'watch'],
            ]],
            ['Moda', 'shirt', 'moda', 'Sezonun öne çıkan giyim, ayakkabı ve aksesuar ürünleri.', [
                ['Giyim', 'shirt'],
                ['Ayakkabı', 'shoe'],
                ['Çanta & Aksesuar', 'bag'],
            ]],
            ['Ev & Yaşam', 'sofa', 'ev', 'Evinize karakter katan dekorasyon ve yaşam ürünleri.', []],
            ['Spor & Outdoor', 'dumbbell', 'spor', 'Antrenman, kamp ve şehir hayatı için ekipmanlar.', []],
            ['Kozmetik & Bakım', 'sparkles', 'kozmetik', 'Cilt bakımı, parfüm ve kişisel bakım ürünleri.', []],
            ['Oyun & Hobi', 'gamepad', 'oyun', 'Oyun konsolları, aksesuarlar ve hobi ürünleri.', []],
        ];

        $ids = [];
        foreach ($tree as $i => [$name, $icon, $key, $desc, $children]) {
            $id = Database::insert('categories', [
                'name' => $name, 'slug' => slugify($name), 'icon' => $icon, 'description' => $desc,
                'sort_order' => $i, 'is_active' => 1, 'created_at' => now(),
            ]);
            $ids[$name] = ['id' => $id, 'palette' => $key];
            foreach ($children as $j => [$childName, $childIcon]) {
                $ids[$childName] = ['id' => Database::insert('categories', [
                    'parent_id' => $id, 'name' => $childName, 'slug' => slugify($childName), 'icon' => $childIcon,
                    'sort_order' => $j, 'is_active' => 1, 'created_at' => now(),
                ]), 'palette' => $key];
            }
        }
        return $ids;
    }

    private static function products(array $cats): array
    {
        // [ad, kategori, ikon, marka, fiyat, eski fiyat, stok, öne çıkan, kısa açıklama, özellikler]
        $products = [
            ['Novatek X12 Pro Akıllı Telefon 256 GB', 'Telefon & Tablet', 'smartphone', 'Novatek', 32999, 36999, 25, 1, '6,7" AMOLED ekran, 50 MP üçlü kamera ve tüm gün dayanan 5000 mAh batarya.', "Ekran: 6,7\" AMOLED 120 Hz\nDahili Hafıza: 256 GB\nRAM: 12 GB\nKamera: 50 MP + 12 MP + 10 MP\nBatarya: 5000 mAh\nRenk: Gece Mavisi\nGaranti: 2 Yıl"],
            ['Novatek A5 Lite Akıllı Telefon 128 GB', 'Telefon & Tablet', 'smartphone', 'Novatek', 11499, null, 40, 0, 'Fiyat/performans odaklı, 90 Hz ekranlı şık akıllı telefon.', "Ekran: 6,5\" LCD 90 Hz\nDahili Hafıza: 128 GB\nRAM: 6 GB\nBatarya: 5000 mAh\nRenk: Beyaz\nGaranti: 2 Yıl"],
            ['Novatek Tab S8 Tablet 11"', 'Telefon & Tablet', 'tablet', 'Novatek', 14999, 16499, 12, 0, '11 inç 2K ekran, dört hoparlör ve kalem desteği ile üretkenlik tableti.', "Ekran: 11\" 2K IPS\nDahili Hafıza: 128 GB\nRAM: 8 GB\nKalem Desteği: Var\nGaranti: 2 Yıl"],
            ['Zenith Book Air 14 Dizüstü Bilgisayar', 'Bilgisayar', 'laptop', 'Zenith', 42999, 47999, 8, 1, '1,2 kg ağırlık, 16 GB RAM ve 18 saate kadar pil ömrüyle ultra ince dizüstü.', "İşlemci: 8 Çekirdek 4,2 GHz\nRAM: 16 GB\nDepolama: 512 GB SSD\nEkran: 14\" 2.8K OLED\nAğırlık: 1,2 kg\nGaranti: 2 Yıl"],
            ['Zenith 27" QHD IPS Monitör', 'Bilgisayar', 'monitor', 'Zenith', 9999, null, 3, 0, '165 Hz yenileme hızı ve %99 sRGB renk doğruluğuna sahip QHD monitör.', "Ekran Boyutu: 27\"\nÇözünürlük: 2560 x 1440\nYenileme Hızı: 165 Hz\nPanel: IPS\nBağlantı: HDMI, DisplayPort, USB-C"],
            ['Zenith Mekanik Klavye RGB', 'Bilgisayar', 'keyboard', 'Zenith', 2499, 2999, 30, 0, 'Değiştirilebilir switch yapısı ve kişiselleştirilebilir RGB aydınlatma.', "Switch: Kırmızı (Lineer)\nAydınlatma: RGB\nBağlantı: USB-C / Bluetooth\nDüzen: Türkçe Q"],
            ['Sonik ANC Kablosuz Kulak Üstü Kulaklık', 'Ses & Kulaklık', 'headphones', 'Sonik', 4299, 5499, 50, 1, 'Aktif gürültü engelleme ve 40 saat pil ömrü ile kesintisiz müzik keyfi.', "Gürültü Engelleme: Aktif (ANC)\nPil Ömrü: 40 saat\nBluetooth: 5.3\nHızlı Şarj: 10 dk = 5 saat\nRenk: Mat Siyah"],
            ['Sonik Boom Taşınabilir Bluetooth Hoparlör', 'Ses & Kulaklık', 'speaker', 'Sonik', 1899, null, 0, 0, 'IP67 su geçirmezlik ve 360° ses ile her ortamda güçlü performans.', "Güç: 30 W\nSu Geçirmezlik: IP67\nPil Ömrü: 20 saat\nBluetooth: 5.2"],
            ['Pulse Fit 3 Akıllı Saat', 'Giyilebilir Teknoloji', 'watch', 'Pulse', 3799, 4299, 22, 1, 'Nabız, uyku ve SpO2 takibi, 100+ spor modu ve 14 gün pil ömrü.', "Ekran: 1,43\" AMOLED\nPil Ömrü: 14 gün\nSu Dayanıklılığı: 5 ATM\nSensörler: Nabız, SpO2, GPS"],
            ['Novatek Aksiyon Kamera 4K', 'Elektronik', 'camera', 'Novatek', 6499, 7299, 14, 0, '4K 60fps video, görüntü sabitleme ve su altı kılıfı ile maceralarınızı kaydedin.', "Video: 4K 60 fps\nFotoğraf: 20 MP\nSabitleme: Elektronik (EIS)\nSu Geçirmezlik: 10 m (kılıfsız)"],
            ['Urbanist Oversize Pamuklu Tişört', 'Giyim', 'shirt', 'Urbanist', 449, 599, 120, 0, '%100 organik pamuk, rahat oversize kalıp ve dayanıklı baskı.', "Materyal: %100 Organik Pamuk\nKalıp: Oversize\nYaka: Bisiklet Yaka\nBedenler: S, M, L, XL"],
            ['Urbanist Basic Kapüşonlu Sweatshirt', 'Giyim', 'shirt', 'Urbanist', 899, null, 60, 0, 'İçi şardonlu, yumuşak dokulu ve günlük kullanıma uygun sweatshirt.', "Materyal: %80 Pamuk, %20 Polyester\nKalıp: Regular\nBedenler: S, M, L, XL"],
            ['Atlas Runner Erkek Koşu Ayakkabısı', 'Ayakkabı', 'shoe', 'Atlas', 2799, 3499, 35, 1, 'Hafif taban, nefes alan file yüzey ve yüksek enerji geri dönüşü.', "Kullanım: Koşu / Antrenman\nTaban: EVA\nÜst Materyal: File\nNumaralar: 40-45"],
            ['Urbanist Classic Deri Sneaker', 'Ayakkabı', 'shoe', 'Urbanist', 1999, null, 4, 0, 'Hakiki deri, minimalist tasarım ve gün boyu konfor.', "Materyal: Hakiki Deri\nTaban: Kauçuk\nNumaralar: 36-44"],
            ['Urbanist Deri Omuz Çantası', 'Çanta & Aksesuar', 'bag', 'Urbanist', 1749, 2199, 18, 0, 'El işçiliği deri, ayarlanabilir askı ve iç bölmeli şık tasarım.', "Materyal: Hakiki Deri\nBoyut: 28 x 20 x 9 cm\nRenk: Taba"],
            ['Atlas Trail 30L Sırt Çantası', 'Çanta & Aksesuar', 'backpack', 'Atlas', 1299, null, 27, 0, 'Su itici kumaş, laptop bölmesi ve ergonomik sırt yapısı.', "Hacim: 30 L\nLaptop Bölmesi: 15,6\"\nMateryal: Su İtici Polyester"],
            ['Linea Polarize Güneş Gözlüğü', 'Çanta & Aksesuar', 'glasses', 'Linea', 1149, 1499, 40, 0, 'UV400 korumalı polarize camlar ve hafif asetat çerçeve.', "Cam: Polarize, UV400\nÇerçeve: Asetat\nUnisex: Evet"],
            ['Linea 925 Ayar Gümüş Kolye', 'Çanta & Aksesuar', 'gem', 'Linea', 2299, null, 9, 0, 'Zirkon taşlı, el yapımı 925 ayar gümüş kolye.', "Materyal: 925 Ayar Gümüş\nTaş: Zirkon\nZincir Uzunluğu: 45 cm"],
            ['Lumen Nordic Masa Lambası', 'Ev & Yaşam', 'lamp', 'Lumen', 899, 1199, 33, 1, 'İskandinav tasarım, kısılabilir sıcak ışık ve doğal ahşap gövde.', "Duy: E27\nMateryal: Ahşap & Metal\nYükseklik: 45 cm\nAmpul: Dahil Değil"],
            ['Lumen Seramik Kupa Seti 4\'lü', 'Ev & Yaşam', 'coffee', 'Lumen', 349, null, 80, 0, 'El yapımı görünümlü, bulaşık makinesinde yıkanabilir seramik kupa seti.', "Adet: 4\nHacim: 350 ml\nMateryal: Seramik"],
            ['Lumen Velvet 3\'lü Koltuk', 'Ev & Yaşam', 'sofa', 'Lumen', 18999, 22999, 5, 1, 'Kadife kumaş, yüksek yoğunluklu sünger ve masif ahşap ayaklar.', "Kumaş: Kadife\nGenişlik: 210 cm\nRenk: Zümrüt Yeşili\nKurulum: Ücretsiz"],
            ['Lumen Monstera Saksı Bitkisi', 'Ev & Yaşam', 'plant', 'Lumen', 459, null, 15, 0, 'Bakımı kolay, iç mekanı ferahlatan dekoratif saksılı bitki.', "Yükseklik: 60-70 cm\nSaksı: Seramik\nIşık İhtiyacı: Orta"],
            ['Atlas Ayarlanabilir Dambıl Seti 20 kg', 'Spor & Outdoor', 'dumbbell', 'Atlas', 2199, 2599, 20, 0, 'Saniyeler içinde ağırlık ayarı, kaymaz tutma yüzeyi.', "Toplam Ağırlık: 20 kg\nAyar Aralığı: 2,5 - 10 kg (tek)\nMateryal: Döküm"],
            ['Atlas Urban 28" Şehir Bisikleti', 'Spor & Outdoor', 'bike', 'Atlas', 12999, 14499, 6, 0, 'Alüminyum kadro, 7 vites ve şehir kullanımı için ideal geometri.', "Kadro: Alüminyum\nJant: 28\"\nVites: 7\nFren: V-Fren"],
            ['Atlas Kamp Çadırı 3 Kişilik', 'Spor & Outdoor', 'tent', 'Atlas', 3299, null, 11, 0, 'Kolay kurulum, su geçirmez dış tente ve geniş iç hacim.', "Kapasite: 3 Kişi\nSu Geçirmezlik: 3000 mm\nAğırlık: 3,4 kg"],
            ['Atlas Çelik Termos Matara 750 ml', 'Spor & Outdoor', 'bottle', 'Atlas', 399, 499, 90, 0, 'Çift cidarlı paslanmaz çelik; 24 saat soğuk, 12 saat sıcak.', "Hacim: 750 ml\nMateryal: Paslanmaz Çelik\nBPA: İçermez"],
            ['Pure Botanica Hyalüronik Asit Serum 30 ml', 'Kozmetik & Bakım', 'droplet', 'Pure Botanica', 389, 449, 70, 1, 'Yoğun nem desteği sağlayan, tüm cilt tiplerine uygun vegan serum.', "Hacim: 30 ml\nCilt Tipi: Tüm Cilt Tipleri\nVegan: Evet"],
            ['Pure Botanica Eau de Parfum 50 ml', 'Kozmetik & Bakım', 'sparkles', 'Pure Botanica', 1299, null, 25, 0, 'Bergamot, yasemin ve sandal ağacı notalarıyla kalıcı unisex parfüm.', "Hacim: 50 ml\nTür: Eau de Parfum\nNotalar: Bergamot, Yasemin, Sandal Ağacı"],
            ['PixelPlay Kablosuz Oyun Kumandası', 'Oyun & Hobi', 'gamepad', 'PixelPlay', 1599, 1899, 38, 0, 'Düşük gecikmeli bağlantı, titreşim desteği ve 30 saat pil.', "Bağlantı: 2.4 GHz / Bluetooth\nUyumluluk: PC, Android, Konsol\nPil Ömrü: 30 saat"],
        ];

        $ids = [];
        $time = time() - 86400 * 40;
        foreach ($products as $i => [$name, $cat, $icon, $brand, $price, $compare, $stock, $featured, $short, $specs]) {
            $slug = slugify($name);
            $created = date('Y-m-d H:i:s', $time + $i * 86400);
            $description = "$short\n\n$name, $brand kalitesiyle günlük hayatınızı kolaylaştırmak için tasarlandı. Özenle seçilmiş malzemeler ve titiz kalite kontrol süreçleri sayesinde uzun yıllar boyunca ilk günkü performansını korur.\n\n## Öne Çıkan Özellikler\n- Orijinal ve faturalı ürün\n- Hızlı ve güvenli kargo\n- 14 gün içinde ücretsiz iade\n- Resmi distribütör garantisi";
            $id = Database::insert('products', [
                'category_id'         => $cats[$cat]['id'],
                'name'                => $name,
                'slug'                => $slug,
                'sku'                 => strtoupper(substr(slugify($brand), 0, 3)) . '-' . str_pad((string) (1001 + $i), 5, '0', STR_PAD_LEFT),
                'barcode'             => '869' . str_pad((string) random_int(0, 999999999), 10, '0', STR_PAD_LEFT),
                'brand'               => $brand,
                'short_description'   => $short,
                'description'         => $description,
                'specs'               => $specs,
                'price'               => $price,
                'compare_price'       => $compare,
                'cost_price'          => round($price * 0.62, 2),
                'tax_rate'            => 20,
                'stock'               => $stock,
                'low_stock_threshold' => 5,
                'status'              => 'active',
                'is_featured'         => $featured,
                'views'               => random_int(40, 900),
                'sales_count'         => random_int(3, 160),
                'meta_title'          => null,
                'meta_description'    => $short,
                'created_at'          => $created,
                'updated_at'          => $created,
            ]);
            $palette = self::PALETTES[$cats[$cat]['palette']];
            for ($v = 0; $v < 3; $v++) {
                $file = "uploads/products/demo-$slug-$v.svg";
                file_put_contents(PUBLIC_PATH . '/' . $file, DemoImage::product($icon, $palette, $v));
                Database::insert('product_images', ['product_id' => $id, 'path' => $file, 'sort_order' => $v]);
            }
            $ids[] = ['id' => $id, 'name' => $name, 'price' => $price, 'sku' => null, 'image' => "uploads/products/demo-$slug-0.svg"];
        }
        return $ids;
    }

    private static function customers(): array
    {
        $people = [
            ['Demo Müşteri', 'demo@example.com', 'İstanbul', 'Kadıköy'],
            ['Ayşe Yılmaz', 'ayse@example.com', 'Ankara', 'Çankaya'],
            ['Mehmet Kaya', 'mehmet@example.com', 'İzmir', 'Karşıyaka'],
            ['Zeynep Demir', 'zeynep@example.com', 'Bursa', 'Nilüfer'],
            ['Can Öztürk', 'can@example.com', 'Antalya', 'Muratpaşa'],
            ['Elif Şahin', 'elif@example.com', 'Eskişehir', 'Tepebaşı'],
        ];
        $result = [];
        foreach ($people as $i => [$name, $email, $city, $district]) {
            $created = date('Y-m-d H:i:s', time() - 86400 * (35 - $i * 4));
            $id = Database::insert('users', [
                'name' => $name, 'email' => $email, 'phone' => '0532' . random_int(1000000, 9999999),
                'password' => password_hash('demo123', PASSWORD_DEFAULT), 'role' => 'customer', 'is_active' => 1,
                'created_at' => $created, 'updated_at' => $created,
            ]);
            $phone = '0532' . random_int(1000000, 9999999);
            Database::insert('addresses', [
                'user_id' => $id, 'title' => 'Ev', 'full_name' => $name, 'phone' => $phone, 'city' => $city,
                'district' => $district, 'address' => 'Örnek Mah. Çiçek Sok. No: ' . random_int(1, 90) . ' D: ' . random_int(1, 20),
                'postal_code' => (string) random_int(10000, 81999), 'is_default' => 1, 'created_at' => $created,
            ]);
            $result[] = ['id' => $id, 'name' => $name, 'email' => $email, 'phone' => $phone, 'city' => $city, 'district' => $district];
        }
        return $result;
    }

    private static function reviews(array $products, array $customers): void
    {
        $comments = [
            [5, 'Beklentimin üzerinde', 'Ürün tam açıklandığı gibi geldi, paketleme çok özenliydi. Kesinlikle tavsiye ederim.'],
            [5, 'Harika ürün', 'Fiyat/performans olarak piyasadaki en iyi seçeneklerden biri. Kargo da çok hızlıydı.'],
            [4, 'Gayet başarılı', 'Kalitesi güzel, sadece kargo bir gün gecikti. Onun dışında memnunum.'],
            [4, 'Memnun kaldım', 'Kullanımı rahat ve şık. Ailemde de herkes beğendi.'],
            [5, 'Mükemmel', 'İkinci kez sipariş veriyorum, yine sorunsuz. Teşekkürler!'],
            [3, 'İdare eder', 'Ürün güzel ama beklediğimden biraz farklı geldi. Yine de fiyatına göre iyi.'],
        ];
        foreach ($products as $i => $p) {
            $count = $i % 3 === 0 ? 3 : ($i % 2 === 0 ? 2 : 1);
            for ($k = 0; $k < $count; $k++) {
                $c = $comments[($i + $k) % count($comments)];
                $u = $customers[($i + $k) % count($customers)];
                Database::insert('reviews', [
                    'product_id' => $p['id'], 'user_id' => $u['id'], 'author_name' => $u['name'], 'rating' => $c[0],
                    'title' => $c[1], 'comment' => $c[2], 'is_approved' => ($i + $k) % 11 === 5 ? 0 : 1,
                    'created_at' => date('Y-m-d H:i:s', time() - 86400 * random_int(1, 25)),
                ]);
            }
        }
    }

    private static function coupons(): void
    {
        $rows = [
            ['HOSGELDIN10', 'percent', 10, 0, 0, null],
            ['INDIRIM150', 'fixed', 150, 1500, 500, null],
            ['YAZ25', 'percent', 25, 2500, 100, date('Y-m-d 23:59:59', strtotime('+60 days'))],
        ];
        foreach ($rows as [$code, $type, $value, $min, $max, $expires]) {
            Database::insert('coupons', [
                'code' => $code, 'type' => $type, 'value' => $value, 'min_total' => $min, 'max_uses' => $max,
                'used_count' => 0, 'starts_at' => null, 'expires_at' => $expires, 'is_active' => 1, 'created_at' => now(),
            ]);
        }
    }

    private static function sliders(): void
    {
        $dir = PUBLIC_PATH . '/uploads/sliders';
        if (!is_dir($dir)) {
            mkdir($dir, 0775, true);
        }
        $slides = [
            ['Yeni Sezon', 'Teknolojide yeni sezon fırsatları', 'Akıllı telefon, bilgisayar ve kulaklıklarda %30\'a varan indirimler sizi bekliyor.', 'Fırsatları Keşfet', 'kategori/elektronik', 'headphones', ['#ff8a5b', '#ef5a24', '#ffffff'], '#14161f', 'dark'],
            ['Moda Haftası', 'Stilini yansıtan parçalar', 'Sezonun trend tişört, sneaker ve aksesuarları ücretsiz kargo avantajıyla.', 'Koleksiyonu İncele', 'kategori/moda', 'shoe', ['#fde68a', '#f59e0b', '#7c2d12'], '#fff4e8', 'light'],
            ['Ev & Yaşam', 'Evinize yeni bir dokunuş', 'Aydınlatmadan dekorasyona, yaşam alanınızı yenileyecek seçkin ürünler.', 'Hemen Alışverişe Başla', 'kategori/ev-yasam', 'lamp', ['#86efac', '#16a34a', '#ffffff'], '#0f2a1d', 'dark'],
        ];
        foreach ($slides as $i => [$eyebrow, $title, $sub, $btn, $link, $icon, $pal, $bg, $theme]) {
            $file = "uploads/sliders/demo-slide-$i.svg";
            file_put_contents(PUBLIC_PATH . '/' . $file, DemoImage::slide($icon, $pal));
            Database::insert('sliders', [
                'eyebrow' => $eyebrow, 'title' => $title, 'subtitle' => $sub, 'button_text' => $btn, 'button_link' => $link,
                'image' => $file, 'theme' => $theme, 'bg_color' => $bg, 'sort_order' => $i, 'is_active' => 1, 'created_at' => now(),
            ]);
        }
    }

    private static function orders(array $products, array $customers): void
    {
        $statuses = ['delivered', 'delivered', 'delivered', 'shipped', 'shipped', 'processing', 'pending', 'pending', 'cancelled', 'delivered'];
        $methods = ['card', 'card', 'card', 'transfer', 'cod'];
        $cargo = ['Yurtiçi Kargo', 'Aras Kargo', 'MNG Kargo'];

        for ($n = 0; $n < 32; $n++) {
            $daysAgo = (int) floor($n * 0.9);
            $created = date('Y-m-d H:i:s', time() - 86400 * $daysAgo - random_int(600, 40000));
            $status = $daysAgo <= 1 ? ['pending', 'processing'][$n % 2] : $statuses[$n % count($statuses)];
            $method = $methods[$n % count($methods)];
            $customer = $customers[$n % count($customers)];

            $lines = [];
            $picked = array_rand($products, random_int(1, 3));
            foreach ((array) $picked as $idx) {
                $p = $products[$idx];
                $qty = $p['price'] > 5000 ? 1 : random_int(1, 2);
                $lines[] = ['p' => $p, 'qty' => $qty, 'total' => $p['price'] * $qty];
            }
            $subtotal = array_sum(array_column($lines, 'total'));
            $shipping = $subtotal >= 750 ? 0 : 49.90;
            $fee = $method === 'cod' ? 29.90 : 0;
            $total = $subtotal + $shipping + $fee;

            $paymentStatus = match (true) {
                $status === 'cancelled' => $method === 'card' ? 'refunded' : 'pending',
                $method === 'card' => 'paid',
                $method === 'transfer' => $status === 'pending' ? 'pending' : 'paid',
                default => $status === 'delivered' ? 'paid' : 'pending',
            };

            $orderId = Database::insert('orders', [
                'order_no' => 'SP' . date('ymd', strtotime($created)) . random_int(10000, 99999),
                'user_id' => $customer['id'], 'status' => $status, 'payment_method' => $method,
                'payment_status' => $paymentStatus, 'payment_ref' => $method === 'card' ? 'DEMO-' . strtoupper(bin2hex(random_bytes(5))) : null,
                'subtotal' => $subtotal, 'discount' => 0, 'shipping_fee' => $shipping, 'payment_fee' => $fee,
                'tax_total' => round($subtotal - $subtotal / 1.2, 2), 'total' => $total, 'coupon_code' => null,
                'customer_name' => $customer['name'], 'customer_email' => $customer['email'], 'customer_phone' => $customer['phone'],
                'ship_city' => $customer['city'], 'ship_district' => $customer['district'],
                'ship_address' => 'Örnek Mah. Çiçek Sok. No: ' . random_int(1, 90), 'ship_postal' => null,
                'invoice_type' => 'individual', 'note' => null,
                'cargo_company' => in_array($status, ['shipped', 'delivered'], true) ? $cargo[$n % 3] : null,
                'tracking_no' => in_array($status, ['shipped', 'delivered'], true) ? (string) random_int(100000000000, 999999999999) : null,
                'ip_address' => '127.0.0.1', 'created_at' => $created, 'updated_at' => $created,
            ]);
            foreach ($lines as $l) {
                Database::insert('order_items', [
                    'order_id' => $orderId, 'product_id' => $l['p']['id'], 'product_name' => $l['p']['name'], 'sku' => null,
                    'image' => $l['p']['image'], 'price' => $l['p']['price'], 'quantity' => $l['qty'], 'total' => $l['total'],
                ]);
            }

            $flow = ['pending'];
            if ($status === 'cancelled') {
                $flow[] = 'cancelled';
            } else {
                foreach (['processing', 'shipped', 'delivered'] as $s) {
                    if (array_search($s, Order::TIMELINE, true) <= array_search($status, Order::TIMELINE, true)) {
                        $flow[] = $s;
                    }
                }
            }
            foreach ($flow as $k => $s) {
                Database::insert('order_history', [
                    'order_id' => $orderId, 'status' => $s, 'note' => $k === 0 ? 'Sipariş oluşturuldu.' : null,
                    'created_by' => null, 'created_at' => date('Y-m-d H:i:s', strtotime($created) + $k * 3600 * 20),
                ]);
            }
        }
    }
}
