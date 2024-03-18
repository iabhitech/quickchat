<?php

namespace App\Controllers;

use App\Controllers\BaseController;
use App\Models\MemberModel;
use App\Models\RoomModel;
use CodeIgniter\API\ResponseTrait;
use CodeIgniter\HTTP\ResponseInterface;

class Room extends BaseController
{
    use ResponseTrait;

    /**
     * @api {get} /rooms Get Rooms List
     * @apiName GetRooms
     * @apiGroup Room
     *
     * @apiHeader {String} authorization Authorization token
     * 
     * @apiSuccess {String} id Room Id.
     * @apiSuccess {String} name Name of the Room.
     * @apiSuccess {String} description Description of the room.
     * @apiSuccess {String} thumbnail Thumbnail of the room.
     * @apiSuccess {String} status Status of the room.
     * @apiSuccess {String} created_by Room's owner id.
     * @apiSuccess {String} created_at Created date of the room.
     * @apiSuccess {String} updated_at Updated date of the room.
     *
     * @apiSuccessExample Success-Response:
     *     HTTP/1.1 200 OK
     *     {
     *        "rooms": [
     *            {
     *              "id": 1,
     *              "name": "Room 1",
     *              "description": "This is a test room",
     *              "thumbnail": "http://example.com/image.jpg",
     *              "status": "active",
     *              "created_by": 1,
     *              "created_at": "2024-03-17 13:54:52",
     *              "updated_at": "2024-03-17 13:54:52"
     *            }
     *        ]
     *      }
     * 
     * @apiSampleRequest /api/v1/rooms
     */
    public function index()
    {
        // get all the rooms created by logged in user
        $roomModel = new RoomModel();
        $rooms = $roomModel->where('created_by', $this->auth->userid)->where('status != ', 'deleted')->findAll();

        return $this->respond($rooms, ResponseInterface::HTTP_OK);
    }

    /**
     * @api {get} /rooms/:id Get Room
     * @apiName GetRoom
     * @apiGroup Room
     * 
     * @apiHeader {String} authorization Authorization token
     * 
     * @apiParam {Number} id Room's unique ID.
     * 
     * @apiSuccess {String} id Room Id.
     * @apiSuccess {String} name Name of the Room.
     * @apiSuccess {String} description Description of the room.
     * @apiSuccess {String} thumbnail Thumbnail of the room.
     * @apiSuccess {String} status Status of the room.
     * @apiSuccess {String} created_by Room's owner id.
     * @apiSuccess {String} created_at Created date of the room.
     * @apiSuccess {String} updated_at Updated date of the room.
     * 
     * @apiSuccessExample Success-Response:
     *    HTTP/1.1 200 OK
     *   {
     *      "id": 1,
     *      "name": "Room 1",
     *      "description": "This is a test room",
     *      "thumbnail": "http://example.com/image.jpg",
     *      "status": "active",
     *      "created_by": 1,
     *      "created_at": "2024-03-17 13:54:52",
     *      "updated_at": "2024-03-17 13:54:52"
     *    }
     * 
     * @apiError RoomNotFound The id of the Room was not found.
     * 
     * @apiErrorExample Error-Response:
     *    HTTP/1.1 404 Not Found
     *  {
     *   "error": "Room not found"
     * }
     * 
     * @apiSampleRequest /api/v1/rooms/:id
     * 
     *
     */
    public function get($id)
    {
        $roomModel = new RoomModel();
        $room = $roomModel->find($id);

        return $this->respond($room, ResponseInterface::HTTP_OK);
    }

