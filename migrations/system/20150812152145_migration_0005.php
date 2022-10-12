<?php

use Phinx\Migration\AbstractMigration;

/**
 * @author Ricard Derheim <rderheim@derheim-software.de>
 */
class Migration0005 extends AbstractMigration{
    /**
     * @return {void}
     */
    public function up(){
        // Update `instance` table
        $this->table('instance')
            ->addColumn('shortDescription', 'text', [
                'null' => true
            ])
            ->save();
    }

    /**
     * @return {void}
     */
    public function down(){
        // Update `instance` table
        $this->table('instance')
            ->dropColumn('shortDescription')
            ->save();
    }
}