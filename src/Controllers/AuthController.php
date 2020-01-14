<?php
namespace Shellus\LaravelAuth\Controllers;

use App\Service\UserService;
use Illuminate\Foundation\Auth\AuthenticatesUsers;
use Illuminate\Validation\ValidationException;
use App\Http\Controllers\Controller;
use Tymon\JWTAuth\JWTAuth;
use Symfony\Component\HttpKernel\Exception\UnauthorizedHttpException;
use Tymon\JWTAuth\Exceptions\TokenBlacklistedException;
use Tymon\JWTAuth\Exceptions\TokenExpiredException;

class AuthController extends Controller
{
    /** @var JWTAuth $auth */
    protected $auth;
    use AuthenticatesUsers;

    /**
     * Create a new AuthController instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->auth = auth('api');
        $this->middleware('auth:api', ['except' => ['login', 'refresh', 'register', 'redirectToProvider', 'handleProviderCallback']]);
    }

    /**
     * Get a JWT via given credentials.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function login()
    {
        $credentials = request(['email', 'password']);

        if (!$token = $this->auth->attempt($credentials)) {

            throw ValidationException::withMessages([
                $this->username() => [trans('auth.failed')],
            ]);
        }

        event(new \Illuminate\Auth\Events\Login('jwt', $this->auth->user(), true));

        return $this->respondWithToken($token);
    }

    public function register()
    {
        UserService::create(request()->all());
        return response()->json(['message' => 'Successfully register']);
    }

    /**
     * Get the authenticated User.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function me()
    {
        return response()->json($this->auth->user());
    }

    /**
     * Log the user out (Invalidate the token).
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function logout()
    {
        $this->auth->logout();

        return response()->json(['message' => 'Successfully logged out']);
    }

    /**
     * Refresh a token.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function refresh()
    {
        try {
            $token = $this->auth->refresh();
            $this->auth->setToken($token);
        } catch (TokenExpiredException $exception) {
            // 过了可刷新时间了
            throw new UnauthorizedHttpException("jwt", "jwt token expired");
        } catch (TokenBlacklistedException $exception) {
            // 已经被刷新过的token
            throw new UnauthorizedHttpException("jwt", "jwt token blacklisted");
        }

        return $this->respondWithToken($token);
    }

    /**
     * Get the token array structure.
     *
     * @param  string $token
     *
     * @return \Illuminate\Http\JsonResponse
     */
    protected function respondWithToken($token)
    {
        return response()->json([
            'access_token' => $token,
            'token_type' => 'bearer',
            'expires_in' => $this->auth->factory()->getTTL() * 60,
            'user' => $this->auth->user(),
        ]);
    }
}
