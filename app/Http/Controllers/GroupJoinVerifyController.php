<?php

namespace App\Http\Controllers;

use App\Common\IP;
use Illuminate\Http\Response;

class GroupJoinVerifyController extends BaseController
{
    /**
     * @return Response
     */
    public function groupJoinVerify(): Response
    {
        return $this->plain(IP::getClientIpInfos());
    }
}
