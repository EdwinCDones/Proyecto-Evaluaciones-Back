<?php

namespace App\Http\Controllers\Authentication;

use App\Exports\UsersExport;
use App\Http\Controllers\Controller;
use App\Http\Requests\Authentication\User\UserCreateRequest;
use App\Http\Requests\Authentication\UserAdministration\UserAdminIndexRequest;
use App\Http\Requests\Authentication\UserRequest;
use App\Models\Authentication\PassworReset;
use App\Models\Authentication\Role;
use App\Models\App\Catalogue;
use App\Models\App\Status;
use App\Models\Authentication\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Maatwebsite\Excel\Facades\Excel;

class  UserAdministrationController extends Controller
{ 
    public function index(Request $request)
    {
        if ($request->has('search')) {
            $users = User::email($request->input('search'))
            ->firstlastname($request->input('search'))
            ->firstname($request->input('search'))
            ->identification($request->input('search'))
            ->secondlastname($request->input('search'))
            ->secondname($request->input('search'))
            ->get();
        }else{
            $users = User::paginate($request->input('per_page'));
        }

        if(sizeof($users)===0){
            return response()->json([
                'data' => null,
                'msg' => [
                    'summary' => 'No se encontraron usuarios',
                    'detail' => 'Intente de nuevo',
                    'code' => '404'
                ]], 404);
        }
            return response()->json([
                'data' => $users,
                'msg' => [
                    'summary' => 'success',
                    'detail' => '',
                    'code' => '200'
                ]], 200);
    }

    public function show($username, Request $request)
    {
        $user = User::
            with(['institutions' => function ($institutions) {
                $institutions->orderBy('name');
            }])
            ->with(['roles' => function ($roles) use ($request) {
                $roles
                    ->with(['permissions' => function ($permissions) {
                        $permissions->with(['route' => function ($route) {
                            $route->with('module')->with('type')->with('status');
                        }])->with('institution');
                    }]);
            }])
            ->where('username', $username)
            ->first();
            if(!$user){
                return response()->json([
                    'data' => null,
                    'msg' => [
                        'summary' => 'No se encontraró al usuario',
                        'detail' => 'Intente de nuevo',
                        'code' => '404'
                    ]], 404);
            }
                return response()->json([
                    'data' => $user,
                    'msg' => [
                        'summary' => 'success',
                        'detail' => '',
                        'code' => '200'
                    ]], 200);
    }

    public function store(Request $request)
    {
        $catalogues = json_decode(file_get_contents(storage_path() . "/catalogues.json"), true);

        $user = new User();
        $user->identification = $request->input('identification');
        $user->username = $request->input('username');
        $user->first_name = $request->input('first_name');
        $user->second_name = $request->input('second_name');
        $user->first_lastname = $request->input('first_lastname');
        $user->second_lastname = $request->input('second_lastname');
        $user->birthdate = $request->input('birthdate');
        $user->email = $request->input('email');
        $user->password = Hash::make($request->input('password'));

        //$blood_type = $catalogues['catalogue']['blood_type']['o+'];
        //$civil_status = $catalogues['catalogue']['civil_status']['married'];
        $ethnicOrigin = $catalogues['catalogue']['ethnic_origin']['mestizo'];
        //$location = $catalogues['catalogue']['location']['country'];
        //$identificationType = $catalogues['catalogue']['identification_type']['cc'];
        //$sex = $catalogues['catalogue']['sex']['male'];
        //$gender = $catalogues['catalogue']['gender']['male'];

        //$user->bloodType()->associate($blood_type);
        //$user->civilStatus()->associate($civil_status);
        $user->ethnicOrigin()->associate($ethnicOrigin);
        //$user->address()->associate($location);
        //$user->identificationType()->associate($identificationType);
        //$user->sex()->associate($sex);
        //$user->gender()->associate($gender);
        $user->status()->associate(Status::find($request->input('status')));
        $user->save();
        return response()->json([
            'data' => $user,
            'msg' => [
                'summary' => 'success',
                'detail' => '',
                'code' => '201'
            ]], 201);
    }

    public function update(Request $request,$userId)
    {
        $user = User::find($userId);

        $user->identification = $request->input('identification');
        $user->username = $request->input('username');
        $user->first_name = $request->input('first_name');
        $user->first_lastname = $request->input('first_lastname');
        $user->birthdate = $request->input('birthdate');
        $user->email = $request->input('email');
        $user->phone = $request->input('phone');

        $user->save();
        return response()->json([
            'data' => $user,
            'msg' => [
                'summary' => 'update',
                'detail' => '',
                'code' => '201'
            ]], 201);
    }

    public function destroy($userId)
    {
        $user = User::find($userId);
        $user->delete();

        return response()->json([
            'data' => $user,
            'msg' => [
                'summary' => 'deleted',
                'detail' => '',
                'code' => '201'
            ]], 201);
    }

    public function export()
    {
        return Excel::download(new UsersExport, 'users.xlsx');
    }

    private function filter($conditions)
    {
        $filters = array();
        foreach ($conditions as $condition) {
            if ($condition['match_mode'] === 'contains') {
                array_push($filters, array($condition['field'], $condition['logic_operator'], '%' . $condition['value'] . '%'));
            }
            if ($condition['match_mode'] === 'start') {
                array_push($filters, array($condition['field'], $condition['logic_operator'], $condition['value'] . '%'));
            }
            if ($condition['match_mode'] === 'end') {
                array_push($filters, array($condition['field'], $condition['logic_operator'], '%' . $condition['value']));
            }
        }
        return $filters;
    }
}
