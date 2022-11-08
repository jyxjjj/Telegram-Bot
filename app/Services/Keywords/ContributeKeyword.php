<?php

namespace App\Services\Keywords;

use App\Common\Conversation;
use App\Jobs\SendMessageJob;
use App\Jobs\SendPhotoJob;
use Longman\TelegramBot\Entities\Keyboard;
use Longman\TelegramBot\Entities\KeyboardButton;
use Longman\TelegramBot\Entities\Message;
use Longman\TelegramBot\Entities\PhotoSize;
use Longman\TelegramBot\Telegram;

class ContributeKeyword extends ContributeStep
{
    public function preExecute(Message $message): bool
    {
        return $message->getChat()->isPrivateChat() && $message->getText() !== '取消投稿' && $message->getText() !== '阿里云盘投稿';
    }

    public function execute(Message $message, Telegram $telegram, int $updateId): void
    {
        $sender = [
            'chat_id' => $message->getChat()->getId(),
            'text' => '',
        ];
        $data = Conversation::get($message->getChat()->getId(), 'contribute');
        if (count($data) > 0 && $data['status'] != 'contribute') {
            $sender['text'] .= "请先开始投稿。\n";
            $this->dispatch((new SendMessageJob($sender, null, 0))->delay(0));
        } else {
            $cvid = $data['cvid'];
            switch ($data[$cvid]['status']) {
                case 'name':
                    $data[$cvid]['name'] = $message->getText();
                    if (strlen($data[$cvid]['name']) > 64) {
                        $sender['text'] .= "名称过长，请重新输入。\n";
                        $this->dispatch((new SendMessageJob($sender, null, 0))->delay(0));
                        break;
                    }
                    if (strlen($data[$cvid]['name']) < 1) {
                        $sender['text'] .= "名称过短，请重新输入。\n";
                        $this->dispatch((new SendMessageJob($sender, null, 0))->delay(0));
                        break;
                    }
                    $data[$cvid]['status'] = 'desc';
                    Conversation::save($message->getChat()->getId(), 'contribute', $data);
                    $sender['text'] .= "请您发送关于分享文件的描述（如影片的<b>剧情梗概</b>；<b>500 字</b>以内，支持特殊格式）。\n";
                    $this->dispatch((new SendMessageJob($sender, null, 0))->delay(0));
                    break;
                case 'desc':
                    $data[$cvid]['desc'] = $message->getText();
                    if (strlen($data[$cvid]['desc']) > 512) {
                        $sender['text'] .= "描述过长，请重新输入。\n";
                        $this->dispatch((new SendMessageJob($sender, null, 0))->delay(0));
                        break;
                    }
                    if (strlen($data[$cvid]['desc']) < 1) {
                        $sender['text'] .= "描述过短，请重新输入。\n";
                        $this->dispatch((new SendMessageJob($sender, null, 0))->delay(0));
                        break;
                    }
                    $data[$cvid]['status'] = 'pic';
                    Conversation::save($message->getChat()->getId(), 'contribute', $data);
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
                        $photos && usort($photos, function (PhotoSize $a, PhotoSize $b) {
                            return $a->getFileSize() <=> $b->getFileSize();
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
                    $data[$cvid]['status'] = 'link';
                    Conversation::save($message->getChat()->getId(), 'contribute', $data);
                    $sender['text'] .= "请发送分享链接，频道接受阿里云盘、百度网盘、OneDrive 和 SharePoint 资源。请确保为永久分享，尽量不要设置提取码。\n";
                    $sender['reply_markup'] = new Keyboard([]);
                    $sender['reply_markup']->setResizeKeyboard(true);
                    $sender['reply_markup']->addRow(new KeyboardButton('取消投稿'));
                    $this->dispatch((new SendMessageJob($sender, null, 0))->delay(0));
                    break;
                case 'link':
                    $data[$cvid]['link'] = $message->getText();
                    $data[$cvid]['status'] = 'tag';
                    Conversation::save($message->getChat()->getId(), 'contribute', $data);
                    $sender['text'] .= "您将要分享的文件搜索词是？\n\n关键词越细分，越容易被查找到。关键词以 # 开头，多个关键词之间用空格分开。\n\n";
                    $sender['text'] .= "为方便群友搜索，关键词用于大家快速简洁的搜索到内容。建议比如电影：【怪奇物语】。关键词添加为：#怪奇 #物语 #怪奇物语 #4K #恐怖 #奇幻\n\n";
                    $sender['text'] .= "关键词越细分，越容易被查找到。关键词以 # 开头，多个关键词之间用空格分开。\n\n";
                    $sender['text'] .= "示例：#怪奇 #物语 #怪奇物语 #4K #恐怖 #奇幻\n";
                    $this->dispatch((new SendMessageJob($sender, null, 0))->delay(0));
                    break;
                case 'tag':
                    $data[$cvid]['tag'] = $message->getText();
                    $data[$cvid]['status'] = 'confirm';
                    Conversation::save($message->getChat()->getId(), 'contribute', $data);
                    $hasPic = $data[$cvid]['pic'] != null;
                    $sender['reply_markup'] = new Keyboard([]);
                    $sender['reply_markup']->setResizeKeyboard(true);
                    $sender['reply_markup']->addRow(new KeyboardButton('确认投稿'));
                    $sender['reply_markup']->addRow(new KeyboardButton('取消投稿'));
                    if ($hasPic) {
                        $sender['photo'] = $data[$cvid]['pic'];
                        $sender['text'] = null;
                        $sender['caption'] = "资源名称：{$data[$cvid]['name']}\n";
                        $sender['caption'] .= "资源简介：{$data[$cvid]['desc']}\n";
                        $sender['caption'] .= "链接：{$data[$cvid]['link']}\n";
                        $sender['caption'] .= "🔍 关键词：{$data[$cvid]['tag']}\n";
                        $this->dispatch((new SendPhotoJob($sender, 0))->delay(0));
                    } else {
                        $sender['text'] = "资源名称：{$data[$cvid]['name']}\n";
                        $sender['text'] .= "资源简介：{$data[$cvid]['desc']}\n";
                        $sender['text'] .= "链接：{$data[$cvid]['link']}\n";
                        $sender['text'] .= "🔍 关键词：{$data[$cvid]['tag']}\n";
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
                    $data[$cvid]['status'] = 'pending';
                    Conversation::save($message->getChat()->getId(), 'contribute', $data);
                    $sender['text'] .= "✅ 投稿成功，我们将稍后通过机器人告知您审核结果，请保持联系畅通 ~\n\n";
                    $sender['text'] .= "审核可能需要一定时间，如果您长时间未收到结果，可联系群内管理员。您现在可以开始下一个投稿。\n";
                    $sender['reply_markup'] = new Keyboard([]);
                    $sender['reply_markup']->setResizeKeyboard(true);
                    $sender['reply_markup']->addRow(new KeyboardButton('阿里云盘投稿'));
                    $this->dispatch((new SendMessageJob($sender, null, 0))->delay(0));
                    break;
                default:
                    break;
            }
        }
    }
}
