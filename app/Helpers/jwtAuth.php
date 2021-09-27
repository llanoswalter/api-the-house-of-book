<?php

namespace app\Helpers;

use Firebase\JWT\JWT;
use Illuminate\Support\Facades\DB;
use App\User;

class JwtAuth
{
    public $key;
    public function __construct()
    {
        $this->key = 'esta-es-la-clave-598473-del-token';
    }

    public function signup($email, $password, $getToken = null)
    {
        $user = User::where([
            'email' => $email,
        ])->first();
        $pwd = password_verify($password, $user->password);
        $signup = false;
        if (is_object($user) && $pwd) {
            $signup = true;
        }
        if ($signup) {
            $token = array(
                'sub'       => $user->id,
                'email'     => $user->email,
                'name'      => $user->name,
                'sername'   => $user->surname,
                'las'       => time(),
                'exp'       => time() + (9 * 60 * 60),
            );
            $jwt = JWT::encode($token, $this->key, 'HS256');
            $decode = JWT::decode($jwt, $this->key, ['HS256']);
            if (is_null($getToken)) {
                $data = $jwt;
            } else {
                $data = $decode;
            }
        } else {
            $data = array(
                'status' => 'error',
                'message' => 'login incorrecto',
                'code' => 404
            );
        }
        return $data;
    }
    public function checkToken($jwt, $getIdentity = false)
    {
        $auth = false;
        try {
            $jwt = str_replace('"', '', $jwt);
            $decode = JWT::decode($jwt, $this->key, ['HS256']);
        } catch (\UnexpectedValueException $e) {
            $auth = false;
        } catch (\DomainException $e) {
            $auth = false;
        }
        if (!empty($decode) && is_object($decode) && isset($decode->sub)) {
            $auth = true;
        } else {
            $auth = false;
        }
        if ($getIdentity) {
            $auth = $decode;
        }
        return $auth;
    }
}