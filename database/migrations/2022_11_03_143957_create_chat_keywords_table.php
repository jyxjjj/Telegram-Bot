<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up()
    {
        Schema::create('chat_keywords', function (Blueprint $table) {
            $table->unsignedBigInteger('id')->autoIncrement()->comment('主键');
            $table->BigInteger('chat_id')->default(0)->comment('聊天ID');
            $table->string('keyword', 128)->default('')->comment('关键字');
            $table->string('target', 16)->default('TEXT')->comment('检测目标');
            $table->string('operation', 16)->default('REPLY')->comment('执行操作');
            $table->longText('data')->default('{}')->comment('操作数据');
            $table->boolean('enable')->default(1)->comment('启用');
            $table->timestamp('created_at')->useCurrent()->comment('创建时间');
            $table->timestamp('updated_at')->useCurrent()->useCurrentOnUpdate()->comment('更新时间');
            $table->timestamp('deleted_at')->nullable()->comment('删除时间');
            $table->comment('聊天关键字表');
            $table->index(['chat_id',], 'chat_id');
            $table->index(['target',], 'target');
            $table->index(['enable',], 'enable');
            $table->engine = 'InnoDB';
            $table->charset = 'utf8mb4';
            $table->collation = 'utf8mb4_general_ci';
        });
    }

    public function down()
    {
        Schema::dropIfExists('chat_keywords');
    }
};
