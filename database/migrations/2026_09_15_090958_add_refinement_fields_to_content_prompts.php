<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('content_prompts', function (Blueprint $table) {
            $table->unsignedTinyInteger('refinement_count')->default(0)->after('error_message');
            $table->boolean('is_manually_edited')->default(false)->after('refinement_count');
        });
    }

    public function down(): void
    {
        Schema::table('content_prompts', function (Blueprint $table) {
            $table->dropColumn(['refinement_count', 'is_manually_edited']);
        });
    }
};