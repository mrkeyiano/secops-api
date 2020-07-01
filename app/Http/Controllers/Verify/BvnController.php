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


      //  $this->rubiesBVN($bvn);

        $this->smileBVN($bvn);





    }

    public function smileBVN($bvn) {

        $partnerID = 437;
        $timestamp = Carbon::now()->timestamp;
        $toHash = $partnerID . ':' . $timestamp;
        $hash256 = hash('sha256', $toHash);
        $key = "LS0tLS1CRUdJTiBQVUJMSUMgS0VZLS0tLS0KTUlHZk1BMEdDU3FHU0liM0RRRUJBUVVBQTRHTkFEQ0JpUUtCZ1FEdEd3Qk5SRk1IUGhyN1RwQUpRNUVSbXVGaAp2ek5yTTVpMGpzbWw4Mk84RE9STVlzc0ZEMkUzU05RNmkxZmRjd1RteE9xODU4dlg0Y3BOR3lOQmNmWG1JQ21yCmJwRVhlcHZXQUw1Q3RFMDNyQUtGTVErUzIyNUZIU21sbnJaY2pFdlNJK1k4Y2tLVllFbmJJdGRmMVBUWDVya1cKRTZocjE1UW9rUmFuTjlKRFd3SURBUUFCCi0tLS0tRU5EIFBVQkxJQyBLRVktLS0tLQo=";



   //     $ok= openssl_public_encrypt($hash256,$encrypted,base64_decode($key));

     //   $result = openssl_verify($hash256, $encrypted, base64_decode($key));

        // $pkEncrypted2 = base64_encode($this->ssl_encrypt($hash256, 'public', base64_decode($key)));
        //  $pkEncrypted = base64_encode($this->rsa_encrypt($hash256,base64_decode($key)));

         // dd($pkEncrypted,$pkEncrypted2, $timestamp, Carbon::now()->timestamp);

       // $signature = $pkEncrypted . "|" . $hash256;


        $fetchsignature = Http::get('127.0.0.1:2021/smile/encrypt');

        dd($fetchsignature);

        $timestamp = $fetchsignature->timestamp;
        $signature = $fetchsignature->signature;



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
            "job_id" => "eegewgewg",
      "user_id" => "3535353dd",
      "job_type" => 5,
        ],
];


        $fetchDetails = Http::withHeaders([
           // 'Authorization' => config('secops.rubies.key'),
            'Content-Type' => 'application/json'
        ])->withOptions([
            'verify' => false,
        ])->post("https://3eydmgh10d.execute-api.us-west-2.amazonaws.com/test/id_verification", $jsonData);

        dd($fetchDetails);


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
    public function ssl_encrypt($source,$type,$key){

//        $maxlength=117;
            $output='';
//        while($source){
//            $input= substr($source,0,$maxlength);
//            $source=substr($source,$maxlength);
//            if($type=='private'){
//                $ok= openssl_private_encrypt($input,$encrypted,$key);
//            }else{
//                $ok= openssl_public_encrypt($input,$encrypted,$key);
//            }


      //  $result = openssl_verify($data, $raw_signature, $key);
            $ok= openssl_public_encrypt($source,$encrypted,$key);

            $output.=$encrypted;
        //}
        return $output;
    }

}
