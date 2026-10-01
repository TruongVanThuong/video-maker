<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('projects', function (Blueprint $table) {
            $table->id(); // ID chính của dự án
            // Khóa ngoại liên kết với bảng users, nếu user bị xóa thì dự án bị xóa theo (onDelete cascade)
            $table->foreignId('user_id')->constrained()->onDelete('cascade'); 
            
            $table->string('title'); // Tiêu đề dự án video
            $table->enum('source_type', ['comic', 'story'])->default('story'); // Nguồn vào: Truyện tranh hay Text chữ
            $table->longText('raw_input'); // Nội dung văn bản thô do user nhập hoặc danh sách ảnh gốc đầu vào
            
            // Trạng thái của chuỗi Pipeline xử lý
            $table->enum('status', [
                'draft',                  // Mới tạo nháp
                'processing',             // Đang bắt đầu xử lý ngầm
                'analyzing_done',         // Đã xử lý xong input/OCR (Xong Job 1)
                'metadata_ready',         // Đã bóc tách cấu trúc JSON cảnh quay (Xong Job 2, 3)
                'voice_and_image_ready',  // Đã sinh xong toàn bộ ảnh và audio cho các cảnh (Xong Job 4, 5)
                'rendering',              // Đã gửi sang bên thứ 3 và đang đợi render video (Job 6)
                'completed',              // Đã nhận webhook, video hoàn thành (Job 7)
                'failed'                  // Bị lỗi ở một bước nào đó
            ])->default('draft');
            
            $table->string('batch_id')->nullable(); // Lưu ID của Job Batch dùng để track tiến độ tạo ảnh/tiếng
            $table->string('render_job_id')->nullable(); // Mã ID render nhận từ API Video (Creatomate/Shotstack) để đối chiếu Webhook
            
            $table->timestamps(); // Thời gian tạo dự án và cập nhật trạng thái
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('projects');
    }
};