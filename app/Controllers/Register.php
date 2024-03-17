<?php

namespace App\Controllers;

use App\Models\UserModel;
use CodeIgniter\API\ResponseTrait;

class Register extends BaseController
{
  use ResponseTrait;

  /**
   * @api {post} /register Register New User
   * @apiName RegisterUser
   * @apiGroup User
   *
   * @apiBody {String} email User's email.
   * @apiBody {String} password User's password.
   * @apiBody {String} confirm_password User's confirm password.
   * @apiBody {String} username Optional User's username.
   * @apiBody {String} firstname Optional User's Firstname.
   * @apiBody {String} lastname Optional User's Lastname.
   * 
   * @apiParamExample {json} Request-Example:
   *    {
   *      "firstname": "John",
   *      "lastname": "Doe",
   *      "username": "johndoe",
   *      "email": "demo@example.com",
   *      "password": "password",
   *      "confirm_password": "password",
   *    }
   *
   * @apiSuccess {String} message Success message.
   *
   * @apiSuccessExample Success-Response:
   *     HTTP/1.1 200 OK
   *     {
   *       "message": "Registered Successfully",
   *     }
   *
   * @apiError InvalidRequest The request is invalid.
   *
   * @apiErrorExample Error-Response:
   *     HTTP/1.1 409 Bad Request
   *     {
   *       "errors": {
   *          "email": "The email field is required."
   *        }
   *       "message": "Invalid Inputs"
   *     }
   * 
   * @apiSampleRequest /api/v1/register
   */
  public function index()
  {
    $rules = [
      'email' => [
        'rules' => 'required|min_length[4]|max_length[255]|valid_email|is_unique[users.email]',
        'errors' => [
          'is_unique' => 'Email already exists'
        ],
      ],
      'password' => ['rules' => 'required|min_length[8]|max_length[255]'],
      'confirm_password'  => ['label' => 'confirm password', 'rules' => 'matches[password]'],
      'username' => [
        'rules' => 'max_length[255]|is_unique[users.username]',
        'errors' => [
          'is_unique' => 'Username already exists'
        ],
      ],
    ];


    if ($this->validate($rules)) {
      $username = $this->request->getVar('username') ?? NULL;
      $model = new UserModel();
      $data = [
        'firstname' => $this->request->getVar('firstname') ?? '',
        'lastname'  => $this->request->getVar('lastname') ?? '',
        'username'  => $username != '' ? $username : NULL,
        'email'    => $this->request->getVar('email'),
        'password' => password_hash($this->request->getVar('password'), PASSWORD_DEFAULT)
      ];
      $model->save($data);

      return $this->respond(['message' => 'Registered Successfully'], 200);
    } else {
      $response = [
        'errors' => $this->validator->getErrors(),
        'message' => 'Invalid Inputs'
      ];
      return $this->fail($response, 409);
    }
  }
}
