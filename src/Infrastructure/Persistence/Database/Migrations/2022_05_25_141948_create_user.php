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
        $sql = 'CREATE TABLE users (
                id UUID NOT NULL,
                password VARCHAR(100) NOT NULL,
                email VARCHAR(150) NOT NULL,
                email_verified_at TIMESTAMP(0) WITHOUT TIME ZONE DEFAULT NULL,
                title VARCHAR(255) DEFAULT NULL,
                first_name VARCHAR(100) DEFAULT NULL,
                last_name VARCHAR(100) DEFAULT NULL,
                middle_name VARCHAR(100) DEFAULT NULL,
                display_name VARCHAR(255) DEFAULT NULL,
                address TEXT DEFAULT NULL,
                country_code VARCHAR(3) DEFAULT NULL,
                phone_number VARCHAR(30) DEFAULT NULL,
                version INT NOT NULL,
                created_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL,
                updated_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL,
                PRIMARY KEY(id)); ';
        DB::getPdo()->exec($sql);

        $sql = 'CREATE INDEX idx_users_id ON users (id)';
        DB::getPdo()->exec($sql);

        $sql = "COMMENT ON COLUMN users.email_verified_at IS '(DC2Type:datetime_immutable)';";
        DB::getPdo()->exec($sql);

        $sql = "COMMENT ON COLUMN users.created_at IS '(DC2Type:datetime_immutable)';";
        DB::getPdo()->exec($sql);

        $sql = "COMMENT ON COLUMN users.updated_at IS '(DC2Type:datetime_immutable)';";
        DB::getPdo()->exec($sql);
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        $sql = 'DROP INDEX idx_users_id';
        DB::getPdo()->exec($sql);

        $sql = 'DROP TABLE users';
        DB::getPdo()->exec($sql);
    }
};
