<?php

namespace Tests\Feature;

use App\Models\Order;
use App\Models\Product;
use App\Models\User;
use DOMDocument;
use DOMXPath;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Relations\Pivot;
use Illuminate\Testing\TestView;
use Tests\TestCase;

class StorefrontViewsTest extends TestCase
{
    private const PRODUCT_NAME = 'Roti <script>alert("roti")</script> & Keju';

    protected function setUp(): void
    {
        parent::setUp();

        // These tests only render unsaved models. Any accidental connection is isolated.
        config([
            'database.default' => 'sqlite',
            'database.connections.sqlite.database' => ':memory:',
            'session.driver' => 'array',
        ]);

        $this->withoutVite();
        $this->withViewErrors([]);
        session()->start();
        request()->setLaravelSession(session()->driver());
        $this->actingAs((new User)->forceFill([
            'id_user' => 7,
            'name' => 'Pelanggan Uji',
            'email' => 'pelanggan@example.test',
        ]));
    }

    public function test_empty_cart_offers_a_route_back_to_the_catalog(): void
    {
        $view = $this->view('user.cart.index', ['cart' => []]);

        $view->assertSee('Keranjang Anda masih kosong.')
            ->assertSee(route('user.dashboard').'#products', false)
            ->assertDontSee('Lanjut ke checkout');
    }

    public function test_populated_cart_keeps_quantity_and_delete_form_contracts_and_escapes_product_names(): void
    {
        $cart = $this->cart();
        session()->put('cart', $cart);

        $view = $this->view('user.cart.index', compact('cart'));

        $view->assertSee(self::PRODUCT_NAME)
            ->assertDontSee(self::PRODUCT_NAME, false)
            ->assertSee('Rp 42.500')
            ->assertSee('2 jenis roti, 3 buah dalam keranjang.')
            ->assertSee(route('checkout.index'), false);

        $xpath = $this->xpath($view);
        foreach (array_keys($cart) as $id) {
            $updateAction = route('cart.update', $id);
            $removeAction = route('cart.remove', $id);
            $this->assertSame(2, $xpath->query('//form[@action="'.$updateAction.'"]//input[@name="_method" and @value="PUT"]')->length);
            $this->assertSame(1, $xpath->query('//form[@action="'.$removeAction.'"]//input[@name="_method" and @value="DELETE"]')->length);
            $this->assertSame(1, $xpath->query('//form[@action="'.$removeAction.'"]//input[@name="_token"]')->length);
        }

        $this->assertSame(1, $xpath->query('//form[@action="'.route('cart.update', 12).'"]//button[@value="decrease" and @disabled]')->length);
        $this->assertSame(0, $xpath->query('//form[@action="'.route('cart.update', 11).'"]//button[@value="decrease" and @disabled]')->length);
    }

    public function test_checkout_total_matches_the_cart_without_an_unpersisted_shipping_fee(): void
    {
        $view = $this->view('user.checkout', ['cart' => $this->cart()]);

        $view->assertSee('Rp 42.500')
            ->assertDontSee('Rp 57.500')
            ->assertDontSee('Rp 15.000')
            ->assertDontSee('Biaya Pengiriman')
            ->assertSee(self::PRODUCT_NAME)
            ->assertDontSee(self::PRODUCT_NAME, false);

        $xpath = $this->xpath($view);
        $this->assertSame(1, $xpath->query('//form[@id="checkout-form" and @method="POST" and @action="'.route('checkout.store').'"]')->length);
        $this->assertSame(1, $xpath->query('//form[@id="checkout-form"]//input[@name="_token"]')->length);
        $this->assertSame(1, $xpath->query('//form[@id="checkout-form"]//textarea[@name="address" and @required]')->length);
        $this->assertSame(1, $xpath->query('//form[@id="checkout-form"]//input[@name="phone" and @required]')->length);
        $this->assertSame(1, $xpath->query('//button[@type="submit" and @form="checkout-form"]')->length);
        foreach (['bank_transfer', 'e_wallet', 'cod'] as $method) {
            $this->assertSame(1, $xpath->query('//select[@name="payment_method" and @required]/option[@value="'.$method.'"]')->length);
        }
    }

    public function test_checkout_restores_old_fields_and_exposes_validation_errors(): void
    {
        session()->flashInput([
            'address' => 'Jl. Roti & Kue No. 7',
            'phone' => '081234567890',
            'payment_method' => 'e_wallet',
        ]);
        $this->withViewErrors(['phone' => 'Nomor telepon wajib diisi.']);

        $view = $this->view('user.checkout', ['cart' => $this->cart()]);

        $view->assertSee('Jl. Roti & Kue No. 7')
            ->assertSee('081234567890')
            ->assertSee('Nomor telepon wajib diisi.');

        $xpath = $this->xpath($view);
        $this->assertSame(1, $xpath->query('//select[@name="payment_method"]/option[@value="e_wallet" and @selected]')->length);
        $this->assertSame(1, $xpath->query('//input[@name="phone" and @aria-invalid="true" and @aria-describedby="phone-error"]')->length);
    }

