<?php

namespace App\Http\Controllers;

use App\Http\Requests;
use App\Preference;
use App\User;
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
        $this->middleware('auth');
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
        // Get current user
        $user = Auth::user();
        // Get token repo
        $tokens = Password::getRepository();
        // Generate a token for the user
        $token = $tokens->create($user);
        // Send a recovery notice
        Mail::send('auth.emails.password_change', ['user' => $user, 'token' => $token], function ($m) use ($user) {
            $m->from('no-reply@sage.edu', 'User Verification');
            $m->to($user->email, $user->name)->subject('Password Change');
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
        // Send a welcome email
        Mail::send('auth.emails.welcome', ['user' => $user, 'token' => $token], function ($m) use ($user) {
            $m->from('no-reply@sage.edu', 'User Verification');
            $m->to($user->email, $user->name)->subject('Welcome!');
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
        // Send a recovery notice
        Mail::send('auth.emails.recovery', ['user' => $user, 'token' => $token], function ($m) use ($user) {
            $m->from('no-reply@sage.edu', 'User Verification');
            $m->to($user->email, $user->name)->subject('Account Recovery');
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
        // Validator rules
        $rules = [
            'application_name' => 'required|max:255',
            'application_email_address' => 'required|email|max:255',
        ];
        $managing_ldap = false;
        // If any of the ldap settings are filled out, require all of them
        if (!empty($data['ldap_servers']) ||
            !empty($data['ldap_port']) ||
            !empty($data['ldap_search_base']) ||
            !empty($data['ldap_domain']) ||
            !empty($data['ldap_bind_user_dn']) ||
            !empty($data['ldap_bind_password'])
        ) {
            $managing_ldap = true;
            $rules['ldap_servers'] = 'required|max:255';
            $rules['ldap_port'] = 'required|integer';
            $rules['ldap_search_base'] = 'required|max:255';
            $rules['ldap_domain'] = 'required|max:255';
            $rules['ldap_bind_user_dn'] = 'required|max:255';
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

        // Are we managing LDAP?
        if ($managing_ldap) {
            $pref->ldap_servers = $data['ldap_servers'];
            $pref->ldap_port = $data['ldap_port'];
            $pref->ldap_search_base = $data['ldap_search_base'];
            $pref->ldap_domain = $data['ldap_domain'];
            $pref->ldap_bind_user_dn = $data['ldap_bind_user_dn'];
            $pref->ldap_bind_password = $data['ldap_bind_password'];
            $pref->ldap_ssl = isset($data['ldap_ssl']) ? true : false;
        }

        // Save the preferences
        $pref->save();
        // Return with a status message
        $request->session()->flash('alert-success', 'Preferences Saved');
        return redirect()->route('preferences');
    }
}
