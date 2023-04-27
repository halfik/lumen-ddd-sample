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
        $sql = 'CREATE TABLE workflows (
                id UUID NOT NULL,
                name VARCHAR(255) NOT NULL,
                company_account__id UUID NOT NULL,
                estimated_revenue INT NOT NULL,
                actual_revenue INT NOT NULL,
                version INT NOT NULL,
                created_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL,
                updated_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL,
                PRIMARY KEY(id)); ';
        DB::getPdo()->exec($sql);

        $sql = 'CREATE INDEX idx_workflows_id ON workflows (id)';
        DB::getPdo()->exec($sql);

        $sql = 'CREATE INDEX idx_workflows_company_account__id ON workflows (company_account__id)';
        DB::getPdo()->exec($sql);

        $sql = "COMMENT ON COLUMN workflows.created_at IS '(DC2Type:datetime_immutable)';";
        DB::getPdo()->exec($sql);

        $sql = "COMMENT ON COLUMN workflows.updated_at IS '(DC2Type:datetime_immutable)';";
        DB::getPdo()->exec($sql);

        $sql = 'ALTER TABLE workflows ADD CONSTRAINT company_account_fk
                    FOREIGN KEY (company_account__id)
                    REFERENCES company_accounts (id)
                    ON DELETE CASCADE';
        DB::getPdo()->exec($sql);
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        $sql = 'DROP INDEX idx_workflows_id';
        DB::getPdo()->exec($sql);

        $sql = 'DROP INDEX idx_workflows_company_account__id';
        DB::getPdo()->exec($sql);

        $sql = 'DROP TABLE workflows';
        DB::getPdo()->exec($sql);
    }
};
