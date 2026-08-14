<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('task_boards', function (Blueprint $table) {
            $table->id();
            $table->foreignId('company_id')->constrained('companies')->onDelete('cascade');
            $table->foreignId('created_by_id')->constrained('users')->onDelete('cascade');
            $table->string('name');
            $table->text('description')->nullable();
            $table->string('color')->default('#4F46E5');
            $table->boolean('is_default')->default(false);
            $table->timestamps();
        });

        Schema::create('task_columns', function (Blueprint $table) {
            $table->id();
            $table->foreignId('task_board_id')->constrained('task_boards')->onDelete('cascade');
            $table->foreignId('company_id')->constrained('companies')->onDelete('cascade');
            $table->string('name');
            $table->string('color')->default('indigo');
            $table->integer('order')->default(0);
            $table->timestamps();
        });

        Schema::create('tasks', function (Blueprint $table) {
            $table->id();
            $table->foreignId('company_id')->constrained('companies')->onDelete('cascade');
            $table->foreignId('task_board_id')->constrained('task_boards')->onDelete('cascade');
            $table->foreignId('task_column_id')->constrained('task_columns')->onDelete('cascade');
            $table->foreignId('created_by_id')->constrained('users')->onDelete('cascade');
            $table->foreignId('assigned_to_id')->nullable()->constrained('users')->onDelete('set null');
            $table->string('title');
            $table->longText('description')->nullable();
            $table->enum('priority', ['low', 'medium', 'high', 'urgent'])->default('medium');
            $table->dateTime('due_date')->nullable();
            $table->integer('order')->default(0);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('tasks');
        Schema::dropIfExists('task_columns');
        Schema::dropIfExists('task_boards');
    }
};
