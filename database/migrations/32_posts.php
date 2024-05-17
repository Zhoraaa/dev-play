<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('posts', function (Blueprint $table) {
            $table->id();

            $table->unsignedBigInteger('author_id')->nullable();
            $table->foreign('author_id')
                ->references('id')->on('users')
                ->onUpdate('cascade')
                ->onDelete('set null');

            $table->unsignedBigInteger('author_mask')->nullable();
            $table->foreign('author_mask')
                ->references('id')->on('dev_teams')
                ->onUpdate('cascade')
                ->onDelete('set null');

            $table->unsignedBigInteger('for_project')->nullable();
            $table->foreign('for_project')
                ->references('id')->on('projects')
                ->onUpdate('cascade')
                ->onDelete('set null');

            $table->boolean('show_true_author');
            $table->text('text');
            $table->foreignId('type_id')->constrained('post_types');

            $table->timestamp('updated_at')->default(DB::raw('CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP'));
            $table->timestamp('created_at')->default(DB::raw('CURRENT_TIMESTAMP'));
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('posts');
    }
};
