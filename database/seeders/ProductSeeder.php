<?php

namespace Database\Seeders;

use App\Models\Product;
use Illuminate\Database\Seeder;

class ProductSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $products = [
            // Laptops (15 items)
            ['code' => 'PROD-001', 'name' => 'MacBook Pro 14" M3 Pro 18GB 512GB', 'description' => 'Laptop profesional dengan chip M3 Pro, layar Liquid Retina XDR 14 inch, RAM 18GB, SSD 512GB', 'price' => 32999000],
            ['code' => 'PROD-002', 'name' => 'ASUS ROG Zephyrus G14 Ryzen 9 RTX 4060', 'description' => 'Gaming laptop dengan AMD Ryzen 9 7940HS, NVIDIA RTX 4060, RAM 16GB, SSD 1TB, layar 14" QHD+ 165Hz', 'price' => 24999000],
            ['code' => 'PROD-003', 'name' => 'Dell XPS 15 Intel Core i7 RTX 4050', 'description' => 'Laptop premium dengan Intel Core i7-13700H, NVIDIA RTX 4050, RAM 16GB, SSD 512GB, layar 15.6" FHD+', 'price' => 27500000],
            ['code' => 'PROD-004', 'name' => 'Lenovo ThinkPad X1 Carbon Gen 11', 'description' => 'Business ultrabook dengan Intel Core i7-1365U, RAM 16GB, SSD 512GB, layar 14" WUXGA', 'price' => 26000000],
            ['code' => 'PROD-005', 'name' => 'HP Pavilion Gaming Ryzen 5 RTX 3050', 'description' => 'Gaming laptop affordable dengan AMD Ryzen 5 5600H, NVIDIA RTX 3050, RAM 8GB, SSD 512GB', 'price' => 11999000],
            ['code' => 'PROD-006', 'name' => 'ASUS VivoBook 14 Intel Core i5', 'description' => 'Laptop everyday dengan Intel Core i5-1235U, RAM 8GB, SSD 512GB, layar 14" FHD', 'price' => 8499000],
            ['code' => 'PROD-007', 'name' => 'Acer Swift 3 AMD Ryzen 7', 'description' => 'Thin and light laptop dengan AMD Ryzen 7 5700U, RAM 16GB, SSD 512GB, layar 14" FHD IPS', 'price' => 10999000],
            ['code' => 'PROD-008', 'name' => 'MSI Katana 15 Intel Core i7 RTX 4070', 'description' => 'Gaming powerhouse dengan Intel Core i7-13620H, RTX 4070, RAM 16GB, SSD 1TB, layar 15.6" FHD 144Hz', 'price' => 22999000],
            ['code' => 'PROD-009', 'name' => 'Microsoft Surface Laptop 5', 'description' => 'Premium ultrabook dengan Intel Core i7-1255U, RAM 16GB, SSD 512GB, layar PixelSense 13.5"', 'price' => 21999000],
            ['code' => 'PROD-010', 'name' => 'Lenovo IdeaPad Slim 3 Ryzen 5', 'description' => 'Budget laptop dengan AMD Ryzen 5 5500U, RAM 8GB, SSD 256GB, layar 14" FHD', 'price' => 6499000],
            ['code' => 'PROD-011', 'name' => 'ASUS ZenBook 14 OLED Intel Core i7', 'description' => 'Premium ultrabook dengan Intel Core i7-1260P, RAM 16GB, SSD 512GB, layar 14" OLED 2.8K', 'price' => 18999000],
            ['code' => 'PROD-012', 'name' => 'HP Envy x360 Ryzen 7 Touchscreen', 'description' => 'Convertible laptop dengan AMD Ryzen 7 5825U, RAM 16GB, SSD 512GB, layar 15.6" FHD touchscreen', 'price' => 15999000],
            ['code' => 'PROD-013', 'name' => 'Acer Predator Helios 300 RTX 4060', 'description' => 'Gaming laptop dengan Intel Core i7-13700HX, RTX 4060, RAM 16GB, SSD 512GB, layar 15.6" FHD 165Hz', 'price' => 23999000],
            ['code' => 'PROD-014', 'name' => 'Dell Inspiron 15 Intel Core i5', 'description' => 'All-purpose laptop dengan Intel Core i5-1235U, RAM 8GB, SSD 512GB, layar 15.6" FHD', 'price' => 9299000],
            ['code' => 'PROD-015', 'name' => 'MacBook Air 13" M2 8GB 256GB', 'description' => 'Ultraportable laptop dengan chip M2, RAM 8GB, SSD 256GB, layar Liquid Retina 13.6 inch', 'price' => 18999000],

            // Handphones (12 items)
            ['code' => 'PROD-016', 'name' => 'iPhone 15 Pro Max 256GB', 'description' => 'Flagship smartphone dengan chip A17 Pro, kamera 48MP, layar Super Retina XDR 6.7", Titanium design', 'price' => 21999000],
            ['code' => 'PROD-017', 'name' => 'Samsung Galaxy S24 Ultra 12/256GB', 'description' => 'Premium Android dengan Snapdragon 8 Gen 3, kamera 200MP, layar Dynamic AMOLED 2X 6.8", S Pen', 'price' => 19999000],
            ['code' => 'PROD-018', 'name' => 'iPhone 14 128GB', 'description' => 'Smartphone dengan chip A15 Bionic, kamera ganda 12MP, layar Super Retina XDR 6.1"', 'price' => 13999000],
            ['code' => 'PROD-019', 'name' => 'Samsung Galaxy A54 8/256GB', 'description' => 'Mid-range smartphone dengan Exynos 1380, kamera 50MP OIS, layar Super AMOLED 6.4" 120Hz', 'price' => 5499000],
            ['code' => 'PROD-020', 'name' => 'Xiaomi 13T Pro 12/256GB', 'description' => 'Flagship killer dengan Dimensity 9200+, kamera Leica 50MP, layar AMOLED 6.67" 144Hz, fast charging 120W', 'price' => 7999000],
            ['code' => 'PROD-021', 'name' => 'OPPO Reno 11 5G 12/256GB', 'description' => 'Premium mid-range dengan Dimensity 7050, kamera 50MP, layar AMOLED 6.7" 120Hz', 'price' => 5299000],
            ['code' => 'PROD-022', 'name' => 'Vivo V30 Pro 12/256GB', 'description' => 'Photography focused phone dengan Dimensity 8200, kamera Zeiss 50MP, layar AMOLED 6.78" 120Hz', 'price' => 6999000],
            ['code' => 'PROD-023', 'name' => 'Google Pixel 8 Pro 12/256GB', 'description' => 'AI-powered smartphone dengan Google Tensor G3, kamera 50MP, layar LTPO OLED 6.7" 120Hz', 'price' => 16999000],
            ['code' => 'PROD-024', 'name' => 'OnePlus 12 16/512GB', 'description' => 'Flagship dengan Snapdragon 8 Gen 3, kamera Hasselblad 50MP, layar AMOLED 6.82" 120Hz, fast charging 100W', 'price' => 14999000],
            ['code' => 'PROD-025', 'name' => 'Realme 11 Pro+ 5G 12/256GB', 'description' => 'Curved display phone dengan Dimensity 7050, kamera 200MP, layar AMOLED 6.7" 120Hz', 'price' => 4999000],
            ['code' => 'PROD-026', 'name' => 'Samsung Galaxy Z Flip 5 8/256GB', 'description' => 'Foldable smartphone dengan Snapdragon 8 Gen 2, layar Flex Window 3.4", kamera 12MP', 'price' => 15999000],
            ['code' => 'PROD-027', 'name' => 'Xiaomi Redmi Note 13 Pro 8/256GB', 'description' => 'Budget friendly dengan Helio G99 Ultra, kamera 200MP, layar AMOLED 6.67" 120Hz', 'price' => 3799000],

            // Cameras (8 items)
            ['code' => 'PROD-028', 'name' => 'Sony Alpha A7 IV Body', 'description' => 'Mirrorless full-frame 33MP, video 4K 60fps, IBIS 5-axis, dual card slot', 'price' => 34999000],
            ['code' => 'PROD-029', 'name' => 'Canon EOS R6 Mark II Body', 'description' => 'Mirrorless full-frame 24MP, video 4K 60fps, IBIS, autofocus Dual Pixel CMOS AF II', 'price' => 38999000],
            ['code' => 'PROD-030', 'name' => 'Fujifilm X-T5 Body', 'description' => 'Mirrorless APS-C 40MP, video 6.2K, IBIS 7-stop, classic retro design', 'price' => 27999000],
            ['code' => 'PROD-031', 'name' => 'Nikon Z6 III Body', 'description' => 'Mirrorless full-frame 24MP, video 4K 120fps, IBIS, partially stacked sensor', 'price' => 36999000],
            ['code' => 'PROD-032', 'name' => 'Canon EOS 90D Kit 18-135mm', 'description' => 'DSLR APS-C 32MP, video 4K 30fps, continuous shooting 10fps, weather-sealed', 'price' => 19999000],
            ['code' => 'PROD-033', 'name' => 'Sony ZV-E10 Kit 16-50mm', 'description' => 'Mirrorless vlogging camera APS-C 24MP, video 4K, product showcase mode, flip screen', 'price' => 13499000],
            ['code' => 'PROD-034', 'name' => 'GoPro Hero 12 Black', 'description' => 'Action camera 5.3K 60fps, HyperSmooth 6.0, waterproof 10m, HDR video', 'price' => 6499000],
            ['code' => 'PROD-035', 'name' => 'DJI Osmo Action 4', 'description' => 'Action camera 4K 120fps, magnetic mounting, waterproof 18m, color temperature sensor', 'price' => 5999000],

            // Accessories & Electronics (10 items)
            ['code' => 'PROD-036', 'name' => 'Logitech MX Master 3S Wireless Mouse', 'description' => 'Premium wireless mouse, 8K DPI sensor, quiet clicks, USB-C charging, multi-device', 'price' => 1599000],
            ['code' => 'PROD-037', 'name' => 'Keychron K8 Pro Mechanical Keyboard', 'description' => 'Wireless mechanical keyboard, hot-swappable switches, RGB backlight, QMK/VIA support', 'price' => 1899000],
            ['code' => 'PROD-038', 'name' => 'Sony WH-1000XM5 Headphones', 'description' => 'Premium noise-cancelling headphones, 30hrs battery, multipoint connection, LDAC codec', 'price' => 5499000],
            ['code' => 'PROD-039', 'name' => 'LG UltraGear 27" QHD 165Hz Monitor', 'description' => '27" IPS monitor 2560x1440, 165Hz, 1ms response time, G-Sync compatible, HDR10', 'price' => 4299000],
            ['code' => 'PROD-040', 'name' => 'Samsung T7 Portable SSD 1TB', 'description' => 'External SSD USB 3.2 Gen2, read 1050MB/s, write 1000MB/s, compact and durable', 'price' => 1799000],
            ['code' => 'PROD-041', 'name' => 'Anker PowerCore 20000mAh Power Bank', 'description' => 'High capacity power bank, dual USB output, PowerIQ technology, surge protection', 'price' => 549000],
            ['code' => 'PROD-042', 'name' => 'TP-Link Archer AX55 WiFi 6 Router', 'description' => 'WiFi 6 router AX3000, 4 Gigabit LAN ports, OFDMA, MU-MIMO, parental controls', 'price' => 1299000],
            ['code' => 'PROD-043', 'name' => 'Blue Yeti USB Microphone', 'description' => 'Professional USB microphone, 4 pickup patterns, mute button, gain control, plug & play', 'price' => 1999000],
            ['code' => 'PROD-044', 'name' => 'Logitech C920 HD Pro Webcam', 'description' => 'Full HD 1080p webcam, 78° field of view, stereo audio, autofocus, light correction', 'price' => 1299000],
            ['code' => 'PROD-045', 'name' => 'iPad Air 5th Gen WiFi 64GB', 'description' => 'Tablet dengan chip M1, layar Liquid Retina 10.9", support Apple Pencil 2, Touch ID', 'price' => 8999000],
        ];

        foreach ($products as $product) {
            Product::create([
                'code' => $product['code'],
                'name' => $product['name'],
                'description' => $product['description'],
                'price' => $product['price'],
                'image' => null,
                'is_active' => true,
            ]);
        }
    }
}
