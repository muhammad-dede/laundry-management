<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('deliveries', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('order_id')->index();
            $table->unsignedBigInteger('courier_id')->index()->nullable();
            $table->string('delivery_status')->default('PENDING'); // PENDING, ASSIGNED, ON_THE_WAY, DELIVERED
            $table->datetime('delivered_at')->nullable();
            $table->timestamps();

            $table->foreign('order_id')->references('id')->on('orders')->onDelete('cascade');
            $table->foreign('courier_id')->references('id')->on('users')->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('deliveries');
    }
};
