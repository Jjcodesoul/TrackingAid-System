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
        Schema::create('resqoperation_forwarded_requests', function (Blueprint $table) {
            $table->id();
            
            // External Identifiers & Tracking
            $table->string('tracking_reference')->nullable()->index();
            $table->string('resqoperation_request_id')->nullable()->index();
            $table->string('source_reference')->nullable();
            
            // Origin Metadata
            $table->string('request_source')->default('ResQOperation');
            $table->string('source_system')->nullable();
            $table->string('request_category')->nullable();
            $table->string('resource_type')->nullable();
            
            // Core Request Payload Details
            $table->string('item_name');
            $table->unsignedInteger('quantity')->default(1);
            $table->string('unit')->nullable();
            $table->string('urgency')->default('Medium');
            
            // Location Information
            $table->string('area_label')->nullable();
            $table->text('area_note')->nullable();
            
            // Operational Status
            $table->string('status')->default('Pending')->index();
            
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('resqoperation_forwarded_requests');
    }
};