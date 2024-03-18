<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class Room extends Migration
{
    public function up()
    {
        $fields = [
            'id' => [
                'type' => 'INT',
                'unsigned' => true,
                'auto_increment' => true,
            ],
            'name' => [
                'type' => 'VARCHAR',
                'constraint' => 64,
            ],
            'description' => [
                'type' => 'TEXT',
                'null' => true,
            ],
            'thumbnail' => [
                'type' => 'VARCHAR',
                'constraint' => 255,
                'null' => true,
            ],
            'status' => [
                'type' => 'ENUM',
                'constraint' => ['active', 'inactive', 'deleted', 'banned'],
                'default' => 'active',
            ],
            'created_by' => [
                'type' => 'INT',
                'unsigned' => true,
            ],
            'deleted_at' => [
                'type' => 'TIMESTAMP',
            ],
            'created_at timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP',
            'updated_at timestamp ON UPDATE CURRENT_TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP',
        ];

        $this->forge->addField($fields);
        $this->forge->addKey('id', true);
        $this->forge->addKey(['status', 'created_by']);
        $this->forge->createTable('rooms');
    }

    public function down()
    {
        $this->forge->dropTable('rooms');
    }
}
