<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateAppSubjectTeacherTable extends Migration
{
    public function up()
    {
        Schema::connection('pgsql-app')->create('subject_teacher', function (Blueprint $table) {
            $table->id();
            $table->foreignId('teacher_id')->constrained('app.teachers');
            $table->foreignId('subject_id')->constrained('app.subjects');
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::connection('pgsql-app')->dropIfExists('subject_teacher');
    }
}
