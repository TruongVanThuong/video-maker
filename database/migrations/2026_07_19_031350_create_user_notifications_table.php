<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('user_notifications', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade'); // Gửi thông báo cho ai
            $table->foreignId('project_id')->nullable()->constrained()->onDelete('set null'); // Thông báo thuộc dự án nào
            
            $table->string('title'); // Tiêu đề thông báo (VD: "Video của bạn đã sẵn sàng!")
            $table->text('message'); // Nội dung chi tiết (VD: "Dự án 'Tóm tắt Đấu Phá Thương Khung' đã dựng xong.")
            $table->boolean('is_read')->default(false); // Trạng thái đã đọc hay chưa
            
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('user_notifications');
    }
};