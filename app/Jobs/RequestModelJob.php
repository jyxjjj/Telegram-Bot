<?php

namespace App\Jobs;

use App\Common\RequestHelper;
use App\Jobs\Base\BaseQueue;
use Illuminate\Support\Carbon;
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
        $startedAt = hrtime(true);
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
        $elapsed = (hrtime(true) - $startedAt) / 1_000_000_000;
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
            $expiresAt = Carbon::now(config('app.timezone'))->addMinutes(10);
            Cache::put($historyKey, $history, $expiresAt);
            $inputTokens = $result['usage']['input_tokens'] ?? '未提供';
            $outputTokens = $result['usage']['output_tokens'] ?? '未提供';
            $totalTokens = $result['usage']['total_tokens'] ?? '未提供';
            $text .= "\n\nTokens：输入 $inputTokens / 输出 $outputTokens / 合计 $totalTokens";
            $text .= "\n请求耗时：" . number_format($elapsed, 2) . ' 秒';
            $text .= "\n上下文：" . count($history) . "/5 轮，$length/512 字符";
            $text .= "\n到期：" . $expiresAt->format('Y-m-d H:i:s');
        }
        $this->data['text'] = htmlspecialchars($text, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
        EditMessageTextWithKeyJob::dispatch($this->data, $this->key)->onQueue('model-messages');
    }
}
