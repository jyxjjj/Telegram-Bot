<?php

namespace App\Jobs;

use App\Common\RequestHelper;
use App\Jobs\Base\BaseQueue;
use GuzzleHttp\Psr7\Utils;
use Illuminate\Support\Carbon;
use Throwable;

class RequestModelJob extends BaseQueue
{
    public function __construct(private array $data, private string $prompt, private int $draftId, private string $user)
    {
        $this->onQueue('model');
    }

    public function handle(): void
    {
        $draft = [
            'chat_id' => $this->data['chat_id'],
            'draft_id' => $this->draftId,
            'text' => '',
        ];
        $timestampKey = "AI_Chat_TS_{$this->data['chat_id']}_{$this->draftId}";
        SendMessageDraftJob::dispatch($draft, $timestampKey, Carbon::now()->getTimestampMs());
        $lastSent = microtime(true);
        try {
            $response = RequestHelper::getInstance(timeout: 60, retry: 1, options: [
                'stream' => true,
                'read_timeout' => 60,
            ])
                ->baseUrl(rtrim(config('openai.base_url'), '/') . '/')
                ->withToken(config('openai.api_key'))
                ->accept('text/event-stream')
                ->post('responses', [
                    'model' => config('openai.model'),
                    'instructions' => config('openai.instructions'),
                    'input' => $this->prompt,
                    'user' => $this->user,
                    'max_output_tokens' => 256,
                    'reasoning' => ['effort' => 'none'],
                    'stream' => true,
                ]);
        } catch (Throwable) {
            $response = null;
        }
        if ($response === null || !$response->successful()) {
            $this->data['text'] = 'Model request failed.';
            SendMessageDraftJob::dispatch($this->data, $timestampKey, Carbon::now()->getTimestampMs(), finished: true);
            return;
        }
        $stream = $response->toPsrResponse()->getBody();
        $text = '';
        $eventData = '';
        $completed = false;
        try {
            while (!$stream->eof()) {
                try {
                    $line = rtrim(Utils::readLine($stream), "\r\n");
                } catch (Throwable) {
                    break;
                }
                if (str_starts_with($line, 'data:')) {
                    $eventData .= substr($line, 5) . "\n";
                    continue;
                }
                if ($line !== '' || $eventData === '') {
                    continue;
                }
                $event = json_decode($eventData, true);
                $eventData = '';
                $type = $event['type'] ?? '';
                if ($type === 'response.output_text.delta' || $type === 'response.refusal.delta') {
                    $text .= $event['delta'];
                    if ($text !== '' && microtime(true) - $lastSent >= 1) {
                        $draft['text'] = $text;
                        SendMessageDraftJob::dispatch($draft, $timestampKey, Carbon::now()->getTimestampMs());
                        $lastSent = microtime(true);
                    }
                } elseif ($type === 'response.completed') {
                    $completed = true;
                    break;
                } elseif ($type === 'response.incomplete') {
                    $completed = ($event['response']['incomplete_details']['reason'] ?? '') === 'max_output_tokens';
                    break;
                } elseif (in_array($type, ['error', 'response.failed'], true)) {
                    break;
                }
            }
        } finally {
            $stream->close();
        }
        if (!$completed || $text === '') {
            $text = 'Model response failed.';
        }
        $this->data['text'] = $text;
        SendMessageDraftJob::dispatch($this->data, $timestampKey, Carbon::now()->getTimestampMs(), finished: true);
    }
}
