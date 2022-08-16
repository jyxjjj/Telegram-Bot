<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up()
    {
        Schema::create('chat_blacklists', function (Blueprint $table) {
            $table->unsignedBigInteger('id')->autoIncrement()->comment('主键');
            $table->BigInteger('chat_id')->default(0)->comment('聊天ID');
            $table->BigInteger('user_id')->default(0)->comment('用户ID');
            $table->enum('operation', ['DELETE', 'BAN', 'RESTRICT'])->default('DELETE')->comment('执行操作');
            $table->timestamp('created_at')->useCurrent()->comment('创建时间');
            $table->timestamp('updated_at')->useCurrent()->comment('更新时间');
            $table->timestamp('deleted_at')->nullable()->comment('删除时间');
            $table->comment('聊天用户黑名单表');
            $table->index(['chat_id', 'user_id'], 'chat_user');
            $table->engine = 'InnoDB';
            $table->charset = 'utf8mb4';
            $table->collation = 'utf8mb4_general_ci';
        });
    }

    public function down()
    {
        Schema::dropIfExists('chat_blacklists');
    }
};
