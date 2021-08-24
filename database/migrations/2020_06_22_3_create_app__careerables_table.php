<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateAppCareerablesTable extends Migration
{
    public function up()
    {
        Schema::connection('pgsql-app')->create('careerables', function (Blueprint $table) {
            $table->id();
            $table->foreignId('career_id')->constrained('app.careers');
            $table->morphs('careerable');
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::connection('pgsql-app')->dropIfExists('careerables');
    }
}
