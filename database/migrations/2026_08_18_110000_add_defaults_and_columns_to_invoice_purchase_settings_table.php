<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('invoice_purchase_settings', function (Blueprint $table) {
            if (!Schema::hasColumn('invoice_purchase_settings', 'invoice_title')) {
                $table->string('invoice_title')->default('Invoice')->nullable();
                $table->string('invoice_subheading')->nullable();
                $table->integer('logo_width')->default(128)->nullable();
                $table->integer('logo_height')->default(128)->nullable();
                $table->text('default_notes')->nullable();
                $table->text('default_footer')->nullable();
                
                $table->string('column_item_name')->default('Items')->nullable();
                $table->string('column_price_name')->default('Price')->nullable();
                $table->string('column_quantity_name')->default('Quantity')->nullable();
                $table->string('template_color')->default('slate-400')->nullable();
                $table->boolean('hide_item_description')->default(false);
                $table->boolean('hide_amount')->default(false);
            }

            if (!Schema::hasColumn('invoice_purchase_settings', 'thermal_title')) {
                $table->string('thermal_title')->default('Receipt')->nullable();
                $table->string('thermal_subheading')->nullable();
                $table->integer('thermal_logo_width')->default(64)->nullable();
                $table->integer('thermal_logo_height')->default(64)->nullable();
                $table->text('thermal_notes')->nullable();
                $table->text('thermal_footer')->nullable();

                $table->string('thermal_column_item_name')->default('Items')->nullable();
                $table->string('thermal_column_price_name')->default('Price')->nullable();
                $table->string('thermal_column_quantity_name')->default('Quantity')->nullable();
                $table->boolean('thermal_hide_item_description')->default(false);
                $table->boolean('thermal_hide_amount')->default(false);
            }

            if (!Schema::hasColumn('invoice_purchase_settings', 'default_printer')) {
                $table->string('default_printer')->default('standard')->nullable();
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('invoice_purchase_settings', function (Blueprint $table) {
            $cols = [
                'invoice_title',
                'invoice_subheading',
                'logo_width',
                'logo_height',
                'default_notes',
                'default_footer',
                'column_item_name',
                'column_price_name',
                'column_quantity_name',
                'hide_item_description',
                'hide_amount',
                'thermal_title',
                'thermal_subheading',
                'thermal_logo_width',
                'thermal_logo_height',
                'thermal_notes',
                'thermal_footer',
                'thermal_column_item_name',
                'thermal_column_price_name',
                'thermal_column_quantity_name',
                'thermal_hide_item_description',
                'thermal_hide_amount',
            ];
            foreach ($cols as $col) {
                if (Schema::hasColumn('invoice_purchase_settings', $col)) {
                    $table->dropColumn($col);
                }
            }
        });
    }
};
