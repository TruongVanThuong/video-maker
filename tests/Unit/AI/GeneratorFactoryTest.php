<?php

declare(strict_types=1);

namespace Tests\Unit\AI;

use App\Services\AI\ImageGeneratorFactory;
use App\Services\AI\Providers\FalAiImageProvider;
use App\Services\AI\Providers\GeminiVoiceProvider;
use App\Services\AI\Providers\MockImageProvider;
use App\Services\AI\Providers\MockVoiceProvider;
use App\Services\AI\Providers\PollinationsImageProvider;
use App\Services\AI\VoiceGeneratorFactory;
use RuntimeException;
use Tests\TestCase;

class GeneratorFactoryTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        config()->set('services.falai.enabled', false);
        config()->set('services.falai.key', null);
        config()->set('services.ai.demo_mode', false);
        config()->set('services.gemini.key', null);
    }

    public function test_image_factory_prefers_falai_when_enabled(): void
    {
        config()->set('services.falai.enabled', true);
        config()->set('services.falai.key', 'fal-key');

        $this->assertInstanceOf(FalAiImageProvider::class, ImageGeneratorFactory::make());
    }

    public function test_image_factory_uses_pollinations_by_default(): void
    {
        $this->assertInstanceOf(PollinationsImageProvider::class, ImageGeneratorFactory::make());
    }

    public function test_image_factory_uses_mock_in_demo_mode(): void
    {
        config()->set('services.ai.demo_mode', true);

        $this->assertInstanceOf(MockImageProvider::class, ImageGeneratorFactory::make());
    }

    public function test_voice_factory_uses_gemini_when_key_present(): void
    {
        config()->set('services.gemini.key', 'gemini-key');

        $this->assertInstanceOf(GeminiVoiceProvider::class, VoiceGeneratorFactory::make());
    }

    public function test_voice_factory_uses_mock_in_demo_mode_without_key(): void
    {
        config()->set('services.ai.demo_mode', true);

        $this->assertInstanceOf(MockVoiceProvider::class, VoiceGeneratorFactory::make());
    }

    public function test_voice_factory_throws_without_any_provider(): void
    {
        $this->expectException(RuntimeException::class);

        VoiceGeneratorFactory::make();
    }
}
