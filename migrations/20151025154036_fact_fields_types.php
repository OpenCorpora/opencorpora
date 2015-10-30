<?php

use Phinx\Migration\AbstractMigration;

class FactFieldsTypes extends AbstractMigration
{
    /**
     * Change Method.
     *
     * More information on this method is available here:
     * http://docs.phinx.org/en/latest/migrations.html#the-change-method
     *
     * Uncomment this method if you would like to use it.
     *
    public function change()
    {
    }
    */
    
    /**
     * Migrate Up.
     */
    public function up() {
        $fact_types = $this->table('fact_types', array('id' => 'fact_type_id', 'engine' => 'InnoDB'));
        $fact_types
            ->addColumn('tagset_id', 'integer')
            ->addColumn('fact_name', 'string', array('limit' => 50))
            ->save();

        $fact_fields = $this->table('fact_fields', array('id' => 'field_id', 'engine' => 'InnoDB'));
        $fact_fields
            ->addColumn('fact_type_id', 'integer')
            ->addColumn('required', 'boolean')
            ->addColumn('repeated', 'boolean')
            ->addColumn('field_name', 'string', array('limit' => 50))
            ->addIndex('fact_type_id')
            ->save();

        $facts = $this->table('facts', array('id' => 'fact_id', 'engine' => 'InnoDB'));
        $facts
            ->addColumn('book_id', 'integer')
            ->addColumn('user_id', 'integer')
            ->addColumn('fact_type_id', 'integer')
            ->addIndex('fact_type_id')
            ->save();

        $fact_field_values = $this->table('fact_field_values', array('id' => 'field_value_id', 'engine' => 'InnoDB'));
        $fact_field_values
            ->addColumn('fact_id', 'integer')
            ->addColumn('field_id', 'integer')
            ->addColumn('object_id', 'integer')
            ->addColumn('entity_id', 'integer')
            ->addColumn('string_value', 'string', array('limit' => 100))
            ->addIndex('fact_id')
            ->save();
        
        $this->table('ne_entities')->addColumn('mention_id', 'integer', array('default' => 0))->update();

        $ne_mentions = $this->table('ne_mentions', array('id' => 'mention_id', 'engine' => 'InnoDB'));
        $ne_mentions
            ->addColumn('object_id', 'integer')
            ->addIndex('object_id')
            ->save();

        $ne_object_types = $this->table('ne_object_types', array('id' => 'object_type_id', 'engine' => 'InnoDB'));
        $ne_object_types
            ->addColumn('tagset_id', 'integer')
            ->addColumn('object_name', 'string', array('limit' => 50))
            ->save();
    
        $ne_objects = $this->table('ne_objects', array('id' => 'object_id', 'engine' => 'InnoDB'));
        $ne_objects
            ->addColumn('object_type_id', 'integer')
            ->addColumn('book_id', 'integer')
            ->addColumn('canon_name', 'string')
            ->addColumn('wikidata_id', 'integer')
            ->save();
    }

    /**
     * Migrate Down.
     */
    public function down() {
        $this->dropTable('fact_types');
        $this->dropTable('fact_fields');
        $this->dropTable('facts');
        $this->dropTable('fact_field_values');
        $this->table('ne_entities')->removeColumn('mention_id')->update();
        $this->dropTable('ne_mentions');
        $this->dropTable('ne_object_types');
        $this->dropTable('ne_objects');
    }
}
