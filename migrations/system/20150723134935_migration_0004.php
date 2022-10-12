<?php

use Phinx\Migration\AbstractMigration;

/**
 * @author Ricard Derheim <rderheim@derheim-software.de>
 */
class Migration0004 extends AbstractMigration{
    /**
     * @return {void}
     */
    public function up(){
        // Update `instance` table
        $this->table('instance')
            ->addColumn('messageAdministration', 'boolean', [
                'null' => false,
                'default' => false
            ])
            ->save();
    }

    /**
     * @return {void}
     */
    public function down(){
        // Update `instance` table
        $this->table('instance')
            ->dropColumn('messageAdministration')
            ->save();
    }
}