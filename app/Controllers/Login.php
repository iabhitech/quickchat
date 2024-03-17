<?php

namespace App\Controllers;

use CodeIgniter\API\ResponseTrait;
use App\Models\UserModel;
use \Firebase\JWT\JWT;

class Login extends BaseController
{
  use ResponseTrait;

    /**
   * @api {post} /login Login User
   * @apiName UserLogin
   * @apiGroup User
   *
   * @apiBody {String} email User's email.
   * @apiBody {String} password User's password.
   * 
   * @apiParamExample {json} Request-Example:
   *    {
   *      "email": "demo@example.com",
   *      "password": "password"
   *    }
   *
   * @apiSuccess {String} message Success message.
   *
   * @apiSuccessExample Success-Response:
   *     HTTP/1.1 200 OK
   *     {
   *       "message": "Login Successful",
   *       "token": "SAMPLE_TOKEN_STRING_HERE",
   *     }
   *
   * @apiError InvalidRequest The request is invalid.
   *
   * @apiErrorExample Error-Response:
   *     HTTP/1.1 401 Bad Request
   *     {
   *       "error": "Invalid username or password.",
   *     }
   * 
   * @apiSampleRequest /api/v1/login
   */
  public function index()
  {
    $userModel = new UserModel();

    $email = $this->request->getVar('email');
    $password = $this->request->getVar('password');

    $user = $userModel->where('email', $email)->first();

    if (is_null($user)) {
      return $this->respond(['error' => 'Invalid username or password.'], 401);
    }

    if ($user['status'] != 'active') {
      return $this->respond(['error' => 'User is not active.'], 401);
    }

    $pwd_verify = password_verify($password, $user['password']);

    if (!$pwd_verify) {
      return $this->respond(['error' => 'Invalid username or password.'], 401);
    }

    $key = getenv('JWT_SECRET');
    $iat = time(); // current timestamp value
    $exp = $iat + 3600;

    $payload = array(
      "iss" => "Issuer of the JWT",
      "aud" => "Audience that the JWT",
      "sub" => "Subject of the JWT",
      "iat" => $iat, //Time the JWT issued at
      "exp" => $exp, // Expiration time of token
      "email" => $user['email'],
    );

    $token = JWT::encode($payload, $key, 'HS256');

    $response = [
      'message' => 'Login Succesfull',
      'token' => $token
    ];

    return $this->respond($response, 200);
  }
}
