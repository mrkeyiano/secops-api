<?php

namespace App\Http\Controllers\BlackList;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Http\Requests\Blacklist\UserAgentRequest;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use App\Traits\HasAlerts;


class UserAgentController extends Controller
{
    use HasAlerts;

    public function verify(UserAgentRequest $request) {

        $contents = Storage::disk('dict')->get('blacklist/user_agents.txt');
        $userAgents = explode(PHP_EOL, strtolower($contents));

        //further search optimization

        /* optimize request, at first instance Opens dict file, then uses array search to get index of key
        * any similar request queries redis for key index with specified useragent
         *  so a redis check before a Storage check
         *  this prevent having it query dictionary every time, instead
         * it checks redis with the specified value if key exist
         * then returns true or false, if false runs a check on storage as per usual
        */

        if(array_search(strtolower($request->useragent), $userAgents) === false) {
            return $this->successAlert('This UserAgent is not blacklisted');
        }

        return $this->failedAlert('This UserAgent has been blacklisted');
    }
}
