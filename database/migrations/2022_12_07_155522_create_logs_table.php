<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up()
    {
        Schema::create('logs', function (Blueprint $table) {
            $table->unsignedBigInteger('id')->autoIncrement()->comment('主键');
            $table->string('channel', 64)->default('')->comment('日志通道');
            $table->string('level', 64)->default('')->comment('日志级别');
            $table->longText('message')->default('')->comment('日志内容');
            $table->longText('context')->default('')->comment('日志上下文');
            $table->longText('extra')->default('')->comment('日志额外信息');
            $table->timestamp('created_at')->useCurrent()->comment('创建时间');
            $table->comment('日志表');
            $table->engine = 'InnoDB';
            $table->charset = 'utf8mb4';
            $table->collation = 'utf8mb4_general_ci';
        });
    }

    public function down()
    {
        Schema::dropIfExists('logs');
    }
};
