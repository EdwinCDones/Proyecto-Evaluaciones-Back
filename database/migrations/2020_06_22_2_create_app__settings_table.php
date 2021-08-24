<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateAppSettingsTable extends Migration
{

    public function up()
    {
        Schema::connection('pgsql-app')->create('settings', function (Blueprint $table) {
            $table->id();
            $table->string('code');
            $table->text('name');
            $table->text('description')->nullable();
            $table->string('value');
            $table->foreignId('type_id')->constrained('app.catalogues');
            $table->foreignId('status_id')->constrained('app.catalogues');
            $table->softDeletes();
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::connection('pgsql-app')->dropIfExists('settings');
    }
}
