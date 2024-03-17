<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class Story extends Migration
{
    public function up()
    {
        $this->forge->addField([
            'id' => [
                'type' => 'INT',
                'unsigned' => true,
                'auto_increment' => true,
            ],
            'user_id' => [
                'type' => 'INT',
                'unsigned' => true,
            ],
            'body' => [
                'type' => 'VARCHAR',
                'constraint' => '1024',
            ],
            'image' => [
                'type' => 'VARCHAR',
                'constraint' => '255',
                'null' => true,
            ],
            'deleted_at' => [
                'type' => 'TIMESTAMP',
            ],
            'created_at timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP',
            'updated_at timestamp ON UPDATE CURRENT_TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP',
        ]);
        $this->forge->addKey('id', true);
        // $this->forge->addForeignKey('user_id', 'users', 'id');
        $this->forge->addKey('user_id');
        $this->forge->createTable('stories');
    }

    public function down()
    {
        $this->forge->dropTable('stories');
    }
}
