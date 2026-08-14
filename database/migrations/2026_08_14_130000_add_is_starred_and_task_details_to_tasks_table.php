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
        Schema::table('tasks', function (Blueprint $table) {
            if (!Schema::hasColumn('tasks', 'is_starred')) {
                $table->boolean('is_starred')->default(false)->after('order');
            }
            if (!Schema::hasColumn('tasks', 'story_points')) {
                $table->string('story_points')->nullable()->after('priority');
            }
            if (!Schema::hasColumn('tasks', 'time_tracked_minutes')) {
                $table->integer('time_tracked_minutes')->default(0)->after('story_points');
            }
            if (!Schema::hasColumn('tasks', 'checklists')) {
                $table->json('checklists')->nullable()->after('tags');
            }
            if (!Schema::hasColumn('tasks', 'subtasks')) {
                $table->json('subtasks')->nullable()->after('checklists');
            }
        });

        if (!Schema::hasTable('task_comments')) {
            Schema::create('task_comments', function (Blueprint $table) {
                $table->id();
                $table->foreignId('task_id')->constrained('tasks')->onDelete('cascade');
                $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
                $table->text('comment');
                $table->timestamps();
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('task_comments');
        Schema::table('tasks', function (Blueprint $table) {
            $table->dropColumn(['is_starred', 'story_points', 'time_tracked_minutes', 'checklists', 'subtasks']);
        });
    }
};
