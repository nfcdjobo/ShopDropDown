<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Mémorise le disque (public, s3…) de chaque fichier : indispensable dès que S3 et le
 * stockage local coexistent (repli automatique). Les fichiers existants sont sur 'public'.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('products', function (Blueprint $table) {
            $table->string('featured_image_disk', 30)->nullable()->after('featured_image');
        });

        Schema::table('product_images', function (Blueprint $table) {
            $table->string('disk', 30)->default('public')->after('path');
        });

        Schema::table('product_proofs', function (Blueprint $table) {
            $table->string('disk', 30)->default('public')->after('path');
        });

        DB::table('products')
            ->whereNotNull('featured_image')
            ->update(['featured_image_disk' => 'public']);
    }

    public function down(): void
    {
        Schema::table('products', fn (Blueprint $t) => $t->dropColumn('featured_image_disk'));
        Schema::table('product_images', fn (Blueprint $t) => $t->dropColumn('disk'));
        Schema::table('product_proofs', fn (Blueprint $t) => $t->dropColumn('disk'));
    }
};
