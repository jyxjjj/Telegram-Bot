<?php

namespace App\Http\Services\Bots;

use App\Http\Services\BaseService;
use GuzzleHttp\Client;
use Longman\TelegramBot\Entities\Update;
use Longman\TelegramBot\Exception\TelegramException;
use Longman\TelegramBot\Request;
use Longman\TelegramBot\Telegram;

class BotCommon extends BaseService
{
    private string $api_base_uri;
    private int $admin_user_id;
    private string $bot_api_key;
    private string $bot_username;
    private string $mysql_db;
    private string $mysql_host;
    private string $mysql_password;
    private int $mysql_port;
    private string $mysql_table_prefix;
    private string $mysql_user;
    private string $proxy;
    private Client $request_client;
    private Telegram $telegram;

    /**
     * @throws TelegramException
     */
    public function __construct()
    {
        $this->api_base_uri = env('TELEGRAM_API_BASE_URI');
        $this->admin_user_id = env('TELEGRAM_ADMIN_USER_ID');
        $this->bot_api_key = env('TELEGRAM_BOT_TOKEN');
        $this->bot_username = env('TELEGRAM_BOT_USERNAME');
        $this->mysql_db = env('DB_DATABASE');
        $this->mysql_host = env('DB_HOST');
        $this->mysql_password = env('DB_PASSWORD');
        $this->mysql_port = env('DB_PORT');
        $this->mysql_table_prefix = config('database.connections.mysql.prefix');
        $this->mysql_user = env('DB_USERNAME');
        $this->proxy = env('TELEGRAM_PROXY');
        $this->request_client = new Client([
            'base_uri' => $this->api_base_uri,
            'proxy' => $this->proxy,
            'timeout' => 60,
        ]);
        $this->telegram = new Telegram($this->bot_api_key, $this->bot_username);
        Request::setClient($this->request_client);
        $this->telegram->enableAdmin($this->admin_user_id);
        $this->enableMysql();
        $this->telegram->setDownloadPath(storage_path('app/telegram'));
        $this->telegram->setUploadPath(storage_path('app/telegram'));
        $this->telegram->addCommandsPath(app_path('Http/Services/Bots/Commands'));
    }

    /**
     * @return Telegram
     */
    public function getTelegram(): Telegram
    {
        return $this->telegram;
    }

    /**
     * @throws TelegramException
     */
    public function enableMysql()
    {
        $this->telegram->enableMySql([
            'host' => $this->mysql_host,
            'port' => $this->mysql_port,
            'user' => $this->mysql_user,
            'password' => $this->mysql_password,
            'database' => $this->mysql_db,
        ], $this->mysql_table_prefix);
    }

    /**
     * @param array|null $data
     * @param int|null $timeout
     * @return Update[]
     * @throws TelegramException
     */
    public function getUpdates(?array $data = null, ?int $timeout = null): array
    {
        $updates = $this->telegram->handleGetUpdates($data, $timeout);
        return $updates->getResult();
    }

    public function clearUpdates()
    {
        Request::getUpdates(['offset' => -1,]);
    }
}
