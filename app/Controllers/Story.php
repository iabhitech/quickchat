<?php

namespace App\Controllers;

use App\Controllers\BaseController;
use App\Models\StoryModel;
use CodeIgniter\API\ResponseTrait;
use CodeIgniter\HTTP\ResponseInterface;

class Story extends BaseController
{
    use ResponseTrait;

    /**
     * @api {get} /stories Get Stories List
     * @apiName GetStories
     * @apiGroup Story
     *
     * @apiHeader {String} authorization Authorization token
     * 
     * @apiSuccess {String} id StoryId of the story.
     * @apiSuccess {String} user_id UserId of the story.
     * @apiSuccess {String} body Body of the story.
     * @apiSuccess {String} image Image of the story.
     * @apiSuccess {String} created_at Created date of the story.
     * @apiSuccess {String} updated_at Updated date of the story.
     *
     * @apiSuccessExample Success-Response:
     *     HTTP/1.1 200 OK
     *     {
     *        "stories": [
     *            {
     *              "id": 1,
     *              "user_id": 1,
     *              "body": "This is a story",
     *              "image": "http://example.com/image.jpg",
     *              "created_at": "2024-03-17 13:54:52",
     *              "updated_at": "2024-03-17 13:54:52"
     *            },
     *            {
     *              "id": 2,
     *              "user_id": 2,
     *              "body": "This is another story",
     *              "image": "http://example.com/image.jpg",
     *              "created_at": "2024-03-17 13:54:52",
     *              "updated_at": "2024-03-17 13:54:52"
     *            }
     *        ]
     *      }
     * 
     * @apiSampleRequest /api/v1/stories
     */
    public function index()
    {
        $storyModel = new StoryModel();
        $stories = $storyModel->getFriendStories($this->auth->userid);

        return $this->respond($stories, ResponseInterface::HTTP_OK);
    }


    /**
     * @api {get} /stories Get Self Stories List
     * @apiName GetSelfStories
     * @apiGroup Story
     *
     * @apiHeader {String} authorization Authorization token
     * 
     * @apiSuccess {String} id StoryId of the story.
     * @apiSuccess {String} user_id UserId of the story.
     * @apiSuccess {String} body Body of the story.
     * @apiSuccess {String} image Image of the story.
     * @apiSuccess {String} created_at Created date of the story.
     * @apiSuccess {String} updated_at Updated date of the story.
     *
     * @apiSuccessExample Success-Response:
     *     HTTP/1.1 200 OK
     *     {
     *        "stories": [
     *            {
     *              "id": 1,
     *              "user_id": 1,
     *              "body": "This is a story",
     *              "image": "http://example.com/image.jpg",
     *              "created_at": "2024-03-17 13:54:52",
     *              "updated_at": "2024-03-17 13:54:52",
     *              "expires_at": "2024-03-17 13:54:52"
     *            },
     *            {
     *              "id": 2,
     *              "user_id": 2,
     *              "body": "This is another story",
     *              "image": "http://example.com/image.jpg",
     *              "created_at": "2024-03-17 13:54:52",
     *              "updated_at": "2024-03-17 13:54:52",
     *              "expires_at": "2024-03-17 13:54:52"
     *            }
     *        ]
     *      }
     * 
     * @apiSampleRequest /api/v1/stories/self
     */
    public function self()
    {
        $storyModel = new StoryModel();
        $stories = $storyModel->where('user_id', $this->auth->userid)->findAll();
        return $this->respond($stories, ResponseInterface::HTTP_OK);
    }

    /**
     * @api {post} /stories Create Story
     * @apiName CreateStory
     * @apiGroup Story
     *
     * @apiHeader {String} authorization Authorization token
     * 
     * @apiBody {String} body Body of the story.
     * @apiBody {String} image Optional Image of the story.
     *
     * @apiParamExample {json} Request-Example:
     *    {
     *      "body": "This is a story",
     *    }
     *
     * @apiSuccess {String} message Story created successfully.
     *
     * @apiSuccessExample Success-Response:
     *     HTTP/1.1 200 OK
     *     {
     *        "message": "Story created successfully."
     *      }
     * 
     * @apiSampleRequest /api/v1/stories
     */
    public function create()
    {
        // upload image to server
        $file = $this->request->getFile('image');
        if ($file && $file->isValid() && !$file->hasMoved()) {
            mkdir(WRITEPATH . 'uploads/stories/', 0755, true); // create directory if not exists
            $newName = $file->getRandomName();
            $file->move(WRITEPATH . 'uploads/stories/', $newName);
            $image = 'stories/' . $newName;
        } else {
            $image = NULL;
        }

        $storyModel = new StoryModel();
        $storyModel->insert([
            'user_id' => $this->auth->userid,
            'body' => $this->request->getVar('body'),
            'image' => $image,
            'deleted_at' => date('Y-m-d H:i:s', strtotime('+24 hours')),
        ]);
        return $this->respond(['message' => 'Story created successfully.'], ResponseInterface::HTTP_OK);
    }

    /**
     * @api {put} /stories/:id Update Story
     * @apiName UpdateStory
     * @apiGroup Story
     *
     * @apiHeader {String} authorization Authorization token
     * 
     * @apiParam {Number} id StoryId of the story.
     * 
     * @apiBody {String} body Body of the story.
     * @apiBody {File} image Optional Image of the story.
     *
     * @apiParamExample {json} Request-Example:
     *    {
     *      "body": "This is a story",
     *    }
     *
     * @apiSuccess {String} message Story updated successfully.
     *
     * @apiSuccessExample Success-Response:
     *     HTTP/1.1 200 OK
     *     {
     *        "message": "Story updated successfully."
     *      }
     * 
     * @apiSampleRequest /api/v1/stories/:id
     */
    public function update($id)
    {
        $storyModel = new StoryModel();
        $story = $storyModel->find($id);

        if(!$story) {
            return $this->fail('Story not found.', ResponseInterface::HTTP_NOT_FOUND);
        }

        if ($story->user_id != $this->auth->userid) {
            return $this->fail('You are not authorized to update this story.', ResponseInterface::HTTP_UNAUTHORIZED);
        }

        // upload image to server
        $file = $this->request->getFile('image');
        if ($file && $file->isValid() && !$file->hasMoved()) {
            mkdir(WRITEPATH . 'uploads/stories/', 0755, true); // create directory if not exists
            $newName = $file->getRandomName();
            $file->move(WRITEPATH . 'uploads/stories/', $newName);
            $image = 'stories/' . $newName;
        } else {
            $image = $story->image;
        }

        $storyModel->update($id, [
            'body' => $this->request->getVar('body'),
            'image' => $image,
        ]);
        return $this->respond(['message' => 'Story updated successfully.'], ResponseInterface::HTTP_OK);
    }

    /**
     * @api {delete} /stories/:id Remove Story
     * @apiName RemoveStory
     * @apiGroup Story
     *
     * @apiHeader {String} authorization Authorization token
     * 
     * @apiParam {Number} id StoryId of the story.
     * 
     * @apiSuccess {String} message Story removed successfully.
     *
     * @apiSuccessExample Success-Response:
     *     HTTP/1.1 200 OK
     *     {
     *        "message": "Story removed successfully."
     *      }
     * 
     * @apiSampleRequest /api/v1/stories/:id
     */
    public function remove($id)
    {
        $storyModel = new StoryModel();
        $story = $storyModel->find($id);
        if ($story->user_id != $this->auth->userid) {
            return $this->fail('You are not authorized to remove this story.', ResponseInterface::HTTP_UNAUTHORIZED);
        }

        $storyModel->delete($id);
        return $this->respond(['message' => 'Story removed successfully.'], ResponseInterface::HTTP_OK);
    }
}
