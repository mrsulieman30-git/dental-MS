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
        Schema::create('imaging_series', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('patient_id')->constrained('patients')->onDelete('cascade');
            $table->foreignUuid('appointment_id')->nullable()->constrained('appointments');
            $table->enum('series_type', [
                'fmx', 'bw', 'pa', 'pan', 'ceph', 'cbct', 
                'intraoral_photo', 'extraoral_photo', 'other'
            ])->default('other');
            $table->string('name', 200);
            $table->timestampTz('taken_at')->useCurrent();
            $table->foreignUuid('taken_by')->constrained('users');
            $table->uuid('device_id')->nullable(); // FK will be defined below
            $table->text('notes')->nullable();
            $table->boolean('is_archived')->default(false);
            $table->timestampsTz();

            $table->index('patient_id');
            $table->index('taken_at');
        });

        Schema::create('images', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('series_id')->constrained('imaging_series')->onDelete('cascade');
            $table->foreignUuid('patient_id')->constrained('patients')->onDelete('cascade');
            $table->enum('image_type', ['xray', 'photo', 'cbct_slice', 'other'])->default('xray');
            $table->integer('tooth_number')->nullable();
            $table->integer('image_number')->nullable();
            $table->string('file_url', 500);
            $table->string('thumbnail_url', 500)->nullable();
            $table->enum('file_format', ['jpg', 'png', 'dcm', 'tiff'])->default('jpg');
            $table->bigInteger('file_size_bytes')->nullable();
            $table->integer('width_px')->nullable();
            $table->integer('height_px')->nullable();
            $table->json('metadata')->nullable(); // exposure settings, device info
            $table->uuid('ai_analysis_id')->nullable(); // FK defined below
            $table->json('annotations')->nullable(); // bounding boxes, etc
            $table->boolean('is_primary_for_claim')->default(false);
            $table->timestampsTz();

            $table->index('series_id');
            $table->index('patient_id');
        });

        Schema::create('imaging_devices', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('location_id')->constrained('locations')->onDelete('cascade');
            $table->string('name', 200);
            $table->string('manufacturer', 100)->nullable();
            $table->string('model', 100)->nullable();
            $table->string('serial_number', 100)->nullable();
            $table->enum('device_type', ['sensor', 'pano', 'cbct', 'camera', 'other'])->default('sensor');
            $table->string('twain_name', 200)->nullable();
            $table->boolean('is_active')->default(true);
            $table->date('calibration_date')->nullable();
            $table->timestampsTz();

            $table->index('location_id');
        });

        Schema::create('ai_image_analyses', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('image_id')->constrained('images')->onDelete('cascade');
            $table->enum('analysis_provider', ['pearl', 'overjet', 'dentiAI', 'videa', 'internal'])->default('internal');
            $table->string('analysis_version', 50)->nullable();
            $table->json('findings')->nullable(); // [{finding_type, tooth_number, confidence, bounding_box, cdt_code_suggestion}]
            $table->timestampTz('analyzed_at')->useCurrent();
            $table->boolean('is_accepted_by_provider')->default(false);
            $table->timestampTz('accepted_at')->nullable();
            $table->json('rejected_findings')->nullable();
            $table->timestampTz('created_at')->useCurrent();

            $table->index('image_id');
        });

        // Update foreign keys that were pending
        Schema::table('imaging_series', function (Blueprint $table) {
            $table->foreign('device_id')->references('id')->on('imaging_devices');
        });

        Schema::table('images', function (Blueprint $table) {
            $table->foreign('ai_analysis_id')->references('id')->on('ai_image_analyses');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('images', function (Blueprint $table) {
            $table->dropForeign(['ai_analysis_id']);
        });
        Schema::table('imaging_series', function (Blueprint $table) {
            $table->dropForeign(['device_id']);
        });
        Schema::dropIfExists('ai_image_analyses');
        Schema::dropIfExists('imaging_devices');
        Schema::dropIfExists('images');
        Schema::dropIfExists('imaging_series');
    }
};
