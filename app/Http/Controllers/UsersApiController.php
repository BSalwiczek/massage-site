<?php
namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\User;
use Illuminate\Support\Facades\Hash;
use Auth;
use Validator;
use Illuminate\Foundation\Auth\VerifiesEmails;
use Illuminate\Auth\Events\Verified;


class UsersApiController extends Controller
{
	public function __construct()
    {
        $this->middleware('guest');
    }

	use VerifiesEmails;
	public $successStatus = 200;
	/**
	* login api
	*
	* @return \Illuminate\Http\Response
	*/
	public function login(){
		if(Auth::attempt(['email' => request('email'), 'password' => request('password')])){
			$user = Auth::user();
			if($user->email_verified_at !== NULL){
					// $success['message'] = "Login successfull";
					return redirect('/');
					// return response()->json(['success' => $success], $this-> successStatus);
				}else{
					return redirect('auth/verify');
					// return response()->json(['error'=>'Please Verify Email'], 401);
			}
		}
		else{
			return response()->json(['error'=>'Unauthorised'], 401);
		}
	}
	/**
	* Register api
	*
	* @return \Illuminate\Http\Response
	*/
	public function register(Request $request)
	{
		$validator = Validator::make($request->all(), [
			'name'=>'required|string|max:30',
    		'surname'=>'required|string|max:30',
    		'email'=>'required|email|max:50|unique:users',
    		'password'=>'required|min:8|max:30',
    		'accept_terms'=>'accepted',
			'password2' => 'required|same:password',
		]);

		if ($validator->fails()) {
			return response()->json(['error'=>$validator->errors()], 401);
		}
		$user = User::create([
            'name' => $request['name'],
            'surname' => $request['surname'],
            'email' => $request['email'],
            'profile_img' => 'default.png',
            'password' => Hash::make($request['password']),
        ]);
		$user->sendApiEmailVerificationNotification();
		$success['message'] = 'Please confirm yourself by clicking on verify user button sent to you on your email';

		return response()->json(['success'=>$success], $this->successStatus);
	}
	/**
	* details api
	*
	* @return \Illuminate\Http\Response
	*/
	public function details()
	{
		$user = Auth::user();
		return response()->json(['success' => $user], $this->successStatus);
	}
}