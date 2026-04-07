<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('games', function (Blueprint $table) {
            $table->unsignedSmallInteger('round')->nullable()->after('phase_id');
            $table->unsignedSmallInteger('bracket_position')->nullable()->after('round');
            $table->foreignId('home_feeder_game_id')->nullable()->after('bracket_position')
                ->constrained('games')->nullOnDelete();
            $table->foreignId('away_feeder_game_id')->nullable()->after('home_feeder_game_id')
                ->constrained('games')->nullOnDelete();
        });

        // Make home_club_id and away_club_id nullable for future bracket games
        Schema::table('games', function (Blueprint $table) {
            $table->foreignId('home_club_id')->nullable()->change();
            $table->foreignId('away_club_id')->nullable()->change();
        });
    }

    public function down(): void
    {
        Schema::table('games', function (Blueprint $table) {
            $table->dropConstrainedForeignId('home_feeder_game_id');
            $table->dropConstrainedForeignId('away_feeder_game_id');
            $table->dropColumn(['round', 'bracket_position']);
        });

        Schema::table('games', function (Blueprint $table) {
            $table->foreignId('home_club_id')->nullable(false)->change();
            $table->foreignId('away_club_id')->nullable(false)->change();
        });
    }
};
