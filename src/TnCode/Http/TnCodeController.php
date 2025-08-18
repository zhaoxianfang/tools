<?php

namespace zxf\TnCode\Http;

use Illuminate\Http\Request;
use zxf\TnCode\TnCode;

class TnCodeController
{
    /**
     * 获取TnCode验证码图片
     */
    public function getImg()
    {
        $tnCode = new TnCode;
        $tnCode->make();
    }

    /**
     * 一验：TnCode验证码
     */
    public function check(Request $request)
    {
        $tncode = new TnCode;
        exit($tncode->check($request->tn_r) ? 'ok' : 'error');
    }
}
