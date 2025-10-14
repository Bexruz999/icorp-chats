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
        Schema::create('contacts', static function (Blueprint $table) {
            $table->id();
            $table->integer('account_id')->index();
            $table->foreignUuid('connection_uuid')->index();
            $table->foreignId('chat_id');
            $table->string('first_name', 25);
            $table->string('last_name', 25);
            $table->string('photo')->nullable();
            $table->string('username', 50)->nullable();
            $table->boolean('status')->default(0);
            //$table->integer('organization_id')->nullable()->index();
            //$table->string('email', 50)->nullable();
            //$table->string('phone', 50)->nullable();
            //$table->string('address', 150)->nullable();
            //$table->string('city', 50)->nullable();
            //$table->string('region', 50)->nullable();
            //$table->string('country', 2)->nullable();
            //$table->string('postal_code', 25)->nullable();
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create('contact_phones', static function (Blueprint $table) {
            $table->id();
            $table->integer('contact_id')->index();
            $table->string('phone', 50)->nullable();
            $table->string('type', 25)->nullable();
            $table->string('label', 50)->nullable();
            $table->boolean('is_primary')->default(0);
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('contact_phones', static function (Blueprint $table) {
            $table->dropForeign(['contact_id']);
        });
        Schema::dropIfExists('contact_phones');
        Schema::dropIfExists('contacts');
    }
};
