<?php

namespace zxf\Qqlogin;

interface Handle
{
    public function authorization();

    public function getAccessToken();

    public function getUserInfo($access_token);
}
