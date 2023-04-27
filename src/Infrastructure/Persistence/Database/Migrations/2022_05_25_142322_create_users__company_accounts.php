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
        $sql = 'CREATE TABLE users__company_accounts (
                id UUID NOT NULL,
                user__id UUID NOT NULL,
                company_account__id UUID NOT NULL,
                role VARCHAR(30) NOT NULL,
                status VARCHAR(50) NOT NULL,
                version INT NOT NULL,
                created_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL,
                updated_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL,
                PRIMARY KEY(id));';
        DB::getPdo()->exec($sql);


        $sql = 'CREATE INDEX idx_users__company_accounts_id ON users__company_accounts (id)';
        DB::getPdo()->exec($sql);

        $sql = 'CREATE UNIQUE INDEX UNIQ_user__company_account ON users__company_accounts (user__id, company_account__id);';
        DB::getPdo()->exec($sql);

        $sql = "COMMENT ON COLUMN users__company_accounts.created_at IS '(DC2Type:datetime_immutable)';";
        DB::getPdo()->exec($sql);

        $sql = "COMMENT ON COLUMN users__company_accounts.updated_at IS '(DC2Type:datetime_immutable)';";
        DB::getPdo()->exec($sql);

        $sql = 'ALTER TABLE "public"."users__company_accounts" ADD CONSTRAINT "user_fk"
                FOREIGN KEY ("user__id") REFERENCES "public"."users" ("id")
                    ON DELETE CASCADE ON UPDATE NO ACTION;';
        DB::getPdo()->exec($sql);

        $sql = 'ALTER TABLE "public"."users__company_accounts" ADD CONSTRAINT "company_account_fk"
                FOREIGN KEY ("company_account__id") REFERENCES "public"."company_accounts" ("id")
                    ON DELETE CASCADE ON UPDATE NO ACTION;';
        DB::getPdo()->exec($sql);


    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        $sql = 'DROP INDEX idx_users__company_accounts_id';
        DB::getPdo()->exec($sql);

        $sql = 'DROP INDEX UNIQ_user__company_account';
        DB::getPdo()->exec($sql);

        $sql = 'DROP TABLE users__company_accounts';
        DB::getPdo()->exec($sql);
    }
};
