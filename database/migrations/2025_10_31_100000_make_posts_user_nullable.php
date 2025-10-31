<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
 
    public function up(): void
    {
        Schema::table('posts', function (Blueprint $table) {
           
            if (Schema::hasColumn('posts', 'user_id')) {
               
                try {
                    $table->dropForeign(['user_id']);
                } catch (\Exception $e) {
                   
                }
                $table->dropColumn('user_id');
            }
        });

        Schema::table('posts', function (Blueprint $table) {
            $table->foreignId('user_id')->nullable()->constrained()->onDelete('cascade');
        });
    }

   
    public function down(): void
    {
        Schema::table('posts', function (Blueprint $table) {

            try {
                $table->dropForeign(['user_id']);
            } catch (\Exception $e) {
            }

            if (Schema::hasColumn('posts', 'user_id')) {
                $table->dropColumn('user_id');
            }
        });

        Schema::table('posts', function (Blueprint $table) {
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
        });
    }
};