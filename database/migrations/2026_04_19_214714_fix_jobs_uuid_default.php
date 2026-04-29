<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // 1. Créer la fonction UUID maison si elle n'existe pas
        DB::statement(<<<'SQL'
            CREATE OR REPLACE FUNCTION app_uuid_v4() RETURNS uuid AS $$
            DECLARE
                b bytea;
            BEGIN
                b := decode(md5(random()::text || clock_timestamp()::text), 'hex');
                b := set_byte(b, 6, (get_byte(b, 6) & 15) | 64);
                b := set_byte(b, 8, (get_byte(b, 8) & 63) | 128);
                RETURN encode(b, 'hex')::uuid;
            END;
            $$ LANGUAGE plpgsql VOLATILE;
        SQL);

        // 2. Ajouter le DEFAULT sur la colonne id de jobs
        DB::statement('ALTER TABLE jobs ALTER COLUMN id SET DEFAULT app_uuid_v4();');
    }

    public function down(): void
    {
        DB::statement('ALTER TABLE jobs ALTER COLUMN id DROP DEFAULT;');
    }
};
