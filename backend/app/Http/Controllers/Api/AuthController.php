<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Auth\SocialAccount;
use App\Models\Auth\User;
use App\Models\Content\Frontend\Address;
use App\Repositories\Frontend\Auth\UserRepository;
use App\Traits\ApiResponser;
use Illuminate\Foundation\Auth\RegistersUsers;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;

/**
 * Class HomeController.
 */
class AuthController extends Controller
{
  use RegistersUsers, ApiResponser;

  /**
   * @var UserRepository
   */
  protected $userRepository;

  /**
   * RegisterController constructor.
   *
   * @param UserRepository $userRepository
   */
  public function __construct(UserRepository $userRepository)
  {
    $this->userRepository = $userRepository;
  }


  public function generateAndSendOTP($phone, $otp)
  {
    try {
      $appUrl = get_setting('site_url', env('APP_URL'));
      if (get_setting('sms_active_otp_message')) {
        $txt = get_setting('sms_otp_message');
        $txt = str_replace('[otp]', $otp, $txt);
        $txt = str_replace('[appUrl]', $appUrl, $txt);
      } else {
        $txt = "Your {$appUrl} One Time Password(OTP) is {$otp} Validity for OTP is 3 minutes. Helpline 01515234363";
      }
      if ($phone) {
        return send_ware_SMS($txt, $phone);
      }
    } catch (\Exception $ex) {
    }
    return false;
  }

  public function checkExistsCustomer(Request $request)
  {
    $validator = Validator::make($request->all(), [
      'phone' => 'nullable|string|min:10|max:20',
      'email' => ['nullable', 'string', 'email'],
    ]);

    $otpCode = rand(1000, 9999);
    $phone = request('phone', null);
    $email = request('email', null);
    if ($validator->fails() || (!$phone && !$email)) {
      return response(['status' => false, 'errors' => $validator->errors(), 'msg' => 'Type your valid mobile or email']);
    }

    $user = $phone ? User::where('phone', $phone)->first() : null;
    $user = $email ? User::where('email', $email)->first() : $user;
    $hasPassword = false;
    if (!$user) {
      $smsResponse = $phone ? $this->generateAndSendOTP($phone, $otpCode) : [];
      $emailUser = str_replace('+88', '', $phone);
      $userData['name'] = null;
      $userData['phone'] = $phone;
      $userData['otp_code'] = $otpCode;
      $userData['password'] = '#chinaexpress@123';
      $userData['email'] = $email ? $email : $emailUser . '@chinaexpress.com.bd';
      $user = $this->userRepository->create($userData, false);

      return response([
        'status' => true,
        'new' => true,
        'hasPassword' => false,
        'smsResponse' => $smsResponse,
        'message' => "OTP send to your phone",
        'data' => [
          "phone" => $phone,
          "email" => $email,
        ]
      ]);
    }

    $hasPassword = !Hash::check('#chinaexpress@123', $user->password);

    if ($user) {
      if (!$hasPassword) {
        $smsResponse = $phone ? $this->generateAndSendOTP($phone, $otpCode) : [];
        $user->update(['otp_code' => $otpCode]);
      }
      return response([
        'status' => true,
        'new' => true,
        'hasPassword' => $hasPassword,
        'message' => "OTP send to your phone",
        'data' => [
          "phone" => $phone,
          "email" => $email,
        ]
      ]);
    }

    return response([
      'status' => false,
      'message' => "System has some trouble, Try again"
    ]);
  }

  public function registerCustomer(Request $request)
  {
    $validator = Validator::make($request->all(), [
      'otp' => ['required', 'string', 'min:4', 'max:4'],
      'email' => ['nullable', 'string', 'email'],
      'phone' => 'nullable|string|min:10|max:20',
    ]);
    $otp_code = request('otp', null);
    $phone = request('phone', null);
    $email = request('email', null);
    if ($validator->fails() || (!$phone && !$email)) {
      return response(['status' => false, 'errors' => $validator->errors(), 'msg' => 'Type your valid mobile or email']);
    }
    $user = $phone ? User::where('phone', $phone)->where('otp_code', $otp_code)->first() : null;
    $user = $email ? User::where('email', $email)->where('otp_code', $otp_code)->first() : $user;

    if ($user) {
      Auth::loginUsingId($user->id, true);
      $data['token'] =  $user->createToken('ChinaExpress')->plainTextToken;
      $data['user'] =  $user;
      return response(['status' => true, 'msg' => 'User login successfully.', 'user' => $data]);
    }

    return response(['status' => false, 'msg' => 'User login failed.', 'user' => []]);
  }

