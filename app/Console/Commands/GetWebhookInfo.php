<?php

namespace App\Console\Commands;

use App\Common\BotCommon;
use Illuminate\Console\Command;
use Longman\TelegramBot\Entities\WebhookInfo;
use Longman\TelegramBot\Exception\TelegramException;
use Longman\TelegramBot\Request;

class GetWebhookInfo extends Command
{
    protected $signature = 'command:GetWebhookInfo';
    protected $description = 'Get Webhook Info https://core.telegram.org/bots/api#getwebhookinfo';

    /**
     * @return int
     */
    public function handle(): int
    {
        try {
            BotCommon::getTelegram();
            $request = Request::getWebhookInfo();
            if (!$request->isOk()) {
                throw new TelegramException($request->getDescription());
            }
            /** @var $result WebhookInfo */
            $result = $request->getResult();
            $url = $result->getUrl() == '' ? 'Not set' : $result->getUrl();
            $ip = $result->getIpAddress() == '' ? '<empty>' : $result->getIpAddress();
            $has_custom_certificate = $result->getHasCustomCertificate() ? 'true' : 'false';
            $pending_update_count = $result->getPendingUpdateCount();
            $last_error_date = $result->getLastErrorDate() == '' ? '<empty>' : $result->getLastErrorDate();
            $last_error_message = $result->getLastErrorMessage() == '' ? '<empty>' : $result->getLastErrorMessage();
            $max_connections = $result->getMaxConnections() == '' ? '40' : $result->getMaxConnections();
            $allowed_updates = count($result->getAllowedUpdates()) == 0 ? '<empty>' : ' - ' . implode("\n - ", $result->getAllowedUpdates());
            self::info("URL: $url");
            self::info("IP: $ip");
            self::info("Has custom certificate: $has_custom_certificate");
            self::info("Pending update count: $pending_update_count");
            self::info("Last error date: $last_error_date");
            self::info("Last error message: $last_error_message");
            self::info("Max connections: $max_connections");
            self::info('Allowed updates: ');
            self::info($allowed_updates);
        } catch (TelegramException $e) {
            self::error($e->getMessage());
        }
        return self::SUCCESS;
    }
}
