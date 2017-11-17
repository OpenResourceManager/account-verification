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
use Illuminate\Support\Str;
use OpenResourceManager\ORM;
use OpenResourceManager\Client\Account as AccountClient;
use Exception;

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
        $key = $prefs->uud_api_key;
        // Load the URL parts
        // Not clean man @todo clean this crap up
        $parts = parse_url($prefs->uud_api_url);
        $version = 1;
        foreach (explode('/', $parts['path']) as $slug) {
            if (starts_with(strtolower($slug), 'v')) {
                $v = substr($slug, -1);
                if (is_int($v)) {
                    $version = intval($v);
                }
            }
        }
        $useSSL = ($parts['scheme'] == 'https') ? true : false;
        $host = $parts['host'];
        if (isset($parts['port'])) {
            $port = $parts['port'];
        } else {
            $port = $useSSL ? '443' : '80';
        }

        // Build an orm connection
        $orm = new ORM($key, $host, $version, $port, $useSSL);
        //Create an Account Client
        $accountClient = new AccountClient($orm);
        // Request user info based on username
        $responseFromUn = $accountClient->getFromIdentifier($data['identifier']);
        $responseFromId = $accountClient->getFromIdentifier($data['identifier']);

        // Verify that we got a good account from the ID
        if ($responseFromId->code != 200) {
            $request->session()->flash('alert-danger', 'We could not find that ID number in our records.');
            $veriRequest->save();
            return redirect()->route('verify_fail')->with('target', $target);
        }

        // Verify that we got a good account from the Username
        if ($responseFromUn->code != 200) {
            $request->session()->flash('alert-danger', 'We could not find that username in our records.');
            $veriRequest->save();
            return redirect()->route('verify_fail')->with('target', $target);
        }

        // Verify that the user accounts returned from username and ID number are the same
        if ($responseFromId->body->data->id != $responseFromUn->body->data->id) {
            $request->session()->flash('alert-danger', 'This user cannot be verified! There is a mismatch between the username and ID number.');
            $veriRequest->save();
            return redirect()->route('verify_fail')->with('target', $target);
        }

        // Store more info about the request, for audits and analytics
        $veriRequest->returned_username = $responseFromId->body->data->username;
        $veriRequest->returned_identifier = $responseFromId->body->data->identifier;
        $veriRequest->returned_user_id = $responseFromId->body->data->id;
        $veriRequest->returned_ssn = $responseFromId->body->data->ssn;
        $veriRequest->returned_dob = $responseFromId->body->data->birth_date;
        $veriRequest->save();

        // Store the target user info so it can be passed onto the next view
        $target['name_first'] = $responseFromId->body->data->name_first;
        $target['name_last'] = $responseFromId->body->data->name_last;

        // Store the remote primary key, to make requests faster
        $target['api_user_id'] = $responseFromId->body->data->id;

        // Verify the the remote SSN matches the SSN that was supplied
        if (strval($responseFromId->body->data->ssn) != strval($data['ssn'])) {
            $request->session()->flash('alert-danger', 'The social security number that was provided did not match our records.');
            $veriRequest->save();
            return redirect()->route('verify_fail')->with('target', $target);
        }

        // Verify the the remote DOB matches the DOB that was supplied
        if ($responseFromId->body->data->birth_date != $data['dob']) {
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
        $prefs = Preference::firstOrFail();
        // Get the target info
        $target = session('target');

        if ($prefs->ldap_enabled) {

            // Generate a unique random token
            do {
                $token = Str::quickRandom(64);
                $exists = LDAPPasswordReset::where('token', $token)->first();
            } while (!empty($exists));

            // Generate a new password reset request
            LDAPPasswordReset::create([
                'user_id' => Auth::user()->id,
                'api_user_id' => $target['api_user_id'],
                'request_username' => $target['username'],
                'name' => $target['name_first'] . ' ' . $target['name_last'],
                'token' => $token,
                'pending' => true
            ]);
            // Show the view with password reset info
            return view('verify_success', ['target' => $target, 'token' => $token, 'can_reset_password' => $prefs->ldap_enabled]);
        } else {
            // Show the view without password reset info
            return view('verify_success', ['target' => $target, 'can_reset_password' => $prefs->ldap_enabled]);
        }
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
