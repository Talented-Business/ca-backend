<?php
namespace App\Http\Controllers;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;
use Illuminate\Support\Str;
use App\User;
use Mail;
use App\Mail\VerifyMail;
use App\Mail\PasswordMail;
use Illuminate\Support\Facades\Hash;

class AuthController extends Controller
{
    /**
     * Create user
     *
     * @param  [string] name
     * @param  [string] email
     * @param  [string] password
     * @param  [string] password_confirmation
     * @return [string] message
     */
    public function register(Request $request)
    {
        $request->validate([
            'name' => 'required|string',
            'email' => 'required|string|email',
            'password' => 'required|string',
        ]);
        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => bcrypt($request->password),
            'verify_code' => Str::random(32),
            'role' => 1
        ]);
        Mail::to($user->email)->send(new VerifyMail($user));
        return response()->json([
            'success' => 'Successfully created user!'
        ], 201);
    }

    public function verify(Request $request, $token) {
        $user = User::where('verify_code', '=', $token)->update(['email_verified' => true]);
        return redirect('/');
    }

    public function reset(Request $request)
    {   
        $email = $request->input('email');
        $request->validate([      
            'email' => 'required|string|email',
        ]);
        $password_code = Str::random(10);
        $user = User::where('email', '=', $email)->update(['password' => Hash::make($password_code)]);
        if ($user) {
            $data = ['token' => $password_code,'name'=>$user->name];
            Mail::to($email)->send(new PasswordMail($data));
            return response()->json(true);
        } else {
            return response()->json(false);
        }
    }
    public function login(Request $request){
        $request->validate([
            'email' => 'required|string|email',
            'password' => 'required|string',
        ]);

        $credentials = request(['email', 'password']);
        if(!Auth::attempt($credentials)){
            
            return response()->json([
                'message' => 'Unauthorized'
            ]);
        }
        $user = Auth::user(); 
        if($user->active==false){
            return response()->json([
                'message' => 'Inactive'
            ]);
        }
        $tokenResult = $user->createToken('Personal Access Token');
        $token = $tokenResult->token;
        $token->save();
        return response()->json([
            'accessToken' => $tokenResult->accessToken,
            'token_type' => 'Bearer',
            'expires_at' => Carbon::parse(
                $tokenResult->token->expires_at
            )->toDateTimeString(),
            'user' => $user
        ]);
    }

    public function logout(Request $request)
    {
        $request->user()->token()->revoke();
        return response()->json([
            'message' => 'Successfully logged out'
        ]);
    }
  
    /**
     * Get the authenticated User
     *
     * @return [json] user object
     */
    public function user(Request $request)
    {
        return response()->json($request->user());
    }

    public function findOrCreateUser($user, $provider) {
        $authUser = User::where('provider_id', $user->id)->first();
        if ($authUser) {
            return $authUser;
        }
        return User::create([
            'name' => $user->name,
            'email' => $user->email,
            'provider' => strtoupper($provider),
            'provider_id' => $user->id
        ]);
    }

    public function change(Request $request, $token) {
        $password = $request->input('password');
        $user = User::where(['password_code' => $token])->update(['password' => bcrypt($password)]);
        if ($user) {
            return response()->json(true);
        } else {
            return response()->json(false);
        }
    }
    /**
     * Login user and create token
     *
     * @param  [string] email
     * @param  [string] password
     * @param  [boolean] remember_me
     * @return [string] access_token
     * @return [string] token_type
     * @return [string] expires_at
     */
    
  
    /**
     * Logout user (Revoke the token)
     *
     * @return [string] message
     */
    
}