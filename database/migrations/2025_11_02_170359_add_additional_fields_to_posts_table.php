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
        Schema::table('posts', function (Blueprint $table) {
            $table->string('category')->nullable()->after('content');
            $table->text('excerpt')->nullable()->after('category');
            $table->integer('read_time')->default(1)->after('excerpt'); // in minutes
            $table->string('tags')->nullable()->after('read_time'); // comma-separated
            $table->integer('views')->default(0)->after('tags');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('posts', function (Blueprint $table) {
            $table->dropColumn(['category', 'excerpt', 'read_time', 'tags', 'views']);
        });
    }
};