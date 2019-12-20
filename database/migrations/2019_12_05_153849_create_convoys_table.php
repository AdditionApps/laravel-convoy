<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateConvoysTable extends Migration
{
    public function __construct()
    {
        $this->connection = config('convoy.database_connection');
    }

    public function up()
    {
        $connectionName = config('convoy.database_connection');
        $tableName = config('convoy.database_name');

        Schema::connection($connectionName)
            ->create($tableName, function (Blueprint $table) {
                $table->uuid('id');
                $table->json('manifest');
                $table->json('config');
                $table->integer('total');
                $table->integer('total_completed')->default(0);
                $table->integer('total_failed')->default(0);
                $table->dateTime('started_at')->nullable();
            });
    }

    public function down()
    {
        $connectionName = config('convoy.database_connection');
        $tableName = config('convoy.database_name');

        Schema::connection($connectionName)->drop($tableName);
    }
}
