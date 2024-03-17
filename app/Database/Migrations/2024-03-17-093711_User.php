<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class User extends Migration
{
    public function up()
    {
        $this->forge->addField([
            'id' => [
                'type' => 'BIGINT',
                'unsigned' => true,
                'auto_increment' => true
            ],
            'username' => [
                'type' => 'VARCHAR',
                'constraint' => '64',
                'unique' => true,
                'null' => true,
            ],
            'status' => [
                'type' => 'ENUM',
                'constraint' => ['active', 'inactive', 'blocked', 'deleted'],
                'default' => 'active',
            ],
            'firstname' => [
                'type' => 'VARCHAR',
                'constraint' => '127',
            ],
            'lastname' => [
                'type' => 'VARCHAR',
                'constraint' => '127',
            ],
            'dob' => [
                'type' => 'DATE',
                'null' => true,
            ],
            'address' => [
                'type' => 'VARCHAR',
                'constraint' => '255',
                'null' => true,
            ],
            'city' => [
                'type' => 'VARCHAR',
                'constraint' => '127',
                'null' => true,
            ],
            'state' => [
                'type' => 'VARCHAR',
                'constraint' => '127',
                'null' => true,
            ],
            'country' => [
                'type' => 'VARCHAR',
                'constraint' => '127',
                'null' => true,
            ],
            'zip' => [
                'type' => 'VARCHAR',
                'constraint' => '16',
                'null' => true,
            ],
            'latitude' => [
                'type' => 'DOUBLE',
                'null' => true,
            ],
            'longitude' => [
                'type' => 'DOUBLE',
                'null' => true,
            ],
            'mobile' => [
                'type' => 'VARCHAR',
                'unique' => true,
                'constraint' => '16',
                'null'    => true,
            ],
            'avatar' => [
                'type' => 'VARCHAR',
                'constraint' => '255',
                'null' => true,
            ],
            'email' => [
                'type' => 'VARCHAR',
                'unique' => true,
                'constraint' => '255',
                'null'     => true,
            ],
            'password' => [
                'type' => 'VARCHAR',
                'constraint' => '255',
            ],
            'created_at timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP',
            'updated_at timestamp ON UPDATE CURRENT_TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP',
        ]);
        $this->forge->addPrimaryKey('id');
        $this->forge->addKey('status');
        $this->forge->createTable('users');
    }

    public function down()
    {
        $this->forge->dropTable('users');
    }
}
