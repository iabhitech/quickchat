<?php

namespace App\Controllers;

use App\Controllers\BaseController;
use App\Models\FriendModel;
use CodeIgniter\API\ResponseTrait;
use CodeIgniter\HTTP\ResponseInterface;

class Friend extends BaseController
{
    use ResponseTrait;

    /**
     * @api {get} /friends Get Friends List
     * @apiName GetFriends
     * @apiGroup Friend
     *
     * @apiHeader {String} authorization Authorization token
     * 
     * @apiSuccess {String} id UserId of the friend.
     * @apiSuccess {String} username Username of the friend.
     * @apiSuccess {String} firstname Firstname of the friend.
     * @apiSuccess {String} lastname Lastname of the friend.
     *
     * @apiSuccessExample Success-Response:
     *     HTTP/1.1 200 OK
     *     {
     *        "users": [
     *            {
     *              "id": 1,
     *              "username": "johndoe",
     *              "firstname": "John",
     *              "lastname": "Doe"
     *            },
     *            {
     *              "id": 2,
     *              "username": "janedoe",
     *              "firstname": "Bob",
     *              "lastname": "Lennon",
     *            }
     *        ]
     *      }
     * 
     * @apiSampleRequest /api/v1/friends
     */
    public function index()
    {
        $friendModel = new FriendModel();
        $friends = $friendModel->getFriends($this->auth->userid);
        return $this->respond($friends, ResponseInterface::HTTP_OK);
    }

    /**
     * @api {get} /friends Get Pending Friend Requests List
     * @apiName GetFriendRequests
     * @apiGroup Friend
     *
     * @apiHeader {String} authorization Authorization token
     * 
     * @apiSuccess {String} id UserId of the friend.
     * @apiSuccess {String} username Username of the friend.
     * @apiSuccess {String} firstname Firstname of the friend.
     * @apiSuccess {String} lastname Lastname of the friend.
     * @apiSuccess {String} status Status of the friend request.
     * @apiSuccess {String} created_at Created date of the friend request.
     *
     * @apiSuccessExample Success-Response:
     *     HTTP/1.1 200 OK
     *     {
     *        "users": [
     *            {
     *              "id": 1,
     *              "username": "johndoe",
     *              "firstname": "John",
     *              "lastname": "Doe",
     *              "status": "pending",
     *              "created_at": "2024-03-17 13:54:52"
     *            },
     *            {
     *              "id": 2,
     *              "username": "janedoe",
     *              "firstname": "Bob",
     *              "lastname": "Lennon",
     *              "status": "pending",
     *              "created_at": "2024-03-17 13:54:52"
     *            }
     *        ]
     *      }
     * 
     * @apiSampleRequest /api/v1/friends/requests
     */
    public function requests()
    {
        $friendModel = new FriendModel();
        $friends = $friendModel->select('users.id,users.username,users.firstname,users.lastname,friends.status,friends.created_at')
            ->join('users', 'users.id = friends.user_id')
            ->where('friends.friend_id', $this->auth->userid)
            ->where('friends.status', 'pending')
            ->findAll();
        return $this->respond($friends, ResponseInterface::HTTP_OK);
    }

    /**
     * @api {post} /friends/:id Add Friend
     * @apiName AddFriend
     * @apiGroup Friend
     *
     * @apiHeader {String} authorization Authorization token
     * 
     * @apiParam {Number} id User ID of the friend.
     *
     * @apiSuccess {String} message Friend request sent.
     *
     * @apiSuccessExample Success-Response:
     *     HTTP/1.1 200 OK
     *     {
     *        "message": "Friend request sent."
     *      }
     * 
     * @apiSampleRequest /api/v1/friends/:id
     */
    public function add($id)
    {
        $friendModel = new FriendModel();

        if($id == $this->auth->userid) {
            return $this->fail('You cannot add yourself as a friend.', ResponseInterface::HTTP_BAD_REQUEST);
        }

        // check if friend request already exists
        $friend = $friendModel->where('user_id', $this->auth->userid)
            ->where('friend_id', $id)
            ->first();

        if ($friend && $friend['status'] == 'pending') {
            return $this->fail('Friend request already sent.', ResponseInterface::HTTP_BAD_REQUEST);
        }

        if ($friend && $friend['status'] == 'active') {
            return $this->fail('You are already friends.', ResponseInterface::HTTP_BAD_REQUEST);
        }

        if ($friend && $friend['status'] == 'blocked') {
            return $this->fail('You are blocked by this user.', ResponseInterface::HTTP_BAD_REQUEST);
        }

        try {

            $friendModel->insert([
                'user_id' => $this->auth->userid,
                'friend_id' => $id,
                'status' => 'pending'
            ]);
        } catch (\Exception $e) {
            return $this->fail('Unable to send friend request.', ResponseInterface::HTTP_BAD_REQUEST);
        }

        return $this->respond(['message' => 'Friend request sent.'], ResponseInterface::HTTP_OK);
    }

    /**
     * @api {put} /friends/:id Accept Friend Request
     * @apiName AcceptFriendRequest
     * @apiGroup Friend
     *
     * @apiHeader {String} authorization Authorization token
     * 
     * @apiParam {Number} id User ID of the friend.
     *
     * @apiSuccess {String} message Friend request accepted.
     *
     * @apiSuccessExample Success-Response:
     *     HTTP/1.1 200 OK
     *     {
     *        "message": "Friend request accepted."
     *      }
     * 
     * @apiSampleRequest /api/v1/friends/:id
     */
    public function update($id)
    {
        $friendModel = new FriendModel();
        if($id == $this->auth->userid) {
            return $this->fail('You cannot add yourself as a friend.', ResponseInterface::HTTP_BAD_REQUEST);
        }

        // check if friend request exists
        $friend = $friendModel->where('user_id', $id)
            ->where('friend_id', $this->auth->userid)
            ->where('status', 'pending')
            ->first();
        
        if (!$friend) {
            return $this->fail('Friend request not found.', ResponseInterface::HTTP_NOT_FOUND);
        }

        $friendModel->where('user_id', $id)
            ->where('friend_id', $this->auth->userid)
            ->set(['status' => 'active'])
            ->update();
        
        // also insert a record for the other user
        $friendModel->save([
            'user_id' => $this->auth->userid,
            'friend_id' => $id,
            'status' => 'active'
        ]);
        return $this->respond(['message' => 'Friend request accepted.'], ResponseInterface::HTTP_OK);
    }

    /**
     * @api {delete} /friends/:id Remove Friend
     * @apiName RemoveFriend
     * @apiGroup Friend
     *
     * @apiHeader {String} authorization Authorization token
     * 
     * @apiParam {Number} id User ID of the friend.
     *
     * @apiSuccess {String} message Friend removed.
     *
     * @apiSuccessExample Success-Response:
     *     HTTP/1.1 200 OK
     *     {
     *        "message": "Friend removed."
     *      }
     * 
     * @apiSampleRequest /api/v1/friends/:id
     */
    public function remove($id)
    {
        $friendModel = new FriendModel();
        
        if($id == $this->auth->userid) {
            return $this->fail('You cannot remove yourself as a friend.', ResponseInterface::HTTP_BAD_REQUEST);
        }

        // check if friend request exists
        $friend = $friendModel->where('user_id', $this->auth->userid)
            ->where('friend_id', $id)
            ->orWhere('user_id', $id)
            ->first();
        
        if (!$friend) {
            return $this->fail('Friend not found.', ResponseInterface::HTTP_NOT_FOUND);
        }

        $friendModel->where('user_id', $this->auth->userid)
            ->where('friend_id', $id)
            ->orWhere('user_id', $id)
            ->delete();
        return $this->respond(['message' => 'Friend removed.'], ResponseInterface::HTTP_OK);
    }
}
