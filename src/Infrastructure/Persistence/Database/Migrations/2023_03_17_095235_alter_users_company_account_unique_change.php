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
        $sql = 'DROP INDEX UNIQ_user__company_account';
        DB::getPdo()->exec($sql);

        $sql = 'CREATE UNIQUE INDEX UNIQ_user__company_account ON users__company_accounts (user__id, company_account__id, role);';
        DB::getPdo()->exec($sql);

    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        $sql = 'DROP INDEX UNIQ_user__company_account';
        DB::getPdo()->exec($sql);

        $sql = 'CREATE UNIQUE INDEX UNIQ_user__company_account ON users__company_accounts (user__id, company_account__id);';
        DB::getPdo()->exec($sql);
    }
};
