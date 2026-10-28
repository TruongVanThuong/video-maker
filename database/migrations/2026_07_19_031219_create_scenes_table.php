<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('scenes', function (Blueprint $table) {
            $table->id(); // ID phân cảnh
            $table->foreignId('project_id')->constrained()->onDelete('cascade'); // Thuộc dự án nào
            
            $table->integer('scene_index'); // Thứ tự xuất hiện của cảnh (Cảnh 1, Cảnh 2, Cảnh 3...)
            $table->text('dialogue'); // Lời thoại hoặc lời dẫn truyện của cảnh này
            $table->string('voice_id')->nullable(); // Cấu hình ID giọng đọc (AI Voice ID) cho cảnh này
            $table->text('image_prompt')->nullable(); // Câu lệnh dùng để sinh ảnh (Prompt AI) do LLM tối ưu
            
            $table->json('camera_rule')->nullable(); // Lưu cấu hình góc máy (Zoom, Pan, chuyển cảnh) do RuleEngine tính toán
            
            $table->string('audio_path')->nullable(); // Đường dẫn tới file tiếng .mp3 (lưu trên Cloud Storage như S3/R2)
            $table->string('image_path')->nullable(); // Đường dẫn tới file ảnh .png/.jpg của cảnh này (lưu trên S3/R2)
            $table->float('duration', 8, 2)->default(0.00); // Thời lượng phân cảnh (tính bằng giây, tự động khớp với độ dài file audio)
            
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('scenes');
    }
};