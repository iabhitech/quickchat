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

  /**
   * @api {post} /users/change-password Change Password
   * @apiName ChangePassword
   * @apiGroup User
   *
   * @apiHeader {String} authorization Authorization token
   *
   * @apiBody {String} old_password User's old password.
   * @apiBody {String} new_password User's new password.
   * @apiBody {String} confirm_password User's confirm password.
   *
   * @apiParamExample {json} Request-Example:
   *    {
   *      "old_password": "oldpassword",
   *      "new_password": "newpassword",
   *      "confirm_password": "newpassword",
   *    }
   *
   * @apiSuccess {String} message Success message.
   *
   * @apiSuccessExample Success-Response:
   *     HTTP/1.1 200 OK
   *     {
   *       "message": "Password Changed Successfully",
   *     }
   *
   * @apiError InvalidRequest The request is invalid.
   *
   * @apiErrorExample Error-Response:
   *     HTTP/1.1 409 Bad Request
   *     {
   *       "errors": {
   *          "old_password": "The old password field is required."
   *        }
   *       "message": "Invalid Inputs"
   *     }
   *
   * @apiSampleRequest /api/v1/users/change-password
   */
  public function changePassword()
  {
    $rules = [
      'old_password' => ['rules' => 'required|min_length[8]|max_length[255]'],
      'new_password' => ['rules' => 'required|min_length[8]|max_length[255]'],
      'confirm_password'  => ['label' => 'confirm password', 'rules' => 'matches[new_password]'],
    ];
    if (!$this->validate($rules)) {
      return $this->fail($this->validator->getErrors());
    }
    $userModel = new UserModel();
    $user = $userModel->find($this->auth->userid);
    if (!password_verify($this->request->getVar('old_password'), $user['password'])) {
      return $this->fail('Old password is incorrect');
    }
    $user['password'] = password_hash($this->request->getVar('new_password'), PASSWORD_DEFAULT);
    $userModel->save($user);
    return $this->respond(['message' => 'Password Changed Successfully'], 200);
  }
}
