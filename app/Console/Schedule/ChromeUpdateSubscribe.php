<?php

namespace App\Console\Schedule;

use App\Common\Config;
use App\Exceptions\Handler;
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
            $updates = $this->getUpdate();
            $datas = TUpdateSubscribes::getAllSubscribeBySoftware('Chrome');
            foreach ($datas as $data) {
                $chat_id = $data['chat_id'];
                $string = $this->getUpdateData($chat_id, $updates);
                $hash = Hash::sha512(str_replace(' (NEW)', '', $string));
                $message = [
                    'chat_id' => $chat_id,
                    'text' => $string,
                ];
                $lastSend = $this->getLastSend($chat_id);
                if (!$lastSend) {
                    $this->dispatch(new SendMessageJob($message, null, 0));
                    $this->setLastSend($chat_id, $hash);
                } else {
                    if ($lastSend != $hash) {
                        $this->dispatch(new SendMessageJob($message, null, 0));
                        $this->setLastSend($chat_id, $hash);
                    }
                }
            }
            return self::SUCCESS;
        } catch (Throwable $e) {
            Handler::logError($e);
            return self::FAILURE;
        }
    }

    /**
     * @return array
     */
    #[ArrayShape([
        'StableAndroidVersion' => 'string',
//        'CanaryAndroidVersion' => 'string',
        'StableWindowsVersion' => 'string',
//        'CanaryWindowsVersion' => 'string',
        'StableMacVersion' => 'string',
//        'CanaryMacVersion' => 'string',
        'StableLinuxVersion' => 'string',
        'StableiOSVersion' => 'string'
    ])]
    private function getUpdate(): array
    {
        $StableAndroidVersion =
//        $CanaryAndroidVersion =
        $StableWindowsVersion =
//        $CanaryWindowsVersion =
        $StableMacVersion =
//        $CanaryMacVersion =
        $StableLinuxVersion =
        $StableiOSVersion = 'Fetch Failed';
        $data = $this->getJson();
        foreach ($data as $ver) {
            $ver['platform'] == 'Android' && $ver['channel'] == 'Stable' && $StableAndroidVersion = $ver['version'];
//            $ver['platform'] == 'Android' && $ver['channel'] == 'Canary' && $CanaryAndroidVersion = $ver['version'];
            $ver['platform'] == 'Windows' && $ver['channel'] == 'Stable' && $StableWindowsVersion = $ver['version'];
//            $ver['platform'] == 'Windows' && $ver['channel'] == 'Canary' && $CanaryWindowsVersion = $ver['version'];
            $ver['platform'] == 'Mac' && $ver['channel'] == 'Stable' && $StableMacVersion = $ver['version'];
//            $ver['platform'] == 'Mac' && $ver['channel'] == 'Canary' && $CanaryMacVersion = $ver['version'];
            $ver['platform'] == 'Linux' && $ver['channel'] == 'Stable' && $StableLinuxVersion = $ver['version'];
            $ver['platform'] == 'iOS' && $ver['channel'] == 'Stable' && $StableiOSVersion = $ver['version'];
        }
        return [
            'StableAndroidVersion' => $StableAndroidVersion,
//            'CanaryAndroidVersion' => $CanaryAndroidVersion,
            'StableWindowsVersion' => $StableWindowsVersion,
//            'CanaryWindowsVersion' => $CanaryWindowsVersion,
            'StableMacVersion' => $StableMacVersion,
//            'CanaryMacVersion' => $CanaryMacVersion,
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
<b>New Versions of Chrome Updated</b>
================
<b>Mac</b>: <code>{$data['StableMacVersion']}</code>
<b>Windows</b>: <code>{$data['StableWindowsVersion']}</code>
<b>Linux</b>: <code>{$data['StableLinuxVersion']}</code>
<b>Android</b>: <code>{$data['StableAndroidVersion']}</code>
<b>iOS</b>: <code>{$data['StableiOSVersion']}</code>
STR;
//<b>Mac</b>(_Canary_): <code>{$data['CanaryMacVersion']}</code>
//<b>Windows</b>(_Canary_): <code>{$data['CanaryWindowsVersion']}</code>
//<b>Android</b>(_Canary_): <code>{$data['CanaryAndroidVersion']}</code>
//STR;
    }

    /**
     * @param int $chat_id
     * @param string $key
     * @return string|false
     */
    private function getLastVersion(int $chat_id, string $key): string|false
    {
        return Cache::get("Schedule::UpdateSubscribe::last_version::$chat_id::Chrome::$key", false);
    }

    /**
     * @param int $chat_id
     * @param string $key
     * @param $version
     * @return bool
     */
    private function setLastVersion(int $chat_id, string $key, $version): bool
    {
        return Cache::put("Schedule::UpdateSubscribe::last_version::$chat_id::Chrome::$key", $version, Carbon::now()->addMonths(3));
    }

    /**
     * @param int $chat_id
     * @return string|false
     */
    private function getLastSend(int $chat_id): string|false
    {
        return Cache::get("Schedule::UpdateSubscribe::last_send::$chat_id::Chrome", false);
    }

    /**
     * @param int $chat_id
     * @param string $hash
     * @return bool
     */
    private function setLastSend(int $chat_id, string $hash): bool
    {
        return Cache::put("Schedule::UpdateSubscribe::last_send::$chat_id::Chrome", $hash, Carbon::now()->addMonths(3));
    }
}
