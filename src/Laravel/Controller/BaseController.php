<?php

namespace zxf\Laravel\Controller;

use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Gate;
use zxf\Laravel\Controller\Trait\ControllerTrait;

/**
 * 扩展基础控制器
 *  继承了此类的控制器，可以在构造函数执行之后，在被调用方法之前，执行初始化方法initialize，initialize方法支持依赖注入
 */
class BaseController extends Controller
{
    use ControllerTrait;

    /**
     * 策略判断(默认使用User模型) 例如： $this->gate::authorize('update', $photo);
     * 设置指定模型的用户判断：$this->gate::forUser(auth('admin')->user())->authorize('update', $article);
     */
    protected string|null|Gate $gate = null;

    public function __construct(Request $request)
    {
        // 此处未加载完中间件, 所以无法使用auth('admin')->check() 等操作

        // 添加一个最后执行的中间件，此时其他中间件已经加载完毕
        $this->middleware(function ($request, $next) {
            // 中间件基本加载完毕
            $this->initHandle($request);
            // 在路由调用方法之前，先调用初始化方法initialize
            // 给控制器新增 initialize 生命周期方法，可用于初始化
            // 甚至可以代替 构造函数，实现依赖注入
            before_calling_methods($this, 'initialize');

            return $next($request);
        });
    }

    // 初始化
    protected function initHandle(Request $request): void
    {
        // 初始化策略类
        $this->gate = Gate::class;
    }
}
