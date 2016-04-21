<?php

namespace App\Http\Controllers;

use App\Http\Requests;
use App\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
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
     * Show the application dashboard.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        return view('home');
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

    public function saveProfile()
    {

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
        if ($validator->fails()) return redirect('users/new')->withErrors($validator)->withInput();
        // Generate a random password
        $data['password'] = bcrypt(Str::quickRandom(8));
        // Determine if the target user should be an admin
        $data['isAdmin'] = isset($data['isAdmin']) ? true : false;
        // Check to see if the user is trashed and if so, restore the user
        $user = User::onlyTrashed()->where('email', $data['email'])->first();
        if ($user) $user->restore();
        // Create the user/update the user
        User::updateOrCreate(['email' => $data['email']], $data);
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
}
