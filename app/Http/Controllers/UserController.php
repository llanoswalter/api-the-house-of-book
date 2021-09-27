<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use App\User;
use App\Helpers\JwtAuth;

use function PHPSTORM_META\type;

class UserController extends Controller
{
    public function index(Request $request, $id)
    {
        $user = User::find($id);
        if (is_object($user)) {
            $data = array(
                'status' => 'success',
                'code' => 200,
                'user' => $user
            );
        } else {
            $data = array(
                'status' => 'error',
                'code' => 404,
                'message' => 'el usuario no existe',
            );
        }
        return response()->json($data, 200);
    }
    public function register(Request $request)
    {
        $json = $request->input('json', null);
        $params = json_decode($json);
        $params_array = json_decode($json, true);
        if (!empty($params_array) && !empty($params)) {

            $params_array = array_map('trim', $params_array);
            $validate = Validator::make($params_array, [
                'name'      => 'Required|Alpha',
                'surname'   => 'Required|Alpha',
                'email'     => 'Required|Email|unique:users',
                'password'  => 'Required|min:6',
            ]);
            if ($validate->fails()) {
                $data = array(
                    'status' => 'error',
                    'code' => 404,
                    'message' => 'el usuario no se ha creado',
                    "error" => $validate->errors()
                );
            } else {
                $pwd = password_hash($params->password, PASSWORD_BCRYPT, ['cost' => 4]);
                $user = new User();
                $user->name = $params->name;
                $user->surname = $params->surname;
                $user->email = $params->email;
                $user->password = $pwd;
                $user->save();
                $data = array(
                    'status' => 'success',
                    'code' => 200,
                    'message' => 'el usuario se ha creado correctamente',
                    'user' => $user
                );
            }
        } else {
            $data = array(
                'status' => 'error',
                'code' => 404,
                'message' => 'los datos enviados no son correcto',
            );
        }
        return response()->json($data, $data['code']);
    }
    public function login(Request $request)
    {
        $jwt = new JwtAuth();
        $json = $request->input('json', null);
        $params = json_decode($json);
        $params_array = json_decode($json, true);
        if (!empty($params_array) && !empty($params)) {

            $params_array = array_map('trim', $params_array);
            $validate = Validator::make($params_array, [
                'email'     => 'Required|Email',
                'password'  => 'Required|min:6',
            ]);
            if ($validate->fails()) {
                $data = array(
                    'status' => 'error',
                    'code' => 404,
                    'message' => 'login incorrecto',
                    "error" => $validate->errors()
                );
            } else {

                $data = $jwt->signup($params->email, $params->password);
                if (!empty($params->getToken)) {
                    $data = $jwt->signup($params->email, $params->password, true);
                }
            }
        } else {
            $data = array(
                'status' => 'error',
                'code' => 404,
                'message' => 'los datos enviados no son correcto',
            );
        }
        return response()->json($data, 200);
    }
    public function update(Request $request)
    {
        $token = $request->header('Authorization');
        $jwt = new JwtAuth();
        $json = $request->input('json', null);
        $params = json_decode($json);
        $params_array = json_decode($json, true);
        if (!empty($params_array) && !empty($params)) {

            $params_array = array_map('trim', $params_array);
            $user = $jwt->checkToken($token, true);
            $validate = Validator::make($params_array, [
                'name'      => 'Alpha',
                'surname'   => 'Alpha',
                'email'     => 'Email',
            ]);
            if ($validate->fails()) {
                $data = array(
                    'status' => 'error',
                    'code' => 404,
                    'message' => 'login incorrecto',
                    "error" => $validate->errors()
                );
            } else {
                unset($params_array['id']);
                unset($params_array['password']);
                unset($params_array['created_at']);
                unset($params_array['updated_at']);
                unset($params_array['remember_token']);
                unset($params_array['image']);

                $userUpdate = User::where('id', $user->sub)->update($params_array);
                $data = array(
                    'status' => 'success',
                    'code' => 200,
                    'message' => 'Se actualizo con exito',
                    'change' => $params_array,
                    'new' => $userUpdate

                );
            }
        } else {
            $data = array(
                'status' => 'error',
                'code' => 404,
                'message' => 'los datos enviados no son correcto',
            );
        }

        return response()->json($data, 200);
    }
    public function destroy(Request $request, $id)
    {
        $token = $request->header('Authorization');
        $jwt = new JwtAuth();
        $userToken = $jwt->checkToken($token, true);
        $user = User::find($id);
        if (is_object($user) && $user->id == $userToken->sub) {

            $user->delete();
            $data = array(
                'status' => 'success',
                'code' => 200,
                'message' => 'Se borro con exito',
            );
        } else {
            $data = array(
                'status' => 'error',
                'code' => 404,
                'message' => 'el usuario no existe',
            );
        }
        return response()->json($data, 200);
    }
}