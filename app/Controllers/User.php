<?php

namespace App\Controllers;

use App\Controllers\BaseController;
use App\Models\UserModel;
use CodeIgniter\API\ResponseTrait;

class User extends BaseController
{

  use ResponseTrait;
  /**
   * @api {get} /users Get Users List
   * @apiName GetUsers
   * @apiGroup User
   *
   * @apiHeader {String} authorization Authorization token
   * 
   * @apiSuccess {String} username Username of the User.
   * @apiSuccess {String} lastname  Lastname of the User.
   * @apiSuccess {String} lastname  Lastname of the User.
   *
   * @apiSuccessExample Success-Response:
   *     HTTP/1.1 200 OK
   *     {
   *        "users": [
   *            {
   *              "username": "johndoe",
   *              "firstname": "John",
   *              "lastname": "Doe"
   *            },
   *            {
   *              "username": "janedoe",
   *              "firstname": "John",
   *              "lastname": "Doe",
   *            }
   *        ]
   *      }
   * 
   * @apiSampleRequest /api/v1/users
   */
  public function index()
  {
    $users = new UserModel();
    return $this->respond(['users' => $users->select("id,username,firstname,lastname,created_at")->findAll()], 200);
  }

  /**
   * @api {get} /users/:id Get User by ID
   * @apiName GetUsersByID
   * @apiGroup User
   *
   * @apiHeader {String} authorization Authorization token
   * 
   * @apiParam {Number} id Users unique ID.
   * 
   * @apiSuccess {String} username Username of the User.
   * @apiSuccess {String} lastname  Lastname of the User.
   * @apiSuccess {String} lastname  Lastname of the User.
   *
   * @apiSuccessExample Success-Response:
   *     HTTP/1.1 200 OK
   *     {
   *          "username": "johndoe",
   *          "firstname": "John",
   *          "lastname": "Doe"
   *     }
   *
   * @apiSampleRequest /api/v1/users/:id
   */
  public function get($id)
  {
    $users = new UserModel();
    return $this->respond(['user' => $users->select('id,username,firstname,lastname,dob,address,city,state,country,zip,latitude,longitude,mobile,email,created_at')->where('id', $id)->first()], 200);
  }
}
