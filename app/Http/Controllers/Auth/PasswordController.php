<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Foundation\Auth\ResetsPasswords;
use Illuminate\Http\Request;
use Validator;
use App\User;
use Hash;
use Password;

class PasswordController extends Controller
{
    /*
    |--------------------------------------------------------------------------
    | Password Reset Controller
    |--------------------------------------------------------------------------
    |
    | This controller is responsible for handling password reset requests
    | and uses a simple trait to include this behavior. You're free to
    | explore this trait and override any methods you wish to tweak.
    |
    */

    use ResetsPasswords;

    /**
     * Create a new password controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware('guest');
    }

    public function getEmail() {
        if (config('auth.passwords.users.use_security_questions')){
            return $this->showPickResetMethod();
        }else{
            return $this->showLinkRequestForm();
        }
    }

    public function showPickResetMethod(){
        return view('auth.passwords.reset_method');
    }

    public function showQuestionsEmailForm(){
        return view('auth.passwords.questions_email');
    }

    public function showAnswersForm(Request $request){
        $data = $request->all();

        $validator = Validator::make($data, [
            'email' => 'required|email|exists:users'
        ]);

        if ($validator->fails()) {
            $this->throwValidationException(
                $request, $validator
            );
        }

        $user = User::whereEmail($data['email'])->first();
        $questions = $user->questions;

        return view('auth.passwords.answers', compact('user', 'questions'));
    }

    public function checkAnswers(Request $request){
        $data = $request->all();

        $numSecurityQuestions = config('auth.passwords.users.num_security_questions');

        $validator = Validator::make($data, [
            'email' => 'required|email|exists:users',
            'answers' => "required|size:$numSecurityQuestions",
            'answers.*' => 'required|max:255'
        ]);

        $validator->setAttributeNames([
            'email' => 'email',
            'answers' => 'security answers',
            'answers.*' => 'security answer',
        ]);

        if ($validator->fails()) {
            $this->throwValidationException(
                $request, $validator
            );
        }

        $user = User::whereEmail($data['email'])->first();
        $answerHashes = $user->answers;
        $fails = [];
        for ($i=0; $i < $numSecurityQuestions; $i++) {
            $answer = $data['answers'][$i];
            $normalized = strtolower(preg_replace('/\s+/', ' ', trim($answer)));
            if (!Hash::check($normalized, $answerHashes[$i])){
                array_push($fails, $i);
            }
        }

        if (sizeof($fails) > 0){
            return $this->sendFailedQuestionResponse($fails, $request);
        }else{
            return $this->redirectToResetLink($request);
        }

    }

    protected function sendFailedQuestionResponse(array $fails, Request $request)
    {
        $errors = [];

        foreach ($fails as $n) {
            $errors["answers.$n"] = "Answer doesn't match.";
        }

        return redirect()->back()
            ->withInput($request->only('name', 'answers'))
            ->withErrors($errors);
    }

    public function redirectToResetLink(Request $request)
    {
        $broker = $this->getBroker();

        $response = Password::broker($broker)->getResetLink(
            $this->getSendResetLinkEmailCredentials($request)
        );

        if ($response == Password::INVALID_USER){
            return $this->getSendResetLinkEmailFailureResponse($response);
        }

        return redirect($response);
    }

    /**
     * Validate the request of sending reset link.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return void
     */
    protected function validateSendResetLinkEmail(Request $request)
    {
        $this->validate($request, ['email' => 'required|email']);
    }
}
