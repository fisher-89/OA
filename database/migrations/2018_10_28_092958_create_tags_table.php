<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateTagsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('tags', function (Blueprint $table) {
            $table->increments('id');
            $table->char('type', 10)->comment('标签类型staff, shops');
            $table->char('name', 10)->comment('标签名称');
            $table->integer('tag_category_id')->comment('标签类别');
            $table->char('description', 50)->default()->comment('描述');
            $table->integer('weight')->default(0)->comment('权重,排序用');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('tags');
    }
}
