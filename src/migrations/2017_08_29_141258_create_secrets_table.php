<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateSecretsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('secrets', function (Blueprint $table) {
            // Unique secret identifier
            $table->string('hash', 32)->unique();
            // Secret text
            $table->string('secretText');
            // UNIX timestamp for creation date
            $table->string('createdAt', 10);
            // UNIX timestamp for expiration date
            $table->string('expiresAt', 10);
            // Remaining views counter
            $table->integer('remainingViews');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('secrets');
    }
}
