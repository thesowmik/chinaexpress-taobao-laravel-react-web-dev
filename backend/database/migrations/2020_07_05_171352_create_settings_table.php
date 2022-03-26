<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateSettingsTable extends Migration
{
  /**
   * Run the migrations.
   *
   * @return void
   */
  public function up()
  {
    Schema::create('settings', function (Blueprint $table) {
      $table->bigIncrements('id');
      $table->timestamp('active')->default(now());
      $table->string('key')->unique();
      $table->text('value')->nullable();
      $table->unsignedBigInteger('user_id');
      $table->timestamps();
      $table->softDeletes();

      // $table->unique(['key', 'value']);
    });
  }

  /**
   * Reverse the migrations.
   *
   * @return void
   */
  public function down()
  {
    Schema::dropIfExists('settings');
  }
}