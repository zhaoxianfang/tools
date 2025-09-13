<?php

namespace zxf\Laravel\Controller\Trait;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;

trait ControllerTrait
{
    /**
     * 返回上一页, 并带上错误信息
     *
     * @param  mixed  $errors  错误信息「字符串、数组、Exception」
     */
    public function backWithError(mixed $errors = '出错啦!'): RedirectResponse
    {
        return redirect()->back()->withInput()->withErrors($errors);
    }

    /**
     * 返回上一页, 并带上提示信息
     *
     * @param  string  $info  提示信息「字符串」
     * @return RedirectResponse
     */
    public function backWithSuccess(string $info = ''): RedirectResponse
    {
        return redirect()->back()->withInput()->with(['success' => $info]);
    }

    public function json(array $data = [], int $status = 200, string $jumpUrl = '', $wait = 3): JsonResponse
    {
        $data['code'] = empty($data['code']) ? $status : $data['code'];
        $data['message'] = empty($data['message']) ? '操作成功' : $data['message'];

        if (! empty($jumpUrl)) {
            $data['url'] = $jumpUrl;
            $data['wait'] = $wait; // 单位秒
        }

        return response()->json($data, $status);
    }

    public function api_json($data = [], $code = 200, $message = '成功', $status = 200): \Illuminate\Http\JsonResponse
    {
        return response()->json(compact('code', 'message', 'data'), $status);
    }

}
