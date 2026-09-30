<?php

declare(strict_types=1);

namespace Tests\Unit\AI\Providers;

use App\Services\AI\Providers\GeminiVoiceProvider;
use Illuminate\Http\Client\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;
use RuntimeException;
use Tests\TestCase;

class GeminiVoiceProviderTest extends TestCase
{
    public function test_it_generates_and_stores_a_wav_file(): void
    {
        Storage::fake('public');

        $pcm = str_repeat("\x10\x20", 24000); // 1 giây PCM 16-bit @24000Hz = 48000 bytes

        Http::fake([
            'generativelanguage.googleapis.com/*' => Http::response([
                'candidates' => [
                    [
                        'content' => [
                            'parts' => [
                                ['inlineData' => [
                                    'mimeType' => 'audio/L16;codec=pcm;rate=24000',
                                    'data' => base64_encode($pcm),
                                ]],
                            ],
                        ],
                    ],
                ],
            ]),
        ]);

        $provider = new GeminiVoiceProvider(apiKey: 'test-key', defaultVoice: 'Kore');

        $result = $provider->generate('Xin chào thế giới', 'Kore');

        $this->assertSame('gemini-tts', $result->provider);
        $this->assertSame(1.0, $result->durationSeconds);
        $this->assertSame('audio/wav', $result->mimeType);
        $this->assertStringStartsWith('audio/', $result->path);
        $this->assertTrue(Storage::disk('public')->exists($result->path));

        Storage::disk('public')->assertExists($result->path);
    }

    public function test_it_uses_default_voice_for_unknown_voice_id(): void
    {
        Http::fake(['*' => Http::response(['candidates' => [['content' => ['parts' => [
            ['inlineData' => [
                'mimeType' => 'audio/L16;codec=pcm;rate=24000',
                'data' => base64_encode(str_repeat("\x00\x00", 480)),
            ]],
        ]]]]])]);

        $provider = new GeminiVoiceProvider(apiKey: 'test-key', defaultVoice: 'Fenrir');

        $provider->generate('hello', 'vn_male_premium_01');

        Http::assertSent(function (Request $request) {
            $payload = $request->data();

            return $payload['generationConfig']['speechConfig']['voiceConfig']['prebuiltVoiceConfig']['voiceName'] === 'Fenrir';
        });
    }

    public function test_it_throws_when_response_has_no_audio(): void
    {
        Http::fake([
            '*' => Http::response(['candidates' => [['content' => ['parts' => [['text' => 'no audio']]]]]]),
        ]);

        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('unexpected response shape');

        (new GeminiVoiceProvider(apiKey: 'test-key'))->generate('hello');
    }

    public function test_it_retries_on_rate_limit(): void
    {
        Http::fake([
            '*' => Http::sequence()
                ->push('too many', 429)
                ->push(['candidates' => [['content' => ['parts' => [
                    ['inlineData' => [
                        'mimeType' => 'audio/L16;codec=pcm;rate=24000',
                        'data' => base64_encode(str_repeat("\x00\x00", 480)),
                    ]],
                ]]]]]),
        ]);

        $result = (new GeminiVoiceProvider(apiKey: 'test-key'))->generate('hello');

        $this->assertSame('gemini-tts', $result->provider);
        Http::assertSentCount(2);
    }
}
