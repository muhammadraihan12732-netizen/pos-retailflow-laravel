<?php

namespace Tests\Feature;

use App\Models\Product;
use App\Models\Sale;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SaleTest extends TestCase
{
    use RefreshDatabase;

    public function test_guest_is_redirected_to_login(): void
    {
        $this->get('/pos')->assertRedirect('/login');
    }

    public function test_cashier_can_create_sale_and_stock_is_reduced(): void
    {
        $cashier = User::factory()->create();
        $product = Product::factory()->create(['stock' => 10, 'selling_price' => 5000]);

        $response = $this->actingAs($cashier)->post('/sales', [
            'items' => [['product_id' => $product->id, 'quantity' => 3]],
            'payment_method' => 'cash',
            'discount' => 500,
            'paid' => 20000,
        ]);

        $sale = Sale::first();
        $response->assertRedirect(route('sales.show', $sale));

        $this->assertEquals(14500, $sale->total);
        $this->assertEquals(5500, $sale->change);
        $this->assertSame(7, $product->fresh()->stock);
        $this->assertDatabaseHas('stock_movements', [
            'product_id' => $product->id, 'type' => 'out', 'quantity' => 3,
        ]);
    }

    public function test_sale_is_rejected_when_stock_is_not_enough(): void
    {
        $cashier = User::factory()->create();
        $product = Product::factory()->create(['stock' => 2, 'selling_price' => 5000]);

        $this->actingAs($cashier)->post('/sales', [
            'items' => [['product_id' => $product->id, 'quantity' => 5]],
            'payment_method' => 'cash',
            'paid' => 50000,
        ])->assertSessionHasErrors('items');

        $this->assertSame(0, Sale::count());
        $this->assertSame(2, $product->fresh()->stock);
    }

    public function test_cashier_cannot_open_product_management(): void
    {
        $this->actingAs(User::factory()->create())->get('/products')->assertForbidden();
    }
}
