<?php

namespace App\Services\Keywords;

use App\Exceptions\Handler;
use App\Jobs\BanMemberJob;
use App\Jobs\DeleteMessageJob;
use App\Jobs\SendMessageJob;
use App\Models\TChatKeywords;
use App\Models\TChatKeywordsOperationEnum;
use App\Models\TChatKeywordsTargetEnum;
use App\Services\Base\BaseKeyword;
use Illuminate\Database\Eloquent\Collection;
use Longman\TelegramBot\Entities\Message;
use Longman\TelegramBot\Telegram;
use Throwable;

class KeywordDetectKeyword extends BaseKeyword
{
    public string $name = 'Keyword Detecter';
    public string $description = 'Match Keywords';
    protected string $pattern = '//';

    public function preExecute(Message $message): bool
    {
        return true;
    }

    public function execute(Message $message, Telegram $telegram, int $updateId): void
    {
        /** @var Collection<TChatKeywords> $keywords */
        $keywords = TChatKeywords::getKeywords($message->getChat()->getId());
        foreach ($keywords as $keyword) {
            try {
                $this->handle($keyword->keyword, $keyword->target, $keyword->operation, $keyword->data, $message, $telegram, $updateId);
            } catch (Throwable $e) {
                Handler::logError($e);
            }
        }
    }

    private function handle(
        string                     $keyword,
        TChatKeywordsTargetEnum    $target,
        TChatKeywordsOperationEnum $operation,
        array                      $data,
        Message                    $message, Telegram $telegram, int $updateId
    )
    {
        switch ($target) {
            case TChatKeywordsTargetEnum::TARGET_CHATID:
                $chatId = $message->getChat()->getId();
                if ($chatId == $keyword) {
                    $this->runOperation($operation, $data, $message, $telegram, $updateId);
                }
                break;
            case TChatKeywordsTargetEnum::TARGET_USERID:
                $userId = $message->getFrom()->getId();
                if ($userId == $keyword) {
                    $this->runOperation($operation, $data, $message, $telegram, $updateId);
                }
                break;
            case TChatKeywordsTargetEnum::TARGET_NAME:
                $name = ($message->getFrom()->getFirstName() ?? '') . ($message->getFrom()->getLastName() ?? '');
                if (str_contains($name, $keyword)) {
                    $this->runOperation($operation, $data, $message, $telegram, $updateId);
                }
                break;
            case TChatKeywordsTargetEnum::TARGET_FROMNAME:
                if ($message->getForwardFrom()) {
                    $fromName = ($message->getForwardFrom()->getFirstName() ?? '') . ($message->getForwardFrom()->getLastName() ?? '');
                    if (str_contains($fromName, $keyword)) {
                        $this->runOperation($operation, $data, $message, $telegram, $updateId);
                    }
                }
                break;
            case TChatKeywordsTargetEnum::TARGET_TITLE:
                if ($message->getForwardFromChat()) {
                    $title = $message->getForwardFromChat()->getTitle();
                    if (str_contains($title, $keyword)) {
                        $this->runOperation($operation, $data, $message, $telegram, $updateId);
                    }
                }
                break;
            case TChatKeywordsTargetEnum::TARGET_TEXT:
                $text = $message->getText() ?? $message->getCaption() ?? '';
                if (str_contains($text, $keyword)) {
                    $this->runOperation($operation, $data, $message, $telegram, $updateId);
                }
                break;
            case TChatKeywordsTargetEnum::TARGET_DICE:
                if ($message->getDice()) {
                    $text = $message->getDice()->getEmoji() ?? '';
                    if (strtoupper(bin2hex($text)) == strtoupper($keyword)) {
                        $this->runOperation($operation, $data, $message, $telegram, $updateId);
                    }
                }
                break;
        }
    }

    private function runOperation(
        TChatKeywordsOperationEnum $operation,
        array                      $data,
        Message                    $message, Telegram $telegram, int $updateId
    )
    {
        switch ($operation) {
            case TChatKeywordsOperationEnum::OPERATION_REPLY:
                $this->reply($data, $message, $telegram, $updateId);
                break;
            case TChatKeywordsOperationEnum::OPERATION_DELETE:
                $this->delete($data, $message, $telegram, $updateId);
                break;
            case TChatKeywordsOperationEnum::OPERATION_WARN:
                $this->warn($data, $message, $telegram, $updateId);
                break;
            case TChatKeywordsOperationEnum::OPERATION_BAN:
                $this->ban($data, $message, $telegram, $updateId);
                break;
            case TChatKeywordsOperationEnum::OPERATION_RESTRICT:
                $this->restrict($data, $message, $telegram, $updateId);
                break;
            case TChatKeywordsOperationEnum::OPERATION_FORWARD:
                $this->forward($data, $message, $telegram, $updateId);
                break;
            case TChatKeywordsOperationEnum::OPERATION_REPEAT:
                $this->repeat($data, $message, $telegram, $updateId);
                break;
        }
    }

    private function reply(array $data, Message $message, Telegram $telegram, int $updateId)
    {
        $sender = [
            'chat_id' => $message->getChat()->getId(),
            'reply_to_message_id' => $message->getMessageId(),
        ];
    }

    private function delete(array $data, Message $message, Telegram $telegram, int $updateId)
    {
        $deleter = [
            'chat_id' => $message->getChat()->getId(),
            'message_id' => $message->getMessageId(),
        ];
        $this->dispatch(new DeleteMessageJob($deleter, 0));
        $sender = [
            'chat_id' => $message->getChat()->getId(),
        ];
        isset($data['text']) && $sender['text'] = $data['text'];
        count($sender) > 1 && $this->dispatch(new SendMessageJob($sender));
    }

    private function warn(array $data, Message $message, Telegram $telegram, int $updateId)
    {

    }

    private function ban(array $data, Message $message, Telegram $telegram, int $updateId)
    {
        $deleter = [
            'chat_id' => $message->getChat()->getId(),
            'message_id' => $message->getMessageId(),
        ];
        $this->dispatch(new DeleteMessageJob($deleter, 0));

        $banner = [
            'chat_id' => $message->getChat()->getId(),
            'message_id' => $message->getMessageId(),
            'user_id' => $message->getFrom()->getId(),
        ];
        $this->dispatch(new BanMemberJob($banner));
    }

    private function restrict(array $data, Message $message, Telegram $telegram, int $updateId)
    {

    }

    private function forward(array $data, Message $message, Telegram $telegram, int $updateId)
    {

    }

    private function repeat(array $data, Message $message, Telegram $telegram, int $updateId)
    {

    }
}
