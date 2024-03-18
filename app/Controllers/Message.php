<?php

namespace App\Controllers;

use App\Controllers\BaseController;
use App\Models\MemberModel;
use App\Models\MessageModel;
use App\Models\RoomModel;
use CodeIgniter\API\ResponseTrait;
use CodeIgniter\HTTP\ResponseInterface;

class Message extends BaseController
{
    use ResponseTrait;
    /**
     * @api {get} /messages/:roomId Get Messages
     * @apiName GetMessages
     * @apiGroup Message
     * 
     * @apiHeader {String} authorization Authorization token
     * 
     * @apiParam {Number} roomId Room's unique ID.
     * 
     * @apiSuccess {String} id Message Id.
     * @apiSuccess {String} message Message body.
     * @apiSuccess {String} created_by Message creater.
     * @apiSuccess {String} created_at Created date of the member.
     * @apiSuccess {String} updated_at Updated date of the member.
     * 
     * @apiSuccessExample Success-Response:
     *    HTTP/1.1 200 OK
     *   {
     *      "messages": [
     *          {
     *              "id": 1,
     *              "message": "Hello World",
     *              "created_by": 1,
     *              "created_at": "2024-03-17 13:54:52",
     *              "updated_at": "2024-03-17 13:54:52"
     *          }
     *      ]
     *    }
     * 
     * @apiError RoomNotFound The roomId of the Room was not found.
     * 
     * @apiErrorExample Error-Response:
     *    HTTP/1.1 404 Not Found
     *  {
     *   "error": "Room not found"
     * }
     * 
     * @apiSampleRequest /api/v1/messages/:roomId
     *
     */
    public function index($roomId)
    {
        // get messages by room id
        $messageModel = new MessageModel();
        $messages = $messageModel->getMessagesByRoomId($roomId, $this->auth->userid);
        return $this->respond(['messages' => $messages], ResponseInterface::HTTP_OK);
    }

    /**
     * @api {post} /messages/:roomId Send Message
     * @apiName SendMessage
     * @apiGroup Message
     * 
     * @apiHeader {String} authorization Authorization token
     * 
     * @apiParam {Number} roomId Room's unique ID.
     * @apiBody {String} message Message body.
     * 
     * @apiSuccess {String} message Message created successfully.
     * 
     * @apiSuccessExample Success-Response:
     *    HTTP/1.1 201 Created
     *   {
     *      "message": "Message created successfully"
     *    }
     * 
     * @apiError RoomNotFound The roomId of the Room was not found.
     * @apiError Forbidden You are not a member of this room.
     * 
     * @apiErrorExample Error-Response:
     *    HTTP/1.1 404 Not Found
     *  {
     *   "error": "Room not found"
     * }
     * 
     * @apiSampleRequest /api/v1/messages/:roomId
     *
     */
    public function create($roomId)
    {
        // validate
        $rules = [
            'message' => 'required'
        ];

        if (!$this->validate($rules)) {
            return $this->fail($this->validator->getErrors(), ResponseInterface::HTTP_BAD_REQUEST);
        }

        // check room exists and user is a member or owner
        $roomModel = new RoomModel();
        $memberModel = new MemberModel();
        $room = $roomModel->find($roomId);

        if (!$room) {
            return $this->fail('Room not found', ResponseInterface::HTTP_NOT_FOUND);
        }

        $member = $memberModel->where('room_id', $roomId)->where('user_id', $this->auth->userid)->first();

        if (!$member && $room['created_by'] != $this->auth->userid) {
            return $this->fail('You are not a member of this room', ResponseInterface::HTTP_FORBIDDEN);
        }

        // create message
        $messageModel = new MessageModel();

        $messageModel->insert([
            'room_id' => $roomId,
            'message' => $this->request->getVar('message'),
            'created_by' => $this->auth->userid
        ]);

        return $this->respond(['message' => 'Message created successfully'], ResponseInterface::HTTP_CREATED);
    }
}
