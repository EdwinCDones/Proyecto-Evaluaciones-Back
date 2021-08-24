<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateAppAdministrativeStaffTable extends Migration
{
    public function up()
    {
        Schema::connection('pgsql-app')->create('administrative_staff', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('authentication.users');
            $table->foreignId('institution_id')->constrained('app.institutions');
            $table->foreignId('position_id')->constrained('app.positions');
            $table->foreignId('administrative_staff_type_id')->nullable()->constrained('app.catalogues');
            $table->foreignId('employment_relationship_id')->nullable()->constrained('app.catalogues');
            $table->foreignId('status_id')->nullable()->constrained('app.catalogues');
            $table->foreignId('town_id')->nullable()->constrained('app.catalogues');
            $table->foreignId('suffrage_province_id')->nullable()->constrained('app.catalogues');
            $table->foreignId('country_nationality_id')->nullable()->constrained('app.catalogues');
            $table->foreignId('disability_type_id')->nullable()->constrained('app.catalogues');
            $table->foreignId('catastrophic_illness_id')->nullable()->constrained('app.catalogues');
            $table->boolean('merit_contest')->default(false);
            $table->unsignedDouble('netsalary')->nullable();
            $table->boolean('disability')->default(false);
            $table->string('conadis_carnet')->nullable();
            $table->date('start_date')->nullable();
            $table->date('end_date')->nullable();
            $table->softDeletes();
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::connection('pgsql-app')->dropIfExists('administrative_staff');
    }
}
