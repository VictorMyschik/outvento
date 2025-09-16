<?php

use App\Models\Catalog\CatalogAttribute;
use App\Models\Catalog\CatalogAttributeValue;
use App\Models\Catalog\CatalogGood;
use App\Models\Catalog\CatalogGoodAttribute;
use App\Models\Catalog\CatalogGroup;
use App\Models\Catalog\CatalogGroupAttribute;
use App\Models\Catalog\CatalogImage;
use App\Models\Catalog\Manufacturer;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create(Manufacturer::getTableName(), function (Blueprint $table) {
            $table->id();
            $table->string('name')->index();
            $table->string('address', 1000)->nullable();

            $table->timestampTz('created_at')->useCurrent();
        });
        Schema::create(CatalogGroup::getTableName(), function (Blueprint $table) {
            $table->id();
            $table->string('name')->unique()->index();
            $table->jsonb('sl')->nullable();
            $table->string('json_link')->nullable();
        });

        Schema::create(CatalogGroupAttribute::getTableName(), function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('group_id')->index();
            $table->string('name', 100);
            $table->integer('sort')->default(0);

            $table->foreign('group_id')->references('id')->on(CatalogGroup::getTableName())->cascadeOnDelete();
        });

        Schema::create(CatalogAttribute::getTableName(), function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('group_attribute_id')->index();
            $table->string('name', 100);
            $table->string('description', 8000)->nullable();
            $table->integer('sort')->default(0);

            $table->foreign('group_attribute_id')->references('id')->on(CatalogGroupAttribute::getTableName())->cascadeOnDelete();
        });

        Schema::create(CatalogAttributeValue::getTableName(), function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('catalog_attribute_id')->index();
            $table->string('text_value', 8000)->nullable()->index();

            $table->unique(['catalog_attribute_id', 'text_value']);
            $table->index(['catalog_attribute_id', 'text_value']);

            $table->foreign('catalog_attribute_id')->references('id')->on(CatalogAttribute::getTableName())->cascadeOnDelete();
        });

        Schema::create(CatalogGood::getTableName(), function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('group_id')->index();
            $table->string('prefix', 500)->nullable();
            $table->string('name', 500)->index();
            $table->string('short_info', 2000)->nullable(); //краткие сведения о товаре
            $table->string('description', 500)->nullable();    // для себя
            $table->unsignedBigInteger("manufacturer_id")->nullable()->index();
            $table->string("parent_good_id")->nullable()->index();
            $table->boolean("is_certification")->default(false);
            $table->integer('int_id')->nullable()->unique()->index();
            $table->string('string_id')->nullable()->unique()->index();
            $table->string('link')->nullable();
            $table->string('vendor_code', 50)->nullable()->unique()->index();
            $table->jsonb('sl')->nullable();

            $table->timestampTz('created_at')->useCurrent();
            $table->timestampTz('updated_at')->nullable()->useCurrentOnUpdate();

            $table->foreign('group_id')->references('id')->on(CatalogGroup::getTableName())->cascadeOnDelete();
            $table->foreign('manufacturer_id')->references('id')->on(Manufacturer::getTableName())->onDelete('set null');
        });

        Schema::create(CatalogGoodAttribute::getTableName(), function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('good_id')->index();
            $table->unsignedBigInteger('attribute_value_id')->index();
            $table->boolean('bool_value')->nullable();

            $table->unique(['good_id', 'attribute_value_id']);

            $table->foreign('good_id')->references('id')->on(CatalogGood::getTableName())->cascadeOnDelete();
            $table->foreign('attribute_value_id')->references('id')->on(CatalogAttributeValue::getTableName())->cascadeOnDelete();
        });

        Schema::create(CatalogImage::getTableName(), function (Blueprint $table): void {
            $table->id();
            $table->unsignedBigInteger('good_id')->index();
            $table->string('original_url')->nullable();
            $table->string('path')->nullable();
            $table->string('hash', 32)->index();
            $table->tinyInteger('type')->index();
            $table->tinyInteger('media_type')->index();

            $table->timestampTz('created_at')->useCurrent();

            $table->foreign('good_id')->references('id')->on(CatalogGood::getTableName())->onDelete('cascade');
        });
    }

    public function down(): void {}
};