  public function loginCustomer(Request $request)
  {
    $validator = Validator::make($request->all(), [
      'phone' => 'nullable|string|min:10|max:20',
      'email' => 'nullable|email|max:191',
      'password' => 'required|string|min:6|max:50'
    ]);

    $phone = request('phone', null);
    $email = request('email', null);
    $password = request('password');
    if ($validator->fails() || (!$phone && !$email)) {
      return response(['status' => false, 'errors' => $validator->errors(), 'msg' => 'Type your valid mobile or email']);
    }

    $user = $phone ? User::where('phone', $phone)->first() : null;
    $user = $email ? User::where('email', $email)->first() : $user;

    if (!Hash::check(trim($password), $user->password)) {
      return response(['errors' => ['password' => ['Password does not match!']]]);
    }

    if (Auth::attempt(['email' => $user->email, 'password' => $password])) {
      $user = Auth::user();
      $success['token'] =  $user->createToken('ChinaExpress')->plainTextToken;
      $success['user'] =  $user;
      return response(['status' => true, 'msg' => 'User login successfully.', 'user' => $success]);
    }

    return response(['status' => false, 'msg' => 'Login failed, Try again', 'user' => []]);
  }

  public function updateProfile(Request $request)
  {
    $user = auth()->user();
    $user_id = $user->id;
    $validator = Validator::make($request->all(), [
      'name' => ['required', 'string', 'max:20',],
      'email' => 'nullable|string|max:191|unique:users,email,' . $user_id,
      'phone' => 'nullable|string|max:191|unique:users,phone,' . $user_id,
      'password' => ['required', 'string', 'confirmed', 'min:6'],
    ]);
    if ($validator->fails()) {
      return response(['status' => false, 'errors' => $validator->errors()]);
    }
    $password = request('password');

    // $user = $this->userRepository->update($user_id, $userData);
    $user = User::find($user_id);
    $user->name = request('name', $user->name);
    $user->first_name = request('name', $user->name);
    $user->email = request('email', $user->email);
    $user->phone = request('phone', $user->phone);
    if($password){
      $user->password = Hash::make(request('password'));
    }
    $user->save();
    
    return response([
      'status' => true,
      'update' => true,
      'message' => "Information updated successfully",
      'user' => $user
    ]);
  }

  public function forgotPassword(Request $request)
  {
    $validator = Validator::make($request->all(), [
      'phone' => 'required|string|min:10|max:20'
    ]);
    if ($validator->fails()) {
      return response(['status' => false, 'errors' => $validator->errors()]);
    }
    $otpCode = rand(1000, 9999);
    $phone = request('phone');
    $user = User::where('phone', $phone)->first();

    if ($user) {
      $smsResponse = $this->generateAndSendOTP($phone, $otpCode);
      session(['otp_code' => $otpCode]);
      return response([
        'status' => true,
        'forgot' => true,
        'smsResponse' => $smsResponse,
        'message' => "OTP send to your phone",
        'data' => [
          "phone" => $phone
        ]
      ]);
    }
    return response([
      'status' => false,
      'forgot' => false,
      'message' => "Your are not register. Registered first!",
      'data' => [
        "phone" => $phone
      ]
    ]);
  }

  public function resetPassword(Request $request)
  {
    $validator = Validator::make($request->all(), [
      'phone' => 'required|string|min:10|max:20',
      'password' => ['required', 'string', 'confirmed', 'Min:6'],
    ]);

    if ($validator->fails()) {
      return response(['status' => false, 'errors' => $validator->errors()]);
    }

    $req_code = (int)request('otp', 00);
    $ses_code = (int)session('otp_code', 11);

    if ($ses_code !== $req_code) {
      return response(['status' => false, 'errors' => ['otp_code' => ['OTP does not match.']]]);
    }

    $phone = request('phone');
    $password = request('password');
    $user = User::where('phone', $phone)->first();
    if ($user) {
      $user->update(['password' => Hash::make($password)]);
      return response([
        'status' => true,
        'login' => true,
        'message' => "Password reset success. Login now",
        'data' => [
          "phone" => $phone
        ]
      ]);
    }

    return response([
      'status' => false,
      'forgot' => true,
      'message' => "Password reset failed",
      'data' => [
        "phone" => $phone
      ]
    ]);
  }


