<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('documents', function (Blueprint $table) {
            $table->tsvector('search_vector')->nullable()->after('metadata');
        });

        DB::statement('CREATE INDEX idx_documents_search_vector ON documents USING GIN(search_vector)');

        DB::statement("
            CREATE OR REPLACE FUNCTION documents_search_vector_update() RETURNS trigger AS \$\$
            DECLARE
                lang regconfig;
            BEGIN
                -- use the language config if it exists in pg_ts_config, otherwise fall back to simple
                lang := COALESCE(
                    (SELECT cfgname::regconfig FROM pg_ts_config WHERE cfgname = NEW.language LIMIT 1),
                    'simple'::regconfig
                );

                NEW.search_vector :=
                    setweight(to_tsvector(lang, coalesce(NEW.title, '')), 'A') ||
                    setweight(to_tsvector(lang, coalesce(NEW.content, '')), 'B') ||
                    setweight(to_tsvector('simple', coalesce(NEW.metadata->>'department', '')), 'C');
                RETURN NEW;
            END;
            \$\$ LANGUAGE plpgsql
        ");

        DB::statement('
            CREATE TRIGGER documents_search_vector_trigger
                BEFORE INSERT OR UPDATE ON documents
                FOR EACH ROW EXECUTE FUNCTION documents_search_vector_update()
        ');
    }

    public function down(): void
    {
        DB::statement('DROP TRIGGER IF EXISTS documents_search_vector_trigger ON documents');
        DB::statement('DROP FUNCTION IF EXISTS documents_search_vector_update');

        Schema::table('documents', function (Blueprint $table) {
            $table->dropIndex('idx_documents_search_vector');
            $table->dropColumn('search_vector');
        });
    }
};
