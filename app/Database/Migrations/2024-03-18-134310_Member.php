<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class Member extends Migration
{
    public function up()
    {
        $fields = [
            'id' => [
                'type' => 'INT',
                'unsigned' => true,
                'auto_increment' => true,
            ],
            'room_id' => [
                'type' => 'VARCHAR',
                'constraint' => 64,
            ],
            'user_id' => [
                'type' => 'VARCHAR',
                'constraint' => 255,
            ],
            'status' => [
                'type' => 'ENUM',
                'constraint' => ['pending', 'active', 'deleted', 'banned'],
                'default' => 'active',
            ],
            'created_by' => [
                'type' => 'INT',
                'unsigned' => true,
            ],
            'created_at timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP',
            'updated_at timestamp ON UPDATE CURRENT_TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP',
        ];

        $this->forge->addField($fields);
        $this->forge->addKey('id', true);
        $this->forge->addKey('room_id');
        $this->forge->addKey('user_id');
        $this->forge->createTable('members');
    }

    public function down()
    {
        $this->forge->dropTable('members');
    }
}
