# Glow Halal — Backend Architecture Specification

> **Status:** Specification only. No application code has been written into the project by this document.
> **Target:** `C:\laragon\www\glowhalal`
> **Last verified:** 2026-08-09 against the installed vendor tree and the Packagist metadata API.

---

## 0. Verified Environment

Everything in this table was read from the machine or from `repo.packagist.org`, not assumed.

| Component | Version | How verified |
|---|---|---|
| Laravel framework | **13.24.0** | `Illuminate\Foundation\Application::VERSION` in `vendor/` |
| PHP | 8.3 (`composer.json` requires `^8.3`) | `composer.json` |
| MySQL | **8.4.3**, InnoDB 8.4.3 | live `PDO` connection to `127.0.0.1:3306` |
| Server charset / collation | `utf8mb4` / `utf8mb4_0900_ai_ci` | `SHOW VARIABLES` |
| `sql_mode` | `ONLY_FULL_GROUP_BY,STRICT_TRANS_TABLES,NO_ZERO_IN_DATE,NO_ZERO_DATE,ERROR_FOR_DIVISION_BY_ZERO,NO_ENGINE_SUBSTITUTION` | `SHOW VARIABLES` |
| Database `glowhalal` | already created | `SHOW DATABASES` |
| `.env` | already switched to `DB_CONNECTION=mysql`, `DB_DATABASE=glowhalal`, `APP_URL=http://glowhalal.test` | file read |
| Tailwind | 4 via `@tailwindcss/vite`, Vite 8 | `package.json`, `vite.config.js` |
| Existing migrations | 3 stock only: users/password_reset_tokens/sessions, cache, jobs | `database/migrations/` |
| Existing app classes | `App\Models\User`, `App\Http\Controllers\Controller`, `App\Providers\AppServiceProvider` | `app/` |
| Git | **not a repository yet** | no `.git` directory |

### 0.1 Dependency resolution — verified against Laravel 13.24.0

This is the part that most often goes wrong. Each constraint below was read from the package's own `composer.json` on Packagist.

| Package | Version | Laravel constraint | Verdict |
|---|---|---|---|
| `filament/filament` (panels) | **v5.7.6** (2026-08-05) | via `filament/support`: `illuminate/contracts: ^11.28\|^12.0\|^13.0` | **OK on Laravel 13** |
| `filament/support` v5.7.6 | — | requires `livewire/livewire: ^4.1` | forces Livewire 4 |
| `livewire/livewire` | **v4.3.5** (2026-08-03) | `illuminate/support: ^10.0\|^11.0\|^12.0\|^13.0` | **OK on Laravel 13** |
| `spatie/laravel-sluggable` | 4.0.3 | `^12.0\|^13.0` | OK |
| `spatie/laravel-permission` | 8.3.0 | `^12.0\|^13.0` | OK |
| `spatie/laravel-sitemap` | 8.2.0 | `^12.0\|^13.0` | OK |
| `spatie/laravel-settings` | 3.9.0 | — | OK |
| `filament/spatie-laravel-settings-plugin` | v5.7.6 | tracks Filament | OK |
| `filament/spatie-laravel-media-library-plugin` | v5.7.6 | tracks Filament | OK (not used — see §4) |
| `spatie/laravel-medialibrary` | 11.23.4 | `^10.2\|^11.0\|^12.0\|^13.0` | OK (not used — see §4) |
| `bezhansalleh/filament-shield` | 4.3.1 | `^11.28\|^12.0\|^13.0`, Filament `^4.0\|^5.0` | OK |
| `staudenmeir/laravel-adjacency-list` | v1.26.1 | `^13.0` | OK |
| `intervention/image` | 4.2.1 | — | OK |

**Two findings worth flagging before you run `composer require`:**

1. **Filament 4 would have blocked Livewire 4.** `filament/support` v4.12.6 requires `livewire/livewire: ^3.5`. Filament 4 *does* now support Laravel 13 (from **v4.9.0**, released 2026-03-17), so "Filament 4 on Laravel 13" is resolvable — but only with Livewire 3. Filament 5 is the only major that pulls Livewire 4. The corrected stack is internally consistent; the original one was too, just on the older Livewire line.

2. **`filament/spatie-laravel-translatable-plugin` has no v4 or v5 release.** Its newest tag is `v3.3.54`. If Urdu localisation of admin-managed content is ever wanted, that plugin is not an option on Filament 5 — plan for either JSON translation columns handled manually, or a locale column with duplicated rows. This does not affect launch (English only), but it is a door that is currently closed.

### 0.2 Locked decisions (do not relitigate)

| Decision | Value |
|---|---|
| Admin panel | Filament **5** |
| Storefront reactivity | Livewire **4** (server-rendered HTML; SEO is a hard requirement) |
| Database | MySQL 8.4 |
| Market / currency | Pakistan / **PKR** |
| Payments at launch | Cash on delivery, manual bank transfer |
| Payments later | JazzCash / Easypaisa via a driver contract — no checkout refactor |

### 0.3 Cross-cutting conventions used throughout this document

**Money is stored as integer minor units (paisa), never as `decimal` or `float`.**
Every monetary column is `unsignedBigInteger` (or signed where a value can go negative, e.g. ledger adjustments) and is suffixed `_amount`. `Rs 2,450.00` is stored as `245000`.

Rationale: percentage coupons, per-line discount apportionment, and partial refunds all produce fractional intermediate values. With `decimal(10,2)` those get rounded at each step and the line totals stop summing to the order total — a real and common bug that surfaces as a one-rupee discrepancy on invoices. Integer arithmetic with a single explicit rounding point at the moment of apportionment is exact and auditable. A `MoneyCast` (§3.9) presents these as a `Money` value object in PHP, so application code never manipulates raw paisa.

