<?php

namespace App\Http\Controllers;

use App\Jobs\SendMessageJob;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Longman\TelegramBot\Entities\InlineKeyboard;
use Longman\TelegramBot\Entities\InlineKeyboardButton;
use Throwable;

class GitHubWebHookController extends BaseController
{
    /**
     * @param Request $request
     * @return JsonResponse
     */
    public function webhook(Request $request): JsonResponse
    {
        try {
            $org = $request->route('org');
            $ua = $request->header('User-Agent');
            if (!str_starts_with($ua, 'GitHub-Hookshot/')) {
                return $this->json(['code' => 400]);
            }
            $secret = (string)env('GITHUB_WEBHOOK_SECRET_' . strtoupper($org));
            $signature = (string)$request->header('X-Hub-Signature-256');
            $body = $request->getContent();
            if (!is_string($body)) {
                return $this->json(['code' => 204]);
            }
            $ok = $this->verifySignature($body, $signature, $secret);
            if (!$ok) {
                return $this->json(['code' => 403]);
            }
            $event = (string)$request->header('X-GitHub-Event');
            $this->handleEvent($org, $event, $request->post());
            return $this->json(['code' => 200,]);
        } catch (Throwable) {
            return $this->json(['code' => 500,]);
        }
    }

    private function verifySignature(string $getContent, string $signature, string $secret): bool
    {
        if (empty($signature)) {
            return false;
        }
        $realSignature = 'sha256=' . hash_hmac('sha256', $getContent, $secret);
        return hash_equals($realSignature, $signature);
    }

    private function handleEvent(string $org, string $event, array $payload): void
    {
        switch ($event) {
            case 'pull_request':
                $this->handlePullRequestEvent($org, $payload);
                break;
            case 'issues':
                $this->handleIssuesEvent($org, $payload);
                break;
            default:
                break;
        }
    }

    private function handleIssuesEvent(string $org, array $payload): void
    {
        $action = $payload['action'];
        $repository = $payload['repository']['name'];
        $operator = $payload['sender']['login'] ?? '-';
        $from = $payload['issue']['user']['login'] ?? '-';
        $issue = $payload['issue']['number'];
        $issueTitle = $payload['issue']['title'] ?? '';
        $data = [
            'chat_id' => -4971290320,
            'text' => '',
            'reply_markup' => new InlineKeyboard([]),
        ];
        $data['reply_markup']->addRow(
            new InlineKeyboardButton([
                'text' => "View #$issue",
                'url' => "https://github.com/$org/$repository/issues/$issue",
            ]),
        );
        switch ($action) {
            case 'opened':
                $data['text'] = <<<EOF
🚨🆕 问题已创建 #$issue
<blockquote>$issueTitle</blockquote>
<blockquote>$repository #$issue</blockquote>
创建人: $from
Status: ⏳ 打开

EOF;
                break;
            case 'closed':
                $state_reason = $payload['issue']['state_reason'] ?? '';
                $state_reason = match ($state_reason) {
                    'completed' => '完成',
                    'not_planned' => '无此计划',
                    'duplicate' => '重复',
                    default => $state_reason,
                };
                $data['text'] = <<<EOF
🚨✅ 问题已关闭 #$issue
<blockquote>$issueTitle</blockquote>
<blockquote>$repository #$issue</blockquote>
创建人: $from
操作人: $operator
Status: ✅ 关闭为 $state_reason

EOF;
                break;
            case 'reopened':
                $data['text'] = <<<EOF
🚨♻️ 问题被重新打开 #$issue
<blockquote>$issueTitle</blockquote>
<blockquote>$repository #$issue</blockquote>
创建人: $from
操作人: $operator
Status: ♻️ 重新打开

EOF;
                break;
            default:
                return;
        }
        $this->dispatch(new SendMessageJob($data, null, 0));
    }

    private function handlePullRequestEvent(string $org, array $payload): void
    {
        $action = $payload['action'];
        $repository = $payload['repository']['name'];
        $operator = $payload['sender']['login'] ?? '-';
        $from = $payload['pull_request']['user']['login'] ?? '-';
        $prNumber = $payload['pull_request']['number'];
        $prTitle = $payload['pull_request']['title'] ?? '';
        $data = [
            'chat_id' => -4971290320,
            'text' => '',
            'reply_markup' => new InlineKeyboard([]),
        ];
        $data['reply_markup']->addRow(
            new InlineKeyboardButton([
                'text' => "View #$prNumber",
                'url' => "https://github.com/$org/$repository/pull/$prNumber",
            ]),
        );
        switch ($action) {
            case 'opened':
                $data['text'] = <<<EOF
🔀🆕 新的拉取请求 #$prNumber
<blockquote>$prTitle</blockquote>
<blockquote>$repository #$prNumber</blockquote>
仓库: $repository
创建人: $from
Status: ⏳ 打开

EOF;

                break;
            case 'closed':
                $merged = $payload['pull_request']['merged'] ?? false;
                if ($merged) {
                    $data['text'] = <<<EOF
🔀✅ 合并了拉取请求 #$prNumber
<blockquote>$prTitle</blockquote>
<blockquote>$repository #$prNumber</blockquote>
创建人: $from
操作人: $operator
Status: ✅ 已合并

EOF;

                } else {
                    $data['text'] = <<<EOF
🔀❌ 关闭了拉取请求 #$prNumber
<blockquote>$prTitle</blockquote>
<blockquote>$repository #$prNumber</blockquote>
创建人: $from
操作人: $operator
Status: ❌ 关闭

EOF;
                }
                break;
            case 'reopened':
                $data['text'] = <<<EOF
🔀♻️ 重新打开拉取请求 #$prNumber
<blockquote>$prTitle</blockquote>
<blockquote>$repository #$prNumber</blockquote>
创建人: $from
操作人: $operator
Status: ♻️ 重新打开

EOF;
                break;
            default:
                return;
        }
        $this->dispatch(new SendMessageJob($data, null, 0));
    }
}
