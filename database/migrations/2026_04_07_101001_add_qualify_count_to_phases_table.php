<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('phases', function (Blueprint $table) {
            $table->unsignedSmallInteger('qualify_count')->default(2)->after('status');
            $table->foreignId('source_phase_id')->nullable()->after('qualify_count')
                ->constrained('phases')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('phases', function (Blueprint $table) {
            $table->dropConstrainedForeignId('source_phase_id');
            $table->dropColumn('qualify_count');
        });
    }
};