    public function test_empty_checkout_and_order_views_render_without_model_queries(): void
    {
        $this->view('user.checkout', ['cart' => []])
            ->assertSee('Belum ada yang dipesan.')
            ->assertDontSee('id="checkout-form"', false);
        $this->view('user.orders.history', ['orders' => new Collection])
            ->assertSee('Anda belum memiliki pesanan.');
        $this->view('user.orders.status', ['pendingOrders' => new Collection])
            ->assertSee('Tidak ada pembayaran tertunda.')
            ->assertDontSee('name="payment_proof"', false);
    }

    public function test_order_history_renders_recorded_totals_payment_methods_and_statuses(): void
    {
        $orders = new Collection([
            $this->order(901, 'bank_transfer', 'pending', 'bukti-901.pdf'),
            $this->order(902, 'e_wallet', 'approved'),
            $this->order(903, 'cod', 'rejected'),
            $this->order(904, 'credit_card', 'completed'),
        ]);

        $view = $this->view('user.orders.history', compact('orders'));

        $view->assertSee('Rp 42.500')
            ->assertDontSee('Rp 57.500')
            ->assertDontSee('Biaya Pengiriman')
            ->assertSee('Transfer bank')
            ->assertSee('E-wallet')
            ->assertSee('Bayar di tempat')
            ->assertSee('Kartu kredit')
            ->assertSee('Menunggu pemeriksaan')
            ->assertSee('Disetujui')
            ->assertSee('Ditolak')
            ->assertSee('Selesai')
            ->assertSee(self::PRODUCT_NAME)
            ->assertDontSee(self::PRODUCT_NAME, false);

        $this->assertSame(4, $this->xpath($view)->query('//article/details/summary')->length);
    }

    public function test_payment_status_supports_pdf_proofs_and_preserves_upload_routes(): void
    {
        $pendingOrders = new Collection([
            $this->order(901, 'bank_transfer', 'pending', 'bukti-901.pdf'),
            $this->order(902, 'e_wallet', 'pending'),
        ]);

        $view = $this->view('user.orders.status', compact('pendingOrders'));

        $view->assertSee('Bukti sudah dikirim.')
            ->assertSee('Sudah membayar?')
            ->assertSee('Menunggu pemeriksaan')
            ->assertSee('Menunggu pembayaran')
            ->assertSee('Format JPG, PNG, atau PDF. Maksimal 2 MB.')
            ->assertSee(asset('storage/proofs/bukti-901.pdf'), false)
            ->assertSee('Rp 42.500')
            ->assertDontSee('Rp 57.500')
            ->assertSee(self::PRODUCT_NAME)
            ->assertDontSee(self::PRODUCT_NAME, false);

        $xpath = $this->xpath($view);
        foreach ([901, 902] as $id) {
            $action = route('orders.uploadProof', ['orderId' => $id]);
            $this->assertSame(1, $xpath->query('//form[@action="'.$action.'" and @method="POST" and @enctype="multipart/form-data"]')->length);
            $this->assertSame(1, $xpath->query('//form[@action="'.$action.'"]//input[@name="_method" and @value="PUT"]')->length);
            $this->assertSame(1, $xpath->query('//form[@action="'.$action.'"]//input[@name="_token"]')->length);
            $this->assertSame(1, $xpath->query('//input[@id="payment-proof-'.$id.'" and @name="payment_proof" and @required and contains(@accept,".pdf")]')->length);
        }
    }

    private function cart(): array
    {
        return [
            11 => ['name' => self::PRODUCT_NAME, 'price' => 12500, 'quantity' => 2, 'image' => 'roti.jpg'],
            12 => ['name' => 'Roti Cokelat', 'price' => 17500, 'quantity' => 1, 'image' => null],
        ];
    }

    private function order(int $id, string $paymentMethod, string $status, ?string $proof = null): Order
    {
        $products = new Collection;
        foreach ($this->cart() as $productId => $item) {
            $product = (new Product)->forceFill(['id_product' => $productId, 'name' => $item['name']]);
            $product->setRelation('pivot', new Pivot([
                'quantity' => $item['quantity'],
                'price_at_order' => $item['price'],
            ]));
            $products->push($product);
        }

        return (new Order)->forceFill([
            'id_order' => $id,
            'id_user' => 7,
            'total_price' => 42500,
            'payment_method' => $paymentMethod,
            'payment_status' => $status,
            'payment_proof' => $proof,
            'address' => 'Jl. Roti No. 7, Bogor',
            'phone' => '081234567890',
            'created_at' => '2026-10-21 02:00:00',
        ])->setRelation('products', $products);
    }

    private function xpath(TestView $view): DOMXPath
    {
        $document = new DOMDocument;
        $document->loadHTML('<?xml encoding="UTF-8">'.(string) $view, LIBXML_NOERROR | LIBXML_NOWARNING);

        return new DOMXPath($document);
    }
}
