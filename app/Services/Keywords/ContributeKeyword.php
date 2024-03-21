<?php /** @noinspection DuplicatedCode */

namespace App\Services\Keywords;

use App\Common\Conversation;
use App\Common\Log\BL;
use App\Common\Log\WL;
use App\Jobs\PassPendingJob;
use App\Jobs\RejectPendingJob;
use App\Jobs\SendMessageJob;
use App\Jobs\SendPhotoJob;
use Exception;
use Longman\TelegramBot\Entities\InlineKeyboard;
use Longman\TelegramBot\Entities\InlineKeyboardButton;
use Longman\TelegramBot\Entities\Keyboard;
use Longman\TelegramBot\Entities\KeyboardButton;
use Longman\TelegramBot\Entities\Message;
use Longman\TelegramBot\Entities\PhotoSize;
use Longman\TelegramBot\Telegram;
use Throwable;

class ContributeKeyword extends ContributeStep
{
    public function preExecute(Message $message): bool
    {
        return $message->getChat()->isPrivateChat() &&
            $message->getText() !== '取消投稿' &&
            $message->getText() !== '分步投稿' &&
            $message->getText() !== '一步投稿' &&
            $message->getText() !== '帮助与反馈' &&
            $message->getText() !== '捐赠信息' &&
            !$message->getReplyToMessage();
    }