    /**
     * @api {post} /rooms Create Room
     * @apiName CreateRoom
     * @apiGroup Room
     * 
     * @apiHeader {String} authorization Authorization token
     * 
     * @apiBody {String} name Name of the Room.
     * @apiBody {String} description Description of the room.
     * @apiBody {File} thumbnail Thumbnail of the room.
     * 
     * @apiSuccess {String} message Success message.
     * 
     * @apiSuccessExample Success-Response:
     *    HTTP/1.1 200 OK
     *   {
     *      "message": "Room is created successfully."
     *    }
     * 
     * @apiError InvalidRequest The request is invalid.
     * 
     * @apiErrorExample Error-Response:
     *    HTTP/1.1 409 Bad Request
     *  {
     *   "errors": {
     *      "name": "The name field is required."
     *    }
     *   "message": "Invalid Inputs"
     * }
     * 
     * @apiSampleRequest /api/v1/rooms
     * 
     *
     */
    public function create()
    {
        // validate request
        $rules = [
            'name' => 'required|min_length[3]|max_length[255]',
            // 'description' => 'required|min_length[3]',
            // 'thumbnail' => 'uploaded[thumbnail]|max_size[thumbnail,1024]|is_image[thumbnail]',
        ];

        if (!$this->validate($rules)) {
            return $this->fail($this->validator->getErrors(), ResponseInterface::HTTP_BAD_REQUEST);
        }

        // upload image to server
        $file = $this->request->getFile('thumbnail');
        if ($file && $file->isValid() && !$file->hasMoved()) {
            mkdir(WRITEPATH . 'uploads/rooms/', 0755, true); // create directory if not exists
            $newName = $file->getRandomName();
            $file->move(WRITEPATH . 'uploads/rooms/', $newName);
            $image = 'rooms/' . $newName;
        } else {
            $image = NULL;
        }

        $roomModel = new RoomModel();
        $roomModel->insert([
            'name' => $this->request->getVar('name'),
            'description' => $this->request->getVar('description'),
            'thumbnail' => $image,
            'status' => 'active', // default status is 'active'
            'created_by' => $this->auth->userid,
        ]);
        return $this->respond(['message' => 'Room is created successfully.'], ResponseInterface::HTTP_OK);
    }

    /**
     * @api {put} /rooms/:id Update Room
     * @apiName UpdateRoom
     * @apiGroup Room
     * 
     * @apiHeader {String} authorization Authorization token
     * 
     * @apiParam {Number} id Room's unique ID.
     * 
     * @apiBody {String} name Name of the Room.
     * @apiBody {String} description Description of the room.
     * @apiBody {File} thumbnail Thumbnail of the room.
     * 
     * @apiSuccess {String} message Success message.
     * 
     * @apiSuccessExample Success-Response:
     *    HTTP/1.1 200 OK
     *   {
     *      "message": "Room is updated successfully."
     *    }
     * 
     * @apiError InvalidRequest The request is invalid.
     * 
     * @apiErrorExample Error-Response:
     *    HTTP/1.1 409 Bad Request
     *  {
     *   "errors": {
     *      "name": "The name field is required."
     *    }
     *   "message": "Invalid Inputs"
     * }
     * 
     * @apiError RoomNotFound The id of the Room was not found.
     * 
     * @apiErrorExample Error-Response:
     *    HTTP/1.1 404 Not Found
     *  {
     *   "error": "Room not found"
     * }
     * 
     * @apiSampleRequest /api/v1/rooms/:id
     * 
     *
     */
    public function update($id)
    {
        // check if room exists and owned by logged in user
        $roomModel = new RoomModel();
        $room = $roomModel->find($id);
        if (!$room) {
            return $this->fail('Room not found', ResponseInterface::HTTP_NOT_FOUND);
        }
        if ($room['created_by'] != $this->auth->userid) {
            return $this->fail('You are not authorized to update this room', ResponseInterface::HTTP_UNAUTHORIZED);
        }

        // validate request
        $rules = [
            'name' => 'required|min_length[3]|max_length[255]',
            // 'description' => 'required|min_length[3]',
            // 'thumbnail' => 'uploaded[thumbnail]|max_size[thumbnail,1024]|is_image[thumbnail]',
        ];

        if (!$this->validate($rules)) {
            return $this->fail($this->validator->getErrors(), ResponseInterface::HTTP_BAD_REQUEST);
        }

        // upload image to server
        $file = $this->request->getFile('thumbnail');
        if ($file && $file->isValid() && !$file->hasMoved()) {
            mkdir(WRITEPATH . 'uploads/rooms/', 0755, true); // create directory if not exists
            $newName = $file->getRandomName();
            $file->move(WRITEPATH . 'uploads/rooms/', $newName);
            $image = 'rooms/' . $newName;
        } else {
            $image = $room['thumbnail'];
        }

        $roomModel->update($id, [
            'name' => $this->request->getVar('name'),
            'description' => $this->request->getVar('description'),
            'thumbnail' => $image,
        ]);

        return $this->respond(['message' => 'Room is updated successfully.'], ResponseInterface::HTTP_OK);
    }

