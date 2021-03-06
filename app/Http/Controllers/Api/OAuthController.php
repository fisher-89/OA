<?php

namespace App\Http\Controllers\Api;

use Illuminate\Http\Request;
use App\Http\Requests;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use Symfony\Component\HttpKernel\Exception\HttpException;
/* model start */

use App\Models\App;
/* model end */

use DB;

class OAuthController extends Controller
{

    protected $appId;
    protected $staffSn;
    protected $redirectUri;
    protected $authCode;
    protected $appToken;
    protected $refreshToken;
    protected $authCodeExpiration = 10;
    protected $appTokenExpiration = 1440;

    /**
     * 获取授权码
     * @param Request $request
     * @return type
     */
    public function getAuthCode(Request $request)
    {
        $this->checkAppId($request);
        $this->checkRedirectUri($request);
        $this->staffSn = app('CurrentUser')->getStaffSn();
        $this->authCode = $this->makeAuthCode();
        $this->saveAuthCode($request);
        return $this->redirectToApp($request);
    }

    /**
     * 获取访问令牌
     * @param Request $request
     */
    public function getAppToken(Request $request)
    {
        try {
            $this->checkRedirectUri($request);
            $this->checkAuthCode($request);
            $this->checkSecret($request);
            $this->saveAppToken();
            $response = $this->makeAppTokenResponse();
            return app('ApiResponse')->makeSuccessResponse($response);
        } catch (HttpException $e) {
            return app('ApiResponse')->makeErrorResponse($e->getMessage(), 501, $e->getStatusCode());
        }
    }

    /**
     * 刷新访问令牌
     * @param Request $request
     * @return type
     */
    public function refreshAppToken(Request $request)
    {
        try {
            $this->checkRefreshToken($request);
            $this->saveAppToken();
            $response = $this->makeAppTokenResponse();
            return app('ApiResponse')->makeSuccessResponse($response);
        } catch (HttpException $e) {
            Log::info('refresh_token error : ' . $e->getMessage());
            return app('ApiResponse')->makeErrorResponse($e->getMessage(), 501, $e->getStatusCode());
        }
    }

    /**
     * 测试重定向路由
     * @param type $request
     */
    private function checkRedirectUri($request)
    {
        if (empty($request->redirect_uri)) {
            abort(500, '缺少重定向路由');
        }
        $this->redirectUri = $request->redirect_uri;
    }

    /* ---- auth_code start ---- */

    /**
     * 检验app_id
     * @param type $request
     */
    private function checkAppId($request)
    {
        if (!$request->has('app_id')) {
            abort(500, '缺少app_id');
        }
        $this->appId = $request->app_id;
    }

    /**
     * 重定向到应用
     * @param type $request
     * @return type
     */
    private function redirectToApp($request)
    {
        $redirectUri = $request->redirect_uri;
        $params['auth_code'] = $this->authCode;
        $params['state'] = $request->state;
        $redirectUri = $redirectUri . '?' . http_build_query($params);
        return redirect()->away($redirectUri);
    }

    /**
     * 重定向到登录界面
     * @param type $request
     * @return type
     */
    private function redirectToLoginPage($request)
    {
        $apiRequest = $request->only(['app_id', 'redirect_uri']);
        $url = url()->current() . '?' . http_build_query($apiRequest);
        return redirect()->to('/login?url=' . urlencode($url))->with(['url' => $url]);
    }

    /**
     * 保存授权码
     */
    private function saveAuthCode($request)
    {
        Cache::put(
            'app_auth_code_' . $this->authCode,
            ['app_id' => $this->appId, 'staff_sn' => $this->staffSn],
            $this->authCodeExpiration
        );
    }

    /**
     * 生成授权码
     * @return type
     */
    private function makeAuthCode()
    {
        $code = md5($this->staffSn . $this->appId . time() . 'code');
        return $code;
    }

    /* ---- auth_code end ---- */

    /* ---- app_token start ---- */

    /**
     * 检验授权码
     * @param type $request
     * @return boolean
     */
    private function checkAuthCode(Request $request)
    {
        if ($request->has('auth_code')) {
            $authCode = $request->auth_code;
        } else {
            abort(500, '缺少auth_code');
        }
        $appAuth = Cache::pull('app_auth_code_' . $authCode);
        if (!empty($appAuth)) {
            $this->appId = $appAuth['app_id'];
            $this->staffSn = $appAuth['staff_sn'];
            return true;
        } else {
            abort(500, '无效授权码');
        }
    }

    /**
     * 检验app验证码
     * @param type $request
     * @return boolean
     */
    private function checkSecret(Request $request)
    {
        if ($request->has('secret')) {
            $secret = $request->secret;
        } else {
            abort(500, '缺少secret');
        }
        $app = App::find($this->appId);
        $secretOnServer = md5($app->app_ticket . $request->auth_code);
        if ($secret == $secretOnServer) {
            return true;
        } else {
            abort(500, '无效app验证码');
        }
    }

    /**
     * 检验更新令牌
     * @param Request $request
     * @return boolean
     */
    private function checkRefreshToken(Request $request)
    {
        if ($request->has('refresh_token')) {
            $refreshToken = $request->refresh_token;
        } else {
            abort(500, '缺少refresh_token');
        }
        $app = DB::table('app_token')
            ->where([['refresh_token', '=', $refreshToken]])
            ->first();
        if (!empty($app)) {
            $this->appId = $app->app_id;
            $this->staffSn = $app->staff_sn;
            return true;
        } else {
            abort(500, '无效更新令牌');
        }
    }

    /**
     * 保存访问令牌
     */
    private function saveAppToken()
    {
        $this->appToken = $this->makeAppToken();
        $this->refreshToken = $this->makeRefreshToken();
        $expiration = time() + $this->appTokenExpiration * 60;
        $app = App::find($this->appId);
        $app->app_token()->syncWithoutDetaching([
            $this->staffSn => ['app_token' => $this->appToken, 'refresh_token' => $this->refreshToken, 'expiration' => $expiration]
        ]);
    }

    /**
     * 生成访问令牌
     * @return string
     */
    private function makeAppToken()
    {
        $token = md5($this->staffSn . $this->appId . time() . 'token');
        return $token;
    }

    /**
     * 生成更新令牌
     * @return type
     */
    private function makeRefreshToken()
    {
        $token = md5($this->staffSn . $this->appId . time() . 'refresh_token');
        return $token;
    }

    /**
     * 生成app_token响应
     * @return type
     */
    private function makeAppTokenResponse()
    {
        return [
            'app_token' => $this->appToken,
            'refresh_token' => $this->refreshToken,
            'staff_sn' => $this->staffSn,
            'expiration' => $this->appTokenExpiration
        ];
    }

    /* ---- app_token end ---- */
}
