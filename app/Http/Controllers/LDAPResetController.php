<?php

namespace App\Http\Controllers;

use App\Preference;
use App\UUD\Client\UUDClient;
use App\UUD\helpers\Security;
use App\UUD\Ldap;
use Carbon\Carbon;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Http\Request;
use App\Http\Requests;
use Illuminate\Support\Facades\Validator;
use App\LDAPPasswordReset;

class LDAPResetController extends Controller
{
    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware('auth');
    }

    public function index(Request $request, $token)
    {
        $prefs = Preference::firstOrFail();
        $reset_request = LDAPPasswordReset::where('token', $token)->firstOrFail();
        $created = strtotime($reset_request->created_at);
        $time_since_request = round(abs(strtotime(Carbon::now()) - $created) / 60);
        if ($time_since_request < $prefs->reset_session_timeout && $reset_request->pending) {
            $api_user_id = $reset_request->api_user_id;
            $client = new UUDClient($prefs->uud_api_url, $prefs->uud_api_key);
            // Create a null email var
            $emails = null;
            // Get any emails from the API
            $result = $client->get_emails_by_user($api_user_id);
            // Was the request successful
            if ($result['body']['success']) {
                // Store the result in a var for convince
                $emails = $result['body']['result'];
                // Is the array empty?
                if (empty($emails)) {
                    // If so null
                    $emails = null;
                } else {
                    $request->session()->flash('alert-success', 'This user has known external email addresses. You should ask the user if they would like to use a new address or one of the known ones.');
                }
            }
            // Show the view
            return view('ldap_passwd_reset', ['emails' => $emails, 'token' => $token]);
        } else {
            // The reset session has expired, return home
            $request->session()->flash('alert-danger', 'That password reset session has expired, please re-verify the user to reset their password.');
            return redirect()->route('home');
        }

    }

    public function sendReset(Request $request, $token)
    {
        $data = $request->all();
        $prefs = Preference::firstOrFail();
        $reset_request = LDAPPasswordReset::where('token', $token)->firstOrFail();
        $api_user_id = $reset_request->api_user_id;
        $created = strtotime($reset_request->created_at);
        $time_since_request = round(abs(strtotime(Carbon::now()) - $created) / 60);
        if ($time_since_request < $prefs->reset_session_timeout && $reset_request->pending) {
            $client = new UUDClient($prefs->uud_api_url, $prefs->uud_api_key);
            $get_result = $client->get_emails_by_user($api_user_id);
            $known_emails = [];
            $email = null;
            $post_email = false;
            $self_service_url = ($prefs->self_service_url && !empty($prefs->self_service_url) && isset($prefs->self_service_url)) ? $prefs->self_service_url : false;

            foreach ($get_result['body']['result'] as $result) {
                $known_emails[] = $result['email'];
            }

            if (!empty($data['email'])) {
                if (!empty($known_emails)) {
                    $rules = [
                        'email' => 'required|email|confirmed|not_in:' . implode(',', $known_emails),
                    ];
                } else {
                    $rules = [
                        'email' => 'required|email|confirmed',
                    ];
                }
                $post_email = true;
                $email = $data['email'];
            } else {
                $rules = [
                    'known_email_address' => 'required|filled|email',
                ];
                $post_email = false;
                $email = $data['known_email_address'];
            }

            // Validate the incoming info
            $validator = Validator::make($data, $rules);

            // If we have errors return to the last page and show the errors
            if ($validator->fails()) {
                $request->session()->flash('alert-danger', 'Please provide a new email address or select a known address if there is one available.');
                return Redirect::back()->withErrors($validator)->withInput($data);
            }

            $name = $reset_request->name;
            $company_name = $prefs->company_name;
            $app_from = $prefs->application_email;

            $new_password = Security::strongPassword(16);

            $ldap = new Ldap();
            $dn = $ldap->samAccountName2Dn($reset_request->request_username);
            $result = $ldap->changePassword($dn, $new_password);

            // If something failed, redirect back and do not notify the user. Give ldap error back in flash message
            if ($result[0] === false) {
                $request->session()->flash('alert-danger', 'We\'ve Encountered an error while setting the user\'s password... ' . $result[1]);
                return Redirect::back()->withInput($data);
            }

            // Mail the new password to the user
            Mail::send('mail.ldap_password_change', ['name' => $name, 'password' => $new_password, 'company_name' => $company_name, 'self_service_url' => $self_service_url], function ($m) use ($email, $name, $app_from, $company_name) {
                $m->from($app_from, $company_name);
                $m->to($email, $name)->subject('Password Reset');
            });

            // Send new email back to the API
            if ($post_email && !empty($email)) $client->post_email_by_user($reset_request->api_user_id, $email);

            // Mark the request as done.
            $reset_request->pending = false;
            $reset_request->save();

            $request->session()->flash('alert-success', 'A new password has been sent to ' . $email . '.');

        } else {
            $request->session()->flash('alert-danger', 'That password reset session has expired, please re-verify the user to reset their password.');
        }

        return redirect()->route('home');
    }

}