    /**
     * @api {delete} /rooms/:id Delete Room
     * @apiName DeleteRoom
     * @apiGroup Room
     * 
     * @apiHeader {String} authorization Authorization token
     * 
     * @apiParam {Number} id Room's unique ID.
     * 
     * @apiSuccess {String} message Success message.
     * 
     * @apiSuccessExample Success-Response:
     *    HTTP/1.1 200 OK
     *   {
     *      "message": "Room is deleted successfully."
     *    }
     * 
     * @apiError RoomNotFound The id of the Room was not found.
     * 
     * @apiErrorExample Error-Response:
     *    HTTP/1.1 404 Not Found
     *  {
     *   "error": "Room not found"
     * }
     * 
     * @apiSampleRequest /api/v1/rooms/:id
     * 
     *
     */
    public function delete($id)
    {
        // check if room exists and owned by logged in user
        $roomModel = new RoomModel();
        $room = $roomModel->find($id);
        if (!$room) {
            return $this->fail('Room not found', ResponseInterface::HTTP_NOT_FOUND);
        }
        if ($room['created_by'] != $this->auth->userid) {
            return $this->fail('You are not authorized to update this room', ResponseInterface::HTTP_UNAUTHORIZED);
        }

        // change status to 'deleted' and update deleted_at field
        $roomModel->update($id, [
            'status' => 'deleted',
            'deleted_at' => date('Y-m-d H:i:s'),
        ]);

        return $this->respond(['message' => 'Room is deleted successfully.'], ResponseInterface::HTTP_OK);
    }

    /**
     * @api {get} /rooms/:id/members Get Members
     * @apiName GetMembers
     * @apiGroup Room
     * 
     * @apiHeader {String} authorization Authorization token
     * 
     * @apiParam {Number} id Room's unique ID.
     * 
     * @apiSuccess {String} id Member Id.
     * @apiSuccess {String} room_id Room's unique ID.
     * @apiSuccess {String} user_id User's unique ID.
     * @apiSuccess {String} status Status of the member.
     * @apiSuccess {String} created_by Member's owner id.
     * @apiSuccess {String} created_at Created date of the member.
     * @apiSuccess {String} updated_at Updated date of the member.
     * 
     * @apiSuccessExample Success-Response:
     *    HTTP/1.1 200 OK
     *   {
     *      "members": [
     *          {
     *              "id": 1,
     *              "room_id": 1,
     *              "user_id": 1,
     *              "status": "active",
     *              "created_by": 1,
     *              "created_at": "2024-03-17 13:54:52",
     *              "updated_at": "2024-03-17 13:54:52"
     *          }
     *      ]
     *    }
     * 
     * @apiError RoomNotFound The id of the Room was not found.
     * 
     * @apiErrorExample Error-Response:
     *    HTTP/1.1 404 Not Found
     *  {
     *   "error": "Room not found"
     * }
     * 
     * @apiSampleRequest /api/v1/rooms/:id/members
     *
     */
    public function getMembers($id)
    {
        // check if room exists and owned by logged in user
        $roomModel = new RoomModel();
        $room = $roomModel->find($id);
        if (!$room) {
            return $this->fail('Room not found', ResponseInterface::HTTP_NOT_FOUND);
        }

        $memberModel = new MemberModel();

        // only room owner and members can see the members
        if ($room['created_by'] != $this->auth->userid) {
            $member = $memberModel->where('room_id', $id)->where('user_id', $this->auth->userid)->first();
            if (!$member) {
                return $this->fail('You are not authorized to view this room', ResponseInterface::HTTP_UNAUTHORIZED);
            }
        }

        // get all the members of the room
        // join with users table to get user details
        $members = $memberModel->select('members.*, users.username, users.firstname, users.lastname, users.email, users.status')->join('users', 'users.id = members.user_id')->where('room_id', $id)->findAll();

        return $this->respond($members, ResponseInterface::HTTP_OK);
    }

