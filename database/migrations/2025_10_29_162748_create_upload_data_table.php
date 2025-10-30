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
        $headers = explode(',', 'unique_key,product_title,product_description,style,available_sizes,brand_logo_image,thumbnail_image,color_swatch_image,product_image,spec_sheet,price_text,suggested_price,category_name,subcategory_name,color_name,color_square_image,color_product_image,color_product_image_thumbnail,size,qty,piece_weight,piece_price,dozens_price,case_price,price_group,case_size,inventory_key,size_index,sanmar_mainframe_color,mill,product_status,companion_styles,msrp,map_pricing,front_model_image_url,back_model_image,front_flat_image,back_flat_image,product_measurements,pms_color,gtin');
        Schema::create('upload_data', function (Blueprint $table) use ($headers) {
            $table->id();
            foreach ($headers as $header) {
                $table->longText($header)->nullable();
            }
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('upload_data');
    }
};
