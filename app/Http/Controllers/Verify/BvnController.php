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

        $requestid = rand().rand();


        $fetchDetails = Http::withHeaders([
            'Authorization' => config('secops.rubies.key'),
            'Content-Type' => 'application/json'
        ])->post(config('secops.rubies.rubies_bvn_checker'), [
            'bvn' => $request->bvn,
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
            'title' => $response['data']['title'],
            'firstname' => $response['data']['firstName'],
            'middlename' => $response['data']['middleName'],
            'lastname' => $response['data']['lastName'],
            'phonenumber' => $response['phoneNumber'],
            'second_phonenumber' => $response['data']['phoneNumber2'],
            'email' => $response['data']['email'],
            'gender' => $response['data']['gender'],
            'dateofbirth' => $response['data']['dateOfBirth'],
            'stateofresidence' => $response['data']['stateOfResidence'],
            'lgaofResidence' => $response['data']['lgaOfResidence'],
            'residentialAddress' => $response['data']['residentialAddress'],
            'base64image' => $response['data']['base64Image'],
        ];


        return $this->successAlert('Request was successful', $data);




    }
    //
}
