<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('addon_versions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('addon_id')->constrained('addons')->cascadeOnDelete();
            $table->string('version');
            $table->longText('changelog')->nullable();
            $table->string('zip_path');
            $table->string('min_lms_version')->nullable();
            $table->unsignedBigInteger('file_size')->default(0);
            $table->string('checksum', 64)->nullable();
            $table->unsignedBigInteger('downloads_count')->default(0);
            $table->boolean('is_latest')->default(false);
            $table->timestamp('released_at')->nullable();
            $table->timestamps();
            $table->unique(['addon_id', 'version']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('addon_versions');
    }
};
