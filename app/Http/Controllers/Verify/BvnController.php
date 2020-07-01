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
use Carbon\Carbon;
use phpseclib\Crypt\RSA as Crypt_RSA;


class BvnController extends Controller
{
    use HasAlerts;


    public function verify(BvnRequest $request) {

        if(isset($request->accountNumber)) {

            $fetch = Http::withHeaders([
                'Authorization' => config('secops.rubies.key'),
                'Content-Type' => 'application/json'
            ])->withOptions([
                    'verify' => false,
                ])->post(config('secops.rubies.root_url').'/nameenquiry', [
                'accountnumber' => $request->accountNumber,
                'bankcode' => $request->bankcode,
            ]);

            if(!isset($fetch)) {
                Log::error($fetch);
                return $this->failedAlert('Name Enquiry service currently unavailable, please try again later.');

            }

            if(isset($fetch->json()["responsemessage"]) && $fetch->json()["responsemessage"] !== "success") {


                return $this->failedAlert('Failed Name Enquiry Request, please send request with valid details or try again later.');

            }



            if(isset($fetch->json()["bvn"]) && !empty($fetch->json()["bvn"])) {
                $bvn = $fetch->json()["bvn"];
            } else {


                return $this->failedAlert('BVN service is currently unavailable for this request, please try again later.');


            }



        } else {
            $bvn = $request->bvn;
        }


      //  $this->rubiesBVN($bvn);

        return $this->smileBVN($bvn);





    }

    public function smileBVN($bvn) {

        $partnerID = config('secops.smile.partner_id');
        $key = config('secops.smile.api_key');


        $fetchsignature = Http::withHeaders([
            'Content-Type' => 'application/json'
        ])->withOptions([
            'verify' => false,
        ])->post(config('secops.encryption.root_url').'/smile/generate/seckey', [
            'apiKey' => $key,
            'partnerId' => $partnerID,
        ])->json();

        $timestamp = $fetchsignature['timestamp'];
        $signature = $fetchsignature['sec_key'];



        $jsonData = [
            "partner_id" => "437",
    "timestamp" => $timestamp,
    "sec_key" => $signature,
    "country" => "NG",
    "id_type" => "BVN",
    "id_number" => $bvn,
    "first_name" => "",
    "middle_name" => "",
    "last_name" => "",
    "phone_number" => "",
    "dob" => "",
    "partner_params" => [
            "job_id" => 'PAT'.Str::uuid(),
      "user_id" => "PAT".rand(),
      "job_type" => 5,
        ],
];


        $response = Http::withHeaders([
            'Content-Type' => 'application/json'
        ])->withOptions([
            'verify' => false,
        ])->post(config('secops.smile.root_url')."/id_verification", $jsonData)->json();



        if(!isset($response['ResultCode'])) {
            Log::error($response);
            return $this->failedAlert('Service currently unavailable, please try again later.');
        }


        if($response['ResultCode'] != "1012") {
            Log::error($response);
            return $this->failedAlert('Service currently unavailable, please try again later.');
        }


        Log::info($response);


        $data = [
            'bvn' => $response['FullData']['BVN'],
            'firstname' => ucwords(strtolower($response['FullData']['FirstName'])),
            'middlename' => ucwords(strtolower($response['FullData']['MiddleName'])),
            'lastname' => ucwords(strtolower($response['FullData']['LastName'])),
            'nameoncard' => ucwords(strtolower($response['FullData']['nameOnCard'])),
            'dateofbirth' => ucwords(strtolower($response['FullData']['DateOfBirth'])),
            'maritalStatus' => ucwords(strtolower($response['FullData']['maritalStatus'])),
            'phonenumber' => $response['FullData']['PhoneNumber1'],
            'phonenumber2' => empty($response['FullData']['PhoneNumber2']) ? 'Not Available' : $response['FullData']['PhoneNumber2'],
            'email' => empty($response['FullData']['email']) ? 'Not Available' : $response['FullData']['email'],
            'gender' => ucfirst($response['FullData']['Gender']),
            'nationality' => $response['FullData']['nationality'],
            'enrollmentBranch' => $response['FullData']['enrollmentBranch'],
            'stateofresidence' => ucfirst(strtolower($response['FullData']['stateOfResidence'])),
            'lgaofResidence' => ucfirst(strtolower($response['FullData']['lgaOfResidence'])),
            'residentialAddress' => ucwords(strtolower($response['FullData']['residentialAddress'])),
            'base64image' => $response['FullData']['ImageBase64'],
        ];


        return $this->successAlert('Request was successful', $data);

    }

    public function rubiesBVN($bvn) {
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

        dd($fetchDetails);

        Log::info($fetchDetails);


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


    public function rsa_encrypt($data, $publickey) {


        $rsa = new Crypt_RSA();
        $rsa->loadKey($publickey);
        $rsa->setEncryptionMode(2);
        $output = $rsa->encrypt($data);

        return $output;
    }

}