    /**
     * @throws Exception
     */
    public function execute(Message $message, Telegram $telegram, int $updateId): void
    {
        $MAX_NAME_LEN = 128;
        $MAX_DESC_LEN = 1024;
        $msgType = $message->getType();
        if ($msgType == 'command') {
            return;
        }
        $user_id = $message->getChat()->getId();
        $user_name = ($message->getChat()->getFirstName() ?? '') . ($message->getChat()->getLastName() ?? '');
        $user_account = $message->getChat()->getUsername() ?? '';
        $sender = [
            'chat_id' => $user_id,
            'text' => '',
        ];
        $data = Conversation::get($user_id, 'contribute');
        if (isset($data['status']) && $data['status'] == 'contribute') {
            $cvid = $data['cvid'];
            switch ($data[$cvid]['status']) {
                case 'name':
                    if ($message->getText() == null) {
                        $sender['text'] = '投稿名称不能为空，请重新输入。';
                        $this->dispatch(new SendMessageJob($sender, null, 0));
                        return;
                    }
                    $data[$cvid]['name'] = str_replace(['<', '>'], ['《', '》'], $message->getText());
                    if (strlen($data[$cvid]['name']) > $MAX_NAME_LEN) {
                        $sender['text'] .= "名称过长，请重新输入。\n";
                        $this->dispatch((new SendMessageJob($sender, null, 0))->delay(0));
                        break;
                    }
                    if (strlen($data[$cvid]['name']) < 1) {
                        $sender['text'] .= "名称过短，请重新输入。\n";
                        $this->dispatch((new SendMessageJob($sender, null, 0))->delay(0));
                        break;
                    }
                    $data[$cvid]['status'] = 'pic';
                    Conversation::save($user_id, 'contribute', $data);
                    $sender['text'] .= "请发送一张与投稿内容相关的<b>静态图片</b>（如：电影海报），以便订阅者快速了解分享内容。\n";
                    $sender['text'] .= "<u><b>发送图片时请勿选择 “无压缩发送”</b></u>。如果不需要，请点击 “不附加图片”。\n";
                    $sender['reply_markup'] = new Keyboard([]);
                    $sender['reply_markup']->setResizeKeyboard(true);
                    $sender['reply_markup']->addRow(new KeyboardButton('不附加图片'));
                    $sender['reply_markup']->addRow(new KeyboardButton('取消投稿'));
                    $this->dispatch((new SendMessageJob($sender, null, 0))->delay(0));
                    break;
                case 'pic':
                    $isCancel = $message->getText() == '不附加图片';
                    if (!$isCancel) {
                        $photos = $message->getPhoto();
                        $photos && usort($photos, function (PhotoSize $left, PhotoSize $right) {
                            return bccomp(
                                bcmul($right->getWidth(), $right->getHeight()),
                                bcmul($left->getWidth(), $left->getHeight())
                            );
                        });
                        $photos && $photoFileId = $photos[0]->getFileId();
                        if (!isset($photoFileId)) {
                            $sender['text'] .= "请发送一张与投稿内容相关的<b>静态图片</b>（如：电影海报），以便订阅者快速了解分享内容。\n";
                            $sender['text'] .= "<u><b>发送图片时请勿选择 “无压缩发送”</b></u>。如果不需要，请点击 “不附加图片”。\n";
                            $this->dispatch((new SendMessageJob($sender, null, 0))->delay(0));
                            break;
                        }
                        $data[$cvid]['pic'] = $photoFileId;
                    } else {
                        $data[$cvid]['pic'] = null;
                    }
                    $data[$cvid]['status'] = 'desc';
                    Conversation::save($user_id, 'contribute', $data);
                    $sender['text'] .= "请您发送关于分享文件的描述（如影片的<b>剧情梗概</b>；<b>500 字</b>以内，支持特殊格式）。\n";
                    $this->dispatch((new SendMessageJob($sender, null, 0))->delay(0));
                    break;
                case 'desc':
                    if ($message->getText() == null) {
                        $sender['text'] = '投稿描述不能为空，请重新输入。';
                        $this->dispatch(new SendMessageJob($sender, null, 0));
                        return;
                    }
                    $data[$cvid]['desc'] = str_replace(['<', '>'], ['《', '》'], $message->getText());
                    if (strlen($data[$cvid]['desc']) > $MAX_DESC_LEN) {
                        $sender['text'] .= "描述过长，请重新输入。\n";
                        $this->dispatch((new SendMessageJob($sender, null, 0))->delay(0));
                        break;
                    }
                    if (strlen($data[$cvid]['desc']) < 1) {
                        $sender['text'] .= "描述过短，请重新输入。\n";
                        $this->dispatch((new SendMessageJob($sender, null, 0))->delay(0));
                        break;
                    }
                    // replace [name](link) to <a href='link'>name</a> of $data[$cvid]['desc']
                    try {
                        $data[$cvid]['desc'] = preg_replace_callback('/\[([^]]+)]\(([^)]+)\)/', function ($linkmatches) {
                            return "<a href='$linkmatches[2]'>$linkmatches[1]</a>";
                        }, $data[$cvid]['desc']);
                    } catch (Throwable) {
                        $data[$cvid]['desc'] = str_replace(['<', '>'], ['《', '》'], $message->getText());
                    }
                    $data[$cvid]['status'] = 'link';
                    Conversation::save($user_id, 'contribute', $data);
                    $sender['text'] .= "请发送分享链接，频道接受阿里云盘、百度网盘、OneDrive 和 SharePoint 资源。请确保为永久分享，尽量不要设置提取码。\n";
                    $sender['reply_markup'] = new Keyboard([]);
                    $sender['reply_markup']->setResizeKeyboard(true);
                    $sender['reply_markup']->addRow(new KeyboardButton('取消投稿'));
                    $this->dispatch((new SendMessageJob($sender, null, 0))->delay(0));
                    break;
                case 'link':
                    $link = $message->getText();
                    if (
                        !$link ||
                        strlen($link) < 8 ||
                        !str_starts_with($link, 'https://www.alipan.com/s/') &&
                        !str_starts_with($link, 'https://www.aliyundrive.com/s/') &&
                        !str_starts_with($link, 'https://pan.baidu.com/s/') &&
                        !str_starts_with($link, 'https://pan.quark.cn/s/') &&
                        !str_starts_with($link, 'https://1drv.ms/') &&
                        !str_starts_with($link, 'https://sharepoint.com/')
                    ) {
                        $sender['text'] .= "链接格式错误，请发送正确的分享链接，频道接受阿里云盘、百度网盘、OneDrive 和 SharePoint 资源。请确保为永久分享，尽量不要设置提取码。\n";
                        $this->dispatch((new SendMessageJob($sender, null, 0))->delay(0));
                        break;
                    }
                    if (str_contains($link, '大小')) {
                        $sender['text'] .= "链接部分禁止带有大小信息，请重新发送，如果必须提供大小信息，请放在描述部分。\n";
                        $this->dispatch((new SendMessageJob($sender, null, 0))->delay(0));
                        break;
                    }
                    $data[$cvid]['link'] = $link;
                    $data[$cvid]['status'] = 'tag';
                    Conversation::save($user_id, 'contribute', $data);
                    $sender['text'] .= "您将要分享的文件搜索词是？\n\n关键词越细分，越容易被查找到。关键词以 # 开头，多个关键词之间用空格分开。\n\n";
                    $sender['text'] .= "为方便群友搜索，关键词用于大家快速简洁的搜索到内容。建议比如电影：【怪奇物语】。关键词添加为：#怪奇 #物语 #怪奇物语 #4K #恐怖 #奇幻\n\n";
                    $sender['text'] .= "关键词越细分，越容易被查找到。关键词以 # 开头，多个关键词之间用空格分开。\n\n";
                    $sender['text'] .= "示例：#怪奇 #物语 #怪奇物语 #4K #恐怖 #奇幻\n";
                    $this->dispatch((new SendMessageJob($sender, null, 0))->delay(0));
                    break;
                case 'tag':
                    $data[$cvid]['tag'] = $message->getText() ?? '无关键词';
                    $data[$cvid]['status'] = 'confirm';
                    Conversation::save($user_id, 'contribute', $data);
                    $hasPic = $data[$cvid]['pic'] != null;
                    $sender['reply_markup'] = new Keyboard([]);
                    $sender['reply_markup']->setResizeKeyboard(true);
                    $sender['reply_markup']->addRow(new KeyboardButton('确认投稿'));
                    $sender['reply_markup']->addRow(new KeyboardButton('取消投稿'));
                    if ($hasPic) {
                        $sender['photo'] = $data[$cvid]['pic'];
                        $sender['text'] = null;
                        $sender['caption'] = "资源名称：{$data[$cvid]['name']}\n\n";
                        $sender['caption'] .= "资源简介：{$data[$cvid]['desc']}\n\n";
                        $sender['caption'] .= "链接：{$data[$cvid]['link']}\n\n";
                        $sender['caption'] .= "🔍 关键词：{$data[$cvid]['tag']}\n\n";
                        $this->dispatch((new SendPhotoJob($sender, 0))->delay(0));
                    } else {
                        $sender['text'] = "资源名称：{$data[$cvid]['name']}\n\n";
                        $sender['text'] .= "资源简介：{$data[$cvid]['desc']}\n\n";
                        $sender['text'] .= "链接：{$data[$cvid]['link']}\n\n";
                        $sender['text'] .= "🔍 关键词：{$data[$cvid]['tag']}\n\n";
                        $this->dispatch((new SendMessageJob($sender, null, 0))->delay(0));
                    }
                    $sender['text'] = "已生成预览，<b>请核对各项信息是否准确</b>，然后使用下方的按钮确认您的投稿内容。\n";
                    $this->dispatch((new SendMessageJob($sender, null, 0))->delay(2));
                    break;
                case 'confirm':
                    $isConfirm = $message->getText() === '确认投稿';
                    if (!$isConfirm) {
                        $sender['text'] .= "您有正在进行中的投稿，请确认您的投稿或取消投稿。";
                        $sender['reply_markup'] = new Keyboard([]);
                        $sender['reply_markup']->setResizeKeyboard(true);
                        $sender['reply_markup']->addRow(new KeyboardButton('确认投稿'));
                        $sender['reply_markup']->addRow(new KeyboardButton('取消投稿'));
                        $this->dispatch((new SendMessageJob($sender, null, 0))->delay(0));
                        break;
                    }

                    $data['status'] = 'free';
                    unset($data['cvid']);
                    $data[$cvid]['status'] = 'pending';
                    Conversation::save($user_id, 'contribute', $data);

                    $sender['text'] .= "✅ 投稿成功，我们将稍后通过机器人告知您审核结果，请保持联系畅通 ~\n\n";
                    $sender['text'] .= "审核可能需要一定时间，如果您长时间未收到结果，可联系群内管理员。您现在可以开始下一个投稿。\n";
                    $sender['reply_markup'] = new Keyboard([]);
                    $sender['reply_markup']->setResizeKeyboard(true);
                    $sender['reply_markup']->addRow(new KeyboardButton('一步投稿'), new KeyboardButton('分步投稿'));
                    $sender['reply_markup']->addRow(new KeyboardButton('帮助与反馈'), new KeyboardButton('捐赠信息'));
                    $this->dispatch((new SendMessageJob($sender, null, 0))->delay(0));

                    $data_pending = Conversation::get('pending', 'pending');
                    $data_pending[$cvid] = $user_id;
                    Conversation::save('pending', 'pending', $data_pending);

                    $user_link = "<a href='tg://user?id=$user_id'>$user_id</a>";

                    unset($sender['reply_markup']);
                    if (WL::get($user_id)) {
                        // 将 '白名单用户{name}的投稿已自动通过审核' 发送到审核群
                        $sender['chat_id'] = env('YPP_SOURCE_ID');
                        $sender['text'] = "白名单：\n";
                        $sender['text'] .= "<a href='{$data[$cvid]['link']}'>{$data[$cvid]['name']}</a>\n\n";
                        $sender['text'] .= "投稿人：$user_link\n";
                        $sender['text'] .= "投稿人昵称：$user_name\n";
                        $sender['text'] .= "投稿人账号：$user_account\n";
                        $sender['text'] .= "点击复制ID：<code>$user_id</code>\n";
                        $sender['text'] .= "投稿ID：$cvid\n";
                        $this->dispatch(new PassPendingJob($cvid));
                        $this->dispatch((new SendMessageJob($sender, null, 0))->delay(0));
                    } elseif (BL::get($user_id)) {
                        // 将 '黑名单用户{name}的投稿已自动拒绝' 发送到审核群
                        $sender['chat_id'] = env('YPP_SOURCE_ID');
                        $sender['text'] = "黑名单：{$data[$cvid]['name']}已自动拒绝\n\n";
                        $sender['text'] .= "链接：{$data[$cvid]['link']}\n\n";
                        $sender['text'] .= "投稿人：$user_link\n";
                        $sender['text'] .= "投稿人昵称：$user_name\n";
                        $sender['text'] .= "投稿人账号：$user_account\n";
                        $sender['text'] .= "点击复制ID：<code>$user_id</code>\n";
                        $sender['text'] .= "投稿ID：$cvid\n";
                        $this->dispatch(new RejectPendingJob($cvid));
                        $this->dispatch((new SendMessageJob($sender, null, 0))->delay(0));
                    } else {
                        //#region 发送投稿到审核群
                        // 判断是否含图片
                        $hasPic = !empty($data[$cvid]['pic']);
                        $sender['chat_id'] = env('YPP_SOURCE_ID');
                        // 生成消息
                        if ($hasPic) {
                            $sender['text'] = null;
                            $sender['photo'] = $data[$cvid]['pic'];
                            $sender['caption'] = "资源名称：{$data[$cvid]['name']}\n\n";
                            $sender['caption'] .= "资源简介：{$data[$cvid]['desc']}\n\n";
                            $sender['caption'] .= "链接：{$data[$cvid]['link']}\n\n";
                            $sender['caption'] .= "🔍 关键词：{$data[$cvid]['tag']}\n\n";
                            $sender['caption'] .= "投稿ID：$cvid\n";
                            $sender['caption'] .= "投稿人：$user_link\n";
                            $sender['caption'] .= "投稿人昵称：$user_name\n";
                            $sender['caption'] .= "投稿人账号：$user_account\n";
                            $sender['caption'] .= "点击复制ID：<code>$user_id</code>\n";
                        } else {
                            $sender['text'] = "资源名称：{$data[$cvid]['name']}\n\n";
                            $sender['text'] .= "资源简介：{$data[$cvid]['desc']}\n\n";
                            $sender['text'] .= "链接：{$data[$cvid]['link']}\n\n";
                            $sender['text'] .= "🔍 关键词：{$data[$cvid]['tag']}\n\n";
                            $sender['text'] .= "投稿ID：$cvid\n";
                            $sender['text'] .= "投稿人：$user_link\n";
                            $sender['text'] .= "投稿人昵称：$user_name\n";
                            $sender['text'] .= "投稿人账号：$user_account\n";
                            $sender['text'] .= "点击复制ID：<code>$user_id</code>\n";
                        }
                        // InlineKeyboard
                        $sender['reply_markup'] = new InlineKeyboard([]);
                        $sender['reply_markup']->addRow(
                            new InlineKeyboardButton([
                                'text' => '通过',
                                'callback_data' => "pendingpass$cvid",
                            ]),
                            new InlineKeyboardButton([
                                'text' => '拒绝',
                                'callback_data' => "pendingreject$cvid",
                            ])
                        );
                        $sender['reply_markup']->addRow(
                            new InlineKeyboardButton([
                                'text' => '忽略',
                                'callback_data' => "pendingignore$cvid",
                            ])
                        );
                        $sender['need_save'] = $cvid;
                        // 发送消息
                        $hasPic && $this->dispatch((new SendPhotoJob($sender, 0))->delay(0));
                        !$hasPic && $this->dispatch((new SendMessageJob($sender, null, 0))->delay(0));
                        //#endregion
                    }
                    break;
            }
        } elseif (isset($data['status']) && $data['status'] == 'contribute2') {
            $cvid = $data['cvid'];
            $messageText = $message->getCaption() ?? $message->getText();
            if (empty($messageText)) {
                $sender['text'] = "格式错误，请重新发送";
                $this->dispatch((new SendMessageJob($sender, null, 0))->delay(0));
                return;
            }
            $matched = preg_match('/(?:资源)?名称：(.+)\n\n(?:资源简介|描述)：((?:.|\n)+)\n\n链接：(https:\/\/www\.aliyundrive\.com\/s\/.+)\n+.+(?:关键词|标签)：(.+)/s', $messageText, $matches)
                ||
                preg_match('/(?:资源)?名称：(.+)\n\n(?:资源简介|描述)：((?:.|\n)+)\n\n链接：(https:\/\/www\.alipan\.com\/s\/.+)\n+.+(?:关键词|标签)：(.+)/s', $messageText, $matches)
                ||
                preg_match('/(?:资源)?名称：(.+)\n\n(?:资源简介|描述)：((?:.|\n)+)\n\n链接：(https:\/\/pan\.quark\.cn\/s\/.+)\n+.+(?:关键词|标签)：(.+)/s', $messageText, $matches)
                ||
                preg_match('/(?:资源)?名称：(.+)\n\n(?:资源简介|描述)：((?:.|\n)+)\n\n链接：(https:\/\/pan\.baidu\.com\/s\/.+)\n+.+(?:关键词|标签)：(.+)/s', $messageText, $matches);
            if ($matched) {
                if (str_contains($matches[3], '大小')) {
                    $sender['text'] = "链接部分禁止带有大小信息，请重新发送，如果必须提供大小信息，请放在描述部分。";
                } else {
                    $data[$cvid]['name'] = str_replace(['<', '>'], ['《', '》'], $matches[1]);
                    $data[$cvid]['desc'] = str_replace(['<', '>'], ['《', '》'], $matches[2]);
                    // replace [name](link) to <a href='link'>name</a> of $data[$cvid]['desc']
                    try {
                        $data[$cvid]['desc'] = preg_replace_callback('/\[([^]]+)]\(([^)]+)\)/', function ($linkmatches) {
                            return "<a href='$linkmatches[2]'>$linkmatches[1]</a>";
                        }, $data[$cvid]['desc']);
                    } catch (Throwable) {
                        $data[$cvid]['desc'] = str_replace(['<', '>'], ['《', '》'], $matches[2]);
                    }
                    if (strlen($data[$cvid]['name']) > $MAX_NAME_LEN || strlen($data[$cvid]['desc']) > $MAX_DESC_LEN) {
                        $sender['text'] = "资源名称或简介过长，请重新发送";
                    } else {
                        $data[$cvid]['link'] = $matches[3];
                        $data[$cvid]['tag'] = $matches[4];
                        $photos = $message->getPhoto() ?? false;
                        $photos && usort($photos, function (PhotoSize $left, PhotoSize $right) {
                            return bccomp(
                                bcmul($right->getWidth(), $right->getHeight()),
                                bcmul($left->getWidth(), $left->getHeight())
                            );
                        });
                        $photos && $photoFileId = $photos[0]->getFileId();
                        if (!isset($photoFileId)) {
                            $data[$cvid]['pic'] = null;
                            $data['status'] = 'contribute';
                            $data[$cvid]['status'] = 'confirm';
                            Conversation::save($user_id, 'contribute', $data);
                            $sender['text'] = "资源名称：{$data[$cvid]['name']}\n\n";
                            $sender['text'] .= "资源简介：{$data[$cvid]['desc']}\n\n";
                            $sender['text'] .= "链接：{$data[$cvid]['link']}\n\n";
                            $sender['text'] .= "🔍 关键词：{$data[$cvid]['tag']}\n\n";
                            $this->dispatch((new SendMessageJob($sender, null, 0))->delay(0));
                        } else {
                            $data[$cvid]['pic'] = $photoFileId;
                            $data['status'] = 'contribute';
                            $data[$cvid]['status'] = 'confirm';
                            Conversation::save($user_id, 'contribute', $data);
                            $sender['photo'] = $data[$cvid]['pic'];
                            $sender['text'] = null;
                            $sender['caption'] = "资源名称：{$data[$cvid]['name']}\n\n";
                            $sender['caption'] .= "资源简介：{$data[$cvid]['desc']}\n\n";
                            $sender['caption'] .= "链接：{$data[$cvid]['link']}\n\n";
                            $sender['caption'] .= "🔍 关键词：{$data[$cvid]['tag']}\n\n";
                            $this->dispatch((new SendPhotoJob($sender, 0))->delay(0));
                        }
                        $sender['reply_markup'] = new Keyboard([]);
                        $sender['reply_markup']->setResizeKeyboard(true);
                        $sender['reply_markup']->addRow(new KeyboardButton('确认投稿'));
                        $sender['reply_markup']->addRow(new KeyboardButton('取消投稿'));
                        $sender['text'] = "已生成预览，<b>请核对各项信息是否准确</b>，然后使用下方的按钮确认您的投稿内容。\n";
                    }
                }
            } else {
                $sender['text'] = "格式错误，请重新发送";
            }
            $this->dispatch((new SendMessageJob($sender, null, 0))->delay(0));
        } else {
            $sender['text'] .= "请先开始投稿。\n";
            $this->dispatch((new SendMessageJob($sender, null, 0))->delay(0));
        }
    }
}
