<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up()
    {
        Schema::create('chat_history_of_bind_channel', function (Blueprint $table) {
            $table->unsignedBigInteger('id')->autoIncrement()->comment('主键');
            $table->bigInteger('channel_id')->comment('频道ID');
            $table->bigInteger('message_id')->comment('消息ID');
            $table->text('content')->comment('内容');
            $table->timestamp('created_at')->useCurrent()->comment('创建时间');
            $table->timestamp('updated_at')->useCurrent()->useCurrentOnUpdate()->comment('更新时间');
            $table->timestamp('deleted_at')->nullable()->default(null)->comment('删除时间');
            $table->comment('绑定频道聊天记录表');
            $table->index('channel_id');

            $table->engine = 'InnoDB';
            $table->charset = 'utf8mb4';
            $table->collation = 'utf8mb4_general_ci';
        });
    }

    public function down()
    {
        Schema::dropIfExists('chat_history_of_bind_channel');
    }
};
