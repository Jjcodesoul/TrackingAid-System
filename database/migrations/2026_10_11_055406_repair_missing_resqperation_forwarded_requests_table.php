
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('resqperation_forwarded_requests')) {
            return;
        }

        Schema::create('resqperation_forwarded_requests', function (Blueprint $table) {
            $table->id();

            $table->string('tracking_reference', 120)->unique();
            $table->string('resqperation_request_id', 120)->index();
            $table->string('source_reference', 120)->nullable()->index();
            $table->string('request_source', 80)->nullable();
            $table->string('source_system', 80)->default('ResQperation');
            $table->string('request_category', 80)->nullable();
            $table->string('resource_type', 150)->nullable();
            $table->string('item_name', 150)->nullable();
            $table->integer('quantity')->default(1);
            $table->string('unit', 50)->nullable();
            $table->string('urgency', 80)->nullable();
            $table->string('area_label')->nullable();
            $table->text('area_note')->nullable();
            $table->string('requested_by')->nullable();
            $table->text('description')->nullable();
            $table->text('validation_notes')->nullable();
            $table->string('validated_by_user_id', 120)->nullable();
            $table->string('forwarded_by_user_id', 120)->nullable();
            $table->string('forwarded_by_name')->nullable();
            $table->string('forwarded_by_role', 80)->nullable();
            $table->string('resqperation_status', 80)->default('forwarded')->index();
            $table->json('payload_json')->nullable();
            $table->timestamp('forwarded_at')->nullable()->index();

            $table->timestamps();
        });
    }

    public function down(): void
    {
        // Intentionally preserve the table and its data.
    }
};