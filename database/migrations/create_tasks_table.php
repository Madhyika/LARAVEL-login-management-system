<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
public function up()
{
    Schema::create('tasks', function (Blueprint $table) {
        $table->id();
        $table->string('title');
        $table->text('content')->nullable();
        $table->foreignId('user_id')->constrained(); // Assuming you want to link tasks to users
        $table->foreignId('parent_id')->nullable()->constrained('tasks'); // Parent-child relationship for tasks
        $table->timestamps();
    });
    
}
public function down()
{
    Schema::table('tasks', function (Blueprint $table) {
        $table->dropColumn('content');
    });
}
};