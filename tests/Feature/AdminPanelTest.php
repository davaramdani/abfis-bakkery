<?php

namespace Tests\Feature;

use App\Models\Order;
use App\Models\Product;
use App\Models\User;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class AdminPanelTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        $this->withoutVite();
        config(['database.default' => 'sqlite', 'database.connections.sqlite.database' => ':memory:', 'session.driver' => 'array', 'cache.default' => 'array']);
        DB::purge('sqlite');
        Schema::create('users', function (Blueprint $table) {
            $table->increments('id_user');
            $table->string('name');
            $table->string('email')->unique();
            $table->string('password');
            $table->boolean('is_admin')->default(false);
            $table->string('role')->default('user');
            $table->rememberToken();
            $table->timestamps();
        });
        Schema::create('products', function (Blueprint $table) {
            $table->increments('id_product');
            $table->string('name');
            $table->text('description')->nullable();
            $table->integer('price');
            $table->integer('stock');
            $table->string('image');
            $table->timestamps();
        });
        Schema::create('orders', function (Blueprint $table) {
            $table->increments('id_order');
            $table->unsignedInteger('id_user');
            $table->integer('total_price');
            $table->string('status')->default('pending');
            $table->string('customer_name');
            $table->string('phone');
            $table->text('address');
            $table->string('payment_method');
            $table->string('payment_status')->default('pending');
            $table->string('payment_proof')->nullable();
            $table->date('order_date')->nullable();
            $table->timestamps();
        });
        Schema::create('order_product', function (Blueprint $table) {
            $table->increments('id_order_product');
            $table->unsignedInteger('order_id');
            $table->unsignedInteger('product_id');
            $table->integer('quantity');
            $table->integer('price_at_order');
            $table->timestamps();
        });
    }

    public function test_admin_login_is_public_and_guests_are_redirected_to_it(): void
    {
        $this->get(route('admin.login'))->assertOk()->assertSee('di balik dapur.')->assertSee(route('admin.login.submit'), false)->assertDontSee('bootstrap.min');
        $this->get(route('admin.dashboard'))->assertRedirect(route('admin.login'));
    }

    public function test_customer_credentials_cannot_sign_into_the_admin_guard(): void
    {
        $customer = $this->customer();
        $this->post(route('admin.login.submit'), ['email' => $customer->email, 'password' => 'test-password'])->assertSessionHasErrors('email');
        $this->assertGuest('admin');
    }

    public function test_dashboard_and_activities_render_real_summaries_in_date_order(): void
    {
        $this->asAdmin();
        $customer = $this->customer();
        $product = $this->product(['stock' => 3]);
        $order = $this->order($customer, $product, 2);
        $order->update(['payment_status' => 'approved']);
        $this->get(route('admin.dashboard'))->assertOk()->assertSeeText('Kabar dari dapur.')->assertSeeText('Rp 25.000')->assertViewHas('totalOrders', 1)->assertViewHas('revenue', 25000)->assertViewHas('lowStockProducts', fn ($rows) => $rows->first()->is($product));
        $this->get(route('admin.activities'))->assertOk()->assertSeeText('Yang baru di Abfis.')->assertViewHas('activities', fn ($rows) => $rows->count() === 3 && $rows->pluck('created_at')->values()->all() === $rows->sortByDesc('created_at')->pluck('created_at')->values()->all());
    }

    public function test_product_filters_and_both_product_forms_are_usable(): void
    {
        $this->asAdmin();
        $low = $this->product(['name' => 'Roti Keju', 'stock' => 2]);
        $this->product(['name' => 'Roti Cokelat', 'stock' => 12]);
        $this->product(['name' => 'Roti Habis', 'stock' => 0]);
        $this->get(route('admin.products.index', ['search' => 'Roti', 'stock' => 'low']))->assertOk()->assertSeeText('Roti Keju')->assertSeeText('Roti Habis')->assertDontSeeText('Roti Cokelat');
        $this->get(route('admin.products.create'))->assertOk()->assertSee('multipart/form-data', false)->assertSee('name="stock"', false);
        $this->get(route('admin.products.edit', $low))->assertOk()->assertSee('value="2"', false)->assertSee('name="_method" value="PUT"', false);
    }

    public function test_order_list_filters_detail_and_edit_preserve_customer_and_product_fields(): void
    {
        $this->asAdmin();
        $customer = $this->customer();
        $product = $this->product();
        $order = $this->order($customer, $product, 2);
        $order->update(['payment_proof' => 'receipt.pdf']);
        $this->get(route('admin.orders.index', ['search' => 'Pelanggan', 'status' => 'pending']))->assertOk()->assertViewHas('orders', fn ($rows) => $rows->total() === 1);
        $this->get(route('admin.orders.show', $order))->assertOk()->assertSee('receipt.pdf')->assertSeeText('Rp 25.000')->assertSee(route('admin.orders.updateStatus', $order), false)->assertSee('name="_method" value="PATCH"', false);
        $this->get(route('admin.orders.create'))->assertOk()->assertSee('name="id_user"', false)->assertSee('name="products[0][id]"', false)->assertSee('value="pending"', false)->assertDontSee('value="tertunda"', false);
        $this->get(route('admin.orders.edit', $order))->assertOk()->assertSee('name="id_user"', false)->assertSee('name="quantities['.$product->id_product.']"', false)->assertSee('type="date"', false)->assertSee('value="2026-10-21"', false);
    }

    public function test_product_pagination_keeps_filters_and_uses_the_editorial_navigation(): void
    {
        $this->asAdmin();
        for ($index = 1; $index <= 13; $index++) {
            $this->product(['name' => 'Roti '.$index, 'stock' => 2]);
        }
        $this->get(route('admin.products.index', ['search' => 'Roti', 'stock' => 'low']))
            ->assertOk()->assertSee('Navigasi halaman')->assertSee('search=Roti', false)
            ->assertSee('stock=low', false)->assertSee('rel="next"', false);
        $this->get(route('admin.products.index', ['search' => 'Roti', 'stock' => 'low', 'page' => 2]))
            ->assertOk()->assertViewHas('products', fn ($rows) => $rows->count() === 3 && $rows->currentPage() === 2);
    }

    public function test_new_admin_order_saves_the_calculated_total_and_reduces_stock(): void
    {
        $this->asAdmin();
        $customer = $this->customer();
        $product = $this->product();
        $this->post(route('admin.orders.store'), $this->orderInput($customer) + ['products' => [['id' => $product->id_product, 'quantity' => 3]]])->assertRedirect(route('admin.orders.index'))->assertSessionHasNoErrors();
        $order = Order::firstOrFail();
        $this->assertSame(37500, $order->total_price);
        $this->assertSame(7, $product->fresh()->stock);
        $this->assertSame(3, $order->products->first()->pivot->quantity);
    }

    public function test_product_creation_requires_a_photo_and_normalizes_the_optional_description(): void
    {
        $this->asAdmin();
        Storage::fake('public');
        $input = ['name' => 'Roti Baru', 'price' => 15000, 'stock' => 8, 'description' => null];
        $this->post(route('admin.products.store'), $input)->assertSessionHasErrors('image');
        $this->post(route('admin.products.store'), $input + ['image' => UploadedFile::fake()->image('roti.png')])
            ->assertRedirect(route('admin.products.index'))->assertSessionHasNoErrors();
        $product = Product::firstOrFail();
        $this->assertSame('', $product->description);
        $this->assertSame(15000, $product->price);
        Storage::disk('public')->assertExists('products/'.$product->image);
    }

    public function test_failed_order_edit_rolls_back_earlier_quantity_and_customer_changes(): void
    {
        $this->asAdmin();
        $customer = $this->customer();
        $first = $this->product(['stock' => 8]);
        $second = $this->product(['name' => 'Bolu', 'stock' => 1]);
        $order = $this->order($customer, $first, 2);
        $order->products()->attach($second, ['quantity' => 1, 'price_at_order' => $second->price]);
        $payload = $this->orderInput($customer) + ['quantities' => [$first->id_product => 3, $second->id_product => 99]];
        $payload['customer_name'] = 'Nama berubah';
        $this->put(route('admin.orders.update', $order), $payload)->assertSessionHasErrors('quantities.'.$second->id_product);
        $this->assertSame('Pelanggan Uji', $order->fresh()->customer_name);
        $this->assertSame(8, $first->fresh()->stock);
        $this->assertSame(2, $order->fresh()->products->find($first->id_product)->pivot->quantity);
    }

    public function test_successful_edit_keeps_recorded_prices_and_updates_total(): void
    {
        $this->asAdmin();
        $customer = $this->customer();
        $product = $this->product(['stock' => 8]);
        $order = $this->order($customer, $product, 2);
        $product->update(['price' => 20000]);
        $this->put(route('admin.orders.update', $order), $this->orderInput($customer) + ['quantities' => [$product->id_product => 3]])->assertRedirect(route('admin.orders.index'))->assertSessionHasNoErrors();
        $this->assertSame(37500, $order->fresh()->total_price);
        $this->assertSame(7, $product->fresh()->stock);
    }

    public function test_payment_status_can_be_saved_as_completed(): void
    {
        $this->asAdmin();
        $order = $this->order($this->customer(), $this->product(), 1);
        $this->patch(route('admin.orders.updateStatus', $order), ['payment_status' => 'completed'])->assertRedirect(route('admin.orders.index'));
        $this->assertSame('completed', $order->fresh()->payment_status);
    }

    public function test_completed_orders_remain_in_the_revenue_summary(): void
    {
        $this->asAdmin();
        $order = $this->order($this->customer(), $this->product(), 2);
        $order->update(['payment_status' => 'completed']);
        $this->get(route('admin.dashboard'))->assertOk()->assertViewHas('revenue', 25000);
    }

    public function test_edit_can_add_a_product_and_recalculate_the_saved_total(): void
    {
        $this->asAdmin();
        $customer = $this->customer();
        $first = $this->product(['stock' => 8]);
        $second = $this->product(['name' => 'Bolu', 'price' => 20000, 'stock' => 5]);
        $order = $this->order($customer, $first, 2);
        $this->put(route('admin.orders.update', $order), $this->orderInput($customer) + [
            'quantities' => [$first->id_product => 2], 'new_product_id' => $second->id_product,
            'new_quantity' => 2, 'action' => 'add_product',
        ])->assertRedirect(route('admin.orders.index'))->assertSessionHasNoErrors();
        $this->assertSame(65000, $order->fresh()->total_price);
        $this->assertSame(3, $second->fresh()->stock);
        $this->assertCount(2, $order->fresh()->products);
    }

    private function asAdmin(): void
    {
        $this->actingAs(User::forceCreate(['name' => 'Admin Abfis', 'email' => 'admin@example.test', 'password' => Hash::make('test-password'), 'is_admin' => true, 'role' => 'admin']), 'admin');
    }

    private function customer(): User
    {
        return User::create(['name' => 'Pelanggan Uji', 'email' => 'pelanggan@example.test', 'password' => Hash::make('test-password'), 'role' => 'user']);
    }

    private function product(array $attributes = []): Product
    {
        return Product::create(array_merge(['name' => 'Roti Mentega', 'price' => 12500, 'stock' => 10, 'description' => 'Roti dari dapur.', 'image' => 'qa.jpg'], $attributes));
    }

    private function order(User $customer, Product $product, int $quantity): Order
    {
        $order = Order::create($this->orderInput($customer) + ['total_price' => $quantity * $product->price]);
        $order->products()->attach($product, ['quantity' => $quantity, 'price_at_order' => $product->price]);
        return $order;
    }

    private function orderInput(User $customer): array
    {
        return ['id_user' => $customer->id_user, 'customer_name' => 'Pelanggan Uji', 'phone' => '08123456789', 'address' => 'Jalan Roti, Bogor', 'payment_method' => 'bank_transfer', 'payment_status' => 'pending', 'order_date' => '2026-10-21'];
    }
}
