<?php

namespace App\Services\Commands;

use App\Jobs\SendMessageJob;
use App\Services\Base\BaseCommand;
use Longman\TelegramBot\Entities\Message;
use Longman\TelegramBot\Telegram;
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
        $param = $message->getText(true);
        $data = [
            'chat_id' => $chatId,
            'text' => $this->getHelp($param),
        ];
        $data['text'] && $this->dispatch(new SendMessageJob($data, null, 0));
    }

    /**
     * @param $commandName
     * @return string
     */
    private function getHelp($commandName): string
    {
        $files = glob(app_path('Services/Commands/*Command.php'));
        $classes = [];
        $help = [];
        foreach ($files as $fileName) {
            $command = basename($fileName, '.php');
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
        } elseif ($commandName == 'ParamDesc') {
            $str = "<b>ParamDesc</b>:\n";
            $str .= "reply_to: It is not a param, you can/should reply to a message to use the command contains this directive.\n";
            $str .= "at: You can/should metion a user via @ to use the command contains this directive.\n";
            $str .= "text_mention: You can/should metion a user who has no username to use the command contains this directive.\n";
            $str .= "user_id: You can/should enter a valid user_id to use the command contains this directive.\n";
            $str .= "unsupported: This directive has not been supported by this command yet.\n";
            $str .= "Text included by {}: Params Must Be Included, but may have default value.\n";
            $str .= "Text included by []: Optional Params.\n";
            return $str;
        } else {
            foreach ($classes as $class) {
                if ($class->name == $commandName) {
                    $str = "Command: <code>$class->name</code>\n";
                    $str .= "Description: <code>$class->description</code>\n";
                    $str .= "Usage: <code>$class->usage</code>\n\n";
                    $str .= "Send <code>/help ParamDesc</code> (Case-Sensitive) to get the param description.\n";
                    return $str;
                }
            }
            return "Command <code>$commandName</code> not found";
        }
    }
}
