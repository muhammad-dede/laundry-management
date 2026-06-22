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
        Schema::create('order_pickups', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('order_id')->index()->nullable();
            $table->unsignedBigInteger('customer_id')->index()->nullable();
            $table->unsignedBigInteger('courier_id')->index()->nullable();
            $table->dateTime('scheduled_at')->nullable();
            $table->dateTime('pickup_at')->nullable();
            $table->string('pickup_status')->default('ASSIGNED'); // ASSIGNED, ON_THE_WAY, PICKED_UP, RECEIVED
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->foreign('order_id')->references('id')->on('orders')->onDelete('set null');
            $table->foreign('customer_id')->references('id')->on('customers')->onDelete('set null');
            $table->foreign('courier_id')->references('id')->on('users')->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('order_pickups');
    }
};
