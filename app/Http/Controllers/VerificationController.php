<?php

namespace App\Http\Controllers;

use App\Http\Requests;
use App\VerificationRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Auth;
use App\User;
use App\UUD\Client\UUDClient;

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
            'ssn' => 'required|max:4|min:4',
            'dob' => 'required|max:255',
        ];

        // Validate the incoming info
        $validator = Validator::make($data, $rules);
        // If we have errors return to the last page and show the errors
        if ($validator->fails()) {
            return Redirect::back()->withErrors($validator, 'verify')->withInput($data);
        }

        $veriRequest = new VerificationRequest();
        $veriRequest->user_id = Auth::user()->id;
        $veriRequest->verified = false;
        $veriRequest->request_username = $data['username'];
        $veriRequest->request_ssn = $data['ssn'];
        $veriRequest->request_dob = $data['dob'];

        // Create a new UUD client;
        $client = new UUDClient(Config::get('uud.api_url'), Config::get('uud.api_key'));
        $user_result = $client->get_user_by_username($data['username']);
        if (!$user_result['body']['success']) {
            $request->session()->flash('alert-danger', 'We could not find that username in our records.');
            $veriRequest->save();
            return redirect()->route('verify_fail');
        }
        $remote_id = $user_result['body']['result']['id'];
        $veriRequest->returned_username = $user_result['body']['result']['username'];
        $veriRequest->returned_user_id = $remote_id;

        $ssn_result = $client->get_ssn_by_user($remote_id);
        $remote_ssn = $ssn_result['body']['result'][0]['ssn'];
        $veriRequest->returned_ssn = $remote_ssn;

        if (!$ssn_result['body']['success'] || intval($remote_ssn) != intval($data['ssn'])) {
            $request->session()->flash('alert-danger', 'The SSN provided did not match our records.');
            $veriRequest->save();
            return redirect()->route('verify_fail');
        }

        $dob_result = $client->get_birth_date_by_user($remote_id);
        $remote_dob = $dob_result['body']['result'][0]['birth_date'];
        $veriRequest->returned_dob = $remote_dob;

        if (!$dob_result['body']['success'] || $remote_dob != $data['dob']) {
            $request->session()->flash('alert-danger', 'The DOB provided did not match our records.');
            $veriRequest->save();
            return redirect()->route('verify_fail');
        }

        $veriRequest->verified = true;
        $veriRequest->save();
        return redirect()->route('verify_success');
    }

    /**
     * @return mixed
     */
    public function verifySuccess()
    {
        return view('verify_success');
    }

    /**
     * @return mixed
     */
    public function verifyFail()
    {
        return view('verify_fail');
    }
}