  public function me()
  {
    $user = auth()->check() ? auth()->user() : null;
    return response([
      'user' => $user
    ]);
  }


  public function logout()
  {
    request()->user()->currentAccessToken()->delete();
    return response([
      'msg' => 'Tokens Revoked'
    ]);
  }


  public function socialLogin()
  {
    $socialData = json_decode(request('socialData'), true);

    if (is_array($socialData)) {
      $data = array_key_exists('_profile', $socialData) ? $socialData['_profile'] : [];
      $provider = array_key_exists('_provider', $socialData) ? $socialData['_provider'] : '';
      $token = array_key_exists('_token', $socialData) ? $socialData['_token'] : [];

      $accessToken = '';
      if (is_array($token)) {
        $accessToken = array_key_exists('accessToken', $token) ? $token['accessToken'] : '';
      }

      if (is_array($data)) {
        $dataEmail = array_key_exists('email', $data) ? $data['email'] : '';
        $dataId = array_key_exists('id', $data) ? $data['id'] : '';
        $fullName = array_key_exists('name', $data) ? $data['name'] : '';
        $firstName = array_key_exists('firstName', $data) ? $data['firstName'] : '';
        $lastName = array_key_exists('firstName', $data) ? $data['lastName'] : '';
        $profilePicURL = array_key_exists('profilePicURL', $data) ? $data['profilePicURL'] : '';

        $fullName = $fullName ? $fullName : ($firstName . ' ' . $lastName);
        $user_email = $dataEmail ?: "{$dataId}@{$provider}.com";

        $user = User::where('email', $user_email)
          ->whereNotNull('active')
          ->select('id', 'name', 'email', 'phone', 'first_name', 'last_name', 'shipping_id', 'billing_id')
          ->first();

        if (!$user) {
          $user = User::create([
            'name' => $fullName,
            'first_name' => $firstName,
            'last_name' => $lastName,
            'email' => $user_email,
            'active' => true,
            'confirmed' => true,
            'password' => null,
            'avatar_type' => $provider,
          ]);
          if ($user) {
            $user->assignRole(config('access.users.default_role'));
          }
        }


        if (!$user->hasProvider($provider)) {
          // Gather the provider data for saving and associate it with the user
          $user->providers()->save(new SocialAccount([
            'provider' => $provider,
            'provider_id' => $dataId,
            'token' => $accessToken,
            'avatar' => $profilePicURL,
          ]));
        } else {
          // Update the users information, token and avatar can be updated.
          $user->providers()->update([
            'token' => $accessToken,
            'avatar' => $profilePicURL,
          ]);
          $user->avatar_type = $provider;
          $user->update();
        }
        if ($user) {
          Auth::loginUsingId($user->id, true);
          return $this->success([
            'user' => \auth()->user(),
          ]);
        }
      }
    }

    return $this->error('Social login fail', 422);
  }


  public function AllAddress()
  {
    $addresses = Address::where('user_id', auth()->id())->latest()->get();

    return $this->success([
      'addresses' => $addresses,
    ]);
  }

  public function StoreNewAddress()
  {
    $id = request('id');

    $data = [
      'name' => request('name'),
      'phone_one' => request('phone'),
      'phone_two' => '',
      'phone_three' => request('district'),
      'address' => request('address'),
      'user_id' => auth()->id(),
    ];


    if ($id) {
      $address = Address::find($id);
      if ($address) {
        $address->update($data);
      }
    } else {
      Address::create($data);
    }

    return $this->success([
      'status' => true,
      'msg' => 'Address updated successfully'
    ]);
  }

  public function deleteAddress()
  {
    $id = request('id');

    if ($id) {
      $address = Address::find($id);
      if ($address) {
        $address->delete();
      }
    }

    return $this->success([
      'status' => true,
      'msg' => 'Address deleted successfully'
    ]);
  }
}