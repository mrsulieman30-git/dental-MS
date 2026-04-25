<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('imaging_devices', function (Blueprint $table) {
            $table->id();
            $table->foreignId('location_id')->constrained('locations')->onDelete('cascade');
            $table->string('name');
            $table->string('manufacturer')->nullable();
            $table->string('model')->nullable();
            $table->string('serial_number')->nullable();
            $table->enum('device_type', ['sensor', 'pano', 'cbct', 'camera', 'other']);
            $table->string('twain_name')->nullable();
            $table->boolean('is_active')->default(true);
            $table->date('calibration_date')->nullable();
            $table->timestamps();
        });

        Schema::create('imaging_series', function (Blueprint $table) {
            $table->id();
            $table->foreignId('patient_id')->constrained('patients')->onDelete('cascade');
            $table->foreignId('appointment_id')->nullable()->constrained('appointments')->onDelete('set null');
            $table->enum('series_type', ['fmx', 'bw', 'pa', 'pan', 'ceph', 'cbct', 'intraoral_photo', 'extraoral_photo', 'other']);
            $table->string('name')->nullable();
            $table->datetime('taken_at');
            $table->foreignId('taken_by')->constrained('users')->onDelete('cascade');
            $table->foreignId('device_id')->nullable()->constrained('imaging_devices')->onDelete('set null');
            $table->text('notes')->nullable();
            $table->boolean('is_archived')->default(false);
            $table->timestamps();
        });

        Schema::create('dental_images', function (Blueprint $table) {
            $table->id();
            $table->foreignId('series_id')->constrained('imaging_series')->onDelete('cascade');
            $table->foreignId('patient_id')->constrained('patients')->onDelete('cascade');
            $table->enum('image_type', ['xray', 'photo', 'cbct_slice', 'other'])->default('xray');
            $table->tinyInteger('tooth_number')->nullable();
            $table->integer('image_number')->default(1);
            $table->string('file_path');
            $table->string('thumbnail_path')->nullable();
            $table->enum('file_format', ['jpg', 'png', 'dcm', 'tiff']);
            $table->integer('file_size_bytes');
            $table->integer('width_px')->nullable();
            $table->integer('height_px')->nullable();
            $table->json('metadata')->nullable(); // exposure settings, device info
            $table->bigInteger('ai_analysis_id')->nullable();
            $table->json('annotations')->nullable();
            $table->boolean('is_primary_for_claim')->default(false);
            $table->timestamps();
        });

        Schema::create('ai_image_analyses', function (Blueprint $table) {
            $table->id();
            $table->foreignId('image_id')->constrained('dental_images')->onDelete('cascade');
            $table->enum('analysis_provider', ['pearl', 'overjet', 'denti_ai', 'videa', 'internal']);
            $table->string('analysis_version')->nullable();
            $table->json('findings'); // array of {finding_type, tooth_number, confidence, bounding_box, cdt_code_suggestion}
            $table->timestamp('analyzed_at');
            $table->boolean('is_accepted_by_provider')->default(false);
            $table->timestamp('accepted_at')->nullable();
            $table->json('rejected_findings')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('ai_image_analyses');
        Schema::dropIfExists('dental_images');
        Schema::dropIfExists('imaging_series');
        Schema::dropIfExists('imaging_devices');
    }
};
