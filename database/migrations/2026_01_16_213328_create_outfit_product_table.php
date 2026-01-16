<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('outfit_product', function (Blueprint $table) {
            $table->id();
            $table->foreignId('outfit_id')->constrained()->onDelete('cascade');
            $table->foreignId('product_id')->constrained()->onDelete('cascade');
            $table->decimal('pos_x', 8, 2)->default(0);
            $table->decimal('pos_y', 8, 2)->default(0);
            $table->decimal('rotation', 8, 2)->default(0);
            $table->decimal('scale_x', 8, 2)->default(1);
            $table->decimal('scale_y', 8, 2)->default(1);
            $table->integer('z_index')->default(0);
            $table->boolean('is_flipped')->default(false);
            $table->foreignId('selected_image_id')->nullable()->constrained('product_images')->onDelete('set null'); // Qué variante de foto
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('outfit_product');
    }
};
