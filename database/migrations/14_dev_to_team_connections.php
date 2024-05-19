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
        Schema::create('dev_to_team_connections', function (Blueprint $table) {
            $table->id();
            $table->foreignId('developer_id')->constrained('users')->onUpdate('cascade')->onDelete('cascade');
            $table->foreignId('team_id')->constrained('dev_teams')->onUpdate('cascade')->onDelete('cascade');
            $table->enum('role', [
                '1' => 'Глава',
                '2' => 'Разработчик',
                '3' => 'Копирайтер',
                '4' => 'Приглашён',
            ]);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('dev_to_team_connections');
    }
};
