<?php

use Phinx\Migration\AbstractMigration;

class InitialDb extends AbstractMigration
{
    public function up() {
        $this->execute(file_get_contents(__DIR__.'/initial_schema.sql'));
    }

    public function down() {
        throw new Exception("Not implemented");
    }
}
