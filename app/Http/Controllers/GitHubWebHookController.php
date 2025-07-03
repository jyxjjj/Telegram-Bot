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
            $data = $this->handleEvent($org, $event, $request->post());
            return $this->json([
                'code' => 200,
                'data' => $data,
            ]);
        } catch (Throwable) {
            return $this->json(['code' => 500]);
        }
    }

    private function handleEvent(string $org, string $event, array $payload): array|string
    {
        return match ($event) {
//            'push' => $this->handlePushEvent($org, $payload),
            'pull_request' => $this->handlePullRequestEvent($org, $payload),
            'issues' => $this->handleIssuesEvent($org, $payload),
            default => '',
        };
    }

    private function handleIssuesEvent(string $org, array $payload): string
    {
        $action = $payload['action'];
        if ($action != 'opened') {
            return '';
        }
        $repository = $payload['repository']['name'];
        $sender = $payload['sender']['login'] ?? 'Unknown User';
        $issue = $payload['issue']['number'];
        $data = [
            'chat_id' => -4971290320,
            'text' => "🐛 New Issue Created\nFrom: $sender\nID: #$issue\n",
        ];
        $data['reply_markup'] = new InlineKeyboard([]);
        $data['reply_markup']->addRow(
            new InlineKeyboardButton([
                'text' => "View #$issue",
                'url' => "https://github.com/$org/$repository/issues/$issue",
            ]),
        );
        $this->dispatch(new SendMessageJob($data, null, 0));
        return 'Issue notification sent successfully.';
    }

    private function handlePullRequestEvent(string $org, array $payload): string
    {
        $action = $payload['action'];
        switch ($action) {
            case 'opened':
                $repository = $payload['repository']['name'];
                $sender = $payload['sender']['login'] ?? 'Unknown User';
                $prNumber = $payload['pull_request']['number'];
                $data = [
                    'chat_id' => -4971290320,
                    'text' => "🔀 New PR Created\nFrom: $sender\nID: #$prNumber\n",
                ];
                $data['reply_markup'] = new InlineKeyboard([]);
                $data['reply_markup']->addRow(
                    new InlineKeyboardButton([
                        'text' => "View #$prNumber",
                        'url' => "https://github.com/$org/$repository/pull/$prNumber",
                    ]),
                );
                $this->dispatch(new SendMessageJob($data, null, 0));
                return 'Pull request opened notification sent successfully.';
            case 'closed':
                $repository = $payload['repository']['name'];
                $sender = $payload['sender']['login'] ?? 'Unknown User';
                $prNumber = $payload['pull_request']['number'];
                $merged = $payload['pull_request']['merged'] ?? false;
                $status = $merged ? '✅Merged' : '⛔Not Merged';
                $data = [
                    'chat_id' => -4971290320,
                    'text' => "🔀PR Closed\nFrom: $sender\nID: #$prNumber\nStatus:$status\n",
                ];
                $data['reply_markup'] = new InlineKeyboard([]);
                $data['reply_markup']->addRow(
                    new InlineKeyboardButton([
                        'text' => "View #$prNumber",
                        'url' => "https://github.com/$org/$repository/pull/$prNumber",
                    ]),
                );
                $this->dispatch(new SendMessageJob($data, null, 0));
                return 'Pull request closed notification sent successfully.';
            default:
                return '';
        }
    }

    private function verifySignature(string $getContent, string $signature, string $secret): bool
    {
        $realSignature = 'sha256=' . hash_hmac('sha256', $getContent, $secret);
        return hash_equals($realSignature, $signature);
    }
}
