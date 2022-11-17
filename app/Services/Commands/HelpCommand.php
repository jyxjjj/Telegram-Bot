<?php

namespace App\Services\Commands;

use App\Jobs\SendMessageJob;
use App\Services\Base\BaseCommand;
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
        $param = $message->getText(true);
        $data = [
            'chat_id' => $chatId,
            'text' => $this->getHelp2($userId),
        ];
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
                    $str .= "<b>ParamDesc:</b>\n";
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

    /**
     * @param int $param
     * @return string
     */
    private function getHelp2(int $param): string
    {
        return <<<EOF
你的用户ID： {$param}
使用问题及建议联系： @zaihua_bot
技术支持请联系： @jyxjjj
我们提供了DMCA及其他版权问题反馈通道
如您有任何版权相关问题，请联系： @zaihua_bot
EOF;
    }
}
