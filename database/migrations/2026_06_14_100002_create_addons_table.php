<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('addons', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('category_id')->nullable()->constrained('categories')->nullOnDelete();
            $table->string('name');
            $table->string('slug')->unique();
            $table->string('package_name');                  // addon.json "name" (installable slug)
            $table->string('vendor')->nullable();            // addon.json "vendor"
            $table->string('tagline')->nullable();
            $table->longText('description')->nullable();
            $table->string('icon_path')->nullable();
            $table->boolean('is_paid')->default(false);
            $table->decimal('price', 10, 2)->default(0);
            $table->string('currency', 10)->default('USD');
            $table->string('status', 20)->default('draft');  // draft | published
            $table->string('latest_version')->nullable();
            $table->string('min_lms_version')->nullable();
            $table->unsignedBigInteger('downloads_count')->default(0);
            $table->decimal('rating_avg', 3, 2)->default(0);
            $table->unsignedInteger('rating_count')->default(0);
            $table->boolean('is_featured')->default(false);
            $table->timestamp('published_at')->nullable();
            $table->timestamps();
            $table->unique(['vendor', 'package_name']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('addons');
    }
};
