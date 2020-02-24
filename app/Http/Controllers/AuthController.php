<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Mail;
use App\User;

class AuthController extends Controller
{
  /**
   * Create a new AuthController instance.
   * requires:
   *
   * email, password
   *
   * @return void
   */
  public function __construct()
  {
    $this->middleware('auth:api', [
      'except' => [
        'login',
        'register',
        'verify',
        'resetPassword'
      ]
    ]);
    // It is recommended to use "jwt.auth", instead of "auth:api".
    // Functioning the same.
  }

  /**
   * Get a JWT via given credentials.
   *
   * @return \Illuminate\Http\JsonResponse
   */
  public function login()
  {
    $credentials = request(['email', 'password']);

    // check if not verified
    $user = User::whereEmail($credentials['email'])->first();
    if (is_null($user)) {
      return response()->json([
        'status' => false,
        'result' => [
          'messageTag' => 'unauthorized',
          'message' => 'Unauthorized'
        ]
      ], 401);
    } else if (!$user->is_verified) {
      return response()->json([
        'status' => false,
        'result' => [
          'messageTag' => 'unauthorized',
          'message' => 'Unauthorized'
        ]
      ], 401);
    }

    if (!$token = auth('api')->attempt($credentials)) {
      return response()->json([
        'status' => false,
        'result' => [
          'messageTag' => 'unauthorized',
          'message' => 'Unauthorized'
        ]
      ], 401);
    }

    return $this->respondWithToken($token);
  }

  /**
   * Get the authenticated User.
   *
   * @return \Illuminate\Http\JsonResponse
   */
  public function me()
  {
    return response()->json([
      'status' => true,
      'result' => auth('api')->user()
    ]);
  }

  /**
   * Log the user out (Invalidate the token).
   *
   * @return \Illuminate\Http\JsonResponse
   */
  public function logout()
  {
    // can get bearer token only from header
    $bearToken = request()->bearerToken();
    echo 'bearToken = '.$bearToken;

    // server error with below
//    $jwtToken = JWTAuth::getToken();

    auth('api')->logout();
//    $token = request()->get('token');
//    JWTAuth::invalidate(JWTAuth::getToken());

    return response()->json(['message' => 'Successfully logged out']);
  }

  /**
   * Refresh a token.
   * if open black list, previous token will be invalidated.
   *
   * getToken doesn't refresh the token
   * two tokens works at the same time.
   *
   * @return \Illuminate\Http\JsonResponse
   */
  public function refresh()
  {
    return $this->respondWithToken(auth('api')->refresh());
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
      'status' => true,
      'result' => [
        'access_token' => $token,
        'token_type' => 'bearer',
        'expires_in' => auth('api')->factory()->getTTL() * 60
      ]
    ]);
  }

  public function verify(Request $request)
  {
    $code = $request->get('code', '');

    $verifying = !empty($code);
    if ($verifying) {
      return $this->doVerification($code);
    } else {
      $email = $request->get('email');
      $user = User::whereEmail($email)->first();
      if (isset($user)) {
        $this->sendAuthEmail($user, $request->get('url'), 'email.verify');
        return response()->json([
          'status' => true,
          'result' => [
            'message' => 'Verification link is sent. Please check your email.',
            'messageTag' => 'verification_link_is_sent_please_check_your_email',
            'userId' => $user->id
          ]
        ]);
      } else {
        return response()->json([
          'status' => false,
          'result' => [
            'message' => 'Email was not registered yet!',
            'messageTag' => 'email_not_registered_yet'
          ]
        ]);
      }
    }
  }

  private function doVerification($code)
  {
    $user = User::whereConfirmationCode($code)->first();
    if (isset($user)) {
      $user->is_verified = true;
      $user->confirmation_code = '';
      $user->save();
      return response()->json([
        'status' => true,
        'result' => [
          'message' => 'Verification Successful!',
          'messageTag' => 'verification_successful'
        ]
      ]);
    } else {
      return response()->json([
        'status' => false,
        'result' => [
          'message' => 'Verification Code is Invalid or Expired! ',
          'messageTag' => 'verification_code_is_invalid_or_expired'
        ]
      ]);
    }
  }

  public function register(Request $request)
  {
    $newUser = $request->all();

    // check email
    if (!array_key_exists('email', $newUser)) {
      return response()->json([
        'status' => false,
        'message' => 'Email duplicated',
        'messageTag' => 'msg_email_duplicated'
      ]);
    }

    if ($newUser['password'] != $newUser['passwordConfirmation']) {
      return response()->json([
        'status' => false,
        'message' => 'Password mismatched.',
        'messageTag' => 'msg_password_mismatched'
      ]);
    }

    if (User::whereEmail($newUser['email'])->count() > 0) {
      return response()->json([
        'status' => false,
        'message' => 'Email already registered',
        'messageTag' => 'email_already_registered'
      ]);
    }
    $newUser['password'] = bcrypt($newUser['password']);
    $user = User::create($newUser);

    $this->sendAuthEmail($user, $request->get('url'), 'email.verify');

    return response()->json([
      'status' => true,
      'result' => [
        'message' => 'Signed up successfully.',
        'messageTag' => 'sign_up_success_and_check_email'
      ]
    ]);
  }

  public function resetPassword(Request $request) {
    return $this->sendResetPasswordEmail($request, 'email.reset');
  }

  protected function sendResetPasswordEmail($request, $view ) {
    $email = $request->get('email');
    $url = $request->get('url');
    if (is_null($email)) {
      return response()->json([
        'status' => false,
        'result' => [
          'message' => 'Email was not provided yet!',
          'messageTag' => 'email_not_provided_yet'
        ]
      ]);
    } else {
      $user = User::whereEmail($email)->first();
      if (is_null($user)) {
        return response()->json([
          'status' => false,
          'result' => [
            'message' => 'Email was not registered yet!',
            'messageTag' => 'email_not_registered_yet'
          ]
        ]);
      } else {
        $this->sendAuthEmail($user, $url, $view);
        return response()->json([
          'status' => true,
          'result' => [
            'message' => 'Password reset email sent successfully.',
            'messageTag' => 'please_check_email_to_reset_password'
          ]
        ]);
      }
    }
  }

  private function sendAuthEmail($user, $url, $view)
  {
    // Send verification email
    $confirmationCode = str_random(30);
    $user->confirmation_code = $confirmationCode;
    $user->save();

    $emailSubject = $view == 'email.reset' ? 'Reset Password' : 'Verify your Email';
    Mail::send($view, [
      'link' => $url . '/' . $confirmationCode,
      'name' => $user->name
    ], function ($message) use ($user, $emailSubject) {
      $message
        ->to($user->email, $user->name)
        ->subject($emailSubject);
    });
  }
}