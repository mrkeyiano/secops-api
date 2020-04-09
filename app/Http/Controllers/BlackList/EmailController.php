<?php

namespace App\Http\Controllers\BlackList;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use App\Http\Requests\Blacklist\EmailRequest;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use App\Traits\HasAlerts;


class EmailController extends Controller
{
    use HasAlerts;

    public function verify(EmailRequest $request) {


        $contents = Storage::disk('dict')->get('blacklist/email_providers.txt');
        $emailProviders = explode(PHP_EOL, strtolower($contents));
        $fetchProvider = explode('@', $request->email);
        $emailProvider = array_pop($fetchProvider);


        if(array_search($emailProvider, $emailProviders) === false) {
            return $this->successAlert('This Email Provider is not blacklisted');
        }

        return $this->failedAlert('This Email Provider has been blacklisted');
    }
}
