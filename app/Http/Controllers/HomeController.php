<?php

namespace App\Http\Controllers;

use App\Http\Requests;
use App\Preference;
use App\User;
use App\UUD\helpers\MailGun;
use App\UUD\Ldap;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;

class HomeController extends Controller
{
    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware('auth')->except('getMaintenance');
    }

    /**
     * Show profile page
     *
     * @return mixed
     */
    public function profile()
    {
        return view('profile');
    }

    public function getMaintenance(Request $request)
    {
        return view('errors.503');
    }

    /**
     * @param Request $request
     * @return mixed
     */
    public function saveProfile(Request $request)
    {
        // Get current user
        $user = Auth::user();
        // Store the request data in a var
        $data = $request->all();
        // Validator rules
        $rules = ['name' => 'required|max:255'];
        // If the new email does not match the old email validate it
        if ($user->email != $data['email']) $rules['email'] = 'required|email|max:255|unique:users';
        // Validate the incoming info
        $validator = Validator::make($data, $rules);
        // If we have errors return to the last page and show the errors
        if ($validator->fails()) return redirect()->route('profile')->withErrors($validator)->withInput();
        // Set their name
        $user->name = $data['name'];
        // Set their email
        $user->email = $data['email'];
        $user->save();
        // Return with a success message
        $request->session()->flash('alert-success', 'Your profile has been updated.');
        return redirect()->route('profile');
    }

    /**
     * @return mixed
     */
    public function changePassword(Request $request)
    {
        // Read our prefs
        $prefs = Preference::firstOrFail();
        // Get current user
        $user = Auth::user();
        // Get token repo
        $tokens = Password::getRepository();
        // Generate a token for the user
        $token = $tokens->create($user);
        // create vars for from and app name
        $app_from = $prefs->application_email;
        $app_name = $prefs->application_name;
        // Send a recovery notice
        Mail::send('auth.emails.password_change', ['user' => $user, 'token' => $token], function ($m) use ($user, $app_from, $app_name) {
            $m->from($app_from, $app_name);
            $m->to($user->email, $user->name);
            $m->subject('Password Change');
            if (env('MAILGUN_TAGS_ENABLE', false)) $m = MailGun::generate_tagged_message($m, env('MAILGUN_TAGS', ''), array('local password reset'));
        });
        // Return with a status message
        $request->session()->flash('alert-warning', 'We\'ve sent you an email that contains a password reset link.' . "\n"
            . 'Keep an eye out for the email, it may be in spam.');
        return redirect()->route('profile');
    }

    /**
     * Show dashboard page
     *
     * @return mixed
     */
    public function dashboard()
    {
        return view('dash');
    }

    /**
     * Show dashboard page
     *
     * @return mixed
     */
    public function timeline()
    {
        return view('timeline');
    }

    /**
     * @return mixed
     */
    public function userIndex()
    {
        return view('users');
    }

    /**
     * Show a user creation page
     *
     * @return mixed
     */
    public function showNewUserForm()
    {
        return view('new_user');
    }

    /**
     * @param Request $request
     * @param $id
     * @return mixed
     */
    public function deleteUser(Request $request, $id)
    {
        // Don't allow a user to delete themselves
        if (intval(Auth::user()->id) === intval($id)) {
            $request->session()->flash('alert-danger', 'You cannot delete your own account!');
            return redirect()->route('newuser');
        }
        // Make sure that the current user is an admin
        if (Auth::user()->isAdmin) {
            User::findOrFail($id)->delete();
            $request->session()->flash('alert-success', 'User was deleted!');
        } else {
            $request->session()->flash('alert-danger', 'You do not have permissions to do that!');
        }
        // Return back to the users page
        return redirect()->route('users');
    }

    /**
     * @param Request $request
     * @return mixed
     */
    public function postNewUser(Request $request)
    {
        // Read our prefs
        $prefs = Preference::firstOrFail();
        // Store the request data in a var
        $data = $request->all();
        // Get the user
        $user = User::withTrashed()->where('email', $data['email'])->first();
        // Validator rules
        $rules = ['name' => 'required|max:255'];
        // If the user does not exists add validator rules for email
        if (!$user) {
            $rules['email'] = 'required|email|max:255|unique:users';
        } else {
            // If the new email does not match the old email validate it
            if ($user->email != $data['email']) $rules['email'] = 'required|email|max:255|unique:users';
        }
        // Validate the incoming info
        $validator = Validator::make($data, $rules);
        // If we have errors return to the last page and show the errors
        if ($validator->fails()) return redirect()->route('newuser')->withErrors($validator)->withInput();
        // Generate a random password
        $data['password'] = bcrypt(Str::quickRandom(8));
        // Determine if the target user should be an admin
        $data['isAdmin'] = isset($data['isAdmin']) ? true : false;
        // Check to see if the user is trashed and if so, restore the user
        $user = User::onlyTrashed()->where('email', $data['email'])->first();
        if ($user) $user->restore();
        // Create the user/update the user
        $user = User::updateOrCreate(['email' => $data['email']], $data);
        // Get token repo
        $tokens = Password::getRepository();
        // Generate a token for the user
        $token = $tokens->create($user);
        // create vars for from and app name
        $app_from = $prefs->application_email;
        $app_name = $prefs->application_name;
        // Send a welcome email
        Mail::send('auth.emails.welcome', ['user' => $user, 'token' => $token], function ($m) use ($user, $app_name, $app_from) {
            $m->from($app_from, $app_name);
            $m->to($user->email, $user->name);
            $m->subject('Welcome!');
            if (env('MAILGUN_TAGS_ENABLE', false)) $m = MailGun::generate_tagged_message($m, env('MAILGUN_TAGS', ''), array('welcome'));
        });
        // Return with a success message
        $request->session()->flash('alert-success', 'User was created!');
        return redirect()->route('users');
    }

    /**
     * @param Request $request
     * @param $id
     */
    public function getUser(Request $request, $id)
    {
        return view('user')->with('user', User::findOrFail($id));
    }

    /**
     * @param Request $request
     * @param $id
     * @return mixed
     */
    public function viewTrashedUser(Request $request, $id)
    {
        return view('user_trash')->with('user', User::onlyTrashed()->findOrFail($id));
    }

    /**
     * @param Request $request
     * @param $id
     * @return mixed
     */
    public function restoreTrashedUser(Request $request, $id)
    {
        // Read our prefs
        $prefs = Preference::firstOrFail();
        // Store the request data in a var
        $data = $request->all();
        // Get the user
        $user = User::onlyTrashed()->findOrFail($id);
        // Validator rules
        $rules = ['name' => 'required|max:255'];
        // If the new email does not match the old email validate it
        if ($user->email != $data['email']) $rules['email'] = 'required|email|max:255|unique:users';
        // Validate the incoming info
        $validator = Validator::make($data, $rules);
        // If we have errors return to the last page and show the errors
        if ($validator->fails()) return redirect('users/' . $id . '/trash')->withErrors($validator)->withInput();
        // Restore the user
        $user->restore();
        // Determine if the target user should be an admin
        $data['isAdmin'] = isset($data['isAdmin']) ? true : false;
        // Generate a random password
        $data['password'] = bcrypt(Str::quickRandom(8));
        // Create the user/update the user
        $user = User::updateOrCreate(['id' => $id], $data);
        // Get token repo
        $tokens = Password::getRepository();
        // Generate a token for the user
        $token = $tokens->create($user);
        // create vars for from and app name
        $app_from = $prefs->application_email;
        $app_name = $prefs->application_name;
        // Send a recovery notice
        Mail::send('auth.emails.recovery', ['user' => $user, 'token' => $token], function ($m) use ($user, $app_from, $app_name) {
            $m->from($app_from, $app_name);
            $m->to($user->email, $user->name);
            $m->subject('Account Recovery');
            if (env('MAILGUN_TAGS_ENABLE', false)) $m = MailGun::generate_tagged_message($m, env('MAILGUN_TAGS', ''), array('account recovery'));
        });
        // Return with a success message
        $request->session()->flash('alert-success', 'User was restored!');
        return redirect()->route('users');
    }

    /**
     * @param Request $request
     * @param $id
     * @return mixed
     */
    public function saveUser(Request $request, $id)
    {
        // Store the request data in a var
        $data = $request->all();
        // Find the target user
        $user = User::findOrFail($id);
        // Validator rules
        $rules = ['name' => 'required|max:255'];
        // If the new email does not match the old email validate it
        if ($user->email != $data['email']) $rules['email'] = 'required|email|max:255|unique:users';
        // Validate the incoming info
        $validator = Validator::make($data, $rules);
        // If we have errors return to the last page and show the errors
        if ($validator->fails()) return redirect('users/' . $id)->withErrors($validator)->withInput();
        // Set their name
        $user->name = $data['name'];
        // Set their email
        $user->email = $data['email'];
        // Only an admin can change someones admin status... don't allow someone to change their own admin status either
        if (Auth::user()->isAdmin && Auth::user()->id != $id) {
            isset($data['isAdmin']) ? $user->isAdmin = true : $user->isAdmin = false;
        }
        // Save the user
        $user->save();
        // Return with a success message
        $request->session()->flash('alert-success', 'User was saved!');
        return redirect('users/' . $id);
    }

    public function getPreferences(Request $request)
    {
        return view('preferences');
    }

    public function savePreferences(Request $request)
    {
        // Store the request data in a var
        $data = $request->all();
        $errors = [];
        // Validator rules
        $rules = [
            'company_name' => 'required|max:255',
            'application_name' => 'required|max:255',
            'application_email_address' => 'required|email|max:255',
            'password_reset_session_timeout' => 'required|integer|max:60|min:1',
            'uud_api_url' => 'required|url',
            'uud_api_key' => 'required|size:64',
        ];

        // If we are getting a self service url, then make sure it is valid
        if (!empty($data['self_service_url'])) {
            $rules['self_service_url'] = 'required|url';
        }

        $managing_ldap = false;
        // If any of the ldap settings are filled out, require all of them
        if (!empty($data['ldap_servers']) ||
            !empty($data['ldap_port']) ||
            !empty($data['ldap_search_base']) ||
            !empty($data['ldap_domain']) ||
            !empty($data['ldap_bind_user']) ||
            !empty($data['ldap_bind_password'])
        ) {
            $managing_ldap = true;
            $rules['ldap_servers'] = 'required|max:255';
            $rules['ldap_port'] = 'required|integer';
            $rules['ldap_search_base'] = 'required|max:255';
            $rules['ldap_domain'] = 'required|max:255';
            $rules['ldap_bind_user'] = 'required|max:255';
            $rules['ldap_bind_password'] = 'required|max:255';
        }

        // Validate the incoming info
        $validator = Validator::make($data, $rules);
        // If we have errors return to the last page and show the errors
        if ($validator->fails()) return redirect()->route('preferences')->withErrors($validator)->withInput();
        // Create a new preference or get the first one.
        $pref = (Preference::all()->count() > 0) ? Preference::all()->first() : new Preference();
        $pref->application_name = $data['application_name'];
        $pref->application_email = $data['application_email_address'];
        $pref->reset_session_timeout = $data['password_reset_session_timeout'];
        $pref->uud_api_url = $data['uud_api_url'];
        $pref->uud_api_key = $data['uud_api_key'];
        $pref->company_name = $data['company_name'];
        // Optional setting should be null when empty in form
        $pref->self_service_url = empty($data['self_service_url']) ? null : $data['self_service_url'];

        // Are we managing LDAP?
        if ($managing_ldap) {
            // Create a new Ldap object
            $ldap = new Ldap;
            // Fix our input into testable formats
            $hosts = $ldap->hosts2Array($data['ldap_servers']);
            $domain = $ldap->convertDomain($data['ldap_domain']);
            // Pass our input into the preferences object
            $pref->ldap_port = $data['ldap_port'];
            $pref->ldap_search_base = $data['ldap_search_base'];
            $pref->ldap_domain = $data['ldap_domain'];
            $pref->ldap_bind_user = $data['ldap_bind_user'];
            $pref->ldap_bind_password = $data['ldap_bind_password'];
            $pref->ldap_ssl = isset($data['ldap_ssl']) ? true : false;
            // Test each host and the credentials that were passed in from the input.
            $save_hosts = [];
            foreach ($hosts as $host) {
                $bind = $ldap->testBind($host, $pref->ldap_port, $pref->ldap_ssl, $pref->ldap_bind_user, $pref->ldap_bind_password, $domain);
                if ($bind['status']) {
                    $save_hosts[] = $host;
                } else {
                    $errors[] = 'Could not bind to LDAP host: ' . $host . ' with error: ' . $bind['message'];
                }
            }
            // If we did not return any hosts that returned success on a bind, redirect back and notify the admin.
            if (empty($save_hosts)) {
                if (empty($errors)) {
                    $request->session()->flash('alert-danger', 'Could not save your LDAP configuration. Unable to bind to any of the hosts provided.');
                } else {
                    $request->session()->flash('alert-danger', 'Could not save your LDAP configuration. Unable to bind to any of the hosts provided.' . ' ' . implode(' - ', $errors));
                }
                return redirect()->back()->withInput();
            }
            // We had partial failures, only some hosts worked, notify the admin
            if (sizeof($save_hosts) != sizeof($hosts) && !empty($save_hosts)) {
                if (empty($errors)) {
                    $request->session()->flash('alert-warning', 'Some of the LDAP hosts that were provided were unable to be verified, they will not be saved.');
                } else {
                    $request->session()->flash('alert-warning', 'Some of the LDAP hosts that were provided were unable to be verified, they will not be saved.' . ' ' . implode(' - ', $errors));
                }
            }
            // Store our final hosts in an array.
            $pref->ldap_servers = implode(',', $save_hosts);
        } else {
            $pref->ldap_servers = null;
            $pref->ldap_port = null;
            $pref->ldap_search_base = null;
            $pref->ldap_domain = null;
            $pref->ldap_bind_user = null;
            $pref->ldap_bind_password = null;
            $pref->ldap_ssl = null;
        }
        // This boolean for ldap cannot be null.
        $pref->ldap_enabled = $managing_ldap;
        // Save the preferences
        $pref->save();
        // Return with a status message
        $request->session()->flash('alert-success', 'Preferences Saved');
        return redirect()->route('preferences');
    }
}
