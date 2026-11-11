<?php

namespace Database\Seeders;

use App\Models\Category;
use App\Models\Customer;
use App\Models\Product;
use App\Models\Sale;
use App\Models\Supplier;
use App\Models\User;
use App\Services\SaleService;
use App\Services\StockService;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    public function run(SaleService $sales, StockService $stock): void
    {
        mt_srand(42);

        // --- Pengguna ---
        $admin = User::create([
            'name' => 'Administrator', 'email' => 'admin@retailflow.test',
            'password' => 'password', 'role' => User::ROLE_ADMIN,
        ]);
        User::create([
            'name' => 'Budi Manajer', 'email' => 'manager@retailflow.test',
            'password' => 'password', 'role' => User::ROLE_MANAGER,
        ]);
        $cashier = User::create([
            'name' => 'Sari Kasir', 'email' => 'kasir@retailflow.test',
            'password' => 'password', 'role' => User::ROLE_CASHIER,
        ]);

        // --- Supplier ---
        foreach ([
            ['PT Sumber Makmur', '031-5551001', 'Jl. Rungkut Industri 10, Surabaya'],
            ['CV Berkah Jaya', '031-5551002', 'Jl. Margomulyo 22, Surabaya'],
            ['UD Maju Terus', '0812-3456-7001', 'Jl. Ahmad Yani 88, Sidoarjo'],
        ] as [$name, $phone, $address]) {
            Supplier::create(compact('name', 'phone', 'address'));
        }

        // --- Pelanggan ---
        foreach ([
            ['Andi Wijaya', '0812-1111-0001', 'Surabaya'],
            ['Dewi Lestari', '0812-1111-0002', 'Sidoarjo'],
            ['Rudi Hartono', '0812-1111-0003', 'Gresik'],
            ['Siti Aminah', '0812-1111-0004', 'Surabaya'],
            ['Toko Sinar Baru', '0812-1111-0005', 'Mojokerto'],
        ] as [$name, $phone, $address]) {
            Customer::create(compact('name', 'phone', 'address'));
        }

        // --- Kategori & Produk: [nama, merek, harga beli, harga jual] ---
        $catalog = [
            'Makanan' => [
                ['Mie Instan Goreng', 'Indomie', 2800, 3500],
                ['Biskuit Cokelat 120g', 'Roma', 7500, 9500],
                ['Keripik Singkong 100g', 'Maicih', 9000, 12000],
                ['Roti Tawar Gandum', 'Sari Roti', 13000, 16500],
                ['Beras Premium 5kg', 'Rojolele', 62000, 72000],
            ],
            'Minuman' => [
                ['Air Mineral 600ml', 'Aqua', 2500, 3500],
                ['Teh Botol 450ml', 'Sosro', 4000, 5500],
                ['Kopi Susu Kaleng', 'Good Day', 5500, 7500],
                ['Susu UHT 1L', 'Ultra', 15500, 18500],
                ['Jus Jeruk 330ml', 'Frisian', 6000, 8000],
            ],
            'Kebutuhan Rumah' => [
                ['Sabun Cuci Piring 800ml', 'Sunlight', 13000, 16000],
                ['Deterjen Bubuk 1kg', 'Rinso', 19000, 23500],
                ['Pewangi Pakaian 900ml', 'Molto', 14500, 18000],
                ['Tisu Gulung 4 Roll', 'Paseo', 11000, 14000],
                ['Pembersih Lantai 800ml', 'Wipol', 12000, 15500],
            ],
            'Perawatan Diri' => [
                ['Sabun Mandi Cair 450ml', 'Lifebuoy', 17000, 21000],
                ['Sampo 170ml', 'Pantene', 20000, 25000],
                ['Pasta Gigi 190g', 'Pepsodent', 9500, 12500],
                ['Sikat Gigi Soft', 'Formula', 6000, 8500],
                ['Deodoran Roll On', 'Rexona', 15000, 19500],
            ],
            'Alat Tulis' => [
                ['Buku Tulis 38 Lembar', 'Sidu', 3000, 4500],
                ['Pulpen Gel Hitam', 'Pilot', 5500, 8000],
                ['Pensil 2B', 'Faber-Castell', 3500, 5000],
                ['Penghapus Putih', 'Staedtler', 2500, 4000],
                ['Stapler Kecil', 'Kenko', 12000, 16000],
            ],
            'Aksesori Elektronik' => [
                ['Kabel USB-C 1m', 'Robot', 25000, 39000],
                ['Earphone Basic', 'JBL', 35000, 55000],
                ['Baterai AA 4pcs', 'ABC', 14000, 18500],
                ['Charger 20W', 'Anker', 85000, 119000],
                ['Powerbank 10000mAh', 'Xiaomi', 160000, 199000],
            ],
        ];

        $n = 0;
        foreach ($catalog as $categoryName => $items) {
            $category = Category::create([
                'name' => $categoryName,
                'description' => "Produk kategori {$categoryName}",
            ]);

            foreach ($items as [$name, $brand, $buy, $sell]) {
                $n++;
                $product = Product::create([
                    'category_id' => $category->id,
                    'sku' => 'SKU-'.str_pad((string) $n, 4, '0', STR_PAD_LEFT),
                    'barcode' => '899'.str_pad((string) (1000000 + $n * 37), 10, '0', STR_PAD_LEFT),
                    'name' => $name,
                    'brand' => $brand,
                    'purchase_price' => $buy,
                    'selling_price' => $sell,
                    'stock' => 0,
                    'min_stock' => 8,
                    'is_active' => true,
                ]);
                $stock->increase($product, mt_rand(40, 120), $admin->id, 'Stok awal');
            }
        }

        // Dua produk dibuat hampir habis agar peringatan stok tampil di dashboard.
        $stock->adjust(Product::where('sku', 'SKU-0004')->first(), 3, $admin->id, 'Penyesuaian stok (demo)');
        $stock->adjust(Product::where('sku', 'SKU-0026')->first(), 6, $admin->id, 'Penyesuaian stok (demo)');

        // --- Transaksi contoh 14 hari terakhir ---
        $productIds = Product::where('stock', '>', 20)->pluck('id')->all();
        $customerIds = Customer::pluck('id')->all();
        $methods = ['cash', 'cash', 'cash', 'qris', 'debit', 'transfer'];

        for ($day = 13; $day >= 0; $day--) {
            $count = mt_rand(3, 7);
            for ($i = 0; $i < $count; $i++) {
                $picked = array_rand(array_flip($productIds), mt_rand(1, 4));
                $picked = (array) $picked;
                $items = array_map(fn ($id) => ['product_id' => $id, 'quantity' => mt_rand(1, 3)], $picked);

                $subtotal = 0;
                foreach ($items as $item) {
                    $subtotal += Product::find($item['product_id'])->selling_price * $item['quantity'];
                }
                $method = $methods[array_rand($methods)];

                $sale = $sales->create([
                    'items' => $items,
                    'customer_id' => mt_rand(0, 2) === 0 ? $customerIds[array_rand($customerIds)] : null,
                    'discount' => mt_rand(0, 5) === 0 ? 1000 : 0,
                    'payment_method' => $method,
                    'paid' => ceil($subtotal / 10000) * 10000,
                ], $cashier->id);

                $when = now()->subDays($day)->setTime(mt_rand(8, 20), mt_rand(0, 59));
                $sale->forceFill(['created_at' => $when, 'updated_at' => $when])->save();
            }
        }
    }
}
