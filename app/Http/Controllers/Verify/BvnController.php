<?php

namespace App\Http\Controllers\Verify;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Http\Requests\Verify\BvnRequest;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Validator;
use App\Traits\HasAlerts;


class BvnController extends Controller
{
    use HasAlerts;


    public function verify(BvnRequest $request) {


       // $reference = 'secops_'.Str::uuid();


        if(isset($request->accountNumber)) {

            $fetchbvn = Http::withHeaders([
                'Authorization' => config('secops.rubies.key'),
                'Content-Type' => 'application/json'
            ])->withOptions([
                    'verify' => false,
                ])->post(config('secops.rubies.root_url').'/nameenquiry', [
                'accountnumber' => $request->accountNumber,
                'bankcode' => $request->bankcode,
            ])->json()['bvn'];


            if(isset($fetchbvn)) {
                $bvn = $fetchbvn;
            } else {
                return $this->failedAlert('Name Enquiry service currently unavailable, please try again later.');


            }



        } else {
            $bvn = $request->bvn;
        }




        $requestid = rand().rand();


        $fetchDetails = Http::withHeaders([
            'Authorization' => config('secops.rubies.key'),
            'Content-Type' => 'application/json'
        ])->withOptions([
            'verify' => false,
        ])->post(config('secops.rubies.rubies_bvn_checker'), [
            'bvn' => $bvn,
            'requestid' => $requestid,
        ]);


        $response = $fetchDetails->json();


        Log::info($response);



        if(!isset($response['responsecode'])) {
            Log::error($response);
            return $this->failedAlert('Service currently unavailable, please try again later.');
        }

        if($response['responsecode'] === "91") {
            return $this->failedAlert('Bvn is invalid, please check and try again.');

        }


        if($response['responsecode'] !== "200") {
            Log::error($response);

            return $this->failedAlert('Service currently unavailable, please try again later.');

        }


        $data = [
            'bvn' => $response['data']['bvn'],
            'firstname' => ucwords(strtolower($response['data']['firstName'])),
            'middlename' => ucwords(strtolower($response['data']['middleName'])),
            'lastname' => ucwords(strtolower($response['data']['lastName'])),
            'maritalStatus' => ucwords(strtolower($response['data']['maritalStatus'])),
            'phonenumber' => $response['phoneNumber'],
            'phonenumber2' => $response['data']['phoneNumber2'],
            'email' => $response['data']['email'],
            'gender' => ucfirst($response['data']['gender']),
            'nationality' => $response['data']['nationality'],
            'enrollmentBranch' => $response['data']['enrollmentBranch'],
            'stateofresidence' => ucfirst(strtolower($response['data']['stateOfResidence'])),
            'lgaofResidence' => ucfirst(strtolower($response['data']['lgaOfResidence'])),
            'residentialAddress' => ucwords(strtolower($response['data']['residentialAddress'])),
            'base64image' => $response['data']['base64Image'],
        ];


        return $this->successAlert('Request was successful', $data);




    }
    //
}
