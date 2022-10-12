<?php

use Phinx\Migration\AbstractMigration;

/**
 * @author Ricard Derheim <rderheim@derheim-software.de>
 */
class Migration0003 extends AbstractMigration{
    /**
     * @return {void}
     */
    public function up(){
        // Update `instance` table
        $this->table('instance')
            ->addColumn('customerNumber', 'string', [
                'null' => true,
                'limit' => 100,
                'after' => 'isTemplate'
            ])
            ->addColumn('usersCountByContract', 'integer', [
                'default' => '0'
            ])
            ->save();
    }

    /**
     * @return {void}
     */
    public function down(){
        // Update `instance` table
        $this->table('instance')
            ->dropColumn('customerNumber')
            ->dropColumn('usersCountByContract')
            ->save();
    }
}