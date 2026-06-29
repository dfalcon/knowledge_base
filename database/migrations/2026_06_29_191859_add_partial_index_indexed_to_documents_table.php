<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::statement("
            CREATE INDEX idx_documents_kb_indexed
            ON documents (knowledge_base_id, created_at DESC)
            WHERE status = 'indexed'
        ");
    }

    public function down(): void
    {
        DB::statement('DROP INDEX IF EXISTS idx_documents_kb_indexed');
    }
};
