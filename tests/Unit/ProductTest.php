<?php

namespace Tests\Unit;

use App\Models\Product;
use PHPUnit\Framework\TestCase;

class ProductTest extends TestCase
{
    public function test_low_stock_detection(): void
    {
        $product = new Product(['stock' => 4, 'min_stock' => 5, 'is_active' => true]);

        $this->assertTrue($product->isLowStock());
        $this->assertSame('Stok Menipis', $product->statusBadge()[0]);
    }

    public function test_out_of_stock_badge(): void
    {
        $product = new Product(['stock' => 0, 'min_stock' => 5, 'is_active' => true]);

        $this->assertSame('Habis', $product->statusBadge()[0]);
    }
}
