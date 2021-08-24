<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateAppStudentsTable extends Migration
{

    public function up()
    {
        Schema::connection('pgsql-app')->create('students', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('authentication.users');
            $table->foreignId('address_id')->constrained('app.address');
            $table->foreignId('school_type_id')->constrained('app.catalogues');
            $table->date('career_start_date')->nullable();// esto cambiar a otra tabla
            $table->year('graduation_year')->nullable();// esto cambiar a otra tabla
            $table->string('cohort')->nullable();// esto cambiar a otra tabla
            $table->softDeletes();
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::connection('pgsql-app')->dropIfExists('students');
    }
}