    /**
     * @api {post} /rooms/:id/members Add Member
     * @apiName AddMember
     * @apiGroup Room
     * 
     * @apiHeader {String} authorization Authorization token
     * 
     * @apiParam {Number} id Room's unique ID.
     * 
     * @apiBody {String} user_id User's unique ID.
     * 
     * @apiSuccess {String} message Success message.
     * 
     * @apiSuccessExample Success-Response:
     *    HTTP/1.1 200 OK
     *   {
     *      "message": "Member is added successfully."
     *    }
     * 
     * @apiError InvalidRequest The request is invalid.
     * 
     * @apiErrorExample Error-Response:
     *    HTTP/1.1 409 Bad Request
     *  {
     *   "errors": {
     *      "user_id": "The user_id field is required."
     *    }
     *   "message": "Invalid Inputs"
     * }
     * 
     * @apiError RoomNotFound The id of the Room was not found.
     * 
     * @apiErrorExample Error-Response:
     *    HTTP/1.1 404 Not Found
     *  {
     *   "error": "Room not found"
     * }
     * 
     * @apiSampleRequest /api/v1/rooms/:id/members
     *
     */
    public function addMember($roomId)
    {
        // check if room exists and owned by logged in user
        $roomModel = new RoomModel();
        $room = $roomModel->find($roomId);
        if (!$room) {
            return $this->fail('Room not found', ResponseInterface::HTTP_NOT_FOUND);
        }
        if ($room['created_by'] != $this->auth->userid) {
            return $this->fail('You are not authorized to update this room', ResponseInterface::HTTP_UNAUTHORIZED);
        }

        // validate request
        $rules = [
            'user_id' => 'required|is_not_unique[users.id]',
        ];

        if (!$this->validate($rules)) {
            return $this->fail($this->validator->getErrors(), ResponseInterface::HTTP_BAD_REQUEST);
        }

        // check if user exists
        $userModel = new \App\Models\UserModel();
        $user = $userModel->find($this->request->getVar('user_id'));
        if (!$user) {
            return $this->fail('User not found', ResponseInterface::HTTP_NOT_FOUND);
        }

        // check if user is already a member
        $memberModel = new MemberModel();
        $member = $memberModel->where('room_id', $roomId)->where('user_id', $this->request->getVar('user_id'))->first();
        if ($member) {
            return $this->fail('User is already a member of this room', ResponseInterface::HTTP_CONFLICT);
        }

        // add user as member
        $memberModel->insert([
            'room_id' => $roomId,
            'user_id' => $this->request->getVar('user_id'),
            'status' => 'active', // default status is 'active'
            'created_by' => $this->auth->userid,
        ]);

        return $this->respond(['message' => 'Member is added successfully.'], ResponseInterface::HTTP_OK);
    }


    /**
     * @api {delete} /rooms/:id/members Remove Member
     * @apiName RemoveMember
     * @apiGroup Room
     * 
     * @apiHeader {String} authorization Authorization token
     * 
     * @apiParam {Number} id Room's unique ID.
     * 
     * @apiBody {String} user_id User's unique ID.
     * 
     * @apiSuccess {String} message Success message.
     * 
     * @apiSuccessExample Success-Response:
     *    HTTP/1.1 200 OK
     *   {
     *      "message": "Member is removed successfully."
     *    }
     * 
     * @apiError InvalidRequest The request is invalid.
     * 
     * @apiErrorExample Error-Response:
     *    HTTP/1.1 409 Bad Request
     *  {
     *   "errors": {
     *      "user_id": "The user_id field is required."
     *    }
     *   "message": "Invalid Inputs"
     * }
     * 
     * @apiError RoomNotFound The id of the Room was not found.
     * 
     * @apiErrorExample Error-Response:
     *    HTTP/1.1 404 Not Found
     *  {
     *   "error": "Room not found"
     * }
     * 
     * @apiSampleRequest /api/v1/rooms/:id/members
     *
     */
    public function removeMember($id)
    {
        // check if room exists and owned by logged in user
        $roomModel = new RoomModel();
        $room = $roomModel->find($id);
        if (!$room) {
            return $this->fail('Room not found', ResponseInterface::HTTP_NOT_FOUND);
        }

        // validate request
        $rules = [
            'user_id' => 'required|is_not_unique[users.id]',
        ];

        if (!$this->validate($rules)) {
            return $this->fail($this->validator->getErrors(), ResponseInterface::HTTP_BAD_REQUEST);
        }

        // check if user exists
        $userModel = new \App\Models\UserModel();
        $user = $userModel->find($this->request->getVar('user_id'));
        if (!$user) {
            return $this->fail('User not found', ResponseInterface::HTTP_NOT_FOUND);
        }

        // check if user is already a member
        $memberModel = new MemberModel();
        $member = $memberModel->where('room_id', $id)->where('user_id', $this->request->getVar('user_id'))->first();
        if (!$member) {
            return $this->fail('User is not a member of this room', ResponseInterface::HTTP_CONFLICT);
        }

        // only room owner can remove member, or user can remove himself
        if ($room['created_by'] != $this->auth->userid && $member['user_id'] != $this->auth->userid) {
            return $this->fail('You are not authorized to update this room', ResponseInterface::HTTP_UNAUTHORIZED);
        }

        // remove user as member
        $memberModel->delete($member['id']);

        return $this->respond(['message' => 'Member is removed successfully.'], ResponseInterface::HTTP_OK);
    }
}
