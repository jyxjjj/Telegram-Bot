<?php

namespace App\Jobs;

use App\Common\RequestHelper;
use App\Jobs\Base\BaseQueue;
use Illuminate\Support\Facades\Cache;
use Throwable;

class RequestModelJob extends BaseQueue
{
    public function __construct(private array $data, private string $prompt, private string $key, private string $user)
    {
        $this->onQueue('model');
    }

    public function handle(): void
    {
        $historyKey = "AI_Chat_History_{$this->data['chat_id']}_{$this->user}";
        $history = Cache::get($historyKey, []);
        $input = [];
        foreach ($history as $round) {
            $input[] = ['role' => 'user', 'content' => $round['user']];
            $input[] = ['role' => 'assistant', 'content' => $round['assistant']];
        }
        $input[] = ['role' => 'user', 'content' => $this->prompt];
        try {
            $response = RequestHelper::getInstance(timeout: 60, retry: 1)
                ->baseUrl(rtrim(config('openai.base_url'), '/') . '/')
                ->withToken(config('openai.api_key'))
                ->post('responses', [
                    'model' => config('openai.model'),
                    'instructions' => config('openai.instructions'),
                    'input' => $input,
                    'user' => $this->user,
                    'max_output_tokens' => 256,
                    'reasoning' => ['effort' => 'none'],
                    'stream' => false,
                ]);
        } catch (Throwable) {
            $response = null;
        }
        if ($response === null || !$response->successful()) {
            $this->data['text'] = 'Model request failed.';
            EditMessageTextWithKeyJob::dispatch($this->data, $this->key)->onQueue('model-messages');
            return;
        }
        $result = $response->json();
        $text = '';
        $completed = ($result['status'] ?? '') === 'completed'
            || (($result['status'] ?? '') === 'incomplete'
                && ($result['incomplete_details']['reason'] ?? '') === 'max_output_tokens');
        if ($completed) {
            foreach ($result['output'] ?? [] as $item) {
                if (($item['type'] ?? '') !== 'message') {
                    continue;
                }
                foreach ($item['content'] ?? [] as $content) {
                    if (($content['type'] ?? '') === 'output_text') {
                        $text .= $content['text'];
                    } elseif (($content['type'] ?? '') === 'refusal') {
                        $text .= $content['refusal'];
                    }
                }
            }
        }
        if ($text === '') {
            $text = 'Model response failed.';
        } else {
            $history[] = ['user' => $this->prompt, 'assistant' => $text];
            $history = array_slice($history, -5);
            $length = 0;
            foreach ($history as $round) {
                $length += mb_strlen($round['user']) + mb_strlen($round['assistant']);
            }
            while ($length > 512) {
                $round = array_shift($history);
                $length -= mb_strlen($round['user']) + mb_strlen($round['assistant']);
            }
            Cache::put($historyKey, $history, 600);
        }
        $this->data['text'] = htmlspecialchars($text, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
        EditMessageTextWithKeyJob::dispatch($this->data, $this->key)->onQueue('model-messages');
    }
}
