<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('tasks', function (Blueprint $table): void {
            $table
                ->string('status')
                ->default('pending')
                ->after('description');

            $table
                ->date('due_date')
                ->nullable()
                ->after('status');
        });

        DB::table('tasks')
            ->where('is_completed', true)
            ->update([
                'status' => 'completed',
            ]);

        Schema::table('tasks', function (Blueprint $table): void {
            $table->dropIndex([
                'user_id',
                'is_completed',
            ]);

            $table->dropColumn('is_completed');

            $table->index([
                'user_id',
                'status',
            ]);

            $table->index([
                'user_id',
                'due_date',
            ]);
        });
    }

    public function down(): void
    {
        Schema::table('tasks', function (Blueprint $table): void {
            $table
                ->boolean('is_completed')
                ->default(false)
                ->after('description');
        });

        DB::table('tasks')
            ->where('status', 'completed')
            ->update([
                'is_completed' => true,
            ]);

        Schema::table('tasks', function (Blueprint $table): void {
            $table->dropIndex([
                'user_id',
                'status',
            ]);

            $table->dropIndex([
                'user_id',
                'due_date',
            ]);

            $table->dropColumn([
                'status',
                'due_date',
            ]);

            $table->index([
                'user_id',
                'is_completed',
            ]);
        });
    }
};
