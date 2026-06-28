<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('knowledge_base_permissions', function (Blueprint $table) {
            $table->uuid('id')->primary()->default(DB::raw('gen_random_uuid()'));
            $table->foreignUuid('knowledge_base_id')->constrained('knowledge_bases')->cascadeOnDelete();
            $table->foreignUuid('user_id')->constrained('users')->cascadeOnDelete();
            $table->boolean('can_read')->default(true);
            $table->boolean('can_write')->default(false);
            $table->foreignUuid('granted_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestampTz('granted_at')->useCurrent();
            $table->timestampTz('updated_at')->nullable();
            $table->unique(['knowledge_base_id', 'user_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('knowledge_base_permissions');
    }
};
