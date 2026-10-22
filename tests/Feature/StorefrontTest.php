<?php

namespace Tests\Feature;

use App\Models\Product;
use DOMDocument;
use DOMXPath;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Testing\TestResponse;
use Tests\TestCase;

class StorefrontTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        $this->withoutVite();
        config([
            'database.default' => 'sqlite',
            'database.connections.sqlite.database' => ':memory:',
            'session.driver' => 'array',
            'cache.default' => 'array',
        ]);
        DB::purge('sqlite');

        // Keep these storefront checks isolated from the application's MySQL migrations.
        Schema::connection('sqlite')->create('products', function (Blueprint $table): void {
            $table->bigIncrements('id_product');
            $table->string('name');
            $table->text('description')->nullable();
            $table->decimal('price', 12, 2);
            $table->unsignedInteger('stock');
            $table->string('image')->nullable();
            $table->timestamps();
        });
    }

    public function test_catalog_shows_available_products_and_formatted_prices(): void
    {
        $available = $this->product(['name' => 'Roti Cokelat', 'price' => 16500]);
        $this->product(['name' => 'Roti Habis', 'stock' => 0]);

        $this->get(route('user.dashboard'))
            ->assertOk()
            ->assertSeeText('Roti Cokelat')
            ->assertSeeText('Rp 16.500')
            ->assertDontSeeText('Roti Habis')
            ->assertViewHas('products', fn ($products) => $products->total() === 1
                && $products->first()->is($available));
    }

    public function test_search_matches_name_or_description_and_keeps_sold_out_products_hidden(): void
    {
        $nameMatch = $this->product(['name' => 'Roti Kopi']);
        $descriptionMatch = $this->product(['name' => 'Bolu Vanilla', 'description' => 'Cocok dengan kopi pagi.']);
        $this->product(['name' => 'Kopi Habis', 'description' => 'Rasa kopi.', 'stock' => 0]);
        $this->product(['name' => 'Roti Keju', 'description' => 'Isian keju.']);

        $this->get(route('user.dashboard', ['q' => ' kopi ']))
            ->assertOk()
            ->assertViewHas('search', 'kopi')
            ->assertViewHas('products', fn ($products) => $products->pluck('id_product')->sort()->values()->all()
                === [$nameMatch->id_product, $descriptionMatch->id_product])
            ->assertDontSeeText('Kopi Habis')
            ->assertDontSeeText('Roti Keju');
    }

    public function test_customer_can_sort_by_price_and_name(): void
    {
        $this->product(['name' => 'Bolu Cokelat', 'price' => 20000]);
        $this->product(['name' => 'Roti Vanilla', 'price' => 10000]);
        $this->product(['name' => 'Apel Pastry', 'price' => 15000]);

        $expectedOrders = [
            'price_asc' => ['Roti Vanilla', 'Apel Pastry', 'Bolu Cokelat'],
            'price_desc' => ['Bolu Cokelat', 'Apel Pastry', 'Roti Vanilla'],
            'name' => ['Apel Pastry', 'Bolu Cokelat', 'Roti Vanilla'],
        ];

        foreach ($expectedOrders as $sort => $names) {
            $this->get(route('user.dashboard', ['sort' => $sort]))
                ->assertOk()
                ->assertViewHas('sort', $sort)
                ->assertViewHas('products', fn ($products) => $products->pluck('name')->all() === $names);
        }
    }

    public function test_unknown_sort_uses_latest_products(): void
    {
        $this->product(['name' => 'Pilihan Lama', 'created_at' => '2026-01-01 08:00:00']);
        $this->product(['name' => 'Pilihan Baru', 'created_at' => '2026-02-01 08:00:00']);

        $this->get(route('user.dashboard', ['sort' => 'unsupported']))
            ->assertOk()
            ->assertViewHas('sort', 'latest')
            ->assertViewHas('products', fn ($products) => $products->pluck('name')->all()
                === ['Pilihan Baru', 'Pilihan Lama']);
    }

    public function test_pagination_preserves_search_and_sort_and_shows_remaining_results(): void
    {
        for ($number = 1; $number <= 14; $number++) {
            $this->product(['name' => sprintf('Kopi %02d', $number), 'price' => 1000 + $number]);
        }
        $this->product(['name' => 'Kopi Habis', 'stock' => 0]);
        $this->product(['name' => 'Roti Keju']);
        $query = ['q' => 'kopi', 'sort' => 'price_desc'];

        $firstPage = $this->get(route('user.dashboard', $query))
            ->assertOk()
            ->assertViewHas('products', fn ($products) => $products->total() === 14
                && $products->count() === 12);

        parse_str(parse_url($firstPage->viewData('products')->nextPageUrl(), PHP_URL_QUERY), $nextQuery);
        $this->assertSame(['q' => 'kopi', 'sort' => 'price_desc', 'page' => '2'], $nextQuery);

        $this->get(route('user.dashboard', [...$query, 'page' => 2]))
            ->assertOk()
            ->assertViewHas('products', fn ($products) => $products->currentPage() === 2
                && $products->pluck('name')->all() === ['Kopi 02', 'Kopi 01'])
            ->assertDontSeeText('Kopi 14');
    }

    public function test_search_with_no_results_offers_a_return_to_the_catalog(): void
    {
        $this->product(['name' => 'Roti Cokelat']);

        $response = $this->get(route('user.dashboard', ['q' => 'pistachio']))
            ->assertOk()
            ->assertViewHas('products', fn ($products) => $products->isEmpty())
            ->assertSeeText('Belum ketemu favoritnya?')
            ->assertSeeText('Lihat semua pilihan');

        $xpath = $this->xpath($response);
        $this->assertSame(route('user.dashboard') . '#products',
            $xpath->evaluate('string(//a[contains(normalize-space(.), "Lihat semua pilihan")]/@href)'));
    }

    public function test_login_route_renders_only_the_login_form(): void
    {
        $response = $this->get(route('login'))->assertOk();
        $xpath = $this->xpath($response);

        $this->assertSame(route('login.submit'), $xpath->evaluate('string(//form/@action)'));
        $this->assertSame('POST', $xpath->evaluate('string(//form/@method)'));
        $this->assertSame(1, $xpath->query('//input[@name="_token"]')->length);
        $this->assertSame(1, $xpath->query('//input[@name="email"]')->length);
        $this->assertSame(1, $xpath->query('//input[@name="password"]')->length);
        $this->assertSame(0, $xpath->query('//input[@name="name" or @name="password_confirmation"]')->length);
        $this->assertSame(route('login'), $xpath->evaluate('string(//a[@aria-current="page"]/@href)'));
    }

    public function test_register_route_renders_the_registration_form_without_javascript(): void
    {
        $response = $this->get(route('register'))->assertOk();
        $xpath = $this->xpath($response);

        $this->assertSame(route('register.submit'), $xpath->evaluate('string(//form/@action)'));
        $this->assertSame('POST', $xpath->evaluate('string(//form/@method)'));
        $this->assertSame(1, $xpath->query('//input[@name="_token"]')->length);
        $this->assertSame(1, $xpath->query('//input[@name="name"]')->length);
        $this->assertSame(1, $xpath->query('//input[@name="email"]')->length);
        $this->assertSame(1, $xpath->query('//input[@name="password" and @minlength="8"]')->length);
        $this->assertSame(1, $xpath->query('//input[@name="password_confirmation"]')->length);
        $this->assertSame(route('register'), $xpath->evaluate('string(//a[@aria-current="page"]/@href)'));
    }

    public function test_sold_out_product_detail_disables_purchase(): void
    {
        $product = $this->product(['name' => 'Roti Terakhir', 'stock' => 0]);
        $response = $this->get(route('products.show', $product->id_product))
            ->assertOk()
            ->assertSeeText('Saat ini stok habis');
        $xpath = $this->xpath($response);

        $this->assertSame(1, $xpath->query('//button[@disabled and normalize-space(.)="Stok habis"]')->length);
        $this->assertSame(0, $xpath->query('//form')->length);
    }

    public function test_available_product_detail_can_be_added_to_cart(): void
    {
        $product = $this->product(['name' => 'Roti Siap Pesan', 'stock' => 7]);
        $response = $this->get(route('products.show', $product->id_product))
            ->assertOk()
            ->assertSeeText('7 tersedia untuk dipesan');
        $xpath = $this->xpath($response);

        $this->assertSame(route('cart.add', $product->id_product), $xpath->evaluate('string(//form/@action)'));
        $this->assertSame(1, $xpath->query('//form/button[@type="submit" and not(@disabled)]')->length);
    }

    private function product(array $attributes = []): Product
    {
        return Product::forceCreate(array_replace([
            'name' => 'Roti Pilihan',
            'description' => 'Roti dari dapur Abfis.',
            'price' => 12000,
            'stock' => 5,
            'image' => null,
        ], $attributes));
    }

    private function xpath(TestResponse $response): DOMXPath
    {
        $document = new DOMDocument();
        $document->loadHTML($response->getContent(), LIBXML_NOERROR | LIBXML_NOWARNING);

        return new DOMXPath($document);
    }
}
