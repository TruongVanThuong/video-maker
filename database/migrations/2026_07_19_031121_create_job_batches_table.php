<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('job_batches', function (Blueprint $table) {
            $table->string('id')->primary(); // ID của batch job (Chuỗi String UUID)
            $table->string('name'); // Tên định danh cho nhóm job
            $table->integer('total_jobs'); // Tổng số job cần chạy
            $table->integer('pending_jobs'); // Số job còn lại đang chờ
            $table->integer('failed_jobs'); // Số job bị lỗi
            $table->longText('failed_job_ids'); // Danh sách ID các job bị lỗi
            $table->mediumText('options')->nullable();
            $table->integer('cancelled_at')->nullable();
            $table->integer('created_at');
            $table->integer('finished_at')->nullable(); // Thời gian hoàn thành toàn bộ batch
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('job_batches');
    }
};