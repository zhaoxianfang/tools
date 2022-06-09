<?php

namespace zxf\login;

interface Handle
{
    public function authorization();

    public function getAccessToken();

    public function getUserInfo($access_token = '');
}
