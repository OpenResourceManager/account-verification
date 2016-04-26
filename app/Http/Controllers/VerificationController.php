<?php

namespace App\Http\Controllers;

use App\Http\Requests;
use App\LDAPPasswordReset;
use App\Preference;
use App\VerificationRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Auth;
use App\UUD\Client\UUDClient;
use Illuminate\Support\Str;

class VerificationController extends Controller
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
     * @return mixed
     */
    public function index()
    {
        return view('home');
    }


    public function verify(Request $request)
    {
        // Store the request data in a var
        $data = $request->all();
        // Define verification rules
        $rules = [
            'username' => 'required|max:255',
            'identifier' => 'required|max:7|min:7',
            'ssn' => 'required|max:4|min:4',
            'dob' => 'required|max:255',
        ];

        // Validate the incoming info
        $validator = Validator::make($data, $rules);
        // If we have errors return to the last page and show the errors
        if ($validator->fails()) {
            return Redirect::back()->withErrors($validator, 'verify')->withInput($data);
        }

        // Store target info
        $target['username'] = $data['username'];
        $target['identifier'] = $data['identifier'];

        // Store info about the request, for audits and analytics
        $veriRequest = new VerificationRequest;
        $veriRequest->user_id = Auth::user()->id;
        $veriRequest->verified = false; // False at this time
        $veriRequest->request_username = $data['username'];
        $veriRequest->request_identifier = $data['identifier'];
        $veriRequest->request_ssn = $data['ssn'];
        $veriRequest->request_dob = $data['dob'];

        // Load our preferences
        $prefs = Preference::firstOrFail();

        // Create a new UUD client;
        $client = new UUDClient($prefs->uud_api_url, $prefs->uud_api_key);

        // Request user info based on username
        $user_result = $client->get_user_by_username($data['username']);
        if (!$user_result['body']['success']) {
            $request->session()->flash('alert-danger', 'We could not find that username in our records.');
            $veriRequest->save();
            return redirect()->route('verify_fail')->with('target', $target);
        }

        // Request user info based on ID number
        $user_result2 = $client->get_user_by_identifier($data['identifier']);
        if (!$user_result2['body']['success']) {
            $request->session()->flash('alert-danger', 'We could not find that ID number in our records.');
            $veriRequest->save();
            return redirect()->route('verify_fail')->with('target', $target);
        }

        // Verify that the user accounts returned from username and ID number are the same
        if ($user_result['body']['result']['id'] != $user_result2['body']['result']['id']) {
            $request->session()->flash('alert-danger', 'This user cannot be verified! There is a mismatch between the username and ID number.');
            $veriRequest->save();
            return redirect()->route('verify_fail')->with('target', $target);
        }

        // Store the target user info so it can be passed onto the next view
        $target = $user_result['body']['result'];
        // Store the remote primary key, to make requests faster
        $remote_id = $user_result['body']['result']['id'];
        $target['api_user_id'] = $remote_id;
        // Store more info about the request, for audits and analytics
        $veriRequest->returned_username = $user_result['body']['result']['username'];
        $veriRequest->returned_identifier = $user_result2['body']['result']['identifier'];
        $veriRequest->returned_user_id = $remote_id;

        // Get the SSN by user's primary key
        $ssn_result = $client->get_ssn_by_user($remote_id);
        $remote_ssn = $ssn_result['body']['result'][0]['ssn'];
        $veriRequest->returned_ssn = $remote_ssn;

        // Verify the the remote SSN matches the SSN that was supplied
        if (!$ssn_result['body']['success'] || intval($remote_ssn) != intval($data['ssn'])) {
            $request->session()->flash('alert-danger', 'The social security number that was provided did not match our records.');
            $veriRequest->save();
            return redirect()->route('verify_fail')->with('target', $target);
        }

        // Get the DOB by the user's primary key
        $dob_result = $client->get_birth_date_by_user($remote_id);
        $remote_dob = $dob_result['body']['result'][0]['birth_date'];
        $veriRequest->returned_dob = $remote_dob;

        // Verify the the remote DOB matches the DOB that was supplied
        if (!$dob_result['body']['success'] || $remote_dob != $data['dob']) {
            $request->session()->flash('alert-danger', 'The date of birth that was provided did not match our records.');
            $veriRequest->save();
            return redirect()->route('verify_fail')->with('target', $target);
        }

        // If we've made it this far the user is verified.
        $veriRequest->verified = true;
        // Save the verification request for our records.
        $veriRequest->save();
        // Redirect to the success page
        return redirect()->route('verify_success')->with('target', $target);
    }

    /**
     * @return mixed
     */
    public function verifySuccess(Request $request)
    {
        // Generate a unique random token
        do {
            $token = Str::quickRandom(64);
            $exists = LDAPPasswordReset::where('token', $token)->first();
        } while (!empty($exists));

        // Get the target info
        $target = session('target');

        // Generate a new password reset request
        LDAPPasswordReset::create([
            'user_id' => Auth::user()->id,
            'api_user_id' => $target['api_user_id'],
            'username' => $target['username'],
            'name' => $target['name_first'] . ' ' . $target['name_last'],
            'token' => $token,
            'pending' => true
        ]);

        // Show the view
        return view('verify_success', ['target' => $target, 'token' => $token]);
    }

    /**
     * @return mixed
     */
    public function verifyFail(Request $request)
    {
        $target = session('target');
        return view('verify_fail', ['target', $target]);
    }
}
