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
        Schema::table('products', function (Blueprint $table) {
            // Renombrar source_id actual a external_id para evitar conflictos
            $table->renameColumn('source_id', 'external_id');
            
            // Añadir nuevas claves foráneas
            $table->foreignId('source_id')->nullable()->after('category_id')->constrained('sources')->onDelete('set null');
            $table->foreignId('brand_id')->nullable()->after('source_id')->constrained('brands')->onDelete('set null');
            
            // Eliminar columnas que ya no usaremos como strings/enums
            $table->dropColumn('marketplace');
            $table->dropIndex(['brand']); // Drop index before column for SQLite compatibility
            $table->dropColumn('brand');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('products', function (Blueprint $table) {
            $table->dropForeign(['source_id']);
            $table->dropForeign(['brand_id']);
            $table->dropColumn(['source_id', 'brand_id']);
            
            $table->string('brand')->nullable()->after('marketplace');
            $table->enum('marketplace', ['taobao', 'weidian', '1688'])->after('external_id');
            $table->renameColumn('external_id', 'source_id');
        });
    }
};
