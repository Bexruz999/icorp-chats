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
        Schema::create('amojoes', static function (Blueprint $table) {
            $table->id();
            $table->foreignId('account_id')->constrained()->cascadeOnDelete();
            $table->foreignId('amo_connection_id')->constrained()->cascadeOnDelete();
            $table->foreignUuid('connection_uuid')->nullable();
            $table->bigInteger('amo_account_id');
            $table->string('amojo_user_id')->unique();
            $table->string('name')->nullable();
            $table->string('email')->nullable();
            $table->tinyInteger('status')->default(0);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('amojoes', static function (Blueprint $table) {
            $table->dropForeign(['account_id']);
            $table->dropForeign(['amo_connection_id']);
            $table->dropForeign(['connection_uuid']);
        });

        Schema::dropIfExists('amojoes');
    }
};
