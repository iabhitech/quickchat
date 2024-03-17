<?php

namespace App\Models;

use CodeIgniter\Model;

class FriendModel extends Model
{
    protected $table            = 'friends';
    protected $primaryKey       = 'id';
    protected $useAutoIncrement = true;
    protected $returnType       = 'array';
    protected $useSoftDeletes   = false;
    protected $protectFields    = true;
    protected $allowedFields    = ['user_id', 'friend_id', 'status'];

    protected bool $allowEmptyInserts = false;

    // Dates
    protected $useTimestamps = false;
    protected $dateFormat    = 'datetime';
    protected $createdField  = 'created_at';
    protected $updatedField  = 'updated_at';
    protected $deletedField  = 'deleted_at';

    // Validation
    protected $validationRules      = [];
    protected $validationMessages   = [];
    protected $skipValidation       = false;
    protected $cleanValidationRules = true;

    // Callbacks
    protected $allowCallbacks = true;
    protected $beforeInsert   = [];
    protected $afterInsert    = [];
    protected $beforeUpdate   = [];
    protected $afterUpdate    = [];
    protected $beforeFind     = [];
    protected $afterFind      = [];
    protected $beforeDelete   = [];
    protected $afterDelete    = [];

    public function getFriends($userId)
    {
        return $this->select('users.id, users.username, users.firstname, users.lastname, users.email, users.mobile, users.status')
            ->join('users', 'users.id = friends.friend_id')
            ->where('friends.user_id', $userId)
            ->where('friends.status', 'active')
            ->orderBy('friends.created_at', 'DESC')
            ->paginate();
    }
}
