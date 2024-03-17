<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class Friend extends Migration
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
            'friend_id' => [
                'type' => 'INT',
                'unsigned' => true,
            ],
            'status' => [
                'type' => 'ENUM',
                'constraint' => ['pending', 'active', 'blocked', 'restricted', 'deleted'],
                'default' => 'pending',
            ],
            'created_at timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP',
            'updated_at timestamp ON UPDATE CURRENT_TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP',
        ]);
        $this->forge->addKey('id', true);
        // $this->forge->addForeignKey('users_id', 'users', 'id');
        // $this->forge->addForeignKey('friend_id', 'users', 'id');
        $this->forge->addUniqueKey(['user_id', 'friend_id'], 'uk_friends');
        $this->forge->addKey('status');
        $this->forge->createTable('friends');
    }

    public function down()
    {
        $this->forge->dropTable('friends');
    }
}
