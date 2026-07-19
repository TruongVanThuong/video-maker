<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('project_medias', function (Blueprint $table) {
            $table->id();
            $table->foreignId('project_id')->constrained()->onDelete('cascade'); // Liên kết tới dự án
            
            $table->enum('media_type', ['final_video'])->default('final_video'); // Loại media (hiện tại là video hoàn chỉnh)
            $table->string('file_url'); // Đường dẫn URL tuyệt đối để xem/tải video (Link CDN hoặc S3)
            $table->string('file_path'); // Đường dẫn tương đối trong hệ thống storage để xử lý xóa/sửa file sau này
            $table->integer('file_size')->comment('Tính bằng bytes'); // Dung lượng file video
            $table->float('total_duration', 8, 2)->comment('Tổng thời lượng video bằng giây'); // Tổng thời lượng video thành phẩm
            
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('project_medias');
    }
};