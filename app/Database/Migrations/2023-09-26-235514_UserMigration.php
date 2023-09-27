<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class UserMigration extends Migration
{
    public function up()
    {
        $this->forge->addField([
            'user_id' => [
                'type'           => 'INT',
                'constraint'     => 5,
                'unsigned'       => true,
                'auto_increment' => true,
            ],
            'user_name' => [
                'type'       => 'VARCHAR',
                'constraint' => 50,
            ],
            'user_email' => [
                'type'       => 'VARCHAR',
                'constraint' => 255,
            ],
            'user_created' => [
                'type' => 'DATETIME',
            ],
            'group_id' => [
                'type'       => 'INT',
                'constraint' => 5,
                'unsigned'   => true,
            ],
            'situation_id' => [
                'type'       => 'INT',
                'constraint' => 5,
                'unsigned'   => true,
            ],
            'client_id' => [
                'type'       => 'INT',
                'constraint' => 5,
            ],
            'user_doc' => [
                'type'       => 'VARCHAR',
                'constraint' => 15,
            ],
            'user_password' => [
                'type'       => 'VARCHAR',
                'constraint' => 255,
            ],
        ]);

        $this->forge->addKey('user_id', true);
        $this->forge->createTable('user');
    }

    public function down()
    {
        $this->forge->dropTable('user');
    }
}
