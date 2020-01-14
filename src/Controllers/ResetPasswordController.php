<?php
namespace Shellus\LaravelAuth\Controllers;

use App\Http\Controllers\Controller;
use App\Service\UserService;
use App\UserModel;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Str;

class ResetPasswordController extends Controller
{
    /**
     * Send a reset link to the given user.
     *
     * @param \Illuminate\Http\Request $request
     * @return \Illuminate\Http\RedirectResponse|\Illuminate\Http\JsonResponse
     * @throws AuthException
     */
    public function sendResetLinkEmail(Request $request)
    {
        $this->sendResetLink($request->only(['email']));
        return response()->json(['message' => '成功发送重置电子邮件']);
    }

    /**
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     * @throws AuthException
     */
    public function resetUserPassword(Request $request)
    {
        $this->validateToken($request->all());

        UserService::changePassword($request->only(['email', 'password']));
        // 修改密码
        return response()->json(['message' => '更改密码成功']);
    }

    protected function validateToken(array $credentials)
    {
        if (empty($credentials['token'])){
            throw new AuthException('没有token');
        }

        $row = \DB::table('password_resets')->where('token', $credentials['token'])->where('email', $credentials['email'])->first();
        if (!$row){
            throw new AuthException('token无效');
        }
        $expire = config('auth.passwords.'.config('auth.defaults.passwords').'.expire');
        if (Carbon::parse($row->created_at)->addMinutes($expire)->lt(Carbon::now())){
            throw new AuthException('token已经过期');
        }
    }
    /**
     * Send a password reset link to a user.
     *
     * @param array $credentials
     * @return string
     * @throws AuthException
     */
    protected function sendResetLink(array $credentials)
    {
        $email = $credentials['email'];

        /** @var UserModel $user */
        $user = UserModel::where('email', $email)->first();
        if (is_null($user)) {
            throw new AuthException('用户不存在');
        }
        $token = $this->createToken($email);

        \Notification::send($user, new ResetPasswordNotification($token));
    }

    protected function createToken($email)
    {
        $token = $this->getToken();
        \DB::table('password_resets')->where('email', $email)->delete();
        $data = ['email' => $email, 'token' => $token, 'created_at' => new Carbon];
        \DB::table('password_resets')->insert($data);
        return $token;
    }
    protected function getToken()
    {
        $key = config('app.key');

        if (Str::startsWith($key, 'base64:')) {
            $key = base64_decode(substr($key, 7));
        }

        $token = hash_hmac('sha256', Str::random(40), $key);

        return \Hash::make($token);
    }

}
