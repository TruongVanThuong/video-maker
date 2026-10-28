<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('ai_jobs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('project_id')->constrained()->onDelete('cascade'); // Thuộc dự án nào
            $table->foreignId('scene_id')->nullable()->constrained()->onDelete('cascade'); // Thuộc phân cảnh nào (có thể null nếu là job tổng)
            
            // Loại công việc AI đang thực hiện
            $table->enum('job_type', ['parse_input', 'build_metadata', 'generate_voice', 'generate_image', 'render_video']);
            $table->string('provider'); // Tên nhà cung cấp dịch vụ (openai, elevenlabs, leonardo, creatomate...)
            
            $table->enum('status', ['pending', 'processing', 'success', 'failed'])->default('pending'); // Trạng thái job
            $table->decimal('api_cost', 8, 4)->default(0.0000); // Chi phí API tiêu tốn thực tế (VD: 0.0150 USD) để thống kê chi phí
            
            $table->longText('response_data')->nullable(); // Lưu phản hồi dạng JSON thô từ API trả về nhằm mục đích debug khi lỗi
            $table->text('error_message')->nullable(); // Lưu thông tin chi tiết lỗi nếu API bị fail
            
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('ai_jobs');
    }
};