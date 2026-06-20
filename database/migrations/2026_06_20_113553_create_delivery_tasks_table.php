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
        Schema::create('delivery_tasks', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('order_id')->index();
            $table->unsignedBigInteger('courier_id')->index()->nullable();
            $table->string('status')->default('PENDING'); // PENDING, ASSIGNED, ON_THE_WAY, DELIVERED, CANCELLED
            $table->datetime('scheduled_at')->nullable();
            $table->datetime('delivered_at')->nullable();
            $table->text('address');
            $table->text('notes')->nullable();
            $table->unsignedBigInteger('created_by')->index()->nullable();
            $table->timestamps();

            $table->foreign('order_id')->references('id')->on('orders')->onDelete('cascade');
            $table->foreign('courier_id')->references('id')->on('users')->onDelete('set null');
            $table->foreign('created_by')->references('id')->on('users')->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('delivery_tasks');
    }
};
