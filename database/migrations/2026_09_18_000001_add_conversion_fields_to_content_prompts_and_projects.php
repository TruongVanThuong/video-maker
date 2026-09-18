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
        Schema::table('content_prompts', function (Blueprint $table) {
            $table->string('conversion_status', 30)->default('unconverted')->after('status');
            $table->foreignId('project_id')->nullable()->after('conversion_status')->constrained('projects')->nullOnDelete();
        });

        Schema::table('projects', function (Blueprint $table) {
            $table->foreignId('content_prompt_id')->nullable()->after('user_id')->constrained('content_prompts')->nullOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('projects', function (Blueprint $table) {
            $table->dropForeign(['content_prompt_id']);
            $table->dropColumn('content_prompt_id');
        });

        Schema::table('content_prompts', function (Blueprint $table) {
            $table->dropForeign(['project_id']);
            $table->dropColumn(['conversion_status', 'project_id']);
        });
    }
};
