<?php

namespace Infrastructure\Persistence\Database\Migrations;

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        $sql = 'CREATE TABLE company_accounts (
                id UUID NOT NULL,
                name VARCHAR(255) NOT NULL,
                is_active BOOLEAN NOT NULL,
                version INT NOT NULL,
                created_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL,
                updated_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL,
                PRIMARY KEY(id));';
        DB::getPdo()->exec($sql);

        $sql = 'CREATE INDEX idx_company_accounts_id ON company_accounts (id)';
        DB::getPdo()->exec($sql);

        $sql = "COMMENT ON COLUMN company_accounts.created_at IS '(DC2Type:datetime_immutable)';";
        DB::getPdo()->exec($sql);

        $sql = "COMMENT ON COLUMN company_accounts.updated_at IS '(DC2Type:datetime_immutable)';";
        DB::getPdo()->exec($sql);
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        $sql = 'DROP INDEX idx_company_accounts_id';
        DB::getPdo()->exec($sql);

        $sql = 'DROP TABLE company_accounts';
        DB::getPdo()->exec($sql);
    }
};
