<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('documents', function (Blueprint $table) {
            $table->uuid('id')->primary()->default(DB::raw('gen_random_uuid()'));
            $table->foreignUuid('knowledge_base_id')->constrained('knowledge_bases')->cascadeOnDelete();
            $table->foreignUuid('uploaded_by')->nullable()->constrained('users')->nullOnDelete();
            $table->string('title', 500);
            $table->text('content')->nullable();
            $table->string('file_name');
            $table->string('file_path', 1000);
            $table->string('mime_type', 100);
            $table->unsignedBigInteger('file_size_bytes');
            $table->string('status', 50)->default('pending'); // pending|processing|indexed|failed
            $table->jsonb('metadata')->default('{}');
            $table->timestampTz('indexed_at')->nullable();
            $table->text('error_message')->nullable();
            $table->timestampsTz();
        });

        DB::statement('CREATE INDEX idx_documents_metadata ON documents USING GIN(metadata)');
    }

    public function down(): void
    {
        Schema::dropIfExists('documents');
    }
};
