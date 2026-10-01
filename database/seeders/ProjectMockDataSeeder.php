<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Models\User;
use App\Models\Project;
use App\Models\Scene;
use App\Models\ProjectMedia;
use App\Models\AiJob;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class ProjectMockDataSeeder extends Seeder
{
    public function run(): void
    {
        // 1. Tạo User Test (Nếu chưa tồn tại)
        $user = User::firstOrCreate(
            ['email' => 'developer@test.com'],
            [
                'name' => 'Solo Developer',
                'password' => Hash::make('password123'),
            ]
        );

        // 2. Tạo một Project giả lập trạng thái đã HOÀN THÀNH (Dùng để test UI hiển thị video)
        $completedProject = Project::create([
            'user_id' => $user->id,
            'title' => 'Đấu Phá Thương Khung - Tóm tắt Tập 1 (Giả lập)',
            'source_type' => 'story',
            'raw_input' => 'Tiêu Viêm là thiên tài tu luyện của Tiêu gia, nhưng đột nhiên mất đi đấu khí...',
            'status' => 'completed',
            'batch_id' => (string) Str::uuid(),
            'render_job_id' => 'render_mock_123456789',
        ]);

        // Mẫu phân cảnh chi tiết cho dự án hoàn thành
        $mockScenesData = [
            [
                'scene_index' => 1,
                'dialogue' => 'Tại đấu khí đại lục, sức mạnh là tất cả. Tiêu Viêm, từng là thiên tài vô song, nay lại trở thành phế vật.',
                'voice_id' => 'vn_male_premium_01',
                'image_prompt' => 'Anime style, a 18-year-old boy with black hair, looking sad, holding a mysterious ring, ancient Chinese background, masterpiece.',
                'camera_rule' => ['type' => 'zoom_in', 'speed' => 'slow'],
                'audio_path' => 'https://www.soundhelix.com/examples/mp3/SoundHelix-Song-1.mp3', // Link audio test
                'image_path' => 'https://picsum.photos/id/1015/1920/1080', // Link ảnh mẫu độ phân giải Full HD
                'duration' => 6.50,
            ],
            [
                'scene_index' => 2,
                'dialogue' => 'Mọi sự khinh bỉ đổ dồn vào cậu, cho đến ngày gia tộc nạp lan đến thoái hôn, sỉ nhục tôn nghiêm của Tiêu Gia.',
                'voice_id' => 'vn_male_premium_01',
                'image_prompt' => 'Anime style, a beautiful girl in elegant white dress standing proudly, elderly people angry in a grand clan hall.',
                'camera_rule' => ['type' => 'pan_left', 'speed' => 'medium'],
                'audio_path' => 'https://www.soundhelix.com/examples/mp3/SoundHelix-Song-2.mp3',
                'image_path' => 'https://picsum.photos/id/1016/1920/1080',
                'duration' => 7.20,
            ],
            [
                'scene_index' => 3,
                'dialogue' => 'Ba năm ước hẹn! Tiêu Viêm tự tay viết hưu thư, thề rằng sẽ khiến Nạp Lan Yên Nhiên phải hối hận.',
                'voice_id' => 'vn_male_premium_01',
                'image_prompt' => 'Anime style, intense aura, the boy biting his finger, writing on a scroll with blood, eyes full of determination.',
                'camera_rule' => ['type' => 'shake_and_zoom', 'speed' => 'fast'],
                'audio_path' => 'https://www.soundhelix.com/examples/mp3/SoundHelix-Song-3.mp3',
                'image_path' => 'https://picsum.photos/id/1018/1920/1080',
                'duration' => 8.00,
            ]
        ];

        foreach ($mockScenesData as $sceneData) {
            Scene::create(array_merge($sceneData, ['project_id' => $completedProject->id]));
        }

        // Tạo thành phẩm Video đầu ra cho Project hoàn thành này
        ProjectMedia::create([
            'project_id' => $completedProject->id,
            'media_type' => 'final_video',
            'file_url' => 'https://commondatastorage.googleapis.com/gtv-videos-bucket/sample/BigBuckBunny.mp4', // Link video test
            'file_path' => 'videos/mock_final_video.mp4',
            'file_size' => 15728640, // 15MB
            'total_duration' => 21.70, // 6.5 + 7.2 + 8.0
        ]);

        // Ghi log chi phí AI giả lập để test phần quản lý ngân sách
        AiJob::create([
            'project_id' => $completedProject->id,
            'job_type' => 'build_metadata',
            'provider' => 'openai',
            'status' => 'success',
            'api_cost' => 0.0150,
            'response_data' => ['usage' => ['total_tokens' => 750]],
        ]);

        // 3. Tạo một Project giả lập trạng thái BỊ LỖI (Dùng để test tính năng Retry/Resume từ Job Ledger)
        $failedProject = Project::create([
            'user_id' => $user->id,
            'title' => 'Đấu La Đại Lục - Tập 5 (Giả lập Lỗi Sinh Ảnh)',
            'source_type' => 'story',
            'raw_input' => 'Đường Tam thức tỉnh Võ Hồn Lam Ngân Thảo...',
            'status' => 'failed',
        ]);

        $failedScene = Scene::create([
            'project_id' => $failedProject->id,
            'scene_index' => 1,
            'dialogue' => 'Đường Tam đứng giữa tế đàn thức tỉnh.',
            'voice_id' => 'vn_female_02',
            'image_prompt' => 'Anime style, a young boy with blue hair activating a glowing magic circle...',
            'duration' => 4.00,
        ]);

        // Tạo 1 Job thành công và 1 Job thất bại để kiểm tra cơ chế kiểm soát lỗi
        AiJob::create([
            'project_id' => $failedProject->id,
            'scene_id' => $failedScene->id,
            'job_type' => 'generate_voice',
            'provider' => 'elevenlabs',
            'status' => 'success',
            'api_cost' => 0.0050,
        ]);

        AiJob::create([
            'project_id' => $failedProject->id,
            'scene_id' => $failedScene->id,
            'job_type' => 'generate_image',
            'provider' => 'leonardo',
            'status' => 'failed',
            'api_cost' => 0.0000,
            'error_message' => 'API Gateway Timeout. Leonardo AI failed to respond within 30 seconds.',
        ]);

        $this->command->info('Seed dữ liệu mẫu thành công! Hãy đăng nhập bằng developer@test.com / password123');
    }
}