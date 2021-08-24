<?php

namespace App\Http\Controllers\Authentication;

use App\Http\Controllers\Controller;
use App\Http\Requests\Authentication\Auth\AuthChangePasswordRequest;
use App\Http\Requests\Authentication\Auth\AuthPasswordForgotRequest;
use App\Http\Requests\Authentication\Auth\AuthResetPasswordRequest;
use App\Http\Requests\Authentication\Auth\AuthUnlockRequest;
use App\Http\Requests\Authentication\Auth\AuthUserUnlockRequest;
use App\Http\Requests\Authentication\Auth\AuthGetRolesRequest;
use App\Http\Requests\Authentication\Auth\AuthGetPermissionsRequest;
use App\Http\Requests\Authentication\Auth\AuthResetAttemptsRequest;
use App\Http\Requests\Authentication\Auth\AuthLogoutAllRequest;
use App\Http\Requests\Authentication\Auth\AuthLogoutRequest;
use App\Http\Requests\Authentication\Auth\AuthGenerateTransactionalCodeRequest;
use App\Mail\EmailMailable;
use App\Mail\Authentication\PasswordForgotMailable;
use App\Mail\Authentication\UserUnlockMailable;
use App\Models\Authentication\Client;
use App\Models\Authentication\PasswordReset;
use App\Models\App\Status;
use App\Models\Authentication\TransactionalCode;
use App\Models\Authentication\UserUnlock;
use App\Models\Authentication\User;
use App\Models\Authentication\Role;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;


class  AuthController extends Controller
{
    public function validateAttempts($username)
    {
        $catalogues = json_decode(file_get_contents(storage_path() . "/catalogues.json"), true);

        $user = User::firstWhere('username', $username);

        if (!$user) {
            return response()->json([
                'data' => null,
                'msg' => [
                    'summary' => 'Usuario no encontrado',
                    'detail' => 'Intente de nuevo',
                    'code' => '404'
                ]], 404);
        }

        $user->attempts = $user->attempts - 1;
        $user->save();

        if ($user->attempts <= 0) {
            $user->status()->associate(Status::where('code', $catalogues['status']['locked'])->first());
            $user->attempts = 0;
            $user->save();

            return response()->json([
                'data' => null,
                'msg' => [
                    'summary' => 'Oops! Su usuario ha sido bloqueado!',
                    'detail' => 'Demasiados intentos de inicio de sesión',
                    'code' => '429'
                ]], 429);
        }

        return response()->json([
            'data' => $user->attempts,
            'msg' => [
                'summary' => 'Contrasaña incorrecta',
                'detail' => "Oops! le quedan {$user->attempts} intentos",
                'code' => '401',
            ]], 401);
    }

    public function resetAttempts(AuthResetAttemptsRequest $request)
    {
        $user = $request->user();

        if (!$user) {
            return response()->json([
                'data' => null,
                'msg' => [
                    'summary' => 'Usuario no encontrado',
                    'detail' => 'Intente de nuevo',
                    'code' => '404'
                ]], 404);
        }

        $user->attempts = User::ATTEMPTS;
        $user->save();

        return response()->json([
            'data' => $user->attempts,
            'msg' => [
                'summary' => 'success',
                'detail' => '',
                'code' => '201',
            ]], 201);
    }

    public function logout(AuthLogoutRequest $request)
    {
        $request->user()->token()->revoke();

        return response()->json([
            'data' => null,
            'msg' => [
                'summary' => 'success',
                'detail' => '',
                'code' => '201'
            ]], 201);
    }

    public function logoutAll(AuthLogoutRequest $request)
    {
        DB::table('oauth_access_tokens')
            ->where('user_id', $request->user()->id)
            ->update([
                'revoked' => true
            ]);

        return response()->json([
            'data' => null,
            'msg' => [
                'summary' => 'success',
                'detail' => '',
                'code' => '201'
            ]], 201);
    }

    public function changePassword(AuthChangePasswordRequest $request)
    {

        $user = $request->user();

        if (!$user) {
            return response()->json([
                'data' => null,
                'msg' => [
                    'summary' => 'Usuario no encontrando',
                    'detail' => 'Intente de nuevo',
                    'code' => '404'
                ]], 404);
        }

        if (!Hash::check(trim($request->input('password_old')), $user->password)) {
            return response()->json([
                'data' => null,
                'msg' => [
                    'summary' => 'La contraseña actual no coincide con la contraseña enviada',
                    'detail' => 'Intente de nuevo',
                    'code' => '400'
                ]], 400);
        }

        $user->password = Hash::make(trim($request->input('password')));
        $user->is_changed_password = true;
        $user->save();

        return response()->json([
            'data' => $user,
            'msg' => [
                'summary' => 'success',
                'detail' => '',
                'code' => '201'
            ]], 201);
    }

    public function passwordForgot(AuthPasswordForgotRequest $request)
    {
        $user = User::where('username', $request->input('username'))
            ->orWhere('email', $request->input('username'))
            ->orWhere('personal_email', $request->input('username'))
            ->first();

        if (!$user) {
            return response()->json([
                'data' => null,
                'msg' => [
                    'summary' => 'Usuario no encontrando',
                    'detail' => 'Intente de nuevo',
                    'code' => '404'
                ]], 404);
        }

        $token = Str::random(70);
        PasswordReset::create([
            'username' => $user->username,
            'token' => $token
        ]);

        Mail::to($user->email)
            ->send(new PasswordForgotMailable(
                'Notificación de restablecimiento de contraseña',
                json_encode(['user' => $user, 'token' => $token]),
                null,
                $request->input('system')
            ));

        return response()->json([
            'data' => $this->hiddenStringEmail($user->email),
            'msg' => [
                'summary' => 'Correo enviado',
                'detail' => $this->hiddenStringEmail($user->email),
                'code' => '201'
            ]], 201);
    }

    public function userLocked(AuthUserUnlockRequest $request)
    {
        $user = User::where('username', $request->input('username'))
            ->orWhere('email', $request->input('username'))
            ->orWhere('personal_email', $request->input('username'))
            ->first();

        if (!$user) {
            return response()->json([
                'data' => null,
                'msg' => [
                    'summary' => 'Usuario no encontrando',
                    'detail' => 'Intente de nuevo',
                    'code' => '404'
                ]], 404);
        }
        $token = Str::random(70);
        UserUnlock::create([
            'username' => $user->username,
            'token' => $token
        ]);

        Mail::to($user->email)
            ->send(new UserUnlockMailable(
                'Notificación de desbloqueo de usuario',
                json_encode(['user' => $user, 'token' => $token]),
                null,
                $request->input('system')
            ));

        return response()->json([
            'data' => $this->hiddenStringEmail($user->email),
            'msg' => [
                'summary' => 'Correo enviado',
                'detail' => $this->hiddenStringEmail($user->email),
                'code' => '201'
            ]], 201);
    }

    public function resetPassword(AuthResetPasswordRequest $request)
    {
        $passworReset = PasswordReset::where('token', $request->token)->first();

        if (!$passworReset) {
            return response()->json([
                'data' => null,
                'msg' => [
                    'summary' => 'Token no encontrado',
                    'detail' => 'Intente de nuevo',
                    'code' => '400'
                ]], 400);
        }

        if (!$passworReset->is_valid) {
            return response()->json([
                'data' => null,
                'msg' => [
                    'summary' => 'Token no valido',
                    'detail' => 'El token ya fue utilizado',
                    'code' => '403'
                ]], 403);
        }

        if ((new Carbon($passworReset->created_at))->addMinutes(10) <= Carbon::now()) {
            return response()->json([
                'data' => null,
                'msg' => [
                    'summary' => 'Token no valido',
                    'detail' => 'El token ha expirado',
                    'code' => '403'
                ]], 403);
        }

        $user = User::firstWhere('username', $passworReset->username);

        if (!$user) {
            return response()->json([
                'data' => null,
                'msg' => [
                    'summary' => 'Usuario no encontrado',
                    'detail' => 'Intente de nuevo',
                    'code' => '404'
                ]], 404);
        }

        $user->password = Hash::make($request->password);
        $user->save();
        $passworReset->update(['is_valid' => false]);
        return response()->json([
            'data' => null,
            'msg' => [
                'summary' => 'Su contraseña fue restablecida',
                'detail' => 'Regrese al Login',
                'code' => '201'
            ]], 201);
    }

    public function unlockUser(AuthUnlockRequest $request)
    {
        $catalogues = json_decode(file_get_contents(storage_path() . "/catalogues.json"), true);
        $userUnlock = UserUnlock::where('token', $request->token)->first();
        if (!$userUnlock) {
            return response()->json([
                'data' => null,
                'msg' => [
                    'summary' => 'Token no encontrado',
                    'detail' => 'Intente de nuevo',
                    'code' => '400'
                ]], 400);
        }
        if (!$userUnlock->is_valid) {
            return response()->json([
                'data' => null,
                'msg' => [
                    'summary' => 'Token no valido',
                    'detail' => 'El token ya fue utilizado',
                    'code' => '403'
                ]], 403);
        }
        if ((new Carbon($userUnlock->created_at))->addMinutes(10) <= Carbon::now()) {
            return response()->json([
                'data' => null,
                'msg' => [
                    'summary' => 'Token no valido',
                    'detail' => 'El token ha expirado',
                    'code' => '403'
                ]], 403);
        }
        $user = User::firstWhere('username', $userUnlock->username);

        if (!$user) {
            return response()->json([
                'data' => null,
                'msg' => [
                    'summary' => 'Usuario no encontrado',
                    'detail' => 'Intente de nuevo',
                    'code' => '404'
                ]], 404);
        }

        $user->password = Hash::make($request->password);
        $user->status()->associate(Status::firstWhere('code', $catalogues['status']['active']));
        $user->attempts = User::ATTEMPTS;

        $user->save();

        $userUnlock->update(['is_valid' => false]);
        return response()->json([
            'data' => null,
            'msg' => [
                'summary' => 'Su usuario fue desbloqueado',
                'detail' => 'Regrese al Login',
                'code' => '201'
            ]], 201);
    }

    public function generateTransactionalCode(AuthGenerateTransactionalCodeRequest $request)
    {
        $user = $request->user();

        if (!$user) {
            return response()->json([
                'data' => null,
                'msg' => [
                    'summary' => 'Usuario no encontrando',
                    'detail' => 'Intente de nuevo',
                    'code' => '404'
                ]], 404);
        }
        $token = mt_rand(100000, 999999);
        TransactionalCode::create([
            'username' => $user->username,
            'token' => $token
        ]);

        Mail::to($user->email)
            ->send(new EmailMailable(
                'Información Código de Seguridad',
                json_encode(['user' => $user])
            ));
        $domainEmail = strlen($user->email) - strpos($user->email, "@");

        return response()->json([
            'data' => $this->hiddenString($user->email, 3, $domainEmail),
            'msg' => [
                'summary' => 'Correo enviado',
                'detail' => $this->hiddenString($user->email, 3, $domainEmail),
                'code' => '201'
            ]], 201);
    }

    public function verifyTransactionalCode(AuthUnlockRequest $request)
    {
        $transactionalCode = TransactionalCode::firstWhere('token', $request->token);

        if (!$transactionalCode) {
            return response()->json([
                'data' => null,
                'msg' => [
                    'summary' => 'Código no encontrado',
                    'detail' => 'Intente de nuevo',
                    'code' => '400'
                ]], 400);
        }
        if (!$transactionalCode->is_valid) {
            return response()->json([
                'data' => null,
                'msg' => [
                    'summary' => 'Código no valido',
                    'detail' => 'El código ya fue utilizado',
                    'code' => '403'
                ]], 403);
        }
        if ((new Carbon($transactionalCode->created_at))->addMinutes(2) <= Carbon::now()) {
            return response()->json([
                'data' => null,
                'msg' => [
                    'summary' => 'Código no válido',
                    'detail' => 'El código ha expirado',
                    'code' => '403'
                ]], 403);
        }
        $user = User::firstWhere('username', $transactionalCode->username);
        if (!$user) {
            return response()->json([
                'data' => null,
                'msg' => [
                    'summary' => 'Usuario no encontrado',
                    'detail' => 'Intente de nuevo',
                    'code' => '404'
                ]], 404);
        }

        $transactionalCode->update(['is_valid' => false]);
        return response()->json([
            'data' => true,
            'msg' => [
                'summary' => 'success',
                'detail' => '',
                'code' => '201'
            ]], 201);
    }

    private function hiddenStringEmail($email, $start = 3)
    {
        $end = strlen($email) - strpos($email, "@");
        $len = strlen($email);
        return substr($email, 0, $start) . str_repeat('*', $len - ($start + $end)) . substr($email, $len - $end, $end);
    }

    public function getRoles(AuthGetRolesRequest $request)
    {
        $user = $request->user();

        $roles = $user->roles()->with('system')
            ->where('institution_id', $request->input('institution'))
            ->where('system_id', $request->input('system'))
            ->get();

        if (sizeof($roles) === 0) {
            return response()->json([
                'data' => null,
                'msg' => [
                    'summary' => 'No se encontraron los roles',
                    'detail' => 'Intente de nuevo',
                    'code' => '404'
                ]], 404);
        }

        return response()->json([
            'data' => $roles,
            'msg' => [
                'summary' => 'success',
                'detail' => '',
                'code' => '200'
            ]], 200);
    }

    public function getPermissions(AuthGetPermissionsRequest $request)
    {
        $role = Role::find($request->input('role'));

        if (!$role) {
            return response()->json([
                'data' => null,
                'msg' => [
                    'summary' => 'No se encontraron los permisos',
                    'detail' => 'Intente de nuevo',
                    'code' => '404'
                ]], 404);
        }

        $permissions = $role->permissions()
            ->with(['route' => function ($route) {
                $route->with('module')->with('type')->with('status');
            }])
            ->with('institution')
            ->get();
        return response()->json([
            'data' => $permissions,
            'msg' => [
                'summary' => 'success',
                'detail' => '',
                'code' => '200'
            ]], 200);
    }
}
