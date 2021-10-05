<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class ChangeColumnOrderStatusTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('order_statuses', function (Blueprint $table) {
            $table->unsignedBigInteger('status_id')->after('status')->nullable();
        });
        Schema::table('order_statuses', function (Blueprint $table) {
            $table->unsignedBigInteger('status_id')->index('status_id')->nullable(false)->change();
            $table->renameColumn('status', 'is_active');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
    }
}
