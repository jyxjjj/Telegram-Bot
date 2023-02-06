<?php

namespace App\Services\Commands;

use App\Jobs\SendMessageJob;
use App\Services\Base\BaseCommand;
use Longman\TelegramBot\Entities\InlineKeyboard;
use Longman\TelegramBot\Entities\InlineKeyboardButton;
use Longman\TelegramBot\Entities\Message;
use Longman\TelegramBot\Telegram;
use RecursiveDirectoryIterator;
use RecursiveIteratorIterator;
use RegexIterator;
use Throwable;

class HelpCommand extends BaseCommand
{
    public string $name = 'help';
    public string $description = 'Show commands list';
    public string $usage = '/help';

    /**
     * @param Message $message
     * @param Telegram $telegram
     * @param int $updateId
     * @return void
     */
    public function execute(Message $message, Telegram $telegram, int $updateId): void
    {
        $chatId = $message->getChat()->getId();
        $userId = $message->getFrom()->getId();
        $data = [
            'chat_id' => $chatId,
//            'text' => $this->getHelp($param),
            'text' => '',
        ];
        $data['text'] .= "你的用户ID： $userId";
        $data['reply_markup'] = new InlineKeyboard([]);
        $button1 = new InlineKeyboardButton([
            'text' => 'DMCA Request',
            'url' => 'https://t.me/zaihua_bot',
        ]);
        $button2 = new InlineKeyboardButton([
            'text' => '版权反馈',
            'url' => 'https://t.me/zaihua_bot',
        ]);
        $button3 = new InlineKeyboardButton([
            'text' => '意见建议',
            'url' => 'https://t.me/zaihua_bot',
        ]);
        $button4 = new InlineKeyboardButton([
            'text' => '技术支持',
            'url' => 'https://t.me/jyxjjj',
        ]);
        $data['reply_markup']->addRow($button1, $button2);
        $data['reply_markup']->addRow($button3, $button4);
        $data['text'] && $this->dispatch(new SendMessageJob($data, null, 0));
    }

    /**
     * @param string|null $commandName
     * @return string
     */
    private function getHelp(?string $commandName): string
    {
        $path = app_path('Services/Commands');
        $files = new RegexIterator(
            new RecursiveIteratorIterator(
                new RecursiveDirectoryIterator($path)
            ),
            '/^.+Command.php$/'
        );
        $classes = [];
        $help = [];
        foreach ($files as $file) {
            $fileName = $file->getFileName();
            $command = str_replace('.php', '', $fileName);
            $command_class = "App\\Services\\Commands\\$command";
            try {
                $command_class = app()->make($command_class);
            } catch (Throwable) {
                continue;
            }
            $classes[] = $command_class;
        }
        if ($commandName == '') {
            foreach ($classes as $class) {
                if ($class->name != 'start') {
                    $help[] = "$class->name - $class->description";
                }
            }
            sort($help);
            return implode("\n", $help);
        } else {
            foreach ($classes as $class) {
                if ($class->name == $commandName) {
                    $str = "Command: <code>$class->name</code>\n";
                    $str .= "Description: <code>$class->description</code>\n";
                    $str .= "Usage: <code>$class->usage</code>\n\n";
                    $str .= "<b>ParamDesc</b>:\n";
                    $str .= "reply_to: It is not a param, you can/should reply to a message to use the command contains this directive.\n";
                    $str .= "at: You can/should metion a user via @ to use the command contains this directive.\n";
                    $str .= "text_mention: You can/should metion a user who has no username to use the command contains this directive.\n";
                    $str .= "user_id: You can/should enter a valid user_id to use the command contains this directive.\n";
                    $str .= "unsupported: This directive has not been supported by this command yet.\n";
                    $str .= "Text included by {}: Params Must Be Included, but may have default value.\n";
                    $str .= "Text included by []: Optional Params.\n";
                    return $str;
                }
            }
            return "Command `$commandName` not found";
        }
    }
}
