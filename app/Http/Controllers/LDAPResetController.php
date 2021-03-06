<?php

namespace App\Http\Controllers;

use App\Preference;
use App\UUD\helpers\MailGun;
use App\UUD\helpers\Security;
use App\UUD\Ldap;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Http\Request;
use App\Http\Requests;
use Illuminate\Support\Facades\Validator;
use App\LDAPPasswordReset;
use OpenResourceManager\Client\Email as EmailClient;

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

            $orm = getORMConnection();
            $emailClient = new EmailClient($orm);
            $response = $emailClient->getForAccount($reset_request->api_user_id);

            if ($response->code != 200) {
                $request->session()->flash('alert-danger', 'Unable to communicate with remote API.');
                return redirect()->route('home');
            }

            $emails = $response->body->data;

            if (!empty($emails)) {
                $request->session()->flash('alert-success', 'This user has known external email addresses. You should ask the user if they would like to use a new address or one of the known ones.');
            } else {
                $emails = null;
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

            $orm = getORMConnection();
            $emailClient = new EmailClient($orm);
            $response = $emailClient->getForAccount($api_user_id);

            if ($response->code != 200) {
                $request->session()->flash('alert-danger', 'Unable to communicate with remote API.');
                return redirect()->route('home');
            }

            $known_emails = [];
            $email = null;
            $post_email = false;
            $self_service_url = ($prefs->self_service_url && !empty($prefs->self_service_url) && isset($prefs->self_service_url)) ? $prefs->self_service_url : false;
            $company_logo_url = ($prefs->company_logo_url && !empty($prefs->company_logo_url) && isset($prefs->company_logo_url)) ? $prefs->company_logo_url : false;

            foreach ($response->body->data as $email) {
                $known_emails[] = $email->address;
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
                return redirect()->back()->withErrors($validator)->withInput($data);
            }

            // External address verification
            $email_domain = strtolower(trim(explode('@', $email)[1]));
            // Is the reset email an internal email?
            if (strval($email_domain) === strval(strtolower(trim($prefs->ldap_domain)))) {
                // If so Redirect back with errors.
                $kind = (empty($data['email'])) ? 'known_email_address' : 'email';
                return redirect()->back()->withErrors([$kind => ['Provide an address from a domain other than: "' . $email_domain . '"']])->withInput($data);
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
            Mail::send('mail.ldap_password_change', ['name' => $name, 'password' => $new_password, 'company_name' => $company_name, 'self_service_url' => $self_service_url, 'company_logo_url' => $company_logo_url], function ($m) use ($email, $name, $app_from, $company_name) {
                $m->from($app_from, $company_name);
                $m->to($email, $name);
                $m->subject('Password Reset');
                if (env('MAILGUN_TAGS_ENABLE', false)) $m = MailGun::generate_tagged_message($m, env('MAILGUN_TAGS', ''), array('ldap password reset'));
            });

            // Send new email back to the API
            if ($post_email && !empty($email)) {
                $response = $emailClient->store($api_user_id, null, null, $email, true, 'Account Verification', null, $app_from);
                if ($response->code != 201) {
                    Log::error($response->raw_body);
                }
            }

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
