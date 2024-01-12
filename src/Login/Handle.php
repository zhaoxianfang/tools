<?php

namespace zxf\Login;

interface Handle
{
    public function authorization();

    public function getAccessToken();

    public function getUserInfo($access_token = '');
}