**Status columns are `string`, not MySQL `ENUM`.**
Backed by PHP enums and cast at the model layer. MySQL `ENUM` requires an `ALTER TABLE` to add a value, which on a large `orders` table is a migration you will not want to run. `string(32)` + a PHP enum gives you the same type safety at the only layer that matters, plus free extensibility. Where the value set is genuinely closed and small, a `CHECK` constraint is added by raw statement (Laravel's `Blueprint` has no `check()` method — verified against `Illuminate\Database\Schema\Blueprint` in the installed tree).

**Primary keys are `bigIncrements`.** Auto-incrementing `BIGINT UNSIGNED` keeps the InnoDB clustered index compact and sequential. Public-facing identifiers that must not be guessable or enumerable (cart tokens, order numbers) get a **separate** `ulid` / formatted column with its own unique index. Do not use UUIDs as primary keys on InnoDB.

**Timestamps are `timestamp` (not `timestampTz`)**, application timezone `Asia/Karachi` set in `config/app.php`, stored as UTC. Every state-transition time gets its own nullable column (`confirmed_at`, `shipped_at`, …) rather than being inferred from a history table — the history table exists as well, but denormalised timestamps make order-list filtering indexable.

**Every table that participates in a public URL carries a `slug`** with a unique index, and every content model has a polymorphic SEO record and slug history (§7).

**Foreign keys**: `restrictOnDelete()` by default. `cascadeOnDelete()` only where the child is genuinely a part of the parent's aggregate (cart items, order addresses, variant attribute links). `nullOnDelete()` on historical snapshots (`order_items.product_id`) so deleting a product never destroys order history.
---

## 1. Database Schema

### 1.1 Migration order

Migrations must run in this order; the numbering encodes the dependency graph. Keep the three stock migrations (`0001_01_01_*`) untouched.

```
0001_01_01_000000_create_users_table.php            (stock — keep)
0001_01_01_000001_create_cache_table.php            (stock — keep)
0001_01_01_000002_create_jobs_table.php             (stock — keep)

2026_08_10_000100_add_customer_columns_to_users_table.php
2026_08_10_000110_create_permission_tables.php      (published by spatie/laravel-permission)
2026_08_10_000120_create_addresses_table.php

2026_08_10_000200_create_categories_table.php
2026_08_10_000210_create_attributes_table.php
2026_08_10_000220_create_attribute_values_table.php

2026_08_10_000300_create_certification_bodies_table.php
2026_08_10_000310_create_ingredients_table.php
2026_08_10_000315_create_ingredient_related_table.php
2026_08_10_000320_create_free_from_attributes_table.php

2026_08_10_000400_create_products_table.php
2026_08_10_000410_create_category_product_table.php
2026_08_10_000420_create_product_variants_table.php
2026_08_10_000430_create_attribute_value_product_variant_table.php
2026_08_10_000440_create_product_images_table.php

2026_08_10_000500_create_product_halal_profiles_table.php
2026_08_10_000510_create_ingredient_product_table.php
2026_08_10_000520_create_product_certifications_table.php
2026_08_10_000530_create_free_from_attribute_product_table.php

2026_08_10_000600_create_inventory_locations_table.php
2026_08_10_000610_create_inventory_items_table.php
2026_08_10_000620_create_inventory_movements_table.php
2026_08_10_000630_create_stock_reservations_table.php

2026_08_10_000700_create_coupons_table.php
2026_08_10_000710_create_coupon_scope_tables.php
2026_08_10_000720_create_carts_table.php
2026_08_10_000730_create_cart_items_table.php

2026_08_10_000800_create_shipping_zones_table.php
2026_08_10_000810_create_shipping_rates_table.php

2026_08_10_000900_create_orders_table.php
2026_08_10_000910_create_order_addresses_table.php
2026_08_10_000920_create_order_items_table.php
2026_08_10_000930_create_order_status_histories_table.php
2026_08_10_000940_create_coupon_redemptions_table.php
2026_08_10_000950_add_converted_order_id_to_carts_table.php   (deferred FK — breaks the cycle)

2026_08_10_001000_create_bank_accounts_table.php
2026_08_10_001010_create_payments_table.php
2026_08_10_001020_create_payment_proofs_table.php

2026_08_10_001100_create_blog_categories_table.php
2026_08_10_001110_create_blog_posts_table.php
2026_08_10_001120_create_tags_table.php
2026_08_10_001130_create_taggables_table.php
2026_08_10_001140_create_blog_post_product_table.php
2026_08_10_001150_create_pages_table.php

2026_08_10_001200_create_seo_metas_table.php
2026_08_10_001210_create_slug_histories_table.php
2026_08_10_001220_create_redirects_table.php

2026_08_10_001300_create_settings_table.php         (published by spatie/laravel-settings)
2026_08_10_001310_create_product_reviews_table.php  (Phase 6 — optional)
2026_08_10_001320_create_newsletter_subscribers_table.php (Phase 6 — optional)
```

**`carts` ↔ `orders` is the one circular FK.** A cart points at the order it converted into; an order points at the cart it came from. Create `carts` without `converted_order_id`, create `orders` with `cart_id`, then add `carts.converted_order_id` in a later migration. Do not try to solve this with a single migration.

---

### 1.2 Identity, customers, addresses

There is **one** `users` table holding both admins and customers, separated by role (`spatie/laravel-permission`). A separate `customers` table was considered and rejected: it is a 1:1 with `users` that would be joined on every storefront request for no isolation benefit, and it forces awkward dual-identity handling when a customer is promoted to staff.

Guests check out **without an account** — mandatory for the Pakistani COD market, where account friction kills conversion. Orders therefore carry a nullable `user_id` plus a denormalised contact snapshot.

```php
// 2026_08_10_000100_add_customer_columns_to_users_table.php
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->string('phone', 20)->nullable()->after('email');
            $table->string('phone_country', 2)->default('PK')->after('phone');
            $table->timestamp('phone_verified_at')->nullable()->after('phone_country');
            $table->date('date_of_birth')->nullable()->after('phone_verified_at');
            $table->string('avatar_path')->nullable()->after('date_of_birth');
            $table->boolean('accepts_marketing')->default(false)->after('avatar_path');
            $table->timestamp('accepts_marketing_at')->nullable()->after('accepts_marketing');
            $table->string('preferred_locale', 5)->default('en')->after('accepts_marketing_at');
            $table->timestamp('last_login_at')->nullable()->after('preferred_locale');
            $table->string('last_login_ip', 45)->nullable()->after('last_login_at');
            $table->boolean('is_blocked')->default(false)->after('last_login_ip');
            $table->softDeletes();

            $table->unique('phone', 'users_phone_unique');
            $table->index(['is_blocked', 'created_at'], 'users_blocked_created_index');
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropUnique('users_phone_unique');
            $table->dropIndex('users_blocked_created_index');
            $table->dropSoftDeletes();
            $table->dropColumn([
                'phone', 'phone_country', 'phone_verified_at', 'date_of_birth',
                'avatar_path', 'accepts_marketing', 'accepts_marketing_at',
                'preferred_locale', 'last_login_at', 'last_login_ip', 'is_blocked',
            ]);
        });
    }
};
```

> `phone` is `unique` **and** nullable. MySQL permits unlimited `NULL`s in a unique index, so guest-created users without a phone are fine. Store phones normalised to E.164 (`+923001234567`) — do the normalisation in a mutator (§3.2), never rely on user input format.

**Roles.** Publish `spatie/laravel-permission`'s migration unchanged. Seed three roles: `super_admin`, `staff`, `customer`. Panel access is gated by `canAccessPanel()` (§4.3), not by an `is_admin` boolean — a boolean cannot express "warehouse staff may edit orders but not products".

```php
// 2026_08_10_000120_create_addresses_table.php
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('addresses', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('label', 40)->nullable();          // "Home", "Office"
            $table->string('first_name', 100);
            $table->string('last_name', 100)->nullable();
            $table->string('phone', 20);
            $table->string('alternate_phone', 20)->nullable();
            $table->string('line_1', 255);
            $table->string('line_2', 255)->nullable();
            $table->string('area', 120)->nullable();           // sector / block / colony
            $table->string('city', 120);
            $table->string('province', 40);                    // App\Enums\PakistanProvince
            $table->string('postal_code', 12)->nullable();     // optional: rarely used in PK
            $table->char('country_code', 2)->default('PK');
            $table->text('delivery_instructions')->nullable(); // "near Faysal Bank, red gate"
            $table->boolean('is_default_shipping')->default(false);
            $table->boolean('is_default_billing')->default(false);
            $table->timestamps();
            $table->softDeletes();

            $table->index(['user_id', 'is_default_shipping'], 'addresses_user_default_ship_index');
            $table->index(['city', 'province'], 'addresses_city_province_index');
        });
    }

    public function down(): void { Schema::dropIfExists('addresses'); }
};
```

> **Pakistan-specific:** postal codes are nullable and largely unused for last-mile delivery; `delivery_instructions` and `area` carry the real routing information. `province` is one of `punjab, sindh, kpk, balochistan, gilgit_baltistan, ajk, islamabad` — a `CHECK` constraint is added below because this set genuinely never changes.

```php
DB::statement("
    ALTER TABLE addresses ADD CONSTRAINT addresses_province_check
    CHECK (province IN ('punjab','sindh','kpk','balochistan','gilgit_baltistan','ajk','islamabad'))
");
```

Enforcing "only one default shipping address per user" at the database level is not worth a trigger; enforce it in an `Address` observer that clears the flag on siblings inside a transaction.

---

### 1.3 Taxonomy: categories and variant attributes

**Categories are hierarchical via a hybrid model**: an adjacency list (`parent_id`) as the source of truth, plus **materialised `path` and `depth`** maintained by an observer.

Why both: the adjacency list is the only representation that is cheap to write (moving a category is one `UPDATE`), while `path` makes "all products in this category and every descendant" a single indexed `LIKE 'x/y/%'` scan instead of a recursive CTE per request. Nested sets were rejected — rebalancing on every insert is a poor trade for a catalogue that gets edited daily. `staudenmeir/laravel-adjacency-list` supplies `ancestors()` / `descendants()` relations via recursive CTE for the admin tree, where write-consistency matters more than read speed.

```php
// 2026_08_10_000200_create_categories_table.php
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('categories', function (Blueprint $table) {
            $table->id();
            $table->foreignId('parent_id')->nullable()
                  ->constrained('categories')->restrictOnDelete();
            $table->string('name', 160);
            $table->string('slug', 180);
            $table->string('path', 512)->nullable()
                  ->comment('Materialised ancestor ids, e.g. "1/7/23". Maintained by CategoryObserver.');
            $table->unsignedTinyInteger('depth')->default(0);
            $table->unsignedInteger('position')->default(0);
            $table->text('description')->nullable();
            $table->string('image_path')->nullable();
            $table->string('image_alt', 255)->nullable();
            $table->string('icon_path')->nullable();
            $table->boolean('is_active')->default(true);
            $table->boolean('is_featured')->default(false);
            $table->boolean('show_in_menu')->default(true);
            $table->unsignedInteger('products_count')->default(0)
                  ->comment('Denormalised active-product count incl. descendants. Rebuilt nightly.');
            $table->timestamps();
            $table->softDeletes();

            $table->unique('slug', 'categories_slug_unique');
            $table->index(['parent_id', 'position'], 'categories_parent_position_index');
            $table->index('path', 'categories_path_index');
            $table->index(['is_active', 'show_in_menu'], 'categories_active_menu_index');
        });
    }

    public function down(): void { Schema::dropIfExists('categories'); }
};
```

> **`slug` is globally unique, not unique-per-parent.** This is deliberate: category URLs are flat (`/category/matte-lipstick`), not nested (`/makeup/lips/matte-lipstick`). Flat category URLs avoid the single worst SEO failure mode in nested catalogues — moving a category under a different parent rewrites the URL of every product path beneath it. See §7.2.
>
> `restrictOnDelete()` on `parent_id` forces the admin to explicitly re-parent children before deleting. Silent cascading deletion of a category subtree is not something anyone should be able to do with one click.

**Variant attributes** (shade, size) are first-class rows, not JSON, because the storefront must filter and facet on them and shades need a swatch colour.

```php
// 2026_08_10_000210_create_attributes_table.php
Schema::create('attributes', function (Blueprint $table) {
    $table->id();
    $table->string('code', 40);                 // 'shade', 'size', 'finish'
    $table->string('name', 80);                 // 'Shade'
    $table->string('type', 20)->default('select'); // App\Enums\AttributeType: select|color|size|text
    $table->boolean('is_variant_defining')->default(true)
          ->comment('False = descriptive only, does not create variant permutations.');
    $table->boolean('is_filterable')->default(true);
    $table->unsignedInteger('position')->default(0);
    $table->timestamps();

    $table->unique('code', 'attributes_code_unique');
    $table->index(['is_filterable', 'position'], 'attributes_filterable_position_index');
});

// 2026_08_10_000220_create_attribute_values_table.php
Schema::create('attribute_values', function (Blueprint $table) {
    $table->id();
    $table->foreignId('attribute_id')->constrained()->cascadeOnDelete();
    $table->string('value', 120);               // 'Nude Rose'
    $table->string('slug', 140);                // 'nude-rose'
    $table->char('hex_color', 7)->nullable();   // '#C08081' — swatch, only for type=color
    $table->string('swatch_image_path')->nullable()
          ->comment('For multi-tone or glitter shades a flat hex is not enough.');
    $table->unsignedInteger('position')->default(0);
    $table->timestamps();

    $table->unique(['attribute_id', 'slug'], 'attribute_values_attribute_slug_unique');
    $table->index(['attribute_id', 'position'], 'attribute_values_attribute_position_index');
});
```

---

### 1.4 Catalogue: products, variants, images

**Every product has at least one variant**, including single-SKU products, which get one auto-created `is_default` variant. This removes the entire class of `if ($product->hasVariants())` branching from cart, inventory, and order code — there is exactly one thing you can add to a cart, and it is a variant.

Price and stock therefore live on the **variant**. `products.price_min_amount` / `price_max_amount` are denormalised for listing, sorting and filtering, and are recomputed by a `ProductVariant` observer.

```php
// 2026_08_10_000400_create_products_table.php
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('products', function (Blueprint $table) {
            $table->id();
            $table->foreignId('primary_category_id')->nullable()
                  ->constrained('categories')->nullOnDelete()
                  ->comment('Drives breadcrumbs and canonical category. Many-to-many lives in category_product.');
            $table->string('name', 200);
            $table->string('slug', 220);
            $table->string('sku_prefix', 40)->nullable();
            $table->string('brand', 100)->nullable();
            $table->text('short_description')->nullable()
                  ->comment('Plain text. Used for listing cards and meta description fallback.');
            $table->longText('description')->nullable()
                  ->comment('Filament RichEditor output. See §4.6 for storage format.');
            $table->longText('how_to_use')->nullable();
            $table->longText('ingredients_note')->nullable()
                  ->comment('Free-text INCI caveats. The structured list is in ingredient_product.');

            // Denormalised pricing, maintained by ProductVariantObserver
            $table->unsignedBigInteger('price_min_amount')->default(0)->comment('paisa');
            $table->unsignedBigInteger('price_max_amount')->default(0)->comment('paisa');
            $table->unsignedBigInteger('compare_at_max_amount')->nullable()->comment('paisa');

            $table->string('status', 20)->default('draft')
                  ->comment('App\Enums\ProductStatus: draft|active|archived');
            $table->timestamp('published_at')->nullable();
            $table->boolean('is_featured')->default(false);
            $table->boolean('is_new_arrival')->default(false);
            $table->unsignedTinyInteger('return_window_days')->default(7);

            // Denormalised aggregates
            $table->unsignedInteger('total_stock')->default(0)
                  ->comment('Sum of available quantity across variants. Maintained by inventory events.');
            $table->unsignedInteger('reviews_count')->default(0);
            $table->decimal('reviews_average', 3, 2)->default(0);
            $table->unsignedBigInteger('views_count')->default(0);
            $table->unsignedInteger('orders_count')->default(0);

            $table->unsignedInteger('position')->default(0);
            $table->timestamps();
            $table->softDeletes();

            $table->unique('slug', 'products_slug_unique');
            $table->index(['status', 'published_at'], 'products_status_published_index');
            $table->index(['primary_category_id', 'status'], 'products_category_status_index');
            $table->index(['status', 'is_featured', 'position'], 'products_featured_index');
            $table->index(['status', 'price_min_amount'], 'products_status_price_index');
            $table->index(['status', 'created_at'], 'products_status_created_index');
            $table->fullText(['name', 'short_description'], 'products_fulltext_index');
        });
    }

    public function down(): void { Schema::dropIfExists('products'); }
};
```

> **On the `fullText` index:** MySQL 8.4 InnoDB full-text search is adequate for a catalogue in the low thousands and costs nothing to add now. It does **not** handle Urdu/Roman-Urdu transliteration or typo tolerance. When search quality becomes a conversion problem, move to Meilisearch/Typesense via Laravel Scout — the index above stays as the fallback driver. Do not build a `LIKE '%term%'` search; it cannot use any index.
>
> **On `total_stock`:** denormalised deliberately. "In stock" filtering and sorting on a listing page is otherwise a correlated subquery across `product_variants` → `inventory_items` on every request. Correctness is maintained by an inventory event listener, and a nightly reconciliation command (§8, Phase 5) re-derives it and logs drift.

```php
// 2026_08_10_000410_create_category_product_table.php
Schema::create('category_product', function (Blueprint $table) {
    $table->foreignId('category_id')->constrained()->cascadeOnDelete();
    $table->foreignId('product_id')->constrained()->cascadeOnDelete();
    $table->unsignedInteger('position')->default(0);

    $table->primary(['category_id', 'product_id']);
    $table->index(['product_id'], 'category_product_product_index');
    $table->index(['category_id', 'position'], 'category_product_category_position_index');
});
```

> Composite primary key, no surrogate `id`, no timestamps. A pivot with a `bigIncrements` PK it never queries by is a wasted clustered index.

```php
// 2026_08_10_000420_create_product_variants_table.php
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('product_variants', function (Blueprint $table) {
            $table->id();
            $table->foreignId('product_id')->constrained()->cascadeOnDelete();
            $table->string('sku', 64);
            $table->string('barcode', 64)->nullable();
            $table->string('name', 200)->nullable()
                  ->comment('Derived label, e.g. "Nude Rose / 4.2g". Denormalised for order snapshots.');

            $table->unsignedBigInteger('price_amount')->comment('paisa, PKR');
            $table->unsignedBigInteger('compare_at_amount')->nullable()
                  ->comment('paisa. Struck-through "was" price. Must exceed price_amount to display.');
            $table->unsignedBigInteger('cost_amount')->nullable()
                  ->comment('paisa. Never exposed to the storefront. Margin reporting only.');

            $table->unsignedInteger('weight_grams')->default(0)
                  ->comment('Drives weight-based shipping rates.');
            $table->unsignedSmallInteger('length_mm')->nullable();
            $table->unsignedSmallInteger('width_mm')->nullable();
            $table->unsignedSmallInteger('height_mm')->nullable();

            $table->boolean('is_default')->default(false);
            $table->boolean('is_active')->default(true);
            $table->boolean('track_inventory')->default(true);
            $table->boolean('allow_backorder')->default(false);
            $table->unsignedSmallInteger('max_per_order')->nullable();
            $table->unsignedInteger('position')->default(0);
            $table->timestamps();
            $table->softDeletes();

            $table->unique('sku', 'product_variants_sku_unique');
            $table->index(['product_id', 'position'], 'product_variants_product_position_index');
            $table->index(['product_id', 'is_default'], 'product_variants_product_default_index');
            $table->index(['is_active', 'price_amount'], 'product_variants_active_price_index');
        });

        DB::statement("
            ALTER TABLE product_variants ADD CONSTRAINT product_variants_compare_at_check
            CHECK (compare_at_amount IS NULL OR compare_at_amount >= price_amount)
        ");
    }

    public function down(): void { Schema::dropIfExists('product_variants'); }
};
```

```php
// 2026_08_10_000430_create_attribute_value_product_variant_table.php
Schema::create('attribute_value_product_variant', function (Blueprint $table) {
    $table->foreignId('product_variant_id')->constrained()->cascadeOnDelete();
    $table->foreignId('attribute_value_id')->constrained()->restrictOnDelete();

    $table->primary(['product_variant_id', 'attribute_value_id'], 'avpv_primary');
    $table->index('attribute_value_id', 'avpv_attribute_value_index');
});
```

> `restrictOnDelete()` on `attribute_value_id`: deleting the "Nude Rose" shade while variants reference it would silently strip their identity. Admin must reassign first.

**Product images** get a dedicated table rather than `spatie/laravel-medialibrary`.

Reason: images must be attachable to a **specific variant** — selecting the "Nude Rose" swatch has to swap the gallery to that shade's photos. Media Library's polymorphic `media` table can model that with collections, but the resulting query ("all images for this product, ordered, with the variant-specific ones first") becomes awkward, and `alt_text` — which is the single highest-value SEO field on an e-commerce image — ends up in a `custom_properties` JSON blob that you cannot index, validate, or bulk-audit. A dedicated table keeps alt text first-class and required.

```php
// 2026_08_10_000440_create_product_images_table.php
Schema::create('product_images', function (Blueprint $table) {
    $table->id();
    $table->foreignId('product_id')->constrained()->cascadeOnDelete();
    $table->foreignId('product_variant_id')->nullable()
          ->constrained()->cascadeOnDelete()
          ->comment('NULL = shared across all variants. Set = shade/size specific gallery.');
    $table->string('disk', 32)->default('public');
    $table->string('path', 255);
    $table->string('alt_text', 255)
          ->comment('Required. Enforced at the Filament layer and by validation, not nullable here.');
    $table->string('mime_type', 64)->nullable();
    $table->unsignedInteger('size_bytes')->nullable();
    $table->unsignedSmallInteger('width')->nullable();
    $table->unsignedSmallInteger('height')->nullable();
    $table->json('conversions')->nullable()
          ->comment('{"thumb":"...webp","md":"...webp","lg":"...webp"} written by GenerateImageConversions job.');
    $table->string('blurhash', 40)->nullable()
          ->comment('LQIP placeholder. Prevents CLS, which is a Core Web Vitals ranking input.');
    $table->boolean('is_primary')->default(false);
    $table->unsignedInteger('position')->default(0);
    $table->timestamps();

    $table->index(['product_id', 'position'], 'product_images_product_position_index');
    $table->index(['product_id', 'is_primary'], 'product_images_product_primary_index');
    $table->index('product_variant_id', 'product_images_variant_index');
});
```

---

### 1.5 Inventory

Stock is a **ledger**, not a counter. `inventory_items` holds the current position; `inventory_movements` holds every change with a reason. Without the ledger, the first "we sold something we didn't have" incident is unresolvable.

```php
// 2026_08_10_000600_create_inventory_locations_table.php
Schema::create('inventory_locations', function (Blueprint $table) {
    $table->id();
    $table->string('code', 32);
    $table->string('name', 120);
    $table->string('city', 120)->nullable();
    $table->string('address', 255)->nullable();
    $table->boolean('is_default')->default(false);
    $table->boolean('is_active')->default(true);
    $table->timestamps();

    $table->unique('code', 'inventory_locations_code_unique');
});
```

> One row is seeded (`code: 'main'`). This table exists now so that adding a second warehouse later is a data change rather than a migration that touches every stock query. It costs one small join that InnoDB will serve from the buffer pool permanently.

```php
// 2026_08_10_000610_create_inventory_items_table.php
Schema::create('inventory_items', function (Blueprint $table) {
    $table->id();
    $table->foreignId('product_variant_id')->constrained()->cascadeOnDelete();
    $table->foreignId('inventory_location_id')->constrained()->restrictOnDelete();
    $table->integer('quantity_on_hand')->default(0)
          ->comment('Signed. Can go negative only when allow_backorder is true.');
    $table->unsignedInteger('quantity_reserved')->default(0)
          ->comment('Held by active carts/unpaid orders. See stock_reservations.');
    $table->integer('quantity_available')->storedAs('quantity_on_hand - quantity_reserved')
          ->comment('Generated column — always consistent, and indexable.');
    $table->unsignedInteger('reorder_level')->default(5);
    $table->unsignedInteger('reorder_quantity')->default(20);
    $table->timestamp('last_counted_at')->nullable();
    $table->timestamps();

    $table->unique(['product_variant_id', 'inventory_location_id'], 'inventory_items_variant_location_unique');
    $table->index('quantity_available', 'inventory_items_available_index');
});
```

> `quantity_available` is a **MySQL stored generated column** (`storedAs()` — confirmed present on Laravel 13's `ColumnDefinition`). It cannot drift from its inputs, and unlike a virtual column a stored one can carry a secondary index, so "low stock" and "in stock" admin filters are index scans.

```php
// 2026_08_10_000620_create_inventory_movements_table.php
Schema::create('inventory_movements', function (Blueprint $table) {
    $table->id();
    $table->foreignId('inventory_item_id')->constrained()->cascadeOnDelete();
    $table->integer('quantity_delta')->comment('Signed. -2 = two units left stock.');
    $table->integer('quantity_after')->comment('Running balance snapshot for audit.');
    $table->string('reason', 32)
          ->comment('App\Enums\StockMovementReason: purchase|sale|return|adjustment|damage|recount|reservation_release');
    $table->nullableMorphs('reference');   // Order, StockReservation, manual adjustment
    $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
    $table->string('note', 255)->nullable();
    $table->timestamp('created_at')->useCurrent();

    $table->index(['inventory_item_id', 'created_at'], 'inventory_movements_item_created_index');
    $table->index('reason', 'inventory_movements_reason_index');
});
```

> Append-only: `created_at` only, no `updated_at`, and no `Model::update()` path. A ledger row that can be edited is not a ledger.

```php
// 2026_08_10_000630_create_stock_reservations_table.php
Schema::create('stock_reservations', function (Blueprint $table) {
    $table->id();
    $table->foreignId('product_variant_id')->constrained()->cascadeOnDelete();
    $table->foreignId('inventory_item_id')->constrained()->cascadeOnDelete();
    $table->morphs('reservable');          // Cart during checkout, Order once placed
    $table->unsignedInteger('quantity');
    $table->string('status', 20)->default('held')
          ->comment('App\Enums\ReservationStatus: held|committed|released|expired');
    $table->timestamp('expires_at')->nullable();
    $table->timestamps();

    $table->index(['status', 'expires_at'], 'stock_reservations_status_expires_index');
    $table->index(['product_variant_id', 'status'], 'stock_reservations_variant_status_index');
});
```

> Reservations are created **at the checkout step, not on add-to-cart**. Reserving on add-to-cart lets a single visitor with a browser tab open starve the catalogue. See §5.5 for the exact locking sequence.

---

### 1.6 Carts

Carts are **database-backed, not session-backed**. Session-only carts die with the session, cannot be recovered, cannot be reported on, and make guest→login merge (§5.3) impossible to do correctly. The session/cookie holds only a `token`.

```php
// 2026_08_10_000720_create_carts_table.php
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('carts', function (Blueprint $table) {
            $table->id();
            $table->ulid('token')->comment('Opaque public identifier stored in the cart cookie.');
            $table->foreignId('user_id')->nullable()->constrained()->cascadeOnDelete();
            $table->string('status', 20)->default('active')
                  ->comment('App\Enums\CartStatus: active|converted|abandoned|merged');
            $table->char('currency', 3)->default('PKR');

            $table->foreignId('coupon_id')->nullable()->constrained()->nullOnDelete();
            $table->string('coupon_code', 40)->nullable()->comment('Snapshot — survives coupon deletion.');

            $table->unsignedBigInteger('subtotal_amount')->default(0);
            $table->unsignedBigInteger('discount_amount')->default(0);
            $table->unsignedBigInteger('shipping_amount')->default(0);
            $table->unsignedBigInteger('tax_amount')->default(0);
            $table->unsignedBigInteger('grand_total_amount')->default(0);
            $table->unsignedInteger('items_count')->default(0);

            $table->string('email', 180)->nullable()->comment('Captured at checkout step 1 — enables abandoned-cart email.');
            $table->foreignId('merged_into_cart_id')->nullable()
                  ->constrained('carts')->nullOnDelete();
            $table->string('ip_address', 45)->nullable();
            $table->string('user_agent', 512)->nullable();
            $table->timestamp('last_activity_at')->nullable();
            $table->timestamp('expires_at')->nullable();
            $table->timestamp('abandoned_email_sent_at')->nullable();
            $table->timestamps();

            $table->unique('token', 'carts_token_unique');
            $table->index(['user_id', 'status'], 'carts_user_status_index');
            $table->index(['status', 'last_activity_at'], 'carts_status_activity_index');
            $table->index(['status', 'expires_at'], 'carts_status_expires_index');
        });
    }

    public function down(): void { Schema::dropIfExists('carts'); }
};

// 2026_08_10_000950_add_converted_order_id_to_carts_table.php  (deferred — breaks the FK cycle)
Schema::table('carts', function (Blueprint $table) {
    $table->foreignId('converted_order_id')->nullable()->after('merged_into_cart_id')
          ->constrained('orders')->nullOnDelete();
});
```

> A partial unique index of the form "one active cart per user" is not expressible in MySQL. Enforce it in `CartManager::forUser()` inside a transaction with `lockForUpdate()`, and let a nightly job collapse any duplicates that slip through.

```php
// 2026_08_10_000730_create_cart_items_table.php
Schema::create('cart_items', function (Blueprint $table) {
    $table->id();
    $table->foreignId('cart_id')->constrained()->cascadeOnDelete();
    $table->foreignId('product_id')->constrained()->cascadeOnDelete();
    $table->foreignId('product_variant_id')->constrained()->cascadeOnDelete();
    $table->unsignedInteger('quantity');
    $table->unsignedBigInteger('unit_price_amount')
          ->comment('Snapshot at add-to-cart. Revalidated against the live price at checkout.');
    $table->unsignedBigInteger('line_total_amount')->default(0);
    $table->string('name_snapshot', 200);
    $table->json('options_snapshot')->nullable()
          ->comment('[{"attribute":"Shade","value":"Nude Rose","hex":"#C08081"}] — renders the cart without joins.');
    $table->string('image_path_snapshot')->nullable();
    $table->timestamps();

    $table->unique(['cart_id', 'product_variant_id'], 'cart_items_cart_variant_unique');
    $table->index('product_variant_id', 'cart_items_variant_index');
});

DB::statement("ALTER TABLE cart_items ADD CONSTRAINT cart_items_quantity_check CHECK (quantity > 0)");
```

> The `unique(cart_id, product_variant_id)` constraint is what makes "add the same shade twice" an atomic `upsert` that increments quantity, rather than a read-then-write race that produces duplicate lines.
>
> The `_snapshot` columns are **presentation cache, not the source of truth**. They let the mini-cart render from one query with zero joins — which matters because it appears in the header on every page. The authoritative price is always re-read at checkout (§5.5).

---

### 1.7 Coupons

```php
// 2026_08_10_000700_create_coupons_table.php
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('coupons', function (Blueprint $table) {
            $table->id();
            $table->string('code', 40)->comment('Stored uppercase. Normalised by a mutator.');
            $table->string('name', 120)->nullable()->comment('Internal label for the admin list.');
            $table->string('description', 255)->nullable()->comment('Shown to the customer on apply.');
            $table->string('type', 20)
                  ->comment('App\Enums\CouponType: percentage|fixed_amount|free_shipping');
            $table->unsignedInteger('percentage_value')->nullable()->comment('Basis points: 1500 = 15.00%');
            $table->unsignedBigInteger('fixed_amount')->nullable()->comment('paisa');
            $table->unsignedBigInteger('max_discount_amount')->nullable()
                  ->comment('paisa. Caps a percentage coupon. Without this, 50%% off a bulk order is unbounded.');
            $table->unsignedBigInteger('min_subtotal_amount')->default(0);

            $table->string('applies_to', 20)->default('all')
                  ->comment('App\Enums\CouponScope: all|products|categories');
            $table->boolean('exclude_discounted_items')->default(false)
                  ->comment('Prevents stacking a coupon on top of a compare_at markdown.');

            $table->unsignedInteger('usage_limit')->nullable()->comment('NULL = unlimited');
            $table->unsignedInteger('usage_limit_per_customer')->nullable();
            $table->unsignedInteger('used_count')->default(0);
            $table->boolean('first_order_only')->default(false);

            $table->timestamp('starts_at')->nullable();
            $table->timestamp('ends_at')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
            $table->softDeletes();

            $table->unique('code', 'coupons_code_unique');
            $table->index(['is_active', 'starts_at', 'ends_at'], 'coupons_active_window_index');
        });

        DB::statement("
            ALTER TABLE coupons ADD CONSTRAINT coupons_value_check CHECK (
                (type = 'percentage'   AND percentage_value IS NOT NULL AND percentage_value BETWEEN 1 AND 10000) OR
                (type = 'fixed_amount' AND fixed_amount IS NOT NULL AND fixed_amount > 0) OR
                (type = 'free_shipping')
            )
        ");
    }

    public function down(): void { Schema::dropIfExists('coupons'); }
};
```

> **Percentages are stored in basis points** (`1500` = 15.00%), same reasoning as money: no floats anywhere in the discount pipeline.

```php
// 2026_08_10_000710_create_coupon_scope_tables.php
Schema::create('coupon_product', function (Blueprint $table) {
    $table->foreignId('coupon_id')->constrained()->cascadeOnDelete();
    $table->foreignId('product_id')->constrained()->cascadeOnDelete();
    $table->boolean('is_excluded')->default(false)->comment('Allow-list by default; true flips to deny-list.');
    $table->primary(['coupon_id', 'product_id']);
});

Schema::create('category_coupon', function (Blueprint $table) {
    $table->foreignId('coupon_id')->constrained()->cascadeOnDelete();
    $table->foreignId('category_id')->constrained()->cascadeOnDelete();
    $table->boolean('is_excluded')->default(false);
    $table->boolean('include_descendants')->default(true);
    $table->primary(['coupon_id', 'category_id']);
});

// 2026_08_10_000940_create_coupon_redemptions_table.php
Schema::create('coupon_redemptions', function (Blueprint $table) {
    $table->id();
    $table->foreignId('coupon_id')->constrained()->restrictOnDelete();
    $table->foreignId('order_id')->constrained()->cascadeOnDelete();
    $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
    $table->string('email', 180)->comment('Enforces per-customer limits for guest checkouts.');
    $table->unsignedBigInteger('discount_amount');
    $table->timestamp('redeemed_at')->useCurrent();

    $table->unique(['coupon_id', 'order_id'], 'coupon_redemptions_coupon_order_unique');
    $table->index(['coupon_id', 'user_id'], 'coupon_redemptions_coupon_user_index');
    $table->index(['coupon_id', 'email'], 'coupon_redemptions_coupon_email_index');
});
```

> `coupon_redemptions` — not a counter on `coupons` — is what makes `usage_limit_per_customer` enforceable for **guests**, who have no `user_id`. The `email` index is the only handle you have on a repeat guest. `coupons.used_count` remains as a denormalised fast path for the admin list, incremented in the same transaction.

---

### 1.8 Shipping

Deliberately minimal — enough for launch, shaped so a courier API (TCS, Leopards, M&P) becomes a driver later.

```php
// 2026_08_10_000800_create_shipping_zones_table.php
Schema::create('shipping_zones', function (Blueprint $table) {
    $table->id();
    $table->string('name', 120);                  // "Karachi", "Punjab", "Rest of Pakistan"
    $table->json('provinces')->nullable();
    $table->json('cities')->nullable()->comment('Lowercased city names. Matched before provinces.');
    $table->boolean('is_fallback')->default(false)->comment('Exactly one zone catches everything else.');
    $table->unsignedInteger('position')->default(0);
    $table->boolean('is_active')->default(true);
    $table->timestamps();

    $table->index(['is_active', 'position'], 'shipping_zones_active_position_index');
});

// 2026_08_10_000810_create_shipping_rates_table.php
Schema::create('shipping_rates', function (Blueprint $table) {
    $table->id();
    $table->foreignId('shipping_zone_id')->constrained()->cascadeOnDelete();
    $table->string('name', 120);                  // "Standard (3–5 days)"
    $table->string('type', 20)->default('flat')
          ->comment('App\Enums\ShippingRateType: flat|free_over|weight_based');
    $table->unsignedBigInteger('amount')->default(0)->comment('paisa');
    $table->unsignedBigInteger('free_over_subtotal_amount')->nullable();
    $table->unsignedInteger('min_weight_grams')->nullable();
    $table->unsignedInteger('max_weight_grams')->nullable();
    $table->unsignedBigInteger('cod_surcharge_amount')->default(0)
          ->comment('COD carries a real courier fee in Pakistan; make it explicit rather than baked in.');
    $table->unsignedTinyInteger('min_delivery_days')->nullable();
    $table->unsignedTinyInteger('max_delivery_days')->nullable();
    $table->boolean('is_active')->default(true);
    $table->unsignedInteger('position')->default(0);
    $table->timestamps();

    $table->index(['shipping_zone_id', 'is_active', 'position'], 'shipping_rates_zone_active_index');
});
```

---

### 1.9 Orders

An order is an **immutable commercial record**. Every field a customer or admin might later dispute is snapshotted at placement — product names, prices, addresses, and (§2.6) the halal certification that was advertised at the time of sale.

```php
// 2026_08_10_000900_create_orders_table.php
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('orders', function (Blueprint $table) {
            $table->id();
            $table->string('order_number', 24)
                  ->comment('Human-facing, e.g. GH-2608-0001427. Generated by OrderNumberGenerator.');
            $table->ulid('public_token')->comment('Unguessable key for guest order-tracking URLs.');

            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('cart_id')->nullable()->constrained()->nullOnDelete();

            // Contact snapshot — guests have no user row
            $table->string('email', 180);
            $table->string('phone', 20);
            $table->string('customer_name', 200);

            $table->string('status', 24)->default('pending')
                  ->comment('App\Enums\OrderStatus: pending|confirmed|shipped|delivered|cancelled|refunded');
            $table->string('payment_status', 24)->default('pending')
                  ->comment('App\Enums\PaymentStatus: pending|awaiting_verification|paid|partially_refunded|refunded|failed');

            $table->char('currency', 3)->default('PKR');
            $table->unsignedBigInteger('subtotal_amount')->default(0);
            $table->unsignedBigInteger('discount_amount')->default(0);
            $table->unsignedBigInteger('shipping_amount')->default(0);
            $table->unsignedBigInteger('cod_fee_amount')->default(0);
            $table->unsignedBigInteger('tax_amount')->default(0);
            $table->unsignedBigInteger('grand_total_amount')->default(0);
            $table->unsignedBigInteger('paid_amount')->default(0);
            $table->unsignedBigInteger('refunded_amount')->default(0);

            $table->foreignId('coupon_id')->nullable()->constrained()->nullOnDelete();
            $table->string('coupon_code', 40)->nullable();

            $table->string('payment_method', 40)
                  ->comment('Payment driver key: cod|bank_transfer|jazzcash|easypaisa. See §6.');
            $table->foreignId('shipping_rate_id')->nullable()->constrained()->nullOnDelete();
            $table->string('shipping_method_name', 120)->nullable();

            $table->string('tracking_number', 80)->nullable();
            $table->string('courier', 60)->nullable();
            $table->string('tracking_url', 512)->nullable();

            $table->text('customer_note')->nullable();
            $table->text('admin_note')->nullable();
            $table->string('cancel_reason', 255)->nullable();

            $table->timestamp('placed_at')->nullable();
            $table->timestamp('confirmed_at')->nullable();
            $table->timestamp('shipped_at')->nullable();
            $table->timestamp('delivered_at')->nullable();
            $table->timestamp('cancelled_at')->nullable();
            $table->timestamp('refunded_at')->nullable();

            $table->string('ip_address', 45)->nullable();
            $table->string('user_agent', 512)->nullable();
            $table->json('utm')->nullable()->comment('{"source":"facebook","medium":"cpc","campaign":"eid-2026"}');

            $table->timestamps();
            $table->softDeletes();

            $table->unique('order_number', 'orders_order_number_unique');
            $table->unique('public_token', 'orders_public_token_unique');
            $table->index(['status', 'created_at'], 'orders_status_created_index');
            $table->index(['payment_status', 'created_at'], 'orders_payment_status_created_index');
            $table->index(['user_id', 'created_at'], 'orders_user_created_index');
            $table->index('email', 'orders_email_index');
            $table->index('phone', 'orders_phone_index');
            $table->index(['payment_method', 'status'], 'orders_method_status_index');
        });
    }

    public function down(): void { Schema::dropIfExists('orders'); }
};
```

> **`order_number` vs `id` vs `public_token`.** Three identifiers, three jobs. `id` joins. `order_number` is what a customer reads out on the phone — sequential-looking and short. `public_token` is what appears in `/orders/track/{token}`, because a sequential `order_number` in a URL means any customer can enumerate every order in the system. Do not collapse these.
>
> **`cod_fee_amount` is a separate column, not folded into `shipping_amount`.** COD in Pakistan carries a courier collection fee of roughly 1–2%. Keeping it separate means you can report on the true cost of offering COD, and you can waive it in a promotion without corrupting shipping figures.

```php
// 2026_08_10_000910_create_order_addresses_table.php
Schema::create('order_addresses', function (Blueprint $table) {
    $table->id();
    $table->foreignId('order_id')->constrained()->cascadeOnDelete();
    $table->string('type', 12)->comment('shipping|billing');
    $table->foreignId('address_id')->nullable()->constrained()->nullOnDelete()
          ->comment('Provenance only. Never read for display — the snapshot below is authoritative.');
    $table->string('first_name', 100);
    $table->string('last_name', 100)->nullable();
    $table->string('phone', 20);
    $table->string('alternate_phone', 20)->nullable();
    $table->string('line_1', 255);
    $table->string('line_2', 255)->nullable();
    $table->string('area', 120)->nullable();
    $table->string('city', 120);
    $table->string('province', 40);
    $table->string('postal_code', 12)->nullable();
    $table->char('country_code', 2)->default('PK');
    $table->text('delivery_instructions')->nullable();
    $table->timestamps();

    $table->unique(['order_id', 'type'], 'order_addresses_order_type_unique');
});
```

> This table is the reason a customer editing their address book cannot retroactively change where a delivered order was sent. `address_id` is kept purely so you can answer "which saved address did they pick" — it must never be dereferenced for rendering an invoice.

```php
// 2026_08_10_000920_create_order_items_table.php
Schema::create('order_items', function (Blueprint $table) {
    $table->id();
    $table->foreignId('order_id')->constrained()->cascadeOnDelete();
    $table->foreignId('product_id')->nullable()->constrained()->nullOnDelete();
    $table->foreignId('product_variant_id')->nullable()->constrained()->nullOnDelete();

    $table->string('sku', 64);
    $table->string('product_name', 200);
    $table->string('variant_name', 200)->nullable();
    $table->string('product_slug', 220)->nullable()->comment('Reorder link. May 404 later — that is acceptable.');
    $table->json('options_snapshot')->nullable();
    $table->string('image_path_snapshot')->nullable();
    $table->json('halal_snapshot')->nullable()
          ->comment('Certification state advertised at purchase. See §2.6 — this is a compliance record.');

    $table->unsignedInteger('quantity');
    $table->unsignedBigInteger('unit_price_amount');
    $table->unsignedBigInteger('line_subtotal_amount');
    $table->unsignedBigInteger('line_discount_amount')->default(0);
    $table->unsignedBigInteger('line_tax_amount')->default(0);
    $table->unsignedBigInteger('line_total_amount');
    $table->unsignedBigInteger('unit_cost_amount')->nullable()->comment('Margin reporting snapshot.');

    $table->unsignedInteger('quantity_refunded')->default(0);
    $table->timestamps();

    $table->index('order_id', 'order_items_order_index');
    $table->index('product_id', 'order_items_product_index');
    $table->index('product_variant_id', 'order_items_variant_index');
    $table->index('sku', 'order_items_sku_index');
});

DB::statement("ALTER TABLE order_items ADD CONSTRAINT order_items_quantity_check CHECK (quantity > 0)");
```

> Every FK to the catalogue is `nullOnDelete()` and paired with a snapshot column. Deleting a discontinued product must never orphan or mutate a historical invoice. `sku` and `product_name` are the durable record; `product_id` is a convenience that may become `NULL`.
>
> `line_discount_amount` holds the **apportioned** share of the order-level coupon discount, computed once at placement using largest-remainder allocation so the item lines always sum exactly to `orders.discount_amount`.

```php
// 2026_08_10_000930_create_order_status_histories_table.php
Schema::create('order_status_histories', function (Blueprint $table) {
    $table->id();
    $table->foreignId('order_id')->constrained()->cascadeOnDelete();
    $table->string('from_status', 24)->nullable();
    $table->string('to_status', 24);
    $table->string('from_payment_status', 24)->nullable();
    $table->string('to_payment_status', 24)->nullable();
    $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete()
          ->comment('NULL = system transition (webhook, scheduled job).');
    $table->string('note', 500)->nullable();
    $table->boolean('customer_notified')->default(false);
    $table->timestamp('created_at')->useCurrent();

    $table->index(['order_id', 'created_at'], 'order_status_histories_order_created_index');
});
```

---

### 1.10 Payments

Payments are a **separate table with a one-to-many relationship to orders**, not columns on `orders`. This is what makes the driver interface in §6 work without a checkout rewrite: a bank transfer that gets rejected and re-submitted is two payment attempts against one order, and a future gateway that authorises then captures is two rows.

```php
// 2026_08_10_001000_create_bank_accounts_table.php
Schema::create('bank_accounts', function (Blueprint $table) {
    $table->id();
    $table->string('bank_name', 120);            // "Meezan Bank"
    $table->string('account_title', 160);
    $table->string('account_number', 40);
    $table->string('iban', 34)->nullable();
    $table->string('branch_code', 20)->nullable();
    $table->string('branch_name', 160)->nullable();
    $table->string('instructions', 500)->nullable();
    $table->boolean('is_active')->default(true);
    $table->unsignedInteger('position')->default(0);
    $table->timestamps();

    $table->index(['is_active', 'position'], 'bank_accounts_active_position_index');
});

// 2026_08_10_001010_create_payments_table.php
Schema::create('payments', function (Blueprint $table) {
    $table->id();
    $table->foreignId('order_id')->constrained()->cascadeOnDelete();
    $table->string('driver', 40)->comment('Payment driver key — matches config/payments.php');
    $table->string('status', 24)->default('pending')
          ->comment('App\Enums\PaymentAttemptStatus: pending|awaiting_verification|authorized|paid|failed|cancelled|refunded');
    $table->char('currency', 3)->default('PKR');
    $table->unsignedBigInteger('amount');
    $table->unsignedBigInteger('refunded_amount')->default(0);

    $table->string('reference', 120)->nullable()
          ->comment('Customer-supplied: bank transaction id, JazzCash TxnRefNo.');
    $table->string('gateway_transaction_id', 120)->nullable();
    $table->foreignId('bank_account_id')->nullable()->constrained()->nullOnDelete();
    $table->string('idempotency_key', 64)->nullable()
          ->comment('Guards against duplicate charges on webhook replay or double-submit.');

    $table->json('gateway_request')->nullable();
    $table->json('gateway_response')->nullable()->comment('Redact secrets before writing. See §6.6.');
    $table->string('failure_code', 60)->nullable();
    $table->string('failure_message', 500)->nullable();

    $table->foreignId('verified_by_user_id')->nullable()->constrained('users')->nullOnDelete();
    $table->timestamp('verified_at')->nullable();
    $table->timestamp('paid_at')->nullable();
    $table->timestamp('expires_at')->nullable()->comment('Manual transfers get a payment window.');
    $table->timestamps();

    $table->unique('idempotency_key', 'payments_idempotency_unique');
    $table->index(['order_id', 'status'], 'payments_order_status_index');
    $table->index(['driver', 'status'], 'payments_driver_status_index');
    $table->index('gateway_transaction_id', 'payments_gateway_txn_index');
});

// 2026_08_10_001020_create_payment_proofs_table.php
Schema::create('payment_proofs', function (Blueprint $table) {
    $table->id();
    $table->foreignId('payment_id')->constrained()->cascadeOnDelete();
    $table->string('disk', 32)->default('private');
    $table->string('path', 255);
    $table->string('original_filename', 255)->nullable();
    $table->string('mime_type', 64)->nullable();
    $table->unsignedInteger('size_bytes')->nullable();
    $table->unsignedBigInteger('declared_amount')->nullable();
    $table->date('declared_paid_on')->nullable();
    $table->string('declared_reference', 120)->nullable();
    $table->string('status', 20)->default('pending')->comment('pending|approved|rejected');
    $table->string('rejection_reason', 500)->nullable();
    $table->foreignId('reviewed_by_user_id')->nullable()->constrained('users')->nullOnDelete();
    $table->timestamp('reviewed_at')->nullable();
    $table->timestamps();

    $table->index(['payment_id', 'status'], 'payment_proofs_payment_status_index');
    $table->index('status', 'payment_proofs_status_index');
});
```

> **`payment_proofs.disk` defaults to `private`, not `public`.** A bank transfer receipt is a screenshot of someone's banking app showing their account number and balance. It must not be served from `storage/app/public`. Configure a `private` disk and serve these through a signed, policy-gated controller route — never a direct URL. This is the single most likely privacy incident in this application.

---

### 1.11 Content: blog and static pages

```php
// 2026_08_10_001100_create_blog_categories_table.php
Schema::create('blog_categories', function (Blueprint $table) {
    $table->id();
    $table->string('name', 120);
    $table->string('slug', 140);
    $table->string('description', 500)->nullable();
    $table->string('image_path')->nullable();
    $table->string('color', 7)->nullable();
    $table->unsignedInteger('position')->default(0);
    $table->boolean('is_active')->default(true);
    $table->unsignedInteger('posts_count')->default(0);
    $table->timestamps();

    $table->unique('slug', 'blog_categories_slug_unique');
    $table->index(['is_active', 'position'], 'blog_categories_active_position_index');
});

// 2026_08_10_001110_create_blog_posts_table.php
Schema::create('blog_posts', function (Blueprint $table) {
    $table->id();
    $table->foreignId('blog_category_id')->nullable()->constrained()->nullOnDelete();
    $table->foreignId('author_id')->nullable()->constrained('users')->nullOnDelete();
    $table->string('title', 220);
    $table->string('slug', 240);
    $table->string('excerpt', 500)->nullable()
          ->comment('Plain text. Feeds listing cards and the meta description fallback.');
    $table->longText('content')->nullable()->comment('Filament RichEditor output. See §4.6.');
    $table->string('cover_image_path')->nullable();
    $table->string('cover_image_alt', 255)->nullable();
    $table->string('status', 20)->default('draft')
          ->comment('App\Enums\PostStatus: draft|scheduled|published|archived');
    $table->timestamp('published_at')->nullable();
    $table->unsignedSmallInteger('reading_time_minutes')->nullable();
    $table->unsignedBigInteger('views_count')->default(0);
    $table->boolean('is_featured')->default(false);
    $table->boolean('allow_comments')->default(false);
    $table->timestamps();
    $table->softDeletes();

    $table->unique('slug', 'blog_posts_slug_unique');
    $table->index(['status', 'published_at'], 'blog_posts_status_published_index');
    $table->index(['blog_category_id', 'status', 'published_at'], 'blog_posts_category_status_index');
    $table->index(['status', 'is_featured'], 'blog_posts_featured_index');
    $table->fullText(['title', 'excerpt'], 'blog_posts_fulltext_index');
});
```

> `status = 'scheduled'` plus `published_at` in the future is handled by a scheduled command that flips rows to `published`, **and** by the `published()` scope requiring `published_at <= now()`. Both, not either: the scope guarantees correctness even if the scheduler is down, and the flip makes the admin list honest.

```php
// 2026_08_10_001120_create_tags_table.php  +  001130_create_taggables_table.php
Schema::create('tags', function (Blueprint $table) {
    $table->id();
    $table->string('name', 80);
    $table->string('slug', 100);
    $table->string('type', 40)->nullable()->comment('blog|product — keeps namespaces separate.');
    $table->unsignedInteger('usage_count')->default(0);
    $table->timestamps();

    $table->unique(['slug', 'type'], 'tags_slug_type_unique');
});

Schema::create('taggables', function (Blueprint $table) {
    $table->foreignId('tag_id')->constrained()->cascadeOnDelete();
    $table->morphs('taggable');
    $table->primary(['tag_id', 'taggable_id', 'taggable_type'], 'taggables_primary');
});

// 2026_08_10_001140_create_blog_post_product_table.php  — "shop this article"
Schema::create('blog_post_product', function (Blueprint $table) {
    $table->foreignId('blog_post_id')->constrained()->cascadeOnDelete();
    $table->foreignId('product_id')->constrained()->cascadeOnDelete();
    $table->unsignedInteger('position')->default(0);
    $table->primary(['blog_post_id', 'product_id']);
    $table->index('product_id', 'blog_post_product_product_index');
});
```

> `blog_post_product` is not decoration. Editorial→product internal links are the mechanism by which blog authority flows to commercial pages, and they are the highest-converting placement on a cosmetics site. Build it in Phase 6, but reserve the table now.

```php
// 2026_08_10_001150_create_pages_table.php
Schema::create('pages', function (Blueprint $table) {
    $table->id();
    $table->string('title', 200);
    $table->string('slug', 220);
    $table->longText('content')->nullable();
    $table->string('template', 40)->default('default')
          ->comment('App\Enums\PageTemplate: default|full_width|contact|faq');
    $table->string('status', 20)->default('draft')->comment('draft|published');
    $table->timestamp('published_at')->nullable();
    $table->boolean('is_system')->default(false)
          ->comment('Privacy, terms, returns. Blocks deletion — checkout links to these.');
    $table->boolean('show_in_footer')->default(false);
    $table->boolean('show_in_header')->default(false);
    $table->unsignedInteger('position')->default(0);
    $table->timestamps();
    $table->softDeletes();

    $table->unique('slug', 'pages_slug_unique');
    $table->index(['status', 'show_in_footer'], 'pages_status_footer_index');
});
```

> `is_system` protects the pages that checkout and the footer link to. A "Terms & Conditions" page deleted by accident becomes a 404 inside the checkout flow — enforce it in the model's `deleting` hook, not just in the UI.

---

### 1.12 Settings

Use `spatie/laravel-settings` (3.9.0) with `filament/spatie-laravel-settings-plugin` (v5.7.6, confirmed released for Filament 5). Publish its migration:

```php
// 2026_08_10_001300_create_settings_table.php  (vendor-published, do not hand-write)
Schema::create('settings', function (Blueprint $table) {
    $table->id();
    $table->string('group');
    $table->string('name');
    $table->boolean('locked')->default(false);
    $table->json('payload');
    $table->timestamps();

    $table->unique(['group', 'name']);
});
```

Settings are then **typed PHP classes**, not string lookups:

```php
namespace App\Settings;

use Spatie\LaravelSettings\Settings;

class StoreSettings extends Settings
{
    public string $store_name;
    public string $support_email;
    public string $support_phone;
    public string $whatsapp_number;
    public bool $cod_enabled;
    public bool $bank_transfer_enabled;
    public int $free_shipping_threshold_amount;   // paisa
    public int $cod_fee_amount;                   // paisa
    public int $low_stock_threshold;
    public bool $maintenance_mode;

    public static function group(): string { return 'store'; }
}

class SeoSettings extends Settings
{
    public string $default_meta_title;
    public string $default_meta_description;
    public ?string $default_og_image_path;
    public ?string $google_site_verification;
    public ?string $gtm_container_id;
    public ?string $facebook_pixel_id;
    public bool $noindex_entire_site;              // staging safety switch

    public static function group(): string { return 'seo'; }
}
```

A typed settings class gives IDE completion, real casts, and validation on save — a `key`/`value` string table gives you `(bool) '0' === true` bugs at 2am. The one cost is that adding a property requires a settings migration; that is a fair trade.

---

### 1.13 Optional Phase 6 tables

Included for completeness; not required for launch.

```php
Schema::create('product_reviews', function (Blueprint $table) {
    $table->id();
    $table->foreignId('product_id')->constrained()->cascadeOnDelete();
    $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
    $table->foreignId('order_id')->nullable()->constrained()->nullOnDelete()
          ->comment('Present = verified purchase, which is what earns the rich-snippet badge.');
    $table->string('author_name', 120);
    $table->unsignedTinyInteger('rating')->comment('1–5');
    $table->string('title', 200)->nullable();
    $table->text('body')->nullable();
    $table->json('image_paths')->nullable();
    $table->string('status', 20)->default('pending')->comment('pending|approved|rejected|spam');
    $table->unsignedInteger('helpful_count')->default(0);
    $table->timestamps();

    $table->index(['product_id', 'status', 'created_at'], 'product_reviews_product_status_index');
    $table->unique(['product_id', 'order_id', 'user_id'], 'product_reviews_one_per_purchase_unique');
});

DB::statement("ALTER TABLE product_reviews ADD CONSTRAINT product_reviews_rating_check CHECK (rating BETWEEN 1 AND 5)");

Schema::create('newsletter_subscribers', function (Blueprint $table) {
    $table->id();
    $table->string('email', 180);
    $table->string('name', 120)->nullable();
    $table->string('status', 20)->default('pending')->comment('pending|subscribed|unsubscribed|bounced');
    $table->string('confirmation_token', 64)->nullable();
    $table->timestamp('confirmed_at')->nullable();
    $table->timestamp('unsubscribed_at')->nullable();
    $table->string('source', 40)->nullable();
    $table->timestamps();

    $table->unique('email', 'newsletter_subscribers_email_unique');
    $table->index('status', 'newsletter_subscribers_status_index');
});
```

> `product_reviews` is what unlocks `AggregateRating` structured data on product pages — star ratings in Google results. Reviews must be **moderated before publication** (`status`), and only `order_id`-backed reviews should be marked `verified`, because Google's review-snippet policy treats unverifiable seller-generated reviews as a manual-action risk.
---

## 2. The Halal Data Model

This is the part of the schema that carries the brand. Modelled as five related tables rather than a `halal_info` text column, because every one of these facts needs to be **queried, filtered, faceted, expired, audited, and rendered as structured data** — none of which a blob supports.

Concretely, the structure below is what makes these possible:

- `/collections/alcohol-free-lipstick` as a real indexable landing page, generated from data rather than hand-built
- "Show me products with no animal-derived ingredients" as an indexed query, not a full-table `LIKE`
- An admin alert 60 days before a halal certificate expires — before a customer finds an expired certificate on a live product page
- An ingredient glossary (`/ingredients/carmine`) — long-tail organic search on exactly the questions this audience asks
- A defensible record of what was claimed at the moment of each sale

### 2.1 Certification bodies

```php
// 2026_08_10_000300_create_certification_bodies_table.php
Schema::create('certification_bodies', function (Blueprint $table) {
    $table->id();
    $table->string('name', 180);                 // "SANHA Halal Associates Pakistan"
    $table->string('short_name', 40)->nullable();// "SANHA"
    $table->string('slug', 200);
    $table->char('country_code', 2)->nullable();
    $table->string('website', 255)->nullable();
    $table->string('verification_url_template', 512)->nullable()
          ->comment('e.g. https://body.example/verify?cert={certificate_number} — {certificate_number} is substituted.');
    $table->string('logo_path')->nullable();
    $table->string('logo_alt', 255)->nullable();
    $table->text('description')->nullable();
    $table->string('accreditation', 255)->nullable()
          ->comment('Accrediting authority, if any. Verify before publishing — see note below.');
    $table->boolean('is_recognised')->default(true)
          ->comment('False = listed for transparency but not presented as an authority.');
    $table->boolean('is_active')->default(true);
    $table->unsignedInteger('position')->default(0);
    $table->timestamps();

    $table->unique('slug', 'certification_bodies_slug_unique');
    $table->index(['is_active', 'position'], 'certification_bodies_active_position_index');
});
```

> **Seed this table with real, verified bodies only.** For the Pakistani market the relevant names include the national regulator (Pakistan Halal Authority) and provincial/private certifiers, plus international bodies whose marks appear on imported stock (JAKIM, MUI/BPJPH, IFANCA, ESMA). **Confirm each body's exact legal name, jurisdiction, and accreditation status with the client before seeding** — publishing a certifier's name and logo incorrectly is a legal and reputational exposure, not a data-quality issue. The schema is correct regardless of which bodies you seed; do not let me pick them for you.
>
> `verification_url_template` is worth the column: linking each certificate number to the issuing body's own lookup page converts an unverifiable claim into a checkable one, which is the entire trust proposition.

### 2.2 Product certifications

```php
// 2026_08_10_000520_create_product_certifications_table.php
Schema::create('product_certifications', function (Blueprint $table) {
    $table->id();
    $table->foreignId('product_id')->constrained()->cascadeOnDelete();
    $table->foreignId('certification_body_id')->constrained()->restrictOnDelete();
    $table->string('certificate_number', 120);
    $table->string('scope', 255)->nullable()
          ->comment('What the certificate actually covers — the product, the facility, or the manufacturer.');
    $table->date('issued_at')->nullable();
    $table->date('expires_at')->nullable();
    $table->string('status', 20)->default('active')
          ->comment('App\Enums\CertificationStatus: active|expiring|expired|pending|revoked');

    $table->string('document_disk', 32)->default('public');
    $table->string('document_path', 255)->nullable();
    $table->string('document_mime', 64)->nullable();
    $table->unsignedInteger('document_size_bytes')->nullable();
    $table->string('document_alt', 255)->nullable();
    $table->boolean('is_publicly_visible')->default(true)
          ->comment('Some certificates carry supplier commercial terms — allow uploading without publishing.');

    $table->string('verification_url', 512)->nullable()->comment('Resolved from the body template, or overridden.');
    $table->text('notes')->nullable()->comment('Internal. Never rendered to the storefront.');
    $table->foreignId('added_by_user_id')->nullable()->constrained('users')->nullOnDelete();
    $table->timestamps();

    $table->unique(['product_id', 'certification_body_id', 'certificate_number'], 'product_certifications_unique');
    $table->index(['product_id', 'status'], 'product_certifications_product_status_index');
    $table->index(['status', 'expires_at'], 'product_certifications_status_expires_index');
    $table->index('expires_at', 'product_certifications_expires_index');
});
```

> **`expires_at` is the operationally important column.** A daily scheduled command (`halal:sync-certificate-status`) moves `active → expiring` at 60 days out, `expiring → expired` on the date, notifies admins via a Filament database notification, and — critically — an expired certificate must stop being rendered as a live claim on the product page while remaining in the record. The `status` column carries that; do not compute expiry inline in a Blade template, because then a page cached for an hour keeps advertising an expired certificate.
>
> `scope` exists because "this manufacturer's facility is certified" and "this specific SKU is certified" are very different claims, and conflating them is the most common way halal marketing becomes misleading.

### 2.3 Ingredients

Ingredients are shared entities, not per-product strings. One `carmine` row, referenced by every product that contains it — so flipping its `halal_status` updates every product at once, and `/ingredients/carmine` becomes a page.

```php
// 2026_08_10_000310_create_ingredients_table.php
Schema::create('ingredients', function (Blueprint $table) {
    $table->id();
    $table->string('name', 180)->comment('Common name, e.g. "Carmine"');
    $table->string('slug', 200);
    $table->string('inci_name', 200)->nullable()
          ->comment('INCI standard name, e.g. "CI 75470". The label-accurate identifier.');
    $table->string('cas_number', 40)->nullable();
    $table->string('ec_number', 40)->nullable();
    $table->json('aliases')->nullable()->comment('["Cochineal","Crimson Lake","Natural Red 4"]');

    $table->string('origin', 24)->nullable()
          ->comment('App\Enums\IngredientOrigin: plant|mineral|synthetic|marine|microbial|animal|unknown');
    $table->string('halal_status', 24)->default('unknown')
          ->comment('App\Enums\HalalStatus: halal|haram|mashbooh|depends_on_source|not_applicable|unknown');
    $table->text('halal_notes')->nullable()
          ->comment('Why. e.g. "Glycerin may be plant- or tallow-derived; source verification required."');

    $table->boolean('is_animal_derived')->default(false);
    $table->boolean('is_alcohol')->default(false)
          ->comment('Intoxicating alcohol only. Fatty alcohols (cetyl, cetearyl) are NOT flagged here.');
    $table->string('function', 80)->nullable()->comment('emollient|surfactant|colourant|preservative|humectant');
    $table->text('description')->nullable()->comment('Short summary. Feeds cards and meta description fallback.');
    $table->text('benefits')->nullable();
    $table->boolean('is_common_allergen')->default(false);
    $table->boolean('is_key_ingredient_candidate')->default(false)
          ->comment('Marketing-worthy actives, e.g. niacinamide — surfaced on product pages.');

    // ---- Editorial content page: /halal-ingredients/{slug} — see §2.7 ----
    $table->longText('content')->nullable()
          ->comment('Filament RichEditor body for the standalone ingredient page.');
    $table->string('verdict_summary', 500)->nullable()
          ->comment('The one-line answer, rendered above the fold: "Carmine is not halal — it is derived from insects."');
    $table->string('hero_image_path')->nullable();
    $table->string('hero_image_alt', 255)->nullable();
    $table->string('status', 20)->default('draft')->comment('App\Enums\PostStatus: draft|published|archived');
    $table->timestamp('published_at')->nullable();
    $table->foreignId('author_id')->nullable()->constrained('users')->nullOnDelete();
    $table->foreignId('reviewed_by_user_id')->nullable()->constrained('users')->nullOnDelete()
          ->comment('Who signed off the halal ruling. These pages make religious claims.');
    $table->timestamp('reviewed_at')->nullable();
    $table->unsignedSmallInteger('reading_time_minutes')->nullable();
    $table->unsignedBigInteger('views_count')->default(0);
    $table->boolean('has_glossary_page')->default(false)
          ->comment('True = a real page exists at /halal-ingredients/{slug}. Drives sitemap inclusion (§7.5).');

    $table->unsignedInteger('products_count')->default(0);
    $table->timestamps();
    $table->softDeletes();

    $table->unique('slug', 'ingredients_slug_unique');
    $table->index('inci_name', 'ingredients_inci_index');
    $table->index(['halal_status', 'is_animal_derived'], 'ingredients_halal_animal_index');
    $table->index(['has_glossary_page', 'status', 'published_at'], 'ingredients_page_published_index');
    $table->fullText(['name', 'description'], 'ingredients_fulltext_index');
});

// Cross-links between ingredient pages — "see also: cochineal, shellac".
// Internal linking is what makes a 20-page content cluster rank as a cluster
// rather than as 20 unrelated orphans.
Schema::create('ingredient_related', function (Blueprint $table) {
    $table->foreignId('ingredient_id')->constrained('ingredients')->cascadeOnDelete();
    $table->foreignId('related_ingredient_id')->constrained('ingredients')->cascadeOnDelete();
    $table->unsignedInteger('position')->default(0);

    $table->primary(['ingredient_id', 'related_ingredient_id'], 'ingredient_related_primary');
    $table->index('related_ingredient_id', 'ingredient_related_reverse_index');
});
```

> **`depends_on_source` is a required status value, not a hedge.** It is the single most important distinction in halal cosmetics. Glycerin, stearic acid, mono- and diglycerides, collagen, and hyaluronic acid are each halal or not *entirely depending on feedstock* — plant vs. tallow, bio-fermented vs. rooster comb. A binary halal/haram field forces staff to guess, and guessing on this brand's core claim is the worst possible failure. `depends_on_source` on the ingredient plus a per-product source note (below) models reality.
>
> Similarly, `is_alcohol` deliberately excludes fatty alcohols. Cetyl and cetearyl alcohol are waxy emollients with no intoxicating properties and are broadly accepted; a naive `name LIKE '%alcohol%'` rule would incorrectly flag half the catalogue and destroy customer trust in the filter. This is why the column is a curated boolean and not a string match.

```php
// 2026_08_10_000510_create_ingredient_product_table.php
Schema::create('ingredient_product', function (Blueprint $table) {
    $table->foreignId('product_id')->constrained()->cascadeOnDelete();
    $table->foreignId('ingredient_id')->constrained()->restrictOnDelete();
    $table->unsignedInteger('position')->default(0)
          ->comment('INCI declaration order — descending concentration. Order is regulatory, preserve it.');
    $table->decimal('concentration_percent', 5, 2)->nullable();
    $table->boolean('is_key_ingredient')->default(false)->comment('Featured on the product page.');
    $table->string('source_note', 255)->nullable()
          ->comment('Resolves ingredients.halal_status = depends_on_source, e.g. "palm-derived glycerin".');
    $table->string('resolved_halal_status', 24)->nullable()
          ->comment('Per-product override of the ingredient default, once the source is verified.');
    $table->timestamps();

    $table->primary(['product_id', 'ingredient_id']);
    $table->index(['product_id', 'position'], 'ingredient_product_product_position_index');
    $table->index('ingredient_id', 'ingredient_product_ingredient_index');
});
```

> `position` reproduces the legally-mandated INCI ordering from the physical packaging. Never re-sort this alphabetically for display — the order carries information (concentration), and a customer comparing the site to the box must see the same sequence.
>
> `resolved_halal_status` is the escape hatch that makes `depends_on_source` actionable: the ingredient says "depends", the pivot records what this particular formulation actually uses, and the product's overall status derives from the resolved values.

### 2.4 "Free from" attributes

These are marketing claims with regulatory weight, so each one is a row with **provenance** — who verified it, when, and on what evidence.

```php
// 2026_08_10_000320_create_free_from_attributes_table.php
Schema::create('free_from_attributes', function (Blueprint $table) {
    $table->id();
    $table->string('code', 48)->comment('alcohol_free, carmine_free, gelatin_free, ...');
    $table->string('name', 100)->comment('"Alcohol-Free"');
    $table->string('slug', 120)->comment('Drives /collections/{slug} landing pages.');
    $table->string('short_description', 255)->nullable();
    $table->text('description')->nullable()->comment('Landing-page body copy. Substantial text ranks.');
    $table->string('icon_path')->nullable();
    $table->string('badge_color', 7)->nullable();
    $table->boolean('is_filterable')->default(true)->comment('Appears in the catalogue facet sidebar.');
    $table->boolean('has_landing_page')->default(false)->comment('Included in the sitemap when true.');
    $table->boolean('requires_verification')->default(true)
          ->comment('True = the claim cannot be published until a staff member signs off.');
    $table->unsignedInteger('products_count')->default(0);
    $table->unsignedInteger('position')->default(0);
    $table->timestamps();

    $table->unique('code', 'free_from_attributes_code_unique');
    $table->unique('slug', 'free_from_attributes_slug_unique');
    $table->index(['is_filterable', 'position'], 'free_from_attributes_filterable_index');
});
```

Seed set (all directly relevant to halal cosmetics, and each one a genuine purchase driver for this audience):

| `code` | Why it matters |
|---|---|
| `alcohol_free` | Ethanol/denatured alcohol; the most-asked question in this category |
| `carmine_free` | CI 75470 — crushed cochineal insect. Ubiquitous in reds and pinks |
| `gelatin_free` | Porcine gelatin in capsules, gels, some setting products |
| `pork_derivative_free` | Umbrella claim covering tallow, porcine collagen, stearates |
| `animal_derived_free` | Stricter than vegan-adjacent claims; derived from ingredient data |
| `wudu_friendly` | Water-permeable formulation permitting ablution — see §2.5 |
| `paraben_free` | Not halal-specific, but table stakes in clean beauty |
| `sulfate_free` | SLS/SLES |
| `cruelty_free` | No animal testing |
| `vegan` | No animal-derived inputs at all |
| `fragrance_free` | Fragrance can carry undeclared alcohol carriers |
| `shellac_free` | Lac-insect resin, common in mascara and nail products |

```php
// 2026_08_10_000530_create_free_from_attribute_product_table.php
Schema::create('free_from_attribute_product', function (Blueprint $table) {
    $table->foreignId('free_from_attribute_id')->constrained()->cascadeOnDelete();
    $table->foreignId('product_id')->constrained()->cascadeOnDelete();
    $table->boolean('is_verified')->default(false);
    $table->foreignId('verified_by_user_id')->nullable()->constrained('users')->nullOnDelete();
    $table->timestamp('verified_at')->nullable();
    $table->string('evidence_note', 500)->nullable()
          ->comment('"Confirmed with supplier, email 2026-07-14" / "Derived from full INCI review".');
    $table->timestamps();

    $table->primary(['free_from_attribute_id', 'product_id'], 'ffap_primary');
    $table->index(['product_id', 'is_verified'], 'ffap_product_verified_index');
    $table->index(['free_from_attribute_id', 'is_verified'], 'ffap_attribute_verified_index');
});
```

> **Only `is_verified = true` rows render on the storefront.** An unverified claim is an internal to-do, not a badge. The storefront relationship is scoped accordingly (§3.4), so it is impossible to accidentally publish an unverified claim by forgetting a `where` in a Blade file — the scope lives on the relation.

**Automatic derivation, with a human gate.** A `DeriveFreeFromClaims` job can propose claims by inspecting the product's ingredient list (no ingredient with `is_alcohol` → propose `alcohol_free`; no `is_animal_derived` → propose `animal_derived_free`). It writes rows with `is_verified = false` and never publishes on its own. Derivation catches omissions; the human gate catches the case where the ingredient list itself is incomplete. Both are needed.

### 2.5 Product halal profile

A 1:1 record holding the product-level halal position that is not reducible to a single ingredient or certificate.

```php
// 2026_08_10_000500_create_product_halal_profiles_table.php
Schema::create('product_halal_profiles', function (Blueprint $table) {
    $table->id();
    $table->foreignId('product_id')->constrained()->cascadeOnDelete();

    $table->string('overall_status', 24)->default('unknown')
          ->comment('App\Enums\HalalStatus. Derived from ingredients + certifications, admin-overridable.');
    $table->boolean('is_certified')->default(false)
          ->comment('Denormalised: has at least one active product_certification. Maintained by observer.');
    $table->boolean('is_self_declared')->default(false)
          ->comment('True = manufacturer statement without third-party certification. Must be labelled as such.');

    $table->string('alcohol_status', 24)->default('none')
          ->comment('App\Enums\AlcoholStatus: none|fatty_alcohol_only|denatured|ethanol|unknown');
    $table->boolean('is_wudu_friendly')->default(false)
          ->comment('Water-permeable formulation permitting ablution. Applies to nail and long-wear products.');
    $table->boolean('is_vegan')->default(false);
    $table->boolean('is_cruelty_free')->default(false);

    $table->string('manufacturing_country', 2)->nullable();
    $table->string('manufacturer_name', 180)->nullable();
    $table->boolean('shared_facility_warning')->default(false)
          ->comment('Produced on lines also handling non-halal inputs. Disclose it.');

    $table->text('summary')->nullable()->comment('Customer-facing paragraph on the product page.');
    $table->text('internal_notes')->nullable();
    $table->timestamp('last_reviewed_at')->nullable();
    $table->foreignId('reviewed_by_user_id')->nullable()->constrained('users')->nullOnDelete();
    $table->timestamps();

    $table->unique('product_id', 'product_halal_profiles_product_unique');
    $table->index(['overall_status', 'is_certified'], 'php_status_certified_index');
    $table->index('is_wudu_friendly', 'php_wudu_index');
});
```

> **`is_wudu_friendly` and `alcohol_status` are the two fields that will differentiate this catalogue from every generic "halal" listing.** Breathable/water-permeable nail polish is a specific, searched-for product category, and `fatty_alcohol_only` lets the site answer the "but it says cetearyl alcohol on the label" objection directly on the page instead of losing the sale.
>
> **`is_self_declared` and `shared_facility_warning` exist to make honesty structurally easy.** A brand whose entire proposition is trust cannot afford a product page that implies third-party certification where only a supplier email exists. Model the weaker claim explicitly and render it as the weaker claim.

### 2.6 The purchase-time halal snapshot

`order_items.halal_snapshot` (§1.9) is written at order placement:

```json
{
  "overall_status": "halal",
  "is_certified": true,
  "is_self_declared": false,
  "alcohol_status": "none",
  "is_wudu_friendly": true,
  "certifications": [
    {
      "body": "SANHA Halal Associates Pakistan",
      "certificate_number": "SNH-2026-04471",
      "issued_at": "2026-01-12",
      "expires_at": "2027-01-11"
    }
  ],
  "free_from": ["alcohol_free", "carmine_free", "gelatin_free"],
  "captured_at": "2026-08-09T11:20:44+05:00"
}
```

This is not redundancy. Certificates expire, formulations get reformulated, and ingredient statuses get corrected. When a customer asks in six months "what was this certified as when I bought it?", the answer must come from the order record, not from the current state of the product — which may legitimately have changed. For a brand selling on religious compliance, this is the compliance record, and it is one JSON column.

### 2.7 The `/halal-ingredients/` content tree

The SEO specification calls for **18–25 standalone ingredient pages at `/halal-ingredients/{slug}`, separate from the blog**. The `ingredients` table above carries the extra columns that makes those real pages rather than glossary stubs: `content`, `verdict_summary`, `hero_image_path`, `status`, `published_at`, `author_id`, `reviewed_by_user_id`.

**These are `Ingredient` records, not `BlogPost` records, and that is the important structural decision.** The alternative — writing them as blog posts — is faster to build and wrong, because the same entity would then exist twice: once as the row that products link to for filtering, and once as the article a customer reads. The two would drift the first time an ingredient's halal status is corrected, and the product filter would disagree with the article explaining the filter. One row, two surfaces:

| Surface | Reads |
|---|---|
| Product page ingredient list | `name`, `inci_name`, `halal_status`, plus the pivot's `source_note` |
| `/halal-ingredients/{slug}` page | `verdict_summary`, `content`, `hero_image_path`, related ingredients |
| Catalogue filtering | `halal_status`, `is_animal_derived`, `is_alcohol` |
| `/halal-ingredients` index | all published rows, grouped by `halal_status` |

`has_glossary_page` gates sitemap inclusion, so the 18–25 written pages are indexed while the remaining ingredient rows — which exist purely to make product ingredient lists complete — are not. **This distinction is essential.** A cosmetics catalogue accumulates hundreds of INCI entries; publishing a thin auto-generated page for every one of them is precisely the doorway-page pattern that gets a content cluster devalued wholesale. Publish the 20 that answer a real question; keep the rest as data.

`reviewed_by_user_id` and `reviewed_at` exist because these pages make **religious rulings**, not product claims. "Is carmine halal?" is a page that will be cited, screenshotted, and argued with. Recording who approved the wording, and when, is a governance requirement rather than a nicety.

`ingredient_related` powers "see also" links between pages. A 20-page cluster with no internal links is 20 orphans; the same 20 pages cross-linked by relevance is a topical cluster, and the difference in how it ranks is substantial.

**Route and model notes:**

```php
Route::get('/halal-ingredients', [IngredientController::class, 'index'])->name('ingredients.index');
Route::get('/halal-ingredients/{ingredient:slug}', [IngredientController::class, 'show'])->name('ingredients.show');
```

```php
#[Scope]
protected function published(Builder $query): void
{
    $query->where('has_glossary_page', true)
          ->where('status', PostStatus::Published)
          ->where(fn (Builder $q) => $q->whereNull('published_at')->orWhere('published_at', '<=', now()));
}
```

Structured data for these pages is `DefinedTerm` inside a `DefinedTermSet`, with `BreadcrumbList`. Where a page directly answers a question ("Is carmine halal?"), add a single `FAQPage` block whose question and answer are **visibly rendered on the page** — invisible FAQ markup is a manual-action risk.
---

## 3. Eloquent Models

### 3.1 Laravel 13 idioms used here — and the Laravel 10/11 patterns they replace

The installed skeleton's own `App\Models\User` already demonstrates the current style, and it is worth matching exactly:

```php
#[Fillable(['name', 'email', 'password'])]
#[Hidden(['password', 'remember_token'])]
class User extends Authenticatable
{
    protected function casts(): array { /* ... */ }
}
```

| Do this (Laravel 13) | Not this (Laravel ≤10) |
|---|---|
| `#[Fillable([...])]` class attribute | `protected $fillable = [...]` |
| `#[Hidden([...])]` | `protected $hidden = [...]` |
| `#[Appends([...])]` | `protected $appends = [...]` |
| `#[Table('...')]`, `#[RouteKey('slug')]` | `protected $table`, `getRouteKeyName()` |
| `#[ObservedBy(ProductObserver::class)]` | `Product::observe()` in a service provider |
| `#[UseFactory(ProductFactory::class)]` | `protected static function newFactory()` |
| `#[Scope] public function active(Builder $q): void` | `public function scopeActive($query)` |
| `#[ScopedBy(PublishedScope::class)]` | `static::addGlobalScope()` in `booted()` |
| `protected function casts(): array` | `protected $casts = []` |
| `Attribute::get(fn () => …)` | `getFooAttribute()` |

`#[Scope]`-annotated methods are called by their **bare name** (`Product::active()`); the `scope` prefix is gone. Verified against `Illuminate\Database\Eloquent\Model::isScopeMethodWithAttribute()` in `vendor/`. The full attribute set available in this installation is: `Appends, Boot, CollectedBy, Connection, DateFormat, Fillable, Guarded, Hidden, Initialize, ObservedBy, RouteKey, Scope, ScopedBy, Table, Touches, Unguarded, UseEloquentBuilder, UseFactory, UsePolicy, UseResource, UseResourceCollection, Visible, WithoutIncrementing, WithoutTimestamps`.

### 3.2 Shared traits

**`HasSlug`** — `spatie/laravel-sluggable` 4.0.3, on every model with a public URL: `Product`, `Category`, `BlogPost`, `BlogCategory`, `Page`, `Ingredient`, `CertificationBody`, `FreeFromAttribute`, `AttributeValue`, `Tag`.

```php
namespace App\Models\Concerns;

use Spatie\Sluggable\SlugOptions;

trait HasSeoSlug
{
    public function getSlugOptions(): SlugOptions
    {
        return SlugOptions::create()
            ->generateSlugsFrom($this->slugSourceField())
            ->saveSlugsTo('slug')
            ->slugsShouldBeNoLongerThan(180)
            ->doNotGenerateSlugsOnUpdate();   // ← the SEO-critical line. See §7.1.
    }

    protected function slugSourceField(): string
    {
        return 'name';
    }
}
```

> `doNotGenerateSlugsOnUpdate()` is non-negotiable. Without it, renaming a product from "Matte Lipstick" to "Matte Lipstick — Limited Edition" silently changes its URL and discards every ranking and backlink it had. Slug changes must be a deliberate, separate admin action that records a redirect (§7.2).

**`HasSeoMeta`** — polymorphic SEO record (§7.3). **`HasSlugHistory`** — records superseded slugs (§7.2). **`RecordsActivity`** — optional audit trail.

**`MoneyCast`** — every `*_amount` column.

```php
namespace App\Casts;

use App\Support\Money;
use Illuminate\Contracts\Database\Eloquent\CastsAttributes;
use Illuminate\Database\Eloquent\Model;

/** @implements CastsAttributes<Money, Money|int|null> */
final class MoneyCast implements CastsAttributes
{
    public function get(Model $model, string $key, mixed $value, array $attributes): ?Money
    {
        return $value === null ? null : new Money((int) $value, $attributes['currency'] ?? 'PKR');
    }

    public function set(Model $model, string $key, mixed $value, array $attributes): ?int
    {
        return match (true) {
            $value === null   => null,
            $value instanceof Money => $value->minorUnits,
            is_int($value)    => $value,
            default => throw new \InvalidArgumentException(
                "Refusing to cast ".get_debug_type($value)." to money. Pass Money or integer paisa."
            ),
        };
    }
}
```

```php
namespace App\Support;

final readonly class Money implements \JsonSerializable, \Stringable
{
    public function __construct(public int $minorUnits, public string $currency = 'PKR') {}

    public static function fromRupees(int|float|string $rupees, string $currency = 'PKR'): self
    {
        return new self((int) round(((float) $rupees) * 100), $currency);
    }

    public function plus(self $other): self  { $this->assertSameCurrency($other); return new self($this->minorUnits + $other->minorUnits, $this->currency); }
    public function minus(self $other): self { $this->assertSameCurrency($other); return new self(max(0, $this->minorUnits - $other->minorUnits), $this->currency); }
    public function times(int $factor): self { return new self($this->minorUnits * $factor, $this->currency); }

    /** Basis points: 1500 = 15.00%. Rounds half-up at the single point where precision is lost. */
    public function percentage(int $basisPoints): self
    {
        return new self((int) round($this->minorUnits * $basisPoints / 10000), $this->currency);
    }

    public function isZero(): bool { return $this->minorUnits === 0; }
    public function toRupees(): float { return $this->minorUnits / 100; }

    public function format(bool $withSymbol = true): string
    {
        $formatted = number_format($this->minorUnits / 100, 0, '.', ',');   // PKR is quoted in whole rupees
        return $withSymbol ? "Rs {$formatted}" : $formatted;
    }

    public function __toString(): string { return $this->format(); }
    public function jsonSerialize(): array { return ['minor' => $this->minorUnits, 'currency' => $this->currency, 'formatted' => $this->format()]; }

    private function assertSameCurrency(self $other): void
    {
        if ($this->currency !== $other->currency) {
            throw new \InvalidArgumentException("Currency mismatch: {$this->currency} vs {$other->currency}");
        }
    }
}
```

> `minus()` clamps at zero deliberately — a discount larger than the subtotal must never produce a negative line. The clamp lives in one place rather than in every calculator.

### 3.3 Backed enums

All status columns cast to backed enums. Those rendered in Filament implement Filament's presentation contracts, so colour and label are defined once and reused by tables, infolists, and the storefront.

```php
namespace App\Enums;

use Filament\Support\Contracts\{HasColor, HasIcon, HasLabel};

enum OrderStatus: string implements HasLabel, HasColor, HasIcon
{
    case Pending   = 'pending';
    case Confirmed = 'confirmed';
    case Shipped   = 'shipped';
    case Delivered = 'delivered';
    case Cancelled = 'cancelled';
    case Refunded  = 'refunded';

    public function getLabel(): string
    {
        return match ($this) {
            self::Pending   => 'Pending',
            self::Confirmed => 'Confirmed',
            self::Shipped   => 'Shipped',
            self::Delivered => 'Delivered',
            self::Cancelled => 'Cancelled',
            self::Refunded  => 'Refunded',
        };
    }

    public function getColor(): string
    {
        return match ($this) {
            self::Pending   => 'warning',
            self::Confirmed => 'info',
            self::Shipped   => 'primary',
            self::Delivered => 'success',
            self::Cancelled => 'danger',
            self::Refunded  => 'gray',
        };
    }

    public function getIcon(): string
    {
        return match ($this) {
            self::Pending   => 'heroicon-o-clock',
            self::Confirmed => 'heroicon-o-check-circle',
            self::Shipped   => 'heroicon-o-truck',
            self::Delivered => 'heroicon-o-home',
            self::Cancelled => 'heroicon-o-x-circle',
            self::Refunded  => 'heroicon-o-arrow-uturn-left',
        };
    }

    /** The single source of truth for the state machine. See §5.7. */
    public function allowedTransitions(): array
    {
        return match ($this) {
            self::Pending   => [self::Confirmed, self::Cancelled],
            self::Confirmed => [self::Shipped, self::Cancelled, self::Refunded],
            self::Shipped   => [self::Delivered, self::Cancelled, self::Refunded],
            self::Delivered => [self::Refunded],
            self::Cancelled, self::Refunded => [],
        };
    }

    public function canTransitionTo(self $target): bool
    {
        return in_array($target, $this->allowedTransitions(), strict: true);
    }

    public function isTerminal(): bool
    {
        return $this->allowedTransitions() === [];
    }

    /** Stock has left the building — cancelling from here must restock. */
    public function hasConsumedStock(): bool
    {
        return in_array($this, [self::Confirmed, self::Shipped, self::Delivered], strict: true);
    }
}
```

Full enum list: `OrderStatus`, `PaymentStatus`, `PaymentAttemptStatus`, `ProductStatus`, `PostStatus`, `PageTemplate`, `CartStatus`, `CouponType`, `CouponScope`, `ShippingRateType`, `StockMovementReason`, `ReservationStatus`, `HalalStatus`, `AlcoholStatus`, `IngredientOrigin`, `CertificationStatus`, `AttributeType`, `PakistanProvince`.

### 3.4 `Product`

```php
namespace App\Models;

use App\Casts\MoneyCast;
use App\Enums\ProductStatus;
use App\Models\Concerns\{HasSeoMeta, HasSeoSlug, HasSlugHistory};
use App\Observers\ProductObserver;
use Illuminate\Database\Eloquent\Attributes\{ObservedBy, RouteKey, Scope, UseFactory};
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\{BelongsTo, BelongsToMany, HasMany, HasOne};
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\Sluggable\HasSlug;

#[UseFactory(\Database\Factories\ProductFactory::class)]
#[ObservedBy(ProductObserver::class)]
#[RouteKey('slug')]
class Product extends Model
{
    use HasFactory, HasSlug, HasSeoSlug, HasSlugHistory, HasSeoMeta, SoftDeletes;

    protected $guarded = ['id'];

    protected function casts(): array
    {
        return [
            'status'               => ProductStatus::class,
            'published_at'         => 'datetime',
            'price_min_amount'     => MoneyCast::class,
            'price_max_amount'     => MoneyCast::class,
            'compare_at_max_amount'=> MoneyCast::class,
            'is_featured'          => 'boolean',
            'is_new_arrival'       => 'boolean',
            'reviews_average'      => 'decimal:2',
        ];
    }

    // ---- Catalogue relations -------------------------------------------------
    public function primaryCategory(): BelongsTo { return $this->belongsTo(Category::class, 'primary_category_id'); }
    public function categories(): BelongsToMany { return $this->belongsToMany(Category::class)->withPivot('position')->orderByPivot('position'); }
    public function variants(): HasMany { return $this->hasMany(ProductVariant::class)->orderBy('position'); }
    public function images(): HasMany { return $this->hasMany(ProductImage::class)->orderBy('position'); }

    public function defaultVariant(): HasOne
    {
        return $this->hasOne(ProductVariant::class)->ofMany([
            'is_default' => 'max',
            'position'   => 'min',
        ], fn (Builder $q) => $q->where('is_active', true));
    }

    public function primaryImage(): HasOne
    {
        return $this->hasOne(ProductImage::class)->ofMany(['is_primary' => 'max', 'position' => 'min']);
    }

    // ---- Halal relations -----------------------------------------------------
    public function halalProfile(): HasOne { return $this->hasOne(ProductHalalProfile::class); }
    public function certifications(): HasMany { return $this->hasMany(ProductCertification::class); }

    public function activeCertifications(): HasMany
    {
        return $this->certifications()
            ->where('status', CertificationStatus::Active)
            ->where('is_publicly_visible', true);
    }

    public function ingredients(): BelongsToMany
    {
        return $this->belongsToMany(Ingredient::class)
            ->withPivot(['position', 'concentration_percent', 'is_key_ingredient', 'source_note', 'resolved_halal_status'])
            ->withTimestamps()
            ->orderByPivot('position');   // INCI order — never re-sort
    }

    public function keyIngredients(): BelongsToMany
    {
        return $this->ingredients()->wherePivot('is_key_ingredient', true);
    }

    public function freeFromAttributes(): BelongsToMany
    {
        return $this->belongsToMany(FreeFromAttribute::class)
            ->withPivot(['is_verified', 'verified_at', 'evidence_note'])
            ->withTimestamps();
    }

    /** Storefront-safe: only verified claims are ever renderable. */
    public function verifiedFreeFromAttributes(): BelongsToMany
    {
        return $this->freeFromAttributes()->wherePivot('is_verified', true)->orderBy('position');
    }

    public function reviews(): HasMany { return $this->hasMany(ProductReview::class); }
    public function blogPosts(): BelongsToMany { return $this->belongsToMany(BlogPost::class); }

    // ---- Scopes (Laravel 13 attribute form) ----------------------------------
    #[Scope]
    protected function published(Builder $query): void
    {
        $query->where('status', ProductStatus::Active)
              ->where(fn (Builder $q) => $q->whereNull('published_at')->orWhere('published_at', '<=', now()));
    }

    #[Scope]
    protected function inStock(Builder $query): void
    {
        $query->where('total_stock', '>', 0);
    }

    #[Scope]
    protected function inCategory(Builder $query, Category $category, bool $includeDescendants = true): void
    {
        $query->whereHas('categories', function (Builder $q) use ($category, $includeDescendants) {
            $includeDescendants
                ? $q->where('categories.id', $category->id)
                    ->orWhere('categories.path', 'like', $category->descendantPathPrefix())
                : $q->where('categories.id', $category->id);
        });
    }

    #[Scope]
    protected function freeFrom(Builder $query, string ...$codes): void
    {
        foreach ($codes as $code) {
            $query->whereHas('freeFromAttributes', fn (Builder $q) => $q
                ->where('code', $code)
                ->where('free_from_attribute_product.is_verified', true));
        }
    }

    #[Scope]
    protected function certified(Builder $query): void
    {
        $query->whereHas('halalProfile', fn (Builder $q) => $q->where('is_certified', true));
    }

    #[Scope]
    protected function search(Builder $query, string $term): void
    {
        $query->whereFullText(['name', 'short_description'], $term);
    }

    // ---- Accessors -----------------------------------------------------------
    protected function priceRange(): Attribute
    {
        return Attribute::get(fn (): string => $this->price_min_amount->minorUnits === $this->price_max_amount->minorUnits
            ? $this->price_min_amount->format()
            : "{$this->price_min_amount->format()} – {$this->price_max_amount->format()}"
        )->shouldCache();
    }

    protected function isOnSale(): Attribute
    {
        return Attribute::get(fn (): bool => $this->compare_at_max_amount !== null
            && $this->compare_at_max_amount->minorUnits > $this->price_max_amount->minorUnits
        )->shouldCache();
    }

    protected function url(): Attribute
    {
        return Attribute::get(fn (): string => route('products.show', $this->slug))->shouldCache();
    }
}
```

> `defaultVariant()` and `primaryImage()` use `HasOne::ofMany()` — a correlated subquery, so eager-loading a listing page of 24 products costs **two** queries, not 48. This is the difference between a 90ms and a 900ms category page. Never solve "give me the first image" with `->images->first()` in a Blade loop.
>
> `verifiedFreeFromAttributes()` exists as a separate relation rather than a `where` at the call site so that the unsafe version is never the convenient one.

### 3.5 `ProductVariant`

```php
#[ObservedBy(ProductVariantObserver::class)]
class ProductVariant extends Model
{
    use HasFactory, SoftDeletes;

    protected $guarded = ['id'];

    protected function casts(): array
    {
        return [
            'price_amount'      => MoneyCast::class,
            'compare_at_amount' => MoneyCast::class,
            'cost_amount'       => MoneyCast::class,
            'is_default'        => 'boolean',
            'is_active'         => 'boolean',
            'track_inventory'   => 'boolean',
            'allow_backorder'   => 'boolean',
        ];
    }

    public function product(): BelongsTo { return $this->belongsTo(Product::class); }
    public function images(): HasMany { return $this->hasMany(ProductImage::class); }
    public function inventoryItems(): HasMany { return $this->hasMany(InventoryItem::class); }

    public function attributeValues(): BelongsToMany
    {
        return $this->belongsToMany(AttributeValue::class)->with('attribute');
    }

    public function inventory(): HasOne
    {
        return $this->hasOne(InventoryItem::class)
            ->whereHas('location', fn (Builder $q) => $q->where('is_default', true));
    }

    #[Scope]
    protected function purchasable(Builder $query): void
    {
        $query->where('is_active', true)
              ->whereHas('product', fn (Builder $q) => $q->published());
    }

    protected function availableQuantity(): Attribute
    {
        return Attribute::get(fn (): int => $this->track_inventory
            ? (int) ($this->inventory?->quantity_available ?? 0)
            : PHP_INT_MAX
        )->shouldCache();
    }

    protected function isInStock(): Attribute
    {
        return Attribute::get(fn (): bool => $this->allow_backorder || $this->available_quantity > 0)->shouldCache();
    }

    /** Snapshot payload for cart_items.options_snapshot and order_items.options_snapshot. */
    public function optionsSnapshot(): array
    {
        return $this->attributeValues->map(fn (AttributeValue $v) => [
            'attribute' => $v->attribute->name,
            'value'     => $v->value,
            'hex'       => $v->hex_color,
        ])->all();
    }
}
```

> `available_quantity` returning `PHP_INT_MAX` when `track_inventory` is false is intentional — it removes every `if (!$variant->track_inventory)` branch from the cart, checkout, and reservation code. One expression, one place.

### 3.6 `Category`

```php
#[ObservedBy(CategoryObserver::class)]
#[RouteKey('slug')]
class Category extends Model
{
    use HasFactory, HasSlug, HasSeoSlug, HasSlugHistory, HasSeoMeta, SoftDeletes,
        \Staudenmeir\LaravelAdjacencyList\Eloquent\HasRecursiveRelationships;

    protected $guarded = ['id'];

    protected function casts(): array
    {
        return ['is_active' => 'boolean', 'is_featured' => 'boolean', 'show_in_menu' => 'boolean', 'depth' => 'integer'];
    }

    public function parent(): BelongsTo { return $this->belongsTo(self::class, 'parent_id'); }
    public function children(): HasMany { return $this->hasMany(self::class, 'parent_id')->orderBy('position'); }
    public function products(): BelongsToMany { return $this->belongsToMany(Product::class)->withPivot('position'); }

    #[Scope] protected function active(Builder $q): void   { $q->where('is_active', true); }
    #[Scope] protected function roots(Builder $q): void    { $q->whereNull('parent_id'); }
    #[Scope] protected function inMenu(Builder $q): void   { $q->where('is_active', true)->where('show_in_menu', true); }

    /** `'1/7/23/%'` — feeds the indexed descendant lookup used by Product::inCategory(). */
    public function descendantPathPrefix(): string
    {
        return trim(($this->path ? $this->path.'/' : '').$this->id, '/').'/%';
    }

    /** Breadcrumbs without recursion: one query, ordered by the materialised path. */
    public function ancestorTrail(): \Illuminate\Support\Collection
    {
        $ids = array_filter(explode('/', (string) $this->path));

        return $ids === []
            ? collect()
            : static::query()->whereIn('id', $ids)->orderByRaw('FIELD(id, '.implode(',', $ids).')')->get();
    }
}
```

`CategoryObserver` maintains `path` and `depth`:

```php
class CategoryObserver
{
    public function saving(Category $category): void
    {
        if (! $category->isDirty('parent_id') && $category->exists) {
            return;
        }

        $parent = $category->parent_id ? Category::find($category->parent_id) : null;
        $category->path  = $parent ? trim(($parent->path ? $parent->path.'/' : '').$parent->id, '/') : null;
        $category->depth = $parent ? $parent->depth + 1 : 0;
    }

    public function saved(Category $category): void
    {
        if ($category->wasChanged('path')) {
            // Re-materialise the subtree. Queue it — a deep move can touch many rows.
            RebuildCategorySubtreePaths::dispatch($category->id);
        }
    }

    public function deleting(Category $category): void
    {
        if ($category->children()->exists()) {
            throw new \App\Exceptions\CategoryHasChildrenException(
                "Re-parent or delete the {$category->children()->count()} child categories first."
            );
        }
    }
}
```

### 3.7 `Cart` and `CartItem`

```php
class Cart extends Model
{
    protected $guarded = ['id'];

    protected function casts(): array
    {
        return [
            'status'             => CartStatus::class,
            'subtotal_amount'    => MoneyCast::class,
            'discount_amount'    => MoneyCast::class,
            'shipping_amount'    => MoneyCast::class,
            'tax_amount'         => MoneyCast::class,
            'grand_total_amount' => MoneyCast::class,
            'last_activity_at'   => 'datetime',
            'expires_at'         => 'datetime',
        ];
    }

    protected static function booted(): void
    {
        static::creating(function (Cart $cart) {
            $cart->token ??= (string) \Illuminate\Support\Str::ulid();
            $cart->expires_at ??= now()->addDays(30);
            $cart->last_activity_at ??= now();
        });
    }

    public function items(): HasMany { return $this->hasMany(CartItem::class); }
    public function user(): BelongsTo { return $this->belongsTo(User::class); }
    public function coupon(): BelongsTo { return $this->belongsTo(Coupon::class); }
    public function convertedOrder(): BelongsTo { return $this->belongsTo(Order::class, 'converted_order_id'); }

    #[Scope] protected function active(Builder $q): void { $q->where('status', CartStatus::Active); }

    #[Scope]
    protected function abandonable(Builder $q): void
    {
        $q->where('status', CartStatus::Active)
          ->whereNotNull('email')
          ->whereNull('abandoned_email_sent_at')
          ->where('items_count', '>', 0)
          ->whereBetween('last_activity_at', [now()->subDays(7), now()->subHours(4)]);
    }

    public function isEmpty(): bool { return $this->items_count === 0; }
    public function getRouteKeyName(): string { return 'token'; }
}
```

```php
class CartItem extends Model
{
    protected $guarded = ['id'];

    protected function casts(): array
    {
        return [
            'unit_price_amount' => MoneyCast::class,
            'line_total_amount' => MoneyCast::class,
            'options_snapshot'  => 'array',
        ];
    }

    public function cart(): BelongsTo { return $this->belongsTo(Cart::class); }
    public function product(): BelongsTo { return $this->belongsTo(Product::class); }
    public function variant(): BelongsTo { return $this->belongsTo(ProductVariant::class, 'product_variant_id'); }

    /** True when the live price has moved since this line was added. Surfaced at checkout. */
    protected function hasPriceChanged(): Attribute
    {
        return Attribute::get(fn (): bool =>
            $this->variant !== null
            && $this->variant->price_amount->minorUnits !== $this->unit_price_amount->minorUnits
        );
    }
}
```

### 3.8 `Order`

```php
#[ObservedBy(OrderObserver::class)]
class Order extends Model
{
    use HasFactory, SoftDeletes;

    protected $guarded = ['id'];

    protected function casts(): array
    {
        return [
            'status'                 => OrderStatus::class,
            'payment_status'         => PaymentStatus::class,
            'subtotal_amount'        => MoneyCast::class,
            'discount_amount'        => MoneyCast::class,
            'shipping_amount'        => MoneyCast::class,
            'cod_fee_amount'         => MoneyCast::class,
            'tax_amount'             => MoneyCast::class,
            'grand_total_amount'     => MoneyCast::class,
            'paid_amount'            => MoneyCast::class,
            'refunded_amount'        => MoneyCast::class,
            'utm'                    => 'array',
            'placed_at'              => 'datetime',
            'confirmed_at'           => 'datetime',
            'shipped_at'             => 'datetime',
            'delivered_at'           => 'datetime',
            'cancelled_at'           => 'datetime',
            'refunded_at'            => 'datetime',
        ];
    }

    public function items(): HasMany { return $this->hasMany(OrderItem::class); }
    public function addresses(): HasMany { return $this->hasMany(OrderAddress::class); }
    public function shippingAddress(): HasOne { return $this->hasOne(OrderAddress::class)->where('type', 'shipping'); }
    public function billingAddress(): HasOne { return $this->hasOne(OrderAddress::class)->where('type', 'billing'); }
    public function payments(): HasMany { return $this->hasMany(Payment::class); }
    public function statusHistories(): HasMany { return $this->hasMany(OrderStatusHistory::class)->latest('created_at'); }
    public function user(): BelongsTo { return $this->belongsTo(User::class); }
    public function coupon(): BelongsTo { return $this->belongsTo(Coupon::class); }
    public function reservations(): MorphMany { return $this->morphMany(StockReservation::class, 'reservable'); }

    public function successfulPayment(): HasOne
    {
        return $this->hasOne(Payment::class)->where('status', PaymentAttemptStatus::Paid)->latestOfMany();
    }

    #[Scope] protected function placed(Builder $q): void      { $q->whereNotNull('placed_at'); }
    #[Scope] protected function awaitingPayment(Builder $q): void { $q->where('payment_status', PaymentStatus::AwaitingVerification); }
    #[Scope] protected function open(Builder $q): void        { $q->whereIn('status', [OrderStatus::Pending, OrderStatus::Confirmed, OrderStatus::Shipped]); }

    #[Scope]
    protected function forGuestToken(Builder $q, string $token): void
    {
        $q->where('public_token', $token);
    }

    protected function balanceDue(): Attribute
    {
        return Attribute::get(fn (): Money => $this->grand_total_amount->minus($this->paid_amount));
    }

    protected function isFullyPaid(): Attribute
    {
        return Attribute::get(fn (): bool => $this->paid_amount->minorUnits >= $this->grand_total_amount->minorUnits);
    }

    public function getRouteKeyName(): string { return 'order_number'; }
}
```

### 3.9 `Ingredient`, `ProductCertification`, `FreeFromAttribute`

```php
#[RouteKey('slug')]
class Ingredient extends Model
{
    use HasFactory, HasSlug, HasSeoSlug, HasSlugHistory, HasSeoMeta, SoftDeletes;

    protected $guarded = ['id'];

    protected function casts(): array
    {
        return [
            'aliases'                     => 'array',
            'origin'                      => IngredientOrigin::class,
            'halal_status'                => HalalStatus::class,
            'status'                      => PostStatus::class,
            'published_at'                => 'datetime',
            'reviewed_at'                 => 'datetime',
            'is_animal_derived'           => 'boolean',
            'is_alcohol'                  => 'boolean',
            'is_common_allergen'          => 'boolean',
            'has_glossary_page'           => 'boolean',
        ];
    }

    public function products(): BelongsToMany
    {
        return $this->belongsToMany(Product::class)
            ->withPivot(['position', 'concentration_percent', 'is_key_ingredient', 'source_note', 'resolved_halal_status']);
    }

    public function author(): BelongsTo { return $this->belongsTo(User::class, 'author_id'); }
    public function reviewer(): BelongsTo { return $this->belongsTo(User::class, 'reviewed_by_user_id'); }

    /** "See also" cluster links for /halal-ingredients/{slug} — §2.7. */
    public function related(): BelongsToMany
    {
        return $this->belongsToMany(self::class, 'ingredient_related', 'ingredient_id', 'related_ingredient_id')
            ->withPivot('position')
            ->orderByPivot('position');
    }

    /** Only rows that are a real, published page at /halal-ingredients/{slug}. */
    #[Scope]
    protected function published(Builder $q): void
    {
        $q->where('has_glossary_page', true)
          ->where('status', PostStatus::Published)
          ->where(fn (Builder $sub) => $sub->whereNull('published_at')->orWhere('published_at', '<=', now()));
    }

    #[Scope]
    protected function questionable(Builder $q): void
    {
        $q->whereIn('halal_status', [HalalStatus::Mashbooh, HalalStatus::DependsOnSource, HalalStatus::Unknown]);
    }

    protected function url(): Attribute
    {
        return Attribute::get(fn (): string => route('ingredients.show', $this->slug))->shouldCache();
    }
}
```

```php
#[ObservedBy(ProductCertificationObserver::class)]
class ProductCertification extends Model
{
    protected $guarded = ['id'];

    protected function casts(): array
    {
        return [
            'issued_at'           => 'date',
            'expires_at'          => 'date',
            'status'              => CertificationStatus::class,
            'is_publicly_visible' => 'boolean',
        ];
    }

    public function product(): BelongsTo { return $this->belongsTo(Product::class); }
    public function body(): BelongsTo { return $this->belongsTo(CertificationBody::class, 'certification_body_id'); }

    #[Scope] protected function active(Builder $q): void { $q->where('status', CertificationStatus::Active); }
    #[Scope] protected function expiringWithin(Builder $q, int $days): void
    {
        $q->whereNotNull('expires_at')->whereBetween('expires_at', [now(), now()->addDays($days)]);
    }

    protected function daysUntilExpiry(): Attribute
    {
        return Attribute::get(fn (): ?int => $this->expires_at?->diffInDays(now(), absolute: false));
    }

    protected function resolvedVerificationUrl(): Attribute
    {
        return Attribute::get(fn (): ?string => $this->verification_url
            ?? ($this->body?->verification_url_template
                ? str_replace('{certificate_number}', urlencode($this->certificate_number), $this->body->verification_url_template)
                : null));
    }
}
```

`ProductCertificationObserver` keeps `product_halal_profiles.is_certified` truthful on every save and delete — this is the flag the storefront badge reads, so it must never be stale.

### 3.10 Observer responsibilities (summary)

| Observer | Maintains |
|---|---|
| `CategoryObserver` | `path`, `depth`; blocks deletion with children; queues subtree rebuild |
| `ProductObserver` | SEO record creation; slug-history row on slug change; `published_at` defaulting |
| `ProductVariantObserver` | `products.price_min_amount` / `price_max_amount` / `compare_at_max_amount`; ensures exactly one `is_default`; auto-creates the `InventoryItem` |
| `ProductCertificationObserver` | `product_halal_profiles.is_certified`; recomputes `overall_status` |
| `OrderObserver` | Writes `order_status_histories` on any status change; stamps `*_at` columns |
| `CartItemObserver` | Recomputes `carts.subtotal_amount` / `items_count`; touches `last_activity_at` |
| `InventoryItemObserver` | Recomputes `products.total_stock`; fires `LowStockDetected` |

> Every one of these maintains a denormalised value. That is a deliberate trade: reads on a storefront outnumber writes by three or four orders of magnitude, so paying on write is correct. The corresponding obligation is the nightly `catalog:reconcile` command (§8 Phase 5) that recomputes all of them from source and logs any drift — a denormalisation without a reconciler is a bug waiting for a quiet weekend.
---

## 4. Filament 5 Admin Panel

### 4.1 What Filament 5 actually is, relative to 4

Worth stating plainly because it changes how much of the ecosystem you can trust: **Filament 5 is a platform-requirements release, not an API-breaking release.** Its entire automated upgrade ruleset is a single Rector rule that widens one parameter type on `Filament\Resources\Resource` (`can()`, `authorize()`, `getAuthorizationResponse()` take `string|UnitEnum $action` instead of `string`). The `Components`, `Actions`, `Columns`, `Filters`, and `Widgets` directory listings are byte-identical between the 4.x and 5.x branches — no class was added, removed, renamed, or moved.

What v5 actually requires: **PHP 8.2+, Laravel 11.28+, Livewire 4.0+, Tailwind 4.1+.** That is the whole point of the release — it is the Livewire 4 upgrade, packaged.

Practical consequences for this build:

- Filament **4** tutorials, plugin docs, and Stack Overflow answers apply directly. Filament **3** ones do not.
- The scaffolded layout moved to `resources/views/layouts/app.blade.php` (Livewire 4 convention).
- Plugin availability is the real v5 risk, not API drift. Every package in §0.1 was version-checked against 5.x.

### 4.2 Packages to install

```bash
composer require filament/filament:"~5.0" -W
php artisan filament:install --panels

composer require filament/spatie-laravel-settings-plugin:"~5.0"
composer require spatie/laravel-settings
composer require spatie/laravel-permission
composer require bezhansalleh/filament-shield
composer require spatie/laravel-sluggable
composer require spatie/laravel-sitemap
composer require staudenmeir/laravel-adjacency-list
composer require intervention/image
```

> **Use `~5.0`, not `^5.0`, when running composer from PowerShell.** Filament's own installation documentation calls this out: PowerShell strips the `^` from version constraints, and you silently get the wrong resolution. This project is on Windows/Laragon, so it will bite you.

Deliberately **not** installed:

| Package | Why not |
|---|---|
| `spatie/laravel-medialibrary` + its Filament plugin | The dedicated `product_images` table (§1.4) keeps `alt_text` first-class and indexable, and models variant-specific galleries directly. Media Library would push both into `custom_properties` JSON. |
| `filament/spatie-laravel-translatable-plugin` | **No v4 or v5 release exists** — abandoned at v3.3.54. English-only at launch, so no impact now; see §0.1 for the consequence if Urdu is ever wanted. |
| `awcodes/filament-tiptap-editor` | Caps at Filament `^3.2`. Filament 5's built-in `RichEditor` is already TipTap-based and covers everything needed here. |

### 4.3 Panel configuration

`filament:install --panels` generates `app/Providers/Filament/AdminPanelProvider.php`. **Verify it was registered in `bootstrap/providers.php`** — Filament's docs describe auto-registration as best-effort, and a missing entry presents as a confusing 404 at `/admin`.

```php
namespace App\Providers\Filament;

use Filament\Http\Middleware\{AuthenticateSession, DisableBladeIconComponents, DispatchServingFilamentEvent};
use Filament\Navigation\NavigationGroup;
use Filament\Pages\Dashboard;
use Filament\Panel;
use Filament\PanelProvider;
use Filament\Support\Colors\Color;
use Illuminate\Cookie\Middleware\{AddQueuedCookiesToResponse, EncryptCookies};
use Illuminate\Foundation\Http\Middleware\PreventRequestForgery;
use Illuminate\Routing\Middleware\SubstituteBindings;
use Illuminate\Session\Middleware\StartSession;
use Illuminate\View\Middleware\ShareErrorsFromSession;

class AdminPanelProvider extends PanelProvider
{
    public function panel(Panel $panel): Panel
    {
        return $panel
            ->default()
            ->id('admin')
            ->path('admin')
            ->login()
            ->passwordReset()
            ->profile()
            ->brandName('Glow Halal')
            ->favicon(asset('favicon.ico'))
            ->colors([
                'primary' => Color::Emerald,   // halal/clean-beauty register; confirm against brand guide
            ])
            ->navigationGroups([
                NavigationGroup::make('Catalogue')->icon('heroicon-o-squares-2x2'),
                NavigationGroup::make('Halal')->icon('heroicon-o-shield-check'),
                NavigationGroup::make('Sales')->icon('heroicon-o-shopping-bag'),
                NavigationGroup::make('Content')->icon('heroicon-o-document-text'),
                NavigationGroup::make('Settings')->icon('heroicon-o-cog-6-tooth')->collapsed(),
            ])
            ->discoverResources(in: app_path('Filament/Resources'), for: 'App\Filament\Resources')
            ->discoverPages(in: app_path('Filament/Pages'), for: 'App\Filament\Pages')
            ->pages([Dashboard::class])
            ->discoverWidgets(in: app_path('Filament/Widgets'), for: 'App\Filament\Widgets')
            ->widgets([
                \App\Filament\Widgets\SalesStatsOverview::class,
                \App\Filament\Widgets\RevenueChart::class,
                \App\Filament\Widgets\ExpiringCertificationsWidget::class,
                \App\Filament\Widgets\LowStockWidget::class,
                \App\Filament\Widgets\LatestOrdersWidget::class,
            ])
            ->databaseNotifications()
            ->middleware([
                EncryptCookies::class,
                AddQueuedCookiesToResponse::class,
                StartSession::class,
                AuthenticateSession::class,
                ShareErrorsFromSession::class,
                PreventRequestForgery::class,
                SubstituteBindings::class,
                DisableBladeIconComponents::class,
                DispatchServingFilamentEvent::class,
            ])
            ->authMiddleware([\Filament\Http\Middleware\Authenticate::class]);
    }
}
```

**Panel access must be gated on the `User` model.** Filament's `FilamentUser` contract carries an explicit security note in its own source: without implementing it, *every authenticated user* can reach the panel whenever `APP_ENV` is not `local`. On a store where customers have accounts, that is a full admin breach.

```php
use Filament\Models\Contracts\FilamentUser;
use Filament\Panel;

class User extends Authenticatable implements FilamentUser
{
    public function canAccessPanel(Panel $panel): bool
    {
        return $panel->getId() === 'admin'
            && ! $this->is_blocked
            && $this->hasAnyRole(['super_admin', 'staff']);
    }
}
```

> Implement this in **Phase 1**, before the first customer account exists. It is not a hardening task for later.

Roles come from `filament-shield` (`php artisan shield:install`), which generates per-resource permissions and a `RoleResource`. Target policy: `super_admin` full access; `staff` full access to Sales and Catalogue but not Settings, Users, or Roles.

### 4.4 Resource inventory

Generated with `php artisan make:filament-resource Product --generate --soft-deletes --view`, producing the v5 nested layout:

```
app/Filament/Resources/
+-- Products/
|   +-- ProductResource.php
|   +-- Pages/{ListProducts,CreateProduct,EditProduct,ViewProduct}.php
|   +-- Schemas/{ProductForm,ProductInfolist}.php
|   +-- Tables/ProductsTable.php
|   +-- RelationManagers/{VariantsRelationManager,ImagesRelationManager,
|                          IngredientsRelationManager,CertificationsRelationManager}.php
+-- Categories/  Orders/  Coupons/  BlogPosts/  ...
```

> Relation managers are the one thing that keeps the older flat path (`XResource/RelationManagers/`) and define `form()` / `table()` as **instance** methods inline, rather than delegating to `Schemas/` and `Tables/` classes.

| Group | Resource | Model | Notes |
|---|---|---|---|
| Catalogue | `ProductResource` | `Product` | Tabbed form; 4 relation managers |
| Catalogue | `CategoryResource` | `Category` | Tree-ordered list, parent select |
| Catalogue | `AttributeResource` | `Attribute` | Values relation manager |
| Catalogue | `InventoryResource` | `InventoryItem` | Read-mostly; adjustment action writes a ledger row |
| Halal | `IngredientResource` | `Ingredient` | Glossary + halal status |
| Halal | `CertificationBodyResource` | `CertificationBody` | |
| Halal | `FreeFromAttributeResource` | `FreeFromAttribute` | |
| Sales | `OrderResource` | `Order` | Status transitions — §4.5 |
| Sales | `PaymentResource` | `Payment` | Bank-transfer verification queue |
| Sales | `CouponResource` | `Coupon` | |
| Sales | `CustomerResource` | `User` | Scoped to the `customer` role |
| Sales | `ShippingZoneResource` | `ShippingZone` | Rates relation manager |
| Content | `BlogPostResource` | `BlogPost` | RichEditor |
| Content | `BlogCategoryResource` | `BlogCategory` | |
| Content | `PageResource` | `Page` | RichEditor; `is_system` guard |
| Content | `RedirectResource` | `Redirect` | §7.2 |
| Settings | `BankAccountResource` | `BankAccount` | |
| Settings | `UserResource` / `RoleResource` | `User` / `Role` | Shield-generated |

Custom pages:

| Page | Base | Purpose |
|---|---|---|
| `Dashboard` | `Filament\Pages\Dashboard` | Widgets |
| `ManageStoreSettings` | `Filament\Pages\SettingsPage` (settings plugin) | `StoreSettings` |
| `ManageSeoSettings` | `Filament\Pages\SettingsPage` | `SeoSettings` |
| `HalalComplianceReport` | `Filament\Pages\Page` | Products missing certification, ingredients needing source review, expiring certificates |

### 4.5 `ProductResource` — form schema

```php
namespace App\Filament\Resources\Products\Schemas;

use App\Enums\ProductStatus;
use Filament\Forms\Components\{FileUpload, Repeater, RichEditor, Select, TextInput, Textarea, Toggle};
use Filament\Schemas\Components\{Grid, Section, Tabs};
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Components\Utilities\Set;
use Filament\Schemas\Schema;
use Illuminate\Support\Str;

class ProductForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema->components([
            Tabs::make()->columnSpanFull()->tabs([

                Tabs\Tab::make('Details')->icon('heroicon-o-information-circle')->schema([
                    Grid::make(['default' => 1, 'lg' => 3])->schema([
                        Section::make()->columnSpan(['lg' => 2])->schema([
                            TextInput::make('name')
                                ->required()->maxLength(200)
                                ->live(onBlur: true)
                                ->afterStateUpdated(fn (string $operation, ?string $state, Set $set) =>
                                    $operation === 'create' ? $set('slug', Str::slug((string) $state)) : null),

                            TextInput::make('slug')
                                ->required()->maxLength(220)
                                ->unique(ignoreRecord: true)
                                ->helperText('Changing this on a published product creates a 301 redirect from the old URL.')
                                ->disabledOn('edit')      // deliberate: see §7.2
                                ->dehydrated(),

                            Textarea::make('short_description')->rows(3)->maxLength(500)
                                ->helperText('Plain text. Used on listing cards and as the meta description fallback.'),

                            RichEditor::make('description')
                                ->toolbarButtons([
                                    ['bold', 'italic', 'underline', 'link'],
                                    ['h2', 'h3'],
                                    ['blockquote', 'bulletList', 'orderedList'],
                                    ['attachFiles'],
                                    ['undo', 'redo'],
                                ])
                                ->fileAttachmentsDisk('public')
                                ->fileAttachmentsDirectory('products/content')
                                ->columnSpanFull(),

                            RichEditor::make('how_to_use')
                                ->toolbarButtons([['bold', 'italic'], ['bulletList', 'orderedList']])
                                ->columnSpanFull(),
                        ]),

                        Section::make('Publishing')->columnSpan(['lg' => 1])->schema([
                            Select::make('status')->options(ProductStatus::class)
                                ->default(ProductStatus::Draft)->required()->native(false),
                            \Filament\Forms\Components\DateTimePicker::make('published_at')->seconds(false),
                            Select::make('primary_category_id')
                                ->relationship('primaryCategory', 'name')
                                ->searchable()->preload()->native(false)
                                ->helperText('Drives breadcrumbs and the canonical category.'),
                            Select::make('categories')
                                ->relationship('categories', 'name')
                                ->multiple()->searchable()->preload(),
                            TextInput::make('brand')->maxLength(100),
                            Toggle::make('is_featured'),
                            Toggle::make('is_new_arrival'),
                        ]),
                    ]),
                ]),

                Tabs\Tab::make('Halal')->icon('heroicon-o-shield-check')->schema([
                    Section::make('Halal profile')
                        ->relationship('halalProfile')     // 1:1 — Filament creates the row on save
                        ->schema([
                            Grid::make(2)->schema([
                                Select::make('overall_status')->options(\App\Enums\HalalStatus::class)->required()->native(false),
                                Select::make('alcohol_status')->options(\App\Enums\AlcoholStatus::class)->required()->native(false)
                                    ->helperText('Fatty alcohols (cetyl, cetearyl) are not intoxicating — use "Fatty alcohol only".'),
                                Toggle::make('is_wudu_friendly')->helperText('Water-permeable; permits ablution.'),
                                Toggle::make('is_vegan'),
                                Toggle::make('is_cruelty_free'),
                                Toggle::make('is_self_declared')
                                    ->helperText('Manufacturer statement without third-party certification. Rendered as the weaker claim.'),
                                Toggle::make('shared_facility_warning'),
                            ]),
                            Textarea::make('summary')->rows(3)->columnSpanFull()
                                ->helperText('Shown on the product page under the halal badge.'),
                            Textarea::make('internal_notes')->rows(2)->columnSpanFull(),
                        ]),

                    Section::make('"Free from" claims')->schema([
                        Select::make('freeFromAttributes')
                            ->relationship('freeFromAttributes', 'name')
                            ->multiple()->preload()
                            ->helperText('Claims only appear on the storefront once verified in the relation manager below.'),
                    ]),
                ]),

                Tabs\Tab::make('SEO')->icon('heroicon-o-magnifying-glass')->schema([
                    Section::make()->relationship('seoMeta')->schema(SeoMetaSchema::components()),
                ]),
            ]),
        ]);
    }
}
```

> `->disabledOn('edit')->dehydrated()` on `slug` is a deliberate friction point. Editing the slug is possible, but only through an explicit "Change URL" action (§7.2) that writes the redirect — because the failure mode of a casually-edited slug is a silently 404'd page that was ranking.
>
> `Section::make()->relationship('halalProfile')` handles the 1:1 `hasOne` without a relation manager, creating the row on first save. Use the same pattern for `seoMeta`.

`ProductVariant` is a relation manager rather than a `Repeater`, because variants own SKUs, prices, and inventory rows and need their own table, filters, and per-row actions. Use a `Repeater` only for genuinely subordinate data.

### 4.6 `ProductResource` — table

```php
namespace App\Filament\Resources\Products\Tables;

use App\Enums\ProductStatus;
use Filament\Actions\{ActionGroup, BulkActionGroup, DeleteBulkAction, DeleteAction, EditAction, ViewAction};
use Filament\Tables\Columns\{IconColumn, ImageColumn, TextColumn};
use Filament\Tables\Filters\{Filter, SelectFilter, TernaryFilter, TrashedFilter};
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class ProductsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->modifyQueryUsing(fn (Builder $query) => $query->with([
                'primaryCategory:id,name', 'primaryImage', 'halalProfile',
            ]))
            ->columns([
                ImageColumn::make('primaryImage.path')->label('')->disk('public')
                    ->height(44)->width(44)->extraImgAttributes(['class' => 'rounded object-cover']),

                TextColumn::make('name')->searchable()->sortable()
                    ->description(fn ($record) => $record->primaryCategory?->name)
                    ->wrap()->limit(60),

                TextColumn::make('price_min_amount')->label('Price')->sortable()
                    ->formatStateUsing(fn ($record) => $record->price_range),

                TextColumn::make('total_stock')->label('Stock')->sortable()->badge()
                    ->color(fn (int $state) => match (true) {
                        $state === 0 => 'danger',
                        $state <= 5  => 'warning',
                        default      => 'success',
                    }),

                TextColumn::make('status')->badge()->sortable(),

                IconColumn::make('halalProfile.is_certified')->label('Certified')
                    ->boolean()->trueIcon('heroicon-o-shield-check')->falseIcon('heroicon-o-shield-exclamation')
                    ->trueColor('success')->falseColor('gray'),

                TextColumn::make('variants_count')->counts('variants')->label('Variants')->toggleable(),
                TextColumn::make('created_at')->dateTime('d M Y')->sortable()->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                SelectFilter::make('status')->options(ProductStatus::class),
                SelectFilter::make('primary_category_id')->relationship('primaryCategory', 'name')->label('Category')->searchable()->preload(),
                TernaryFilter::make('is_featured'),
                Filter::make('out_of_stock')->query(fn (Builder $q) => $q->where('total_stock', 0))->label('Out of stock'),
                Filter::make('uncertified')
                    ->label('Missing halal certification')
                    ->query(fn (Builder $q) => $q->whereDoesntHave('certifications',
                        fn (Builder $c) => $c->where('status', \App\Enums\CertificationStatus::Active))),
                TrashedFilter::make(),
            ])
            ->recordActions([
                ActionGroup::make([ViewAction::make(), EditAction::make(), DeleteAction::make()]),
            ])
            ->toolbarActions([
                BulkActionGroup::make([DeleteBulkAction::make()]),
            ])
            ->defaultSort('created_at', 'desc');
    }
}
```

> Note the v5/v4 table method names: **`recordActions()`** (not `actions()`) and **`toolbarActions()`** (not `bulkActions()`). Also `BadgeColumn` still exists but is deprecated in source — use `TextColumn::make(...)->badge()` as above.
>
> `modifyQueryUsing()` with eager loads is not optional. Without it the image, category, and halal columns each fire a query per row: 25 rows becomes ~75 queries.

### 4.7 `OrderResource` — status transitions

Order editing is **deliberately not a form**. Orders are immutable commercial records; the admin performs *transitions*, each of which is an explicit action with its own confirmation, its own required inputs, and its own side effects (stock, notifications, history). A free-form edit page over `orders` invites someone to change a total after the fact.

`ViewOrder` is the primary page, built from an infolist, with transitions as header actions:

```php
namespace App\Filament\Resources\Orders\Pages;

use App\Enums\OrderStatus;
use App\Models\Order;
use App\Services\Orders\OrderStateMachine;
use Filament\Actions\Action;
use Filament\Forms\Components\{Select, Textarea, TextInput, Toggle};
use Filament\Notifications\Notification;
use Filament\Resources\Pages\ViewRecord;

class ViewOrder extends ViewRecord
{
    protected static string $resource = \App\Filament\Resources\Orders\OrderResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Action::make('confirm')
                ->label('Confirm order')
                ->icon('heroicon-o-check-circle')->color('info')
                ->visible(fn (Order $record) => $record->status->canTransitionTo(OrderStatus::Confirmed))
                ->requiresConfirmation()
                ->modalDescription('Confirming commits stock for every line on this order.')
                ->schema([
                    Textarea::make('note')->label('Internal note')->rows(2),
                    Toggle::make('notify_customer')->default(true),
                ])
                ->action(fn (Order $record, array $data) =>
                    app(OrderStateMachine::class)->transition($record, OrderStatus::Confirmed, $data)),

            Action::make('ship')
                ->label('Mark shipped')
                ->icon('heroicon-o-truck')->color('primary')
                ->visible(fn (Order $record) => $record->status->canTransitionTo(OrderStatus::Shipped))
                ->schema([
                    TextInput::make('tracking_number')->required()->maxLength(80),
                    Select::make('courier')->required()->native(false)
                        ->options(['tcs' => 'TCS', 'leopards' => 'Leopards', 'mp' => 'M&P', 'postex' => 'PostEx', 'other' => 'Other']),
                    Toggle::make('notify_customer')->default(true),
                ])
                ->action(fn (Order $record, array $data) =>
                    app(OrderStateMachine::class)->transition($record, OrderStatus::Shipped, $data)),

            Action::make('deliver')
                ->label('Mark delivered')
                ->icon('heroicon-o-home')->color('success')
                ->visible(fn (Order $record) => $record->status->canTransitionTo(OrderStatus::Delivered))
                ->requiresConfirmation()
                ->modalDescription('For COD orders this also records payment as collected.')
                ->action(fn (Order $record) =>
                    app(OrderStateMachine::class)->transition($record, OrderStatus::Delivered)),

            Action::make('cancel')
                ->label('Cancel order')
                ->icon('heroicon-o-x-circle')->color('danger')
                ->visible(fn (Order $record) => $record->status->canTransitionTo(OrderStatus::Cancelled))
                ->requiresConfirmation()
                ->schema([
                    Select::make('cancel_reason')->required()->native(false)->options([
                        'customer_request'   => 'Customer requested',
                        'out_of_stock'       => 'Out of stock',
                        'undeliverable'      => 'Address undeliverable',
                        'payment_failed'     => 'Payment not received',
                        'suspected_fraud'    => 'Suspected fraud',
                        'cod_refused'        => 'COD refused at door',
                    ]),
                    Textarea::make('note')->rows(2),
                    Toggle::make('restock')->label('Return items to stock')->default(true),
                ])
                ->action(fn (Order $record, array $data) =>
                    app(OrderStateMachine::class)->transition($record, OrderStatus::Cancelled, $data)),

            Action::make('refund')
                ->label('Record refund')
                ->icon('heroicon-o-arrow-uturn-left')->color('warning')
                ->visible(fn (Order $record) => $record->status->canTransitionTo(OrderStatus::Refunded))
                ->schema([
                    TextInput::make('amount')->numeric()->required()->prefix('Rs')
                        ->helperText('Full or partial. Entered in rupees.'),
                    Textarea::make('note')->rows(2)->required(),
                ])
                ->action(fn (Order $record, array $data) =>
                    app(OrderStateMachine::class)->transition($record, OrderStatus::Refunded, $data)),
        ];
    }
}
```

> **`->schema()`, not `->form()`.** Both work in v5, but `HasSchema::form()` is marked `@deprecated Use schema() instead` in Filament's own source. Write new code against `schema()`.
>
> `->visible()` is driven by `OrderStatus::canTransitionTo()` — the same enum method the service uses to authorise the transition (§3.3, §5.7). The UI cannot offer a transition the domain would reject, and the domain still rejects it independently if something forges the request. One definition, enforced twice.

`cod_refused` as a distinct cancellation reason matters commercially: COD refusal rates are the single largest cost driver for Pakistani e-commerce, and you cannot manage what you have not categorised.

The bank-transfer verification queue lives on `PaymentResource` as `approveProof` / `rejectProof` actions, both delegating to `PaymentManager` (§6.4). Proof images are served through a signed, policy-gated route — never a public disk URL (§1.10).

### 4.8 Image uploads

```php
FileUpload::make('path')
    ->label('Image')
    ->image()
    ->imageEditor()
    ->imageEditorAspectRatioOptions(['1:1', '4:5', '16:9'])
    ->disk('public')
    ->directory('products')
    ->visibility('public')
    ->maxSize(4096)                       // KB
    ->acceptedFileTypes(['image/jpeg', 'image/png', 'image/webp'])
    ->required(),

TextInput::make('alt_text')
    ->required()
    ->maxLength(255)
    ->helperText('Describe the image for screen readers and search engines. Include the shade name.'),
```

`alt_text` is `required()` at the form layer by design — the column is `NOT NULL`, and the helper text tells the admin what a useful value looks like. Missing alt text is both an accessibility failure and forfeited image-search traffic, and cosmetics is a category where Google Images converts.

Core `FileUpload` has no conversions pipeline. A `GenerateImageConversions` queued job (Intervention Image v4) writes WebP renditions and the blurhash into `product_images.conversions` after upload.

### 4.9 Widgets

```php
namespace App\Filament\Widgets;

use App\Enums\{OrderStatus, PaymentStatus};
use App\Models\{Order, Payment};
use App\Support\Money;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class SalesStatsOverview extends BaseWidget
{
    protected static ?int $sort = 1;

    protected function getStats(): array
    {
        $today = Order::placed()->whereDate('placed_at', today());

        return [
            Stat::make('Orders today', (clone $today)->count())
                ->description('Awaiting confirmation: '.Order::where('status', OrderStatus::Pending)->count())
                ->descriptionIcon('heroicon-m-clock')
                ->color('info'),

            Stat::make('Revenue today', (new Money((int) (clone $today)->sum('grand_total_amount')))->format())
                ->descriptionIcon('heroicon-m-banknotes')
                ->color('success'),

            Stat::make('Bank transfers to verify', Payment::where('driver', 'bank_transfer')
                    ->where('status', \App\Enums\PaymentAttemptStatus::AwaitingVerification)->count())
                ->description('Customers are waiting on these')
                ->descriptionIcon('heroicon-m-document-check')
                ->color('warning'),
        ];
    }
}
```

`ExpiringCertificationsWidget` is the halal-specific one and earns its dashboard slot: a `TableWidget` over `ProductCertification::expiringWithin(60)`, ordered by `expires_at`. An expired certificate on a live product page is the worst failure this brand can have, and it fails *silently* — nothing breaks, the claim just quietly becomes false. Put it where someone sees it every morning.

Also: `RevenueChart` (`ChartWidget`, 30-day line — note `protected ?string $heading`, **not** `protected static`), `LowStockWidget` (`inventory_items.quantity_available <= reorder_level`), `LatestOrdersWidget` (`TableWidget`, 10 most recent).
---

## 5. Cart and Checkout Architecture (Livewire 4)

### 5.1 What changed in Livewire 4, and what it means here

Unlike Filament 5, **Livewire 4 is a genuinely large release.** Writing v3 patterns will produce code that runs but behaves subtly differently. The changes that matter for a cart:

**Components are single-file by default.** `php artisan make:livewire cart.mini` now creates `resources/views/components/cart/⚡mini.blade.php` — PHP and Blade in one file, with an anonymous `new class extends Component` block. Class-based components at `app/Livewire/` still work via `--class`, and the component *name* is identical in all three formats, so the format is a per-component choice, not an architectural one.

```
Single-file   resources/views/components/cart/⚡mini.blade.php     → cart.mini
Multi-file    resources/views/components/cart/⚡mini/mini.php      → cart.mini
Class-based   app/Livewire/Cart/Mini.php                          → cart.mini
Page (SFC)    resources/views/pages/⚡checkout.blade.php           → pages::checkout
```

> The ⚡ prefix is a real Unicode character in the filename, and it is what stops Livewire components colliding with Blade anonymous components in the same `resources/views/components/` directory. Disable it with `'make_command' => ['emoji' => false]` in `config/livewire.php` if it causes friction with any tooling — but the collision problem is real, so keep the components in a namespace if you do.

**Recommendation for this project:** single-file for the small presentational components (`AddToCart`, `MiniCart`), **class-based for `Checkout`**. Checkout carries constructor-injected services, ~400 lines of logic, and the heaviest test suite in the application; that belongs in `app/Livewire/Checkout.php` where it is a normal, testable PHP class.

**`wire:model` modifier semantics changed (v4.1).** `.blur` and `.change` now control **client-side** sync timing, not just network requests. Any v3 `wire:model.blur` must become `wire:model.live.blur` to keep its old behaviour. This bites quantity inputs directly.

| v3 | v4 equivalent |
|---|---|
| `wire:model.blur` | `wire:model.live.blur` |
| `wire:model.change` | `wire:model.live.change` |
| `wire:model.lazy` | unchanged — backwards compatible |

**Routing uses `Route::livewire()`** for full-page components instead of `Route::get(…, Component::class)`.

**Component tags must be self-closed**: `<livewire:cart.mini />`.

**Islands** (`@island`) update a region of a component without re-rendering the whole thing.

**Config renames**: `layout` → `component_layout` (`'layouts::app'`), `lazy_placeholder` → `component_placeholder`, and `smart_wire_keys` now defaults to `true`.

**The update endpoint URL now contains a hash** (`/livewire-{hash}/update`). If a WAF, CDN rule, or `robots.txt` entry references `/livewire/update`, it will silently stop matching.

### 5.2 SEO: the constraint that drove the whole stack choice

Livewire renders the **complete component HTML server-side on the initial request**. `<livewire:cart.mini />` in the header arrives as real markup; a crawler with JavaScript disabled sees the finished page. That is precisely why Livewire was chosen over a JS SPA, and it holds for every component below.

**The one way to lose it — and it is easy to lose by accident:**

- `@island(lazy: true)` and `@island(defer: true)` do **not** render their contents into the initial HTML. They arrive in a follow-up request.
- `#[Lazy]` components render a placeholder first.
- `wire:init` fires after page load.

None of these may ever wrap product descriptions, ingredient lists, halal certification details, prices, reviews, or category listings. They are correct for the mini-cart badge, the "recently viewed" strip, and dashboard-style widgets — content whose absence from the HTML costs nothing.

**Make this testable rather than a matter of discipline.** A feature test asserting that the raw product page response contains the product name, price, INCI ingredient list, and JSON-LD block will fail loudly the first time someone wraps the wrong region in a lazy island. Write it in Phase 5.

### 5.3 Cart identity: guests and logged-in users

The cart lives in the database (§1.6). The **cookie holds only an opaque ULID token**.

```php
namespace App\Services\Cart;

final class CartManager
{
    public const COOKIE = 'gh_cart';
    public const COOKIE_DAYS = 30;

    public function current(?bool $createIfMissing = true): ?Cart
    {
        $user = auth()->user();

        if ($user) {
            $cart = Cart::active()->where('user_id', $user->id)->latest('id')->first();

            if ($cart) {
                return $cart;
            }
        }

        if ($token = request()->cookie(self::COOKIE)) {
            $cart = Cart::active()->where('token', $token)->first();

            if ($cart) {
                // Claim an anonymous cart for a user who logged in mid-session.
                if ($user && $cart->user_id === null) {
                    $cart->update(['user_id' => $user->id]);
                }

                return $cart;
            }
        }

        return $createIfMissing ? $this->create($user) : null;
    }

    private function create(?User $user): Cart
    {
        $cart = Cart::create(['user_id' => $user?->id, 'currency' => 'PKR']);

        Cookie::queue(
            Cookie::make(self::COOKIE, $cart->token, self::COOKIE_DAYS * 24 * 60,
                path: '/', domain: null, secure: app()->isProduction(), httpOnly: true, sameSite: 'lax')
        );

        return $cart;
    }
}
```

**Why a cookie rather than the session.** `SESSION_LIFETIME` is 120 minutes and `SESSION_DRIVER=database`; a session-carried cart evaporates over lunch. A 30-day cookie holding a token, with the cart itself in the database, survives browser restarts, enables abandoned-cart recovery, and lets an admin see real carts in the admin panel. The cookie is `httpOnly` and `SameSite=Lax` — it is an opaque ULID, so leaking it exposes one anonymous basket, but there is no reason to expose it to JavaScript at all.

Resolve once per request via middleware and bind it, so no component ever re-queries:

```php
$this->app->scoped(Cart::class, fn () => app(CartManager::class)->current());
```

### 5.4 Merge on login

The interesting case is a guest with a cart who logs in and **already has a saved cart**. Neither may be discarded silently — a customer who added items yesterday, then more today as a guest, expects both.

```php
namespace App\Listeners;

use Illuminate\Auth\Events\Login;

final class MergeGuestCartOnLogin
{
    public function __construct(private readonly CartManager $carts) {}

    public function handle(Login $event): void
    {
        $token = request()->cookie(CartManager::COOKIE);

        if (! $token) {
            return;
        }

        $guestCart = Cart::active()->where('token', $token)->whereNull('user_id')->with('items')->first();

        if (! $guestCart || $guestCart->items->isEmpty()) {
            return;
        }

        $userCart = Cart::active()->where('user_id', $event->user->id)
            ->where('id', '!=', $guestCart->id)->latest('id')->first();

        if (! $userCart) {
            $guestCart->update(['user_id' => $event->user->id]);

            return;
        }

        DB::transaction(function () use ($guestCart, $userCart) {
            foreach ($guestCart->items as $item) {
                $existing = $userCart->items()->where('product_variant_id', $item->product_variant_id)->first();

                if ($existing) {
                    // Take the larger quantity, do not sum: a customer who set "2" in both
                    // places means 2, not 4. Summing produces surprise quantities.
                    $existing->update([
                        'quantity' => max($existing->quantity, $item->quantity),
                    ]);

                    continue;
                }

                $userCart->items()->create($item->only([
                    'product_id', 'product_variant_id', 'quantity', 'unit_price_amount',
                    'name_snapshot', 'options_snapshot', 'image_path_snapshot',
                ]));
            }

            $guestCart->update([
                'status'              => CartStatus::Merged,
                'merged_into_cart_id' => $userCart->id,
            ]);

            app(CartCalculator::class)->recalculate($userCart->fresh('items'));

            Cookie::queue(Cookie::make(CartManager::COOKIE, $userCart->token,
                CartManager::COOKIE_DAYS * 24 * 60, httpOnly: true, sameSite: 'lax'));
        });
    }
}
```

> **`max()` not `+` on quantity collision** is the judgement call here, and it is worth stating in the spec because both are defensible. Summing surprises people: adding one lipstick as a guest, having previously saved one, should not silently produce two. `max()` never over-charges and never silently inflates a basket. If the client disagrees, it is a one-line change in one place.
>
> The guest cart is marked `merged` and retained rather than deleted. Merge bugs are otherwise unrecoverable, and this is exactly the kind of code that gets one subtle bug in its life.

### 5.5 The component tree

```
<livewire:cart.add-to-cart :product="$product" />       product page — shade/size picker + quantity
<livewire:cart.mini />                                   header — badge and drawer
Route::livewire('/cart', 'pages::cart')                  full cart page
Route::livewire('/checkout', 'pages::checkout')          checkout (class-based)
```

They coordinate by event, not by shared state:

```php
$this->dispatch('cart-updated');       // from AddToCart / CartPage
#[On('cart-updated')] public function refresh(): void {}   // in MiniCart
```

**`AddToCart`** (single-file, `resources/views/components/cart/⚡add-to-cart.blade.php`):

```blade
<?php

use App\Models\{Product, ProductVariant};
use App\Services\Cart\CartService;
use Livewire\Attributes\{Computed, Locked, On};
use Livewire\Component;

new class extends Component {
    #[Locked]
    public int $productId;

    public ?int $variantId = null;
    public int $quantity = 1;

    public function mount(Product $product): void
    {
        $this->productId = $product->id;
        $this->variantId = $product->defaultVariant?->id;
    }

    #[Computed]
    public function product(): Product
    {
        return Product::with(['variants.attributeValues.attribute', 'variants.inventory'])->findOrFail($this->productId);
    }

    #[Computed]
    public function variant(): ?ProductVariant
    {
        return $this->variantId ? $this->product->variants->firstWhere('id', $this->variantId) : null;
    }

    #[Computed]
    public function maxQuantity(): int
    {
        $variant = $this->variant;

        if (! $variant) {
            return 0;
        }

        return min($variant->max_per_order ?? 99, max(0, $variant->available_quantity), 99);
    }

    public function selectVariant(int $variantId): void
    {
        // Guard: only variants belonging to THIS product are selectable.
        abort_unless($this->product->variants->contains('id', $variantId), 422);

        $this->variantId = $variantId;
        $this->quantity  = 1;

        unset($this->variant, $this->maxQuantity);   // bust the computed cache

        $this->dispatch('variant-selected', variantId: $variantId);   // swaps the gallery
    }

    public function add(CartService $cart): void
    {
        $this->validate([
            'variantId' => 'required|integer',
            'quantity'  => 'required|integer|min:1|max:'.$this->maxQuantity,
        ], [
            'quantity.max' => 'Only :max left in stock.',
        ]);

        $cart->add($this->variant, $this->quantity);

        $this->dispatch('cart-updated');
        $this->dispatch('cart-drawer-open');
    }
};
?>

<div>
    {{-- Shade swatches --}}
    <div class="flex flex-wrap gap-2" role="radiogroup" aria-label="Shade">
        @foreach ($this->product->variants as $variant)
            <button type="button"
                    wire:key="variant-{{ $variant->id }}"
                    wire:click="selectVariant({{ $variant->id }})"
                    @disabled(! $variant->is_in_stock)
                    aria-checked="{{ $variantId === $variant->id ? 'true' : 'false' }}"
                    role="radio"
                    class="...">
                {{ $variant->name }}
            </button>
        @endforeach
    </div>

    <input type="number" wire:model.live.blur="quantity" min="1" max="{{ $this->maxQuantity }}">
    @error('quantity') <p class="text-sm text-red-600">{{ $message }}</p> @enderror

    <button type="button" wire:click="add" wire:loading.attr="disabled" @disabled($this->maxQuantity === 0)>
        <span wire:loading.remove wire:target="add">
            {{ $this->maxQuantity === 0 ? 'Out of stock' : 'Add to bag' }}
        </span>
        <span wire:loading wire:target="add">Adding…</span>
    </button>
</div>
```

Three things worth calling out:

- **`#[Locked]` on `$productId`.** Public Livewire properties round-trip through the browser and are attacker-modifiable. Without `#[Locked]`, a user can repoint the component at any product. Every identifier property in this application must be `#[Locked]`, and `selectVariant()` still re-validates ownership server-side because defence in depth is cheap here.
- **`wire:model.live.blur`, not `wire:model.blur`.** Under v4 semantics the latter would not sync to the server at all until another action fired, and the max-quantity validation would appear not to run.
- **`unset($this->variant, $this->maxQuantity)`** after changing `$variantId`. `#[Computed]` caches for the request; without busting, the stock check reads the previous shade's stock.

**`MiniCart`** stays cheap by rendering from the denormalised `carts.items_count` / `subtotal_amount` columns (§1.6) — no joins on a component that appears on every page:

```php
#[On('cart-updated')]
public function refresh(): void
{
    unset($this->cart);
}

#[Computed]
public function cart(): ?Cart
{
    return app(CartManager::class)->current(createIfMissing: false);
}
```

> `createIfMissing: false` in the header matters: otherwise every crawler hit, every bot, and every 404 creates a `carts` row. That table would fill with empty carts and the abandoned-cart reporting would become meaningless.

### 5.6 Live totals without a page reload

`CartService` mutates; `CartCalculator` derives. Every mutation ends by recalculating and persisting the denormalised totals, so any component can render the cart from one row.

```php
final class CartCalculator
{
    public function __construct(
        private readonly CouponValidator $coupons,
        private readonly ShippingCalculator $shipping,
    ) {}

    public function recalculate(Cart $cart, ?string $city = null, ?string $paymentMethod = null): CartTotals
    {
        $cart->loadMissing('items.variant');

        $subtotal = new Money(0);

        foreach ($cart->items as $item) {
            $line = $item->unit_price_amount->times($item->quantity);
            $item->updateQuietly(['line_total_amount' => $line->minorUnits]);
            $subtotal = $subtotal->plus($line);
        }

        $discount = $cart->coupon
            ? $this->coupons->discountFor($cart->coupon, $cart, $subtotal)
            : new Money(0);

        $shipping = $this->shipping->for($cart, $subtotal->minus($discount), $city);
        $codFee   = $paymentMethod === 'cod' ? $this->codFee($cart) : new Money(0);
        $tax      = new Money(0);   // Retail prices in PK are quoted GST-inclusive; see note.

        $grand = $subtotal->minus($discount)->plus($shipping)->plus($codFee)->plus($tax);

        $cart->forceFill([
            'subtotal_amount'    => $subtotal->minorUnits,
            'discount_amount'    => $discount->minorUnits,
            'shipping_amount'    => $shipping->minorUnits,
            'tax_amount'         => $tax->minorUnits,
            'grand_total_amount' => $grand->minorUnits,
            'items_count'        => $cart->items->sum('quantity'),
            'last_activity_at'   => now(),
        ])->save();

        return new CartTotals($subtotal, $discount, $shipping, $codFee, $tax, $grand);
    }
}
```

> **Tax is modelled but zero at launch.** Pakistani retail cosmetics prices are customarily quoted GST-inclusive, so displaying a separate tax line would confuse customers and inflate the visible total. The columns and the calculator hook exist because "we now need to show GST separately" or "we need to itemise it for B2B invoices" is a plausible future request, and retrofitting a tax column into an orders table that already has history is genuinely painful. Zero cost now, large saving later.

In the cart page, quantity updates flow through Livewire's normal round trip — no page reload, no manual JavaScript:

```blade
<input type="number"
       wire:model.live.debounce.400ms="quantities.{{ $item->id }}"
       min="1" max="{{ $item->variant->available_quantity }}">
```

```php
public function updatedQuantities(mixed $value, string $key): void
{
    $item = $this->cart->items()->findOrFail((int) $key);

    $this->cartService->updateQuantity($item, max(1, (int) $value));

    unset($this->cart, $this->totals);

    $this->dispatch('cart-updated');
}
```

The 400ms debounce is the difference between one request and one request *per keystroke* — on a mobile connection in Pakistan, that is the difference between a usable cart and a broken one.

### 5.7 Stock validation at checkout

This is where correctness actually matters, and where most implementations are quietly wrong. Two customers buying the last unit simultaneously must not both succeed.

**Stock is not reserved when items are added to the cart.** Doing so lets a single abandoned tab hold the last unit hostage. It is reserved at order placement, inside a transaction with row locks.

```php
final class PlaceOrderAction
{
    public function execute(Cart $cart, CheckoutData $data): Order
    {
        return DB::transaction(function () use ($cart, $data) {

            // 1. Lock the cart itself — blocks a double-submitted checkout.
            $cart = Cart::whereKey($cart->id)->lockForUpdate()->firstOrFail();

            if ($cart->status !== CartStatus::Active) {
                throw new CartAlreadyConvertedException($cart);
            }

            $items = $cart->items()->with('variant.product')->get();

            if ($items->isEmpty()) {
                throw new EmptyCartException;
            }

            // 2. Lock every inventory row, ordered by id — consistent ordering prevents deadlock.
            $variantIds  = $items->pluck('product_variant_id')->unique()->sort()->values();
            $inventories = InventoryItem::whereIn('product_variant_id', $variantIds)
                ->orderBy('id')->lockForUpdate()->get()->keyBy('product_variant_id');

            // 3. Validate availability AND price against live data.
            $problems = [];

            foreach ($items as $item) {
                $variant = $item->variant;

                if (! $variant?->is_active || $variant->product?->status !== ProductStatus::Active) {
                    $problems[] = CheckoutProblem::unavailable($item);

                    continue;
                }

                if ($variant->track_inventory && ! $variant->allow_backorder) {
                    $available = (int) ($inventories[$variant->id]->quantity_available ?? 0);

                    if ($available < $item->quantity) {
                        $problems[] = CheckoutProblem::insufficientStock($item, $available);
                    }
                }

                if ($variant->price_amount->minorUnits !== $item->unit_price_amount->minorUnits) {
                    $problems[] = CheckoutProblem::priceChanged($item, $variant->price_amount);
                }
            }

            if ($problems !== []) {
                throw new CheckoutValidationException($problems);   // transaction rolls back
            }

            // 4. Re-validate the coupon at placement — limits may have been consumed since.
            if ($cart->coupon) {
                $this->coupons->assertValid($cart->coupon, $cart, $data->email);
            }

            // 5. Recalculate totals server-side. Never trust a total from the browser.
            $totals = $this->calculator->recalculate($cart, $data->shippingCity, $data->paymentMethod);

            // 6. Create the order, snapshot everything (§1.9, §2.6).
            $order = $this->buildOrder($cart, $data, $totals);

            // 7. Decrement stock and write the ledger, still inside the lock.
            foreach ($items as $item) {
                $this->inventory->commitForOrder($order, $item, $inventories[$item->product_variant_id] ?? null);
            }

            // 8. Close the cart.
            $cart->update([
                'status'             => CartStatus::Converted,
                'converted_order_id' => $order->id,
            ]);

            return $order;
        }, attempts: 3);
    }
}
```

Six things that sequence gets right:

1. **`lockForUpdate()` on the cart** makes a double-clicked "Place order" button harmless — the second request blocks, then finds the cart already `converted` and throws.
2. **Locking inventory rows in `id` order** prevents the classic deadlock where two concurrent orders grab the same two variants in opposite sequence.
3. **Availability *and* price are re-validated** against live data. A price snapshot taken at add-to-cart can be hours old.
4. **Problems are collected, not thrown on the first failure.** A customer with three unavailable items should be told about all three at once, not made to retry three times.
5. **Totals are recalculated server-side.** The browser's number is display state; it is never an input to what gets charged.
6. **`attempts: 3`** lets Laravel retry on deadlock, which under real concurrency will happen occasionally regardless of care taken.

Problems surface in the Livewire component as actionable messages ("Nude Rose — only 1 left, quantity reduced"), with the cart adjusted, rather than as a generic failure.

### 5.8 The order state machine

`OrderStatus::canTransitionTo()` (§3.3) is the single definition. The Filament UI reads it to decide which buttons to show; the service reads it to decide whether to obey.

```
                 ┌──────────────► cancelled ◄────────────┐
                 │                    ▲                  │
   pending ──► confirmed ────────► shipped ────────► delivered
                 │                    │                  │
                 └────────► refunded ◄┴──────────────────┘
```

| From | To | Side effects |
|---|---|---|
| `pending` | `confirmed` | Commit reservations; decrement stock; confirmation email/SMS; stamp `confirmed_at` |
| `pending` | `cancelled` | Release reservations; stamp `cancelled_at` + reason |
| `confirmed` | `shipped` | Require tracking number + courier; shipped notification; stamp `shipped_at` |
| `confirmed` | `cancelled` | **Restock**; release reservations |
| `shipped` | `delivered` | COD: mark payment collected; stamp `delivered_at`; trigger review request (Phase 10) |
| `shipped` | `cancelled` | Restock on return receipt; requires an explicit admin note |
| `delivered`/`shipped` | `refunded` | Record refund via the payment driver; optional restock; stamp `refunded_at` |

`cancelled` and `refunded` are terminal — `allowedTransitions()` returns `[]`, and `isTerminal()` makes that checkable rather than implied.

```php
final class OrderStateMachine
{
    public function transition(Order $order, OrderStatus $to, array $context = []): Order
    {
        return DB::transaction(function () use ($order, $to, $context) {
            $order = Order::whereKey($order->id)->lockForUpdate()->firstOrFail();
            $from  = $order->status;

            if ($from === $to) {
                return $order;               // idempotent — a double-clicked button is a no-op
            }

            if (! $from->canTransitionTo($to)) {
                throw new InvalidOrderTransitionException($order, $from, $to);
            }

            $order->fill(['status' => $to, ...$this->timestampFor($to), ...$this->contextColumns($to, $context)])->save();

            OrderStatusHistory::create([
                'order_id'          => $order->id,
                'from_status'       => $from->value,
                'to_status'         => $to->value,
                'user_id'           => auth()->id(),
                'note'              => $context['note'] ?? null,
                'customer_notified' => (bool) ($context['notify_customer'] ?? false),
            ]);

            match ($to) {
                OrderStatus::Confirmed => $this->onConfirmed($order, $context),
                OrderStatus::Shipped   => $this->onShipped($order, $context),
                OrderStatus::Delivered => $this->onDelivered($order, $context),
                OrderStatus::Cancelled => $this->onCancelled($order, $from, $context),
                OrderStatus::Refunded  => $this->onRefunded($order, $context),
                default                => null,
            };

            event(new OrderStatusChanged($order, $from, $to));

            return $order;
        });
    }
}
```

> **`$from === $to` returns early rather than throwing.** Transitions arrive from admin clicks, webhooks, and scheduled jobs, and all three can duplicate. An idempotent no-op is correct; an exception would turn a harmless double-delivery webhook into a support ticket.
>
> **Notifications are dispatched from the `OrderStatusChanged` event listener, queued**, not inline. An SMS gateway timing out must never roll back a status transition that has already decremented stock.
---

## 6. Payment Driver Architecture

### 6.1 The design constraint

The requirement is that JazzCash or Easypaisa can be added later **without refactoring checkout**. That is only achievable if checkout never learns what a payment method *is*. So the contract is built around the one question checkout actually needs answered:

> "I have placed this order. What happens next — am I done, do I redirect the customer somewhere, or do I show them instructions?"

Every payment method in existence answers with one of those three. COD answers "done". Bank transfer answers "show instructions". JazzCash answers "redirect". Checkout branches on the *answer*, not on the driver, so adding a fourth driver changes zero lines of checkout code.

The second constraint is that gateways push state asynchronously. Verification and callback handling therefore live on the driver too, behind a single webhook route that dispatches by driver key.

### 6.2 The contract

```php
namespace App\Contracts\Payments;

use App\Models\Order;
use App\Models\Payment;
use App\Support\Money;
use Illuminate\Http\Request;

interface PaymentDriver
{
    /** Stable machine key. Persisted in orders.payment_method and payments.driver. Never rename. */
    public function key(): string;

    /** Customer-facing label at checkout. */
    public function label(): string;

    public function description(): ?string;

    public function iconPath(): ?string;

    /** @return array<int, PaymentCapability> */
    public function capabilities(): array;

    /**
     * Is this method offerable for this basket right now?
     * COD returns false above a value ceiling or outside serviceable cities;
     * a gateway returns false when its credentials are unconfigured.
     */
    public function isAvailableFor(PaymentContext $context): bool;

    /** Any surcharge this method adds — the COD collection fee. Money::zero() for most. */
    public function surchargeFor(PaymentContext $context): Money;

    /**
     * Begin payment for a placed order. Creates the Payment row.
     * MUST be idempotent on $idempotencyKey: calling twice returns the same outcome
     * and never creates a second charge.
     */
    public function initiate(Order $order, string $idempotencyKey): PaymentInitiation;

    /**
     * Handle an inbound gateway callback/webhook. Implementations MUST verify
     * authenticity (HMAC/signature) before trusting any field.
     */
    public function handleCallback(Request $request): CallbackResult;

    /** Re-check a payment's state. Manual drivers consult the admin decision; gateways poll. */
    public function verify(Payment $payment): PaymentOutcome;

    /** Full or partial refund. Manual drivers record intent; gateways call the API. */
    public function refund(Payment $payment, Money $amount, string $reason): PaymentOutcome;
}
```

```php
namespace App\Contracts\Payments;

enum PaymentCapability
{
    case Redirect;              // sends the customer off-site
    case Webhook;               // receives asynchronous callbacks
    case ManualVerification;    // a human approves it
    case ProofUpload;           // customer uploads evidence
    case Refund;                // programmatic refunds
    case CollectOnDelivery;     // money arrives at the door, not at checkout
}
```

### 6.3 Value objects

```php
namespace App\Contracts\Payments;

use App\Models\Payment;

enum PaymentAction
{
    case Completed;     // nothing further — go to the thank-you page
    case Redirect;      // POST/GET the customer to the gateway
    case Instructions;  // render on-site instructions (bank details, proof upload)
    case Failed;
}

final readonly class PaymentInitiation
{
    private function __construct(
        public PaymentAction $action,
        public Payment $payment,
        public ?string $redirectUrl = null,
        public string $redirectMethod = 'POST',
        /** @var array<string, string> */
        public array $redirectFields = [],
        public ?string $instructionsView = null,
        /** @var array<string, mixed> */
        public array $instructionsData = [],
        public ?string $failureMessage = null,
    ) {}

    public static function completed(Payment $payment): self
    {
        return new self(PaymentAction::Completed, $payment);
    }

    /** @param array<string, string> $fields */
    public static function redirect(Payment $payment, string $url, array $fields = [], string $method = 'POST'): self
    {
        return new self($payment->action ?? PaymentAction::Redirect, $payment,
            redirectUrl: $url, redirectMethod: $method, redirectFields: $fields);
    }

    /** @param array<string, mixed> $data */
    public static function instructions(Payment $payment, string $view, array $data = []): self
    {
        return new self(PaymentAction::Instructions, $payment, instructionsView: $view, instructionsData: $data);
    }

    public static function failed(Payment $payment, string $message): self
    {
        return new self(PaymentAction::Failed, $payment, failureMessage: $message);
    }
}

final readonly class PaymentOutcome
{
    public function __construct(
        public bool $successful,
        public \App\Enums\PaymentAttemptStatus $status,
        public ?string $reference = null,
        public ?string $message = null,
        /** @var array<string, mixed> */
        public array $raw = [],
    ) {}
}

final readonly class CallbackResult
{
    public function __construct(
        public bool $verified,
        public ?Payment $payment,
        public PaymentOutcome $outcome,
        public ?string $redirectUrl = null,
        public string $acknowledgement = 'OK',
    ) {}
}

final readonly class PaymentContext
{
    public function __construct(
        public \App\Support\Money $subtotal,
        public \App\Support\Money $grandTotal,
        public ?string $city = null,
        public ?string $province = null,
        public ?\App\Models\User $user = null,
        public int $itemsCount = 0,
    ) {}
}
```

> `PaymentInitiation`'s private constructor plus named factories is doing real work: it makes the illegal states unconstructible. There is no way to produce a `Redirect` initiation without a URL, or a `Failed` one without a message. Checkout can `match` on `$initiation->action` exhaustively and be certain the fields it needs are populated.

### 6.4 The manager

```php
namespace App\Services\Payments;

use App\Contracts\Payments\{PaymentContext, PaymentDriver};
use Illuminate\Contracts\Container\Container;

final class PaymentManager
{
    /** @var array<string, PaymentDriver> */
    private array $resolved = [];

    public function __construct(
        private readonly Container $container,
        /** @var array<string, array{driver: class-string<PaymentDriver>, enabled: bool, config: array}> */
        private readonly array $config,
    ) {}

    public function driver(string $key): PaymentDriver
    {
        return $this->resolved[$key] ??= $this->resolve($key);
    }

    /** @return array<int, PaymentDriver> Ordered, filtered to what this basket may use. */
    public function availableFor(PaymentContext $context): array
    {
        return collect($this->config)
            ->filter(fn (array $c) => $c['enabled'] ?? false)
            ->keys()
            ->map(fn (string $key) => $this->driver($key))
            ->filter(fn (PaymentDriver $d) => $d->isAvailableFor($context))
            ->values()
            ->all();
    }

    private function resolve(string $key): PaymentDriver
    {
        $config = $this->config[$key]
            ?? throw new \App\Exceptions\UnknownPaymentDriverException("No payment driver registered for [{$key}].");

        return $this->container->make($config['driver'], ['config' => $config['config'] ?? []]);
    }
}
```

```php
// config/payments.php
return [
    'default' => 'cod',

    'drivers' => [
        'cod' => [
            'driver'  => \App\Services\Payments\Drivers\CashOnDeliveryDriver::class,
            'enabled' => env('PAYMENTS_COD_ENABLED', true),
            'config'  => [
                'max_order_amount'   => 5_000_000,   // paisa — Rs 50,000 ceiling
                'fee_amount'         => 15_000,      // paisa — Rs 150 collection fee
                'serviceable_cities' => null,        // null = everywhere
            ],
        ],

        'bank_transfer' => [
            'driver'  => \App\Services\Payments\Drivers\BankTransferDriver::class,
            'enabled' => env('PAYMENTS_BANK_TRANSFER_ENABLED', true),
            'config'  => [
                'payment_window_hours' => 48,
                'require_proof'        => true,
            ],
        ],

        // Drop-in later. No checkout change required.
        'jazzcash' => [
            'driver'  => \App\Services\Payments\Drivers\JazzCashDriver::class,
            'enabled' => env('PAYMENTS_JAZZCASH_ENABLED', false),
            'config'  => [
                'merchant_id'    => env('JAZZCASH_MERCHANT_ID'),
                'password'       => env('JAZZCASH_PASSWORD'),
                'integrity_salt' => env('JAZZCASH_INTEGRITY_SALT'),
                'endpoint'       => env('JAZZCASH_ENDPOINT'),
            ],
        ],
    ],
];
```

Bound in `AppServiceProvider`:

```php
$this->app->singleton(PaymentManager::class, fn ($app) =>
    new PaymentManager($app, config('payments.drivers')));
```

### 6.5 Cash on delivery

```php
namespace App\Services\Payments\Drivers;

use App\Contracts\Payments\{CallbackResult, PaymentCapability, PaymentContext, PaymentDriver, PaymentInitiation, PaymentOutcome};
use App\Enums\PaymentAttemptStatus;
use App\Models\{Order, Payment};
use App\Support\Money;
use Illuminate\Http\Request;

final class CashOnDeliveryDriver implements PaymentDriver
{
    public function __construct(private readonly array $config = []) {}

    public function key(): string { return 'cod'; }
    public function label(): string { return 'Cash on Delivery'; }
    public function description(): ?string { return 'Pay the courier in cash when your order arrives.'; }
    public function iconPath(): ?string { return 'images/payments/cod.svg'; }

    public function capabilities(): array
    {
        return [PaymentCapability::CollectOnDelivery, PaymentCapability::ManualVerification];
    }

    public function isAvailableFor(PaymentContext $context): bool
    {
        $max = (int) ($this->config['max_order_amount'] ?? PHP_INT_MAX);

        if ($context->grandTotal->minorUnits > $max) {
            return false;
        }

        $cities = $this->config['serviceable_cities'] ?? null;

        return $cities === null
            || in_array(mb_strtolower((string) $context->city), $cities, strict: true);
    }

    public function surchargeFor(PaymentContext $context): Money
    {
        return new Money((int) ($this->config['fee_amount'] ?? 0));
    }

    public function initiate(Order $order, string $idempotencyKey): PaymentInitiation
    {
        $payment = Payment::firstOrCreate(
            ['idempotency_key' => $idempotencyKey],
            [
                'order_id' => $order->id,
                'driver'   => $this->key(),
                'status'   => PaymentAttemptStatus::Pending,
                'currency' => $order->currency,
                'amount'   => $order->grand_total_amount->minorUnits,
            ],
        );

        // Nothing to collect now — the courier collects at the door.
        return PaymentInitiation::completed($payment);
    }

    public function handleCallback(Request $request): CallbackResult
    {
        throw new \LogicException('COD has no callback surface.');
    }

    /** Settled by the Delivered transition, which calls markCollected(). */
    public function verify(Payment $payment): PaymentOutcome
    {
        return new PaymentOutcome(
            successful: $payment->status === PaymentAttemptStatus::Paid,
            status: $payment->status,
        );
    }

    public function markCollected(Payment $payment, ?int $collectedMinorUnits = null): PaymentOutcome
    {
        $payment->forceFill([
            'status'  => PaymentAttemptStatus::Paid,
            'amount'  => $collectedMinorUnits ?? $payment->amount,
            'paid_at' => now(),
        ])->save();

        return new PaymentOutcome(true, PaymentAttemptStatus::Paid, message: 'Cash collected on delivery.');
    }

    public function refund(Payment $payment, Money $amount, string $reason): PaymentOutcome
    {
        // Cash refunds happen off-system; record the intent for reconciliation.
        $payment->increment('refunded_amount', $amount->minorUnits);

        return new PaymentOutcome(true, PaymentAttemptStatus::Refunded,
            message: "Manual cash refund of {$amount->format()} recorded. Settle offline.");
    }
}
```

> `isAvailableFor()` doing the value ceiling is the important line. Unbounded COD is how Pakistani stores lose money to refused high-value deliveries; making it a config value rather than a hardcoded rule means the client can tune it without a deploy.

### 6.6 Bank transfer

```php
final class BankTransferDriver implements PaymentDriver
{
    public function __construct(private readonly array $config = []) {}

    public function key(): string { return 'bank_transfer'; }
    public function label(): string { return 'Bank Transfer'; }
    public function description(): ?string { return 'Transfer to our account and upload your receipt. We verify within one business day.'; }

    public function capabilities(): array
    {
        return [PaymentCapability::ManualVerification, PaymentCapability::ProofUpload, PaymentCapability::Refund];
    }

    public function isAvailableFor(PaymentContext $context): bool
    {
        return \App\Models\BankAccount::where('is_active', true)->exists();
    }

    public function surchargeFor(PaymentContext $context): Money { return new Money(0); }

    public function initiate(Order $order, string $idempotencyKey): PaymentInitiation
    {
        $account = \App\Models\BankAccount::where('is_active', true)->orderBy('position')->firstOrFail();
        $hours   = (int) ($this->config['payment_window_hours'] ?? 48);

        $payment = Payment::firstOrCreate(
            ['idempotency_key' => $idempotencyKey],
            [
                'order_id'        => $order->id,
                'driver'          => $this->key(),
                'status'          => PaymentAttemptStatus::Pending,
                'currency'        => $order->currency,
                'amount'          => $order->grand_total_amount->minorUnits,
                'bank_account_id' => $account->id,
                'expires_at'      => now()->addHours($hours),
            ],
        );

        return PaymentInitiation::instructions($payment, 'checkout.instructions.bank-transfer', [
            'account'   => $account,
            'amount'    => $order->grand_total_amount,
            'reference' => $order->order_number,
            'expiresAt' => $payment->expires_at,
        ]);
    }

    public function handleCallback(Request $request): CallbackResult
    {
        throw new \LogicException('Bank transfer is verified by an administrator, not by callback.');
    }

    public function verify(Payment $payment): PaymentOutcome
    {
        $approved = $payment->proofs()->where('status', 'approved')->exists();

        return new PaymentOutcome(
            successful: $approved,
            status: $approved ? PaymentAttemptStatus::Paid : PaymentAttemptStatus::AwaitingVerification,
        );
    }

    /** Called by the Filament approve action. */
    public function approveProof(\App\Models\PaymentProof $proof, \App\Models\User $admin): PaymentOutcome
    {
        return \DB::transaction(function () use ($proof, $admin) {
            $proof->forceFill([
                'status' => 'approved', 'reviewed_by_user_id' => $admin->id, 'reviewed_at' => now(),
            ])->save();

            $payment = $proof->payment;
            $payment->forceFill([
                'status'               => PaymentAttemptStatus::Paid,
                'reference'            => $proof->declared_reference,
                'verified_by_user_id'  => $admin->id,
                'verified_at'          => now(),
                'paid_at'              => now(),
            ])->save();

            return new PaymentOutcome(true, PaymentAttemptStatus::Paid, reference: $payment->reference);
        });
    }

    public function refund(Payment $payment, Money $amount, string $reason): PaymentOutcome
    {
        $payment->increment('refunded_amount', $amount->minorUnits);

        return new PaymentOutcome(true, PaymentAttemptStatus::Refunded,
            message: "Refund of {$amount->format()} recorded. Transfer manually and note the reference.");
    }
}
```

An `ExpireStalePayments` scheduled command releases stock reservations and cancels orders whose bank-transfer window lapsed without an approved proof.

### 6.7 How JazzCash slots in

The sketch below is **structural, not a working integration** — it shows the shape the contract imposes. Field names and the exact hash construction must be taken from JazzCash's current merchant integration document at the time you build it; do not implement this from memory, including mine.

```php
final class JazzCashDriver implements PaymentDriver
{
    public function __construct(private readonly array $config = []) {}

    public function key(): string { return 'jazzcash'; }
    public function label(): string { return 'JazzCash'; }

    public function capabilities(): array
    {
        return [PaymentCapability::Redirect, PaymentCapability::Webhook, PaymentCapability::Refund];
    }

    public function isAvailableFor(PaymentContext $context): bool
    {
        return filled($this->config['merchant_id']) && filled($this->config['integrity_salt']);
    }

    public function surchargeFor(PaymentContext $context): Money { return new Money(0); }

    public function initiate(Order $order, string $idempotencyKey): PaymentInitiation
    {
        $payment = Payment::firstOrCreate(
            ['idempotency_key' => $idempotencyKey],
            [
                'order_id' => $order->id, 'driver' => $this->key(),
                'status'   => PaymentAttemptStatus::Pending,
                'currency' => $order->currency,
                'amount'   => $order->grand_total_amount->minorUnits,
            ],
        );

        $fields = $this->buildRequestFields($order, $payment);
        $fields['pp_SecureHash'] = $this->signature($fields);

        $payment->update(['gateway_request' => $this->redact($fields)]);

        return PaymentInitiation::redirect($payment, $this->config['endpoint'], $fields, 'POST');
    }

    public function handleCallback(Request $request): CallbackResult
    {
        $payload = $request->all();

        // Verify BEFORE trusting a single field.
        if (! hash_equals($this->signature($payload), (string) ($payload['pp_SecureHash'] ?? ''))) {
            return new CallbackResult(false, null,
                new PaymentOutcome(false, PaymentAttemptStatus::Failed, message: 'Signature mismatch'),
                acknowledgement: 'INVALID');
        }

        $payment = Payment::where('driver', $this->key())
            ->where('gateway_transaction_id', $payload['pp_TxnRefNo'] ?? null)
            ->firstOrFail();

        // Amount must match what we asked for — never trust the gateway's echoed total.
        if ((int) ($payload['pp_Amount'] ?? 0) !== $payment->amount) {
            return new CallbackResult(false, $payment,
                new PaymentOutcome(false, PaymentAttemptStatus::Failed, message: 'Amount mismatch'));
        }

        $success = ($payload['pp_ResponseCode'] ?? null) === '000';
        /* ... persist status, redacted response, paid_at ... */

        return new CallbackResult(true, $payment, new PaymentOutcome(
            $success,
            $success ? PaymentAttemptStatus::Paid : PaymentAttemptStatus::Failed,
            reference: $payload['pp_RetreivalReferenceNo'] ?? null,
        ));
    }

    public function verify(Payment $payment): PaymentOutcome { /* status inquiry API */ }
    public function refund(Payment $payment, Money $amount, string $reason): PaymentOutcome { /* refund API */ }

    /** @param array<string, string> $fields */
    private function signature(array $fields): string
    {
        unset($fields['pp_SecureHash']);
        ksort($fields);

        return strtoupper(hash_hmac('sha256',
            $this->config['integrity_salt'].'&'.implode('&', array_filter($fields, 'strlen')),
            $this->config['integrity_salt']));
    }

    /** Never persist credentials into gateway_request/gateway_response. */
    private function redact(array $fields): array
    {
        return \Illuminate\Support\Arr::except($fields, ['pp_Password', 'pp_SecureHash']);
    }
}
```

Three rules that any gateway driver must honour, and that the contract makes natural to honour:

1. **Verify the signature before reading any field.** A callback endpoint is public; anything unsigned in it is attacker-controlled.
2. **Re-check the amount against your own `payments.amount`.** A gateway echoing back a total is not proof of what was charged.
3. **Redact secrets before writing `gateway_request` / `gateway_response`.** Those JSON columns will end up in a database export sooner or later.

### 6.8 The one webhook route

```php
// routes/web.php
Route::post('/payments/{driver}/callback', PaymentCallbackController::class)
    ->name('payments.callback')
    ->withoutMiddleware([\Illuminate\Foundation\Http\Middleware\PreventRequestForgery::class]);
```

```php
final class PaymentCallbackController
{
    public function __construct(private readonly PaymentManager $payments, private readonly OrderStateMachine $orders) {}

    public function __invoke(Request $request, string $driver): Response
    {
        $result = $this->payments->driver($driver)->handleCallback($request);

        if (! $result->verified) {
            Log::warning('Rejected payment callback', ['driver' => $driver, 'ip' => $request->ip()]);

            return response($result->acknowledgement, 400);
        }

        if ($result->outcome->successful && $result->payment) {
            $this->orders->paymentSucceeded($result->payment);
        }

        return $result->redirectUrl
            ? redirect()->to($result->redirectUrl)
            : response($result->acknowledgement);
    }
}
```

One route, forever. Adding Easypaisa means writing a driver class and adding a config entry — no route, no controller, and no checkout change. That is the whole point of the exercise.

> CSRF is excluded because the caller is a gateway server, not a browser session. The *replacement* for CSRF is the driver's signature verification — which is why `handleCallback()` returning `verified: false` must be treated as a hard 400 and logged, not swallowed.
---

## 7. SEO-Critical Backend Concerns

SEO is a hard requirement, and Livewire 4 was chosen specifically to keep HTML server-rendered. That decision only pays off if the backend also gets URLs, redirects, metadata, and sitemaps right — a perfectly server-rendered page at a URL that changed last week is still lost traffic.

### 7.1 Slug generation and uniqueness

`spatie/laravel-sluggable` with the shared `HasSeoSlug` trait (§3.2). Three rules:

1. **Slugs are globally unique per model**, enforced by a unique index — not just by the package's in-PHP uniqueness check, which races under concurrent creation.
2. **Slugs never regenerate on update** (`doNotGenerateSlugsOnUpdate()`).
3. **Category slugs are flat**, so URLs are `/category/matte-lipstick`, not `/makeup/lips/matte-lipstick`.

Rule 3 deserves its justification. Nested URL paths look tidier and are frequently requested. But they couple a product or category's URL to its position in a tree that merchandisers reorganise constantly — and every reorganisation then rewrites hundreds of URLs at once, converting a routine catalogue edit into a mass redirect event. Flat slugs with a breadcrumb trail derived from `parent_id` give you the identical breadcrumb UI and identical `BreadcrumbList` structured data, with URLs that survive reorganisation. Take the tidiness loss.

**URL map:**

| Route | Pattern |
|---|---|
| Product | `/products/{slug}` |
| Category | `/category/{slug}` |
| Blog index / post | `/blog`, `/blog/{slug}` |
| Blog category | `/blog/category/{slug}` |
| Static page | `/{slug}` — catch-all, registered **last** |
| Halal ingredient pages | `/halal-ingredients`, `/halal-ingredients/{slug}` — §2.7 |
| "Free from" landing | `/collections/{slug}` |
| Certification body | `/halal-certification/{slug}` |

> The `/{slug}` page catch-all must be the final route registered in `routes/web.php`, and must exclude reserved prefixes (`admin`, `livewire`, `storage`, `api`). Otherwise a page slugged `products` shadows the entire catalogue.

Reserve a blocklist at validation time (`admin`, `api`, `livewire`, `storage`, `cart`, `checkout`, `login`, `register`, `account`, `orders`, `products`, `category`, `blog`, `collections`, `ingredients`, `sitemap.xml`, `robots.txt`) so an admin cannot slug a page into a collision.

### 7.2 Slug changes and redirects

This is the mechanism that turns the highest-frequency SEO own-goal into a non-event.

```php
// 2026_08_10_001210_create_slug_histories_table.php
Schema::create('slug_histories', function (Blueprint $table) {
    $table->id();
    $table->morphs('sluggable');
    $table->string('slug', 240);
    $table->string('model_type_key', 40)->comment('product|category|blog_post|page — for URL reconstruction.');
    $table->timestamp('created_at')->useCurrent();

    $table->unique(['sluggable_type', 'slug'], 'slug_histories_type_slug_unique');
    $table->index(['sluggable_type', 'sluggable_id'], 'slug_histories_model_index');
});

// 2026_08_10_001220_create_redirects_table.php
Schema::create('redirects', function (Blueprint $table) {
    $table->id();
    $table->string('from_path', 512)->comment('Normalised: leading slash, no query string, no trailing slash.');
    $table->string('to_path', 512);
    $table->unsignedSmallInteger('status_code')->default(301);
    $table->string('source', 20)->default('manual')->comment('manual|slug_change|import');
    $table->boolean('is_active')->default(true);
    $table->unsignedInteger('hits')->default(0);
    $table->timestamp('last_hit_at')->nullable();
    $table->timestamps();

    $table->unique('from_path', 'redirects_from_path_unique');
    $table->index(['is_active', 'from_path'], 'redirects_active_path_index');
});
```

**Two layers, because they fail differently.**

*Layer 1 — slug history resolution.* Route-model binding first tries the live slug; on miss it consults `slug_histories` and issues a 301 to the current URL. This handles slug changes automatically and permanently, with no admin action.

```php
// App\Providers\AppServiceProvider::boot()
Route::bind('productSlug', function (string $slug) {
    if ($product = Product::published()->where('slug', $slug)->first()) {
        return $product;
    }

    $historical = SlugHistory::where('sluggable_type', Product::class)->where('slug', $slug)->first();

    abort_unless($historical, 404);

    // 301 out of the binder — the old URL must never render 200 with duplicate content.
    abort(redirect()->away(route('products.show', $historical->sluggable->slug), 301));
});
```

*Layer 2 — the `redirects` table*, consulted by middleware on any 404 before the error page renders. This covers everything history cannot: migrated URLs from a previous site, deleted products pointed at their category, campaign vanity URLs, and typo fixes.

```php
final class HandleRedirects
{
    public function handle(Request $request, Closure $next): Response
    {
        $response = $next($request);

        if ($response->getStatusCode() !== 404) {
            return $response;
        }

        $path = '/'.trim($request->path(), '/');

        $redirect = Cache::remember("redirect:{$path}", now()->addHour(),
            fn () => Redirect::where('is_active', true)->where('from_path', $path)->first());

        if (! $redirect) {
            return $response;
        }

        RecordRedirectHit::dispatch($redirect->id)->afterResponse();

        return redirect()->to($redirect->to_path, $redirect->status_code);
    }
}
```

> Running **after** the response and only on 404 means zero cost on the 99.9% of requests that resolve normally. Putting redirect lookup in front of every request is a database query per pageview to serve a rounding error's worth of traffic.
>
> `hits` and `last_hit_at` are recorded asynchronously (`afterResponse()`) — a redirect must never be slowed down by its own analytics. They earn their place by telling you which legacy URLs still receive traffic, and which redirect rules are dead weight.

**Changing a slug is an explicit admin action**, not a side effect of editing a field (§4.5 disables the slug input on edit):

```php
Action::make('changeUrl')
    ->label('Change URL')
    ->icon('heroicon-o-link')
    ->requiresConfirmation()
    ->modalDescription('The current URL will 301-redirect to the new one. Rankings usually recover, but never instantly — only do this if the URL is genuinely wrong.')
    ->schema([
        TextInput::make('slug')->required()->maxLength(220)
            ->rule(fn (Product $record) => Rule::unique('products', 'slug')->ignore($record)),
    ])
    ->action(function (Product $record, array $data) {
        DB::transaction(function () use ($record, $data) {
            $old = $record->slug;

            SlugHistory::firstOrCreate([
                'sluggable_type' => $record::class,
                'sluggable_id'   => $record->id,
                'slug'           => $old,
            ], ['model_type_key' => 'product']);

            Redirect::updateOrCreate(
                ['from_path' => "/products/{$old}"],
                ['to_path' => "/products/{$data['slug']}", 'status_code' => 301, 'source' => 'slug_change', 'is_active' => true],
            );

            $record->update(['slug' => $data['slug']]);
            Cache::forget("redirect:/products/{$old}");
        });
    });
```

**Redirect chains.** When a slug changes twice, `a → b` and `b → c` become a chain, and chains dilute link equity and eventually stop being followed. Before inserting, rewrite any existing redirect whose `to_path` equals the old path to point at the new destination. Do it in the same transaction, and a nightly `seo:flatten-redirects` command catches anything inserted manually.

### 7.3 Meta fields on every content model

A polymorphic `seo_metas` table, not meta columns duplicated across six tables.

```php
// 2026_08_10_001200_create_seo_metas_table.php
Schema::create('seo_metas', function (Blueprint $table) {
    $table->id();
    $table->morphs('seoable');
    $table->string('meta_title', 180)->nullable();
    $table->string('meta_description', 320)->nullable();
    $table->string('canonical_url', 512)->nullable();
    $table->string('og_title', 180)->nullable();
    $table->string('og_description', 320)->nullable();
    $table->string('og_image_path')->nullable();
    $table->string('og_type', 40)->nullable()->default('website');
    $table->string('twitter_card', 40)->nullable()->default('summary_large_image');
    $table->boolean('is_indexable')->default(true);
    $table->boolean('is_followable')->default(true);
    $table->json('structured_data_overrides')->nullable();
    $table->timestamps();

    $table->unique(['seoable_type', 'seoable_id'], 'seo_metas_seoable_unique');
});
```

The trade-off is one extra join per page — negligible, and always eager-loaded. What it buys: adding `twitter_card` later is one migration instead of six, and the Filament SEO tab (§4.5) is a single reusable schema class mounted on every resource.

```php
namespace App\Models\Concerns;

trait HasSeoMeta
{
    public function seoMeta(): \Illuminate\Database\Eloquent\Relations\MorphOne
    {
        return $this->morphOne(\App\Models\SeoMeta::class, 'seoable');
    }

    /** Resolved metadata with a sane fallback chain. Never returns empty strings. */
    public function seo(): \App\Support\Seo\ResolvedSeo
    {
        return \App\Support\Seo\SeoResolver::for($this);
    }
}
```

The fallback chain matters more than the table. An empty `<title>` is worse than an imperfect generated one, so `SeoResolver` resolves in order:

| Field | Resolution order |
|---|---|
| `title` | `seoMeta.meta_title` → model name/title → `SeoSettings::default_meta_title` |
| `description` | `seoMeta.meta_description` → `short_description`/`excerpt` → first 160 chars of body text, tags stripped → settings default |
| `og_image` | `seoMeta.og_image_path` → primary product image → cover image → settings default |
| `canonical` | `seoMeta.canonical_url` → the model's own route |
| `robots` | `SeoSettings::noindex_entire_site` → `seoMeta.is_indexable` → derived (unpublished ⇒ `noindex`) |

> `SeoSettings::noindex_entire_site` short-circuits everything as a **staging safety switch**. Getting a staging environment indexed and cannibalising the production site is a genuinely common, genuinely expensive accident. One boolean, checked first.

**Canonical rules that must be enforced server-side**, because they cannot be fixed in a template later:

- **Paginated listings**: page 2+ is self-canonical, never canonical to page 1. Canonicalising pagination to page 1 de-indexes deeper products.
- **Faceted filters**: any URL carrying a filter combination beyond a single curated facet gets `noindex, follow`. Left unmanaged, a catalogue with 12 "free from" attributes generates thousands of thin, near-duplicate crawlable URLs and burns the crawl budget that should be spent on products.
- **Curated facets are the exception**: `/collections/alcohol-free` is a real page with its own `description`, its own `seo_metas` row, and full indexability — which is exactly why `free_from_attributes.has_landing_page` (§2.4) exists as a column. One indexable page per *marketable* facet, `noindex` for arbitrary combinations.
- **Sort orders and tracking parameters** (`?sort=`, `?utm_*`) canonical to the clean URL.

### 7.4 Structured data

Emitted server-side as JSON-LD from the same models, so it cannot drift from the visible page:

| Page | Schema.org types |
|---|---|
| Product | `Product` + `Offer` (price, `PKR`, availability from `total_stock`), `AggregateRating` once reviews exist, `BreadcrumbList` |
| Category | `CollectionPage`, `BreadcrumbList`, `ItemList` |
| Blog post | `BlogPosting` (author, `datePublished`, `dateModified`, `image`), `BreadcrumbList` |
| Static page | `WebPage` / `FAQPage` when `template = faq` |
| Halal ingredient page | `DefinedTerm` within a `DefinedTermSet`, `BreadcrumbList`, optional `FAQPage` (§2.7) |
| Global | `Organization` (+ `logo`, `sameAs`), `WebSite` with `SearchAction` |

> Only emit `AggregateRating` from **approved, order-backed** reviews (§1.13). Rating markup that a visitor cannot see on the page is a manual-action risk, and losing rich results on a cosmetics catalogue is a large CTR hit.
>
> `Offer.availability` must read from live stock. `InStock` markup on a sold-out product is both a ranking liability and a bad customer experience.

#### Price parity between the rendered page and `Product.offers.price`

The SEO specification requires that the price a visitor sees is server-rendered and **exactly** matches the JSON-LD `Product.offers.price`. A mismatch is not cosmetic: Google treats a structured-data price that disagrees with the visible price as a rich-result violation, and the penalty is loss of the price snippet on every product.

The architecture already makes this achievable — the requirement is only that it is enforced in one place rather than trusted.

**Both values must derive from the same `Money` instance in the same request.** Never format the display price in Blade and separately query the price for the JSON-LD builder; that is exactly how the two drift when a sale price lands mid-request or a variant is switched.

```php
namespace App\Support\Seo;

final readonly class ProductOffer
{
    public function __construct(
        public \App\Support\Money $price,
        public string $sku,
        public bool $inStock,
    ) {}

    /** What the customer reads: "Rs 2,450" */
    public function display(): string
    {
        return $this->price->format();
    }

    /** What schema.org reads: "2450.00" — same minor units, no second lookup. */
    public function structuredValue(): string
    {
        return number_format($this->price->minorUnits / 100, 2, '.', '');
    }
}
```

The page controller resolves **one** `ProductOffer` and passes it to both the Blade partial and the JSON-LD builder. Neither may compute a price independently.

Three specific traps this must survive:

1. **Prices live on variants, not products (§1.4).** The offer emitted must be the offer *displayed*. When a shade is preselected, emit that variant's `Offer` with its own `sku`. When the page opens on a range, emit an `AggregateOffer` with `lowPrice` and `highPrice` from `price_min_amount` / `price_max_amount` — never a single `Offer` carrying the minimum while the page shows a range.

2. **Livewire must not change the price after render.** `AddToCart` (§5.5) updates the displayed price when a customer picks a different shade, but the JSON-LD is emitted once, server-side, in the page layout. That is correct and intended: structured data describes the page as delivered, and crawlers do not execute the shade picker. **The initial server render must therefore emit the offer for the default variant — the same variant the page renders selected.** If those ever disagree, the mismatch is permanent and invisible.

3. **`priceCurrency` is `PKR`, and `priceValidUntil` should be set** (a year out is conventional) — Google warns on offers without it.

**Test this rather than trusting it.** A feature test that fetches a product page, extracts the rendered price string and the JSON-LD `offers.price`, and asserts they describe the same minor-unit value costs ten minutes and catches the entire class of regression. Add it in Phase 5 alongside the server-render assertions in §5.2.

### 7.5 Sitemap data sources

`spatie/laravel-sitemap` 8.2.0, generated by a scheduled command into a **sitemap index** plus per-type sitemaps — not one monolithic file. Splitting by type means a catalogue update does not invalidate the blog sitemap, and Search Console reports indexation per content type, which is how you actually diagnose coverage problems.

```
/sitemap.xml                    ← index
/sitemaps/products.xml
/sitemaps/categories.xml
/sitemaps/blog.xml
/sitemaps/pages.xml
/sitemaps/collections.xml       ← free_from_attributes with has_landing_page
/sitemaps/halal-ingredients.xml ← ingredients with has_glossary_page AND status = published
```

| Sitemap | Source query | `lastmod` | `priority` |
|---|---|---|---|
| products | `Product::published()` | `updated_at` | 0.9 featured / 0.8 in stock / 0.5 out of stock |
| categories | `Category::active()->where('products_count','>',0)` | `updated_at` | 0.7 |
| blog | `BlogPost::published()` | `updated_at` | 0.6 |
| pages | `Page::where('status','published')` | `updated_at` | 0.4 |
| collections | `FreeFromAttribute::where('has_landing_page',true)` | `updated_at` | 0.7 |
| halal-ingredients | `Ingredient::published()` (§2.7 scope) | `updated_at` | 0.6 |

**Every source query must exclude anything with `seo_metas.is_indexable = false`.** A sitemap that submits `noindex` URLs sends contradictory signals and degrades trust in the whole file.

Two hard rules: empty categories are excluded (a category page with zero products is a soft-404 waiting to happen), and `lastmod` reflects genuine content change — bumping it on every nightly regeneration trains crawlers to ignore it.

```php
// routes/console.php
Schedule::command('sitemap:generate')->dailyAt('03:00');
Schedule::command('seo:flatten-redirects')->weekly();
Schedule::command('halal:sync-certificate-status')->dailyAt('02:00');
Schedule::command('catalog:reconcile')->dailyAt('04:00');
Schedule::command('carts:expire')->hourly();
Schedule::command('payments:expire-stale')->hourly();
Schedule::command('posts:publish-scheduled')->everyFifteenMinutes();
```

`robots.txt` is served dynamically so `SeoSettings::noindex_entire_site` can flip a staging environment to `Disallow: /` without a deploy, and so `Sitemap: https://glowhalal.pk/sitemap.xml` always points at the current host. Always disallow `/cart`, `/checkout`, `/account`, `/admin`, and `/*?sort=`.

### 7.6 Backend concerns that show up as Core Web Vitals

Core Web Vitals are a ranking input, and several of them are decided in the backend:

- **`product_images.width` / `height` are stored** so `<img>` can always carry explicit dimensions. Missing dimensions cause layout shift, which is CLS, which is a ranking factor. This is why those columns exist.
- **`blurhash`** gives an inline LQIP placeholder with no extra request.
- **WebP conversions** are generated asynchronously and served with `<picture>` fallbacks.
- **Cache the header/footer navigation tree** (`Category::inMenu()` with children). It renders on every page; without caching it is 2–3 queries per request, every request.
- **Cache category product counts** rather than counting live.
- **The `products_fulltext_index`** exists so search never degrades into an unindexable `LIKE '%term%'` scan, which is the most common cause of a slow storefront.
---

## 8. Build Order

Ordered by dependency, not by visibility. Each phase ends at a point where the application still runs and something is verifiable — no phase leaves the system in a state where the next one is a prerequisite for testing the last.

### Phase 0 — Foundation (half a day)

**This project is not a git repository yet. Fix that before writing anything else.**

```bash
cd C:\laragon\www\glowhalal
git init
git add -A
git commit -m "Laravel 13.24 skeleton, pre-architecture"
```

- [ ] `git init` + initial commit, and confirm `.gitignore` covers `.env`, `/vendor`, `/node_modules`, `/storage/*.key`, `/public/build`
- [ ] `.env` is already on MySQL/`glowhalal`/`http://glowhalal.test` — confirm `php artisan migrate` connects
- [ ] Set `'timezone' => 'Asia/Karachi'` in `config/app.php`
- [ ] Delete `database/database.sqlite` and the `post-create-project-cmd` SQLite touch line in `composer.json`
- [ ] Add the `private` disk to `config/filesystems.php` (payment proofs, §1.10)
- [ ] `composer require` the §4.2 package set — **use `~5.0` not `^5.0` in PowerShell**
- [ ] `php artisan filament:install --panels`; verify `AdminPanelProvider` is in `bootstrap/providers.php`

> Committing before the architecture lands gives you a clean diff for everything that follows. It costs 30 seconds and it is the difference between "revert the schema experiment" and "manually undo 40 files".

### Phase 1 — Identity and access (1 day)

Depends on: Phase 0.

- [ ] `users` alter migration; `phone` normalisation mutator (E.164)
- [ ] `spatie/laravel-permission` migration; seed `super_admin`, `staff`, `customer`
- [ ] **`FilamentUser::canAccessPanel()` on `User`** — §4.3. Do this now, not later
- [ ] `shield:install`, generate policies
- [ ] `addresses` table + `Address` model + default-address observer
- [ ] `make:filament-user`; log in at `/admin`

**Verifiable:** an admin logs in; a user without a role cannot.

### Phase 2 — Catalogue core (3–4 days)

Depends on: Phase 1.

- [ ] `categories` + `CategoryObserver` (path/depth) + `RebuildCategorySubtreePaths` job
- [ ] `attributes`, `attribute_values`
- [ ] `products`, `category_product`, `product_variants`, `attribute_value_product_variant`, `product_images`
- [ ] Models with relations, casts, scopes; `MoneyCast` + `Money`; enums
- [ ] `HasSeoSlug`; `ProductVariantObserver` (price denormalisation, single default, auto `InventoryItem`)
- [ ] Filament: `CategoryResource`, `AttributeResource`, `ProductResource` + Variants/Images relation managers
- [ ] `GenerateImageConversions` job (Intervention Image v4)
- [ ] Factories + a seeder producing ~50 realistic products

**Verifiable:** create a product with two shades and three images entirely through the admin; `price_min_amount` updates itself.

> Seed realistic data now, not at the end. Every subsequent phase is easier to build and test against a catalogue that looks like the real one, and pagination/N+1 problems surface at 50 products, not at 5.

### Phase 3 — Halal data model (2 days)

Depends on: Phase 2.

- [ ] `certification_bodies`, `ingredients`, `free_from_attributes`
- [ ] `product_halal_profiles`, `ingredient_product`, `product_certifications`, `free_from_attribute_product`
- [ ] `ProductCertificationObserver`; `halal:sync-certificate-status` command
- [ ] Filament: `IngredientResource`, `CertificationBodyResource`, `FreeFromAttributeResource`; Halal tab and relation managers on `ProductResource`
- [ ] `ExpiringCertificationsWidget`
- [ ] **Seed certification bodies only after the client confirms exact legal names** (§2.1)
- [ ] `DeriveFreeFromClaims` job (proposes unverified claims; never publishes)

**Verifiable:** a product shows a certified badge; expiring a certificate removes it from the storefront claim path.

### Phase 4 — Inventory (1–2 days)

Depends on: Phase 2.

- [ ] `inventory_locations` (seed `main`), `inventory_items` (with the `quantity_available` generated column), `inventory_movements`, `stock_reservations`
- [ ] `InventoryService`: `adjust()`, `reserve()`, `commit()`, `release()` — all writing ledger rows
- [ ] `InventoryItemObserver` → `products.total_stock`
- [ ] Filament `InventoryResource` with an adjustment action; `LowStockWidget`
- [ ] `stock:release-expired` scheduled command

**Verifiable:** an adjustment moves `quantity_on_hand`, writes a movement row, and updates `products.total_stock`.

### Phase 5 — Storefront read paths (3–4 days)

Depends on: Phases 2, 3.

- [ ] Route map (§7.1) with the page catch-all registered **last**
- [ ] Route-model binding with slug-history fallback (§7.2)
- [ ] Home, category, product, and search pages — server-rendered Blade
- [ ] `seo_metas`, `slug_histories`, `redirects` tables; `HasSeoMeta`; `SeoResolver`; `HandleRedirects` middleware
- [ ] JSON-LD builders (§7.4); dynamic `robots.txt`; `sitemap:generate`
- [ ] Facet filtering with `noindex` on uncurated combinations
- [ ] Navigation-tree caching
- [ ] `catalog:reconcile` command

**Verifiable:** every page renders complete HTML with `curl` and JavaScript disabled — the whole reason Livewire was chosen. Check this explicitly; it is the requirement most easily lost by accident.

### Phase 6 — Cart and checkout (4–5 days)

Depends on: Phases 4, 5. **The highest-risk phase — see §5.**

- [ ] `carts`, `cart_items`, `coupons` + scope tables
- [ ] `CartManager` (resolution, cookie, merge-on-login) and `CartCalculator`
- [ ] Livewire 4 components: `AddToCart`, `MiniCart`, `CartPage`, `Checkout`
- [ ] `Authenticated` listener → cart merge
- [ ] `CouponValidator` + redemption limits (including guest-by-email)
- [ ] `shipping_zones`, `shipping_rates`; `ShippingCalculator`
- [ ] `orders`, `order_addresses`, `order_items`, `order_status_histories`, `coupon_redemptions`, deferred `carts.converted_order_id`
- [ ] `OrderNumberGenerator`; `PlaceOrderAction` with the §5.5 locking sequence
- [ ] `OrderStateMachine` + `OrderStatus::canTransitionTo()`

**Verifiable:** a guest completes a COD order; stock decrements exactly once under a concurrent double-submit.

### Phase 7 — Payments (2 days)

Depends on: Phase 6.

- [ ] `bank_accounts`, `payments`, `payment_proofs`; `private` disk wired
- [ ] `PaymentDriver` contract, value objects, `PaymentManager`, `config/payments.php`
- [ ] `CashOnDeliveryDriver`, `BankTransferDriver`
- [ ] `PaymentCallbackController` + route (unused at launch, in place for later)
- [ ] Filament `PaymentResource` verification queue; signed proof-serving route
- [ ] `payments:expire-stale` command
- [ ] Order confirmation email/SMS

**Verifiable:** a bank-transfer order accepts a proof upload, an admin approves it, and the order becomes paid. The proof is **not** reachable at a public URL — verify by pasting the path while logged out.

### Phase 8 — Content and admin polish (2–3 days)

Depends on: Phase 5.

- [ ] `blog_categories`, `blog_posts`, `tags`, `taggables`, `blog_post_product`, `pages`
- [ ] Filament resources with `RichEditor`; storefront rendering via `RichContentRenderer` (§4.5)
- [ ] Blog and page storefront routes; `posts:publish-scheduled`
- [ ] `spatie/laravel-settings` classes + Filament settings pages
- [ ] `RedirectResource`
- [ ] `CustomerResource`, `CouponResource`, `ShippingZoneResource`, `BankAccountResource`
- [ ] Dashboard widgets

### Phase 9 — Hardening and launch (2–3 days)

- [ ] Rate limiting: login, checkout, coupon application, callbacks
- [ ] Validation and authorisation review on every public write path
- [ ] `spatie/laravel-honeypot` on public forms
- [ ] Order confirmation / shipped / delivered notifications
- [ ] Queue worker + supervisor; `QUEUE_CONNECTION=database` is already set
- [ ] Backups (`spatie/laravel-backup`), error tracking, uptime monitoring
- [ ] `php artisan optimize`, OPcache, Vite production build
- [ ] Load-test the category page and checkout
- [ ] **Turn `SeoSettings::noindex_entire_site` off — and verify `robots.txt` on production**

### Phase 10 — Post-launch

`product_reviews` + `AggregateRating`; `newsletter_subscribers`; abandoned-cart recovery (the table already supports it); "shop this article" blocks; JazzCash/Easypaisa driver; Meilisearch via Scout if search quality becomes a conversion problem.

### Critical path

```
0 → 1 → 2 → 3 ─┐
        └→ 4 ──┼→ 6 → 7 → 9
        └→ 5 ──┘
                5 → 8
```

Phases 3, 4, and 5 can run in parallel once 2 lands. Phase 6 is the sequential bottleneck and the one to protect: it is where correctness bugs are most expensive and least visible, and it should not be compressed to recover time lost earlier.

### Estimate

Roughly **22–28 working days** for a single developer, excluding design and content. Phase 6 alone is a fifth of it.

---

## 9. Decisions Register

Compact record of the judgement calls, so they can be revisited deliberately rather than rediscovered.

| # | Decision | Alternative rejected | Reason |
|---|---|---|---|
| 1 | Money as integer paisa | `decimal(10,2)` | Discount apportionment and partial refunds drift with per-step rounding |
| 2 | `string` + PHP enum for statuses | MySQL `ENUM` | Adding a value must not require `ALTER TABLE` on `orders` |
| 3 | Every product has ≥1 variant | Nullable variants | Removes all `hasVariants()` branching from cart/inventory/orders |
| 4 | Dedicated `product_images` | `spatie/laravel-medialibrary` | Variant-specific galleries; `alt_text` must be first-class and indexable |
| 5 | DB-backed carts | Session-only carts | Merge-on-login, abandoned-cart recovery, reporting |
| 6 | Flat category slugs | Nested URL paths | Re-parenting a category must not rewrite hundreds of URLs |
| 7 | Polymorphic `seo_metas` | Meta columns per table | One schema change instead of six; one reusable Filament schema |
| 8 | Adjacency list + materialised path | Nested sets | Cheap writes; indexed descendant reads without recursion |
| 9 | Inventory as a ledger | A counter column | "We sold what we didn't have" must be answerable |
| 10 | `payments` as its own table | Columns on `orders` | Retries, partial payments, and multiple attempts per order |
| 11 | Orders transition via actions | An editable order form | Orders are immutable commercial records |
| 12 | Reserve stock at checkout | Reserve at add-to-cart | An open browser tab must not starve the catalogue |
| 13 | Guest checkout, nullable `user_id` | Forced registration | Account friction is fatal in the Pakistani COD market |
| 14 | `doNotGenerateSlugsOnUpdate()` | Auto-regenerating slugs | A rename must not silently discard rankings and backlinks |
| 15 | Halal facts as five related tables | A `halal_info` text blob | Filtering, expiry, audit, structured data, landing pages |
| 16 | `depends_on_source` halal status | Binary halal/haram | Glycerin, stearic acid, and collagen are genuinely source-dependent |
| 17 | Halal snapshot on `order_items` | Reading live product state | Certificates expire; the sale record must not change |
| 18 | Payment drivers return an *action* | Checkout branching per method | New gateways require zero checkout changes |
| 19 | Payment proofs on a `private` disk | The `public` disk | They are screenshots of customers' bank accounts |
| 20 | Redirects checked only on 404 | Middleware on every request | One query per pageview to serve a rounding error's traffic |
