<?php

namespace App\Http\Controllers\Auth;

use App\User;
use Validator;
use App\Http\Controllers\Controller;
use Illuminate\Foundation\Auth\ThrottlesLogins;
use Illuminate\Foundation\Auth\AuthenticatesAndRegistersUsers;

class AuthController extends Controller
{
    /*
    |--------------------------------------------------------------------------
    | Registration & Login Controller
    |--------------------------------------------------------------------------
    |
    | This controller handles the registration of new users, as well as the
    | authentication of existing users. By default, this controller uses
    | a simple trait to add these behaviors. Why don't you explore it?
    |
    */

    use AuthenticatesAndRegistersUsers, ThrottlesLogins;

    /**
     * Where to redirect users after login / registration.
     *
     * @var string
     */
    protected $redirectTo = '/';

    /**
     * Create a new authentication controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware($this->guestMiddleware(), ['except' => 'logout']);
    }

    /**
     * Get a validator for an incoming registration request.
     *
     * @param  array  $data
     * @return \Illuminate\Contracts\Validation\Validator
     */
    protected function validator(array $data)
    {

        $rules = [
            'name' => 'required|username_chars|max:24|unique:users,name',
            'email' => 'required|email|max:255|unique:users',
            'password' => 'required|min:6|confirmed',
        ];

        if (config('auth.passwords.users.use_security_questions')){
            $numSecurityQuestions = config('auth.passwords.users.num_security_questions');
            $rules = array_merge($rules, [
                'questions' => "required|size:$numSecurityQuestions",
                'answers' => "required|size:$numSecurityQuestions",
                'questions.*' => 'required|max:255',
                'answers.*' => 'required|max:255'
            ]);
        }

        $validator = Validator::make($data, $rules);

        $validator->setAttributeNames([
            'name' => 'username',
            'email' => 'email',
            'password' => 'password',
            'questions' => 'security questions',
            'answers' => 'security answers',
            'questions.*' => 'security question',
            'answers.*' => 'security answer',
        ]);

        return $validator;
    }

    /**
     * Create a new user instance after a valid registration.
     *
     * @param  array  $data
     * @return User
     */
    protected function create(array $data)
    {
        $user = User::create([
            'name' => $data['name'],
            'email' => $data['email'],
            'password' => bcrypt($data['password']),
            'questions' => $data['questions'],
            'answers' => array_map(function ($v) {
                $normalized = strtolower(preg_replace('/\s+/', ' ', trim($v)));
                return bcrypt($normalized);
            }, $data['answers'])
        ]);

        // create blank profile
        $user->profile()->create([]);

        return $user;
    }
}
