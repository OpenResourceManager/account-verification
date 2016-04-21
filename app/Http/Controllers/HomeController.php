<?php

namespace App\Http\Controllers;

use App\Http\Requests;
use App\User;
use Illuminate\Http\Request;
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
     * Post user creation data
     *
     * @param array $data
     */
    public function postNewUser(Request $request)
    {

        $data = $request->all();

        $validator = Validator::make($data, [
            'name' => 'required|max:255',
            'email' => 'required|email|max:255|unique:users'
        ]);

        if ($validator->fails()) {
            return redirect('users/new')->withErrors($validator)->withInput();
        }

        $data['password'] = bcrypt(Str::quickRandom(8));

        $data['isAdmin'] = isset($data['isAdmin']) ? true : false;

        User::create($data);

        $request->session()->flash('alert-success', 'User was successful added!');

        return redirect('new/user');
    }
}
