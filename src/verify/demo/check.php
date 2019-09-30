<?php
require_once dirname(__FILE__) . '/ImgCode.php';
$tn = ImgCode::instance();
if ($tn->check()) {
    $_SESSION['tncode_check'] = 'ok';
    // echo "ok";
    echo json_encode(['code' => 200, 'msg' => '成功']);
} else {
    $_SESSION['tncode_check'] = 'error';
    // echo "error";
    echo json_encode(['code' => 403, 'msg' => '失败']);
}
