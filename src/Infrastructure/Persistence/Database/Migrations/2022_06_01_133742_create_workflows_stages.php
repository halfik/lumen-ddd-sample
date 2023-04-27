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
        $sql = 'CREATE TABLE workflows_stages (
            id UUID NOT NULL,
            workflow__id UUID NOT NULL,
            type TEXT NOT NULL,
            name VARCHAR(255) NOT NULL,
            position SMALLINT NOT NULL DEFAULT 0,
            estimated_revenue INT NOT NULL,
            actual_revenue INT NOT NULL,
            version INT NOT NULL,
            created_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL,
            updated_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL,
            PRIMARY KEY(id));     ';
        DB::getPdo()->exec($sql);


        $sql = 'CREATE INDEX idx_workflows_stages_id ON workflows_stages (id)';
        DB::getPdo()->exec($sql);

        $sql = 'CREATE INDEX idx_workflows_stages_workflow__id ON workflows_stages (workflow__id)';
        DB::getPdo()->exec($sql);

        $sql = "COMMENT ON COLUMN users__company_accounts.created_at IS '(DC2Type:datetime_immutable)';";
        DB::getPdo()->exec($sql);

        $sql = "COMMENT ON COLUMN users__company_accounts.updated_at IS '(DC2Type:datetime_immutable)';";
        DB::getPdo()->exec($sql);

        $sql = 'ALTER TABLE workflows_stages ADD CONSTRAINT workflow_fk
                    FOREIGN KEY (workflow__id)
                    REFERENCES workflows (id)
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
        $sql = 'DROP INDEX idx_workflows_stages_id';
        DB::getPdo()->exec($sql);

        $sql = 'DROP INDEX idx_workflows_stages_workflow__id';
        DB::getPdo()->exec($sql);

        $sql = 'DROP TABLE workflows_stages';
        DB::getPdo()->exec($sql);
    }
};
