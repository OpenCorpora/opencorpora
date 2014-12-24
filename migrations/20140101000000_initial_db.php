<?php

use Phinx\Migration\AbstractMigration;

class InitialMigration extends AbstractMigration
{
    public function up() {
        $this->execute(file_get_contents('initial_schema.sql'));
    }

    public function down() {
        throw new Exception("Not implemented");
    }
}
