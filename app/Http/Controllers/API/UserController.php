<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\AppBaseController;
use App\Http\Controllers\CommonController;
use App\Mail\InvitationMail;
use App\Mail\WelcomeEmail;
use App\Mail\WelcomeMail;
use App\User;
use App\UserInvitationsToken;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Mail;
use Illuminate\Support\Facades\Validator;

class UserController extends AppBaseController
{
    public function sendInvitationMail(Request $request)
    {
        try {
            $validation = Validator::make($request->all(), [
                'email' => 'required|unique:user_invitations_tokens',
            ], [
                'email.unique' => 'Invitation already sent to this email!',
            ]);

            if ($validation->fails()) {
                return $this->responseError($validation->messages());
            }
            $template = 'mail.invitation';
            $subject = 'Signup Invitation for MiniApp';
            $email = $request->email;
            $user = UserInvitationsToken::create([
                'email' => $email,
                'invitation_token' => time() . md5(uniqid(rand(), true))
            ]);

            Mail::to($request->email)->send(new InvitationMail($user));

            return $this->responseSuccess('We have sent mail on ' . $request->email . '.');
        } catch (\Exception $e) {
            return $this->responseError($e->getMessage());
        }
    }

    public function userRegistration(Request $request)
    {
        try {
            $validation = Validator::make($request->all(), [
                'user_name' => ['required', 'string', 'min:4', 'max:255', 'unique:users', 'alpha_dash'],
                'password' => 'required',
                'token' => 'required'
            ]);
            if ($validation->fails()) {
                return $this->responseError($validation->messages());
            }
            $emailId = UserInvitationsToken::where('invitation_token', $request->token)->first();
            if (!$emailId) {
                return $this->responseError('Seems like token is expired.Or user has already registered with this mail.');
            }
            $user = User::create([
                'email' => $emailId['email'],
                'user_name' => $request->user_name,
                'password' => Hash::make($request->password),
                'verification_code' => time() . md5(uniqid(rand(), true))
            ]);

            Mail::to($emailId['email'])->send(new WelcomeMail($user));
            $emailId->delete();
            return $this->responseSuccess('We have sent you verification mail. Please verify email and complete the registration');
        } catch (\Exception $e) {
            return $this->responseError($e->getMessage());
        }
    }

    public function userVerification($token)
    {
        try {
            $user = User::whereVerificationCode($token)->first();
            if ($user) {
                User::where('id', $user->id)->update(['is_verify' => 1]);
                return $this->responseSuccess("Verification done.");
            } else {
                return $this->responseError("No any user found with this code.");
            }
        } catch (\Exception $e) {
            return $this->responseError($e->getMessage());
        }
    }

    public function userLogin(Request $request)
    {
        try {
            $validation = Validator::make($request->all(), [
                'user_name' => ['required', 'string', 'min:4', 'max:255', 'alpha_dash'],
                'password' => 'required'
            ]);
            if ($validation->fails()) {
                return $this->responseError($validation->messages());
            }
            $user = User::where('user_name', $request->user_name)->first();
            if ($user->is_verify == 0) {
                return $this->responseError("Your email id not verified yet");
            }
            if (Hash::check($request->password, $user->password)) {
                return $this->responseWithData($user);
            } else {
                return $this->responseError("Your credentials are not matched.");
            }
        } catch (\Exception $e) {
            return $this->responseError($e->getMessage());
        }
    }

    public function updateProfile(Request $request)
    {
        try {
            $validation = Validator::make($request->all(), [
                'user_id' => 'required',
                'user_name' => 'required|string|min:4|max:255|alpha_dash|unique:users,user_name,' . $request->user_id,
                'email' => 'required|unique:users,email,' . $request->user_id,
                'avatar' => ['mimes:jpg,png,PNG,jpeg,gif', 'dimensions:max_width=256,max_height=256'],
                'contact_no' => 'required|numeric',
                'address' => 'required',
            ]);
            if ($validation->fails()) {
                return $this->responseError($validation->messages());
            }
            $input = $request->all();
            if (isset($input['avatar'])) {
                $input['avatar_image'] = time() . '.' . $request->avatar->getClientOriginalExtension();
                $request->avatar->move(public_path('avatar'), $input['avatar_image']);
            } else {
                $input['avatar_image'] = null;
            }

            User::where('user_name', $input['user_name'])
                ->update(['user_name' => $input['user_name'],
                    'email' => $input['email'],
                    'name' => $input['name'],
                    'avatar' => $input['avatar_image'],
                    'contact_no' => $input['contact_no'],
                    'address' => $input['address']]);

            return $this->responseSuccess("User profile updated successfully.");

        } catch (\Exception $e) {
            return $this->responseError($e->getMessage());
        }
    }
}
