<?php

namespace App\Console\Schedule;

use App\Common\Config;
use App\Jobs\SendMessageJob;
use App\Models\TUpdateSubscribes;
use Carbon\Carbon;
use DESMG\RFC6986\Hash;
use Illuminate\Console\Command;
use Illuminate\Foundation\Bus\DispatchesJobs;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use JetBrains\PhpStorm\ArrayShape;
use Throwable;

class ChromeUpdateSubscribe extends Command
{
    use DispatchesJobs;

    protected $signature = 'subscribe:chrome';
    protected $description = 'Get Chrome Newest Version then push to target chat';

    public function __construct()
    {
        parent::__construct();
    }

    public function handle(): int
    {
        try {
            self::info('Start to get Chrome newest versions');
            $updates = $this->getUpdate();
            self::info('Get Chrome newest versions successfully');
            /** @var TUpdateSubscribes[] $datas */
            $datas = TUpdateSubscribes::getAllSubscribeBySoftware('Chrome');
            self::info('Get all subscribers');
            foreach ($datas as $data) {
                $chat_id = $data->chat_id;
                self::info("Start to process {$chat_id}");
                $string = $this->getUpdateData($chat_id, $updates);
                $hash = Hash::sha512(str_replace(' (NEW)', '', $string));
                $message = [
                    'chat_id' => $chat_id,
                    'text' => $string,
                ];
                $lastSend = $this->getLastSend($chat_id);
                if (!$lastSend) {
                    self::info("Haven't send any update to {$chat_id}");
                    $this->dispatch(new SendMessageJob($message, null, 0));
                    $this->setLastSend($chat_id, $hash);
                    self::info("Send update to {$chat_id} successfully");
                } else {
                    if ($lastSend != $hash) {
                        $this->dispatch(new SendMessageJob($message, null, 0));
                        $this->setLastSend($chat_id, $hash);
                        self::info("Send update to {$chat_id} successfully");
                    } else {
                        self::info("No new update for {$chat_id}");
                    }
                }
            }
            return self::SUCCESS;
        } catch (Throwable $e) {
            self::error($e->getMessage());
            return self::FAILURE;
        }
    }

    /**
     * @return array
     */
    #[ArrayShape([
        'StableAndroidVersion' => 'string',
        'CanaryAndroidVersion' => 'string',
        'StableWindowsVersion' => 'string',
        'CanaryWindowsVersion' => 'string',
        'StableMacVersion' => 'string',
        'CanaryMacVersion' => 'string',
        'StableLinuxVersion' => 'string',
        'StableiOSVersion' => 'string'
    ])]
    private function getUpdate(): array
    {
        $StableAndroidVersion =
        $CanaryAndroidVersion =
        $StableWindowsVersion =
        $CanaryWindowsVersion =
        $StableMacVersion =
        $CanaryMacVersion =
        $StableLinuxVersion =
        $StableiOSVersion = 'Fetch Failed';
        $data = $this->getJson();
        foreach ($data as $ver) {
            $ver['platform'] == 'Android' && $ver['channel'] == 'Stable' && $StableAndroidVersion = $ver['version'];
            $ver['platform'] == 'Android' && $ver['channel'] == 'Canary' && $CanaryAndroidVersion = $ver['version'];
            $ver['platform'] == 'Windows' && $ver['channel'] == 'Stable' && $StableWindowsVersion = $ver['version'];
            $ver['platform'] == 'Windows' && $ver['channel'] == 'Canary' && $CanaryWindowsVersion = $ver['version'];
            $ver['platform'] == 'Mac' && $ver['channel'] == 'Stable' && $StableMacVersion = $ver['version'];
            $ver['platform'] == 'Mac' && $ver['channel'] == 'Canary' && $CanaryMacVersion = $ver['version'];
            $ver['platform'] == 'Linux' && $ver['channel'] == 'Stable' && $StableLinuxVersion = $ver['version'];
            $ver['platform'] == 'iOS' && $ver['channel'] == 'Stable' && $StableiOSVersion = $ver['version'];
        }
        return [
            'StableAndroidVersion' => $StableAndroidVersion,
            'CanaryAndroidVersion' => $CanaryAndroidVersion,
            'StableWindowsVersion' => $StableWindowsVersion,
            'CanaryWindowsVersion' => $CanaryWindowsVersion,
            'StableMacVersion' => $StableMacVersion,
            'CanaryMacVersion' => $CanaryMacVersion,
            'StableLinuxVersion' => $StableLinuxVersion,
            'StableiOSVersion' => $StableiOSVersion,
        ];
    }

    /**
     * @return array
     */
    private function getJson(): array
    {
        $headers = Config::CURL_HEADERS;
        $ts = Carbon::now()->getTimestamp();
        $headers['User-Agent'] .= "; Telegram-ChromeUpdate-Subscriber-Runner/$ts";
        return Http::
        withHeaders($headers)
            ->get('https://chromiumdash.appspot.com/fetch_releases?channel=Stable,Canary&platform=Windows,Mac,iOS,Android,Linux&num=1&offset=0')
            ->json();
    }

    /**
     * @param int $chat_id
     * @param array $data
     * @return string
     */
    private function getUpdateData(int $chat_id, array $data): string
    {
        foreach ($data as $k => &$v) {
            $last[$k] = $this->getLastVersion($chat_id, $k);
            if ($last[$k]) {
                if ($last[$k] != $v) {
                    $this->setLastVersion($chat_id, $k, $v);
                    $v .= ' (NEW)';
                }
            } else {
                $this->setLastVersion($chat_id, $k, $v);
            }
        }
        return <<<STR
*New Versions of Chrome Updated*
================
*Mac*: `{$data['StableMacVersion']}`
*Windows*: `{$data['StableWindowsVersion']}`
*Linux*: `{$data['StableLinuxVersion']}`
*Android*: `{$data['StableAndroidVersion']}`
*iOS*: `{$data['StableiOSVersion']}`

*Mac*(_Canary_): `{$data['CanaryMacVersion']}`
*Windows*(_Canary_): `{$data['CanaryWindowsVersion']}`
*Android*(_Canary_): `{$data['CanaryAndroidVersion']}`
STR;
    }

    /**
     * @param int $chat_id
     * @param string $key
     * @return string|false
     */
    private function getLastVersion(int $chat_id, string $key): string|false
    {
        return Cache::get("Schedule::UpdateSubscribe::last_version::{$chat_id}::Chrome::{$key}", false);
    }

    /**
     * @param int $chat_id
     * @param string $key
     * @param $version
     * @return bool
     */
    private function setLastVersion(int $chat_id, string $key, $version): bool
    {
        return Cache::put("Schedule::UpdateSubscribe::last_version::{$chat_id}::Chrome::{$key}", $version, Carbon::now()->addMonths(3));
    }

    /**
     * @param int $chat_id
     * @return string|false
     */
    private function getLastSend(int $chat_id): string|false
    {
        return Cache::get("Schedule::UpdateSubscribe::last_send::{$chat_id}::Chrome", false);
    }

    /**
     * @param int $chat_id
     * @param string $hash
     * @return bool
     */
    private function setLastSend(int $chat_id, string $hash): bool
    {
        return Cache::put("Schedule::UpdateSubscribe::last_send::{$chat_id}::Chrome", $hash, Carbon::now()->addMonths(3));
    }
}
