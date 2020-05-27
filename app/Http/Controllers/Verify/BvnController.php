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

        $reference = 'secops_'.Str::uuid();

        $fetchDetails = Http::withHeaders([
            'Authorization' => config('secops.rubies_open_api.key'),
            'Content-Type' => 'application/json'
        ])->post(config('secops.rubies_open_api.root_url').'/verifybvn', [
            'bvn' => $request->bvn,
            'reference' => $reference,
        ]);

        $response = $fetchDetails->json();
        Log::info($response);



        if(!isset($response['responsecode']) || $response['responsecode'] != "00") {
            Log::error($response);
            return $this->failedAlert('Service Unavailable');
        }



        $data = [
            'firstname' => $response['data']['firstName'],
            'middlename' => $response['data']['middleName'],
            'lastname' => $response['data']['lastName'],
            'gender' => $response['data']['gender'],
            'dateofbirth' => $response['data']['dateOfBirth'],
            'phonenumber' => $response['phoneNumber'],
            'base64image' => $response['base64Image'],
        ];


        return $this->successAlert('Request was successful', $data);




    }
    //
}
