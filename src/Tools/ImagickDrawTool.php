<?php

namespace zxf\Tools;

use ImagickDraw;
use ImagickPixel;
use Exception;

/**
 * 图像处理 (ImagickDraw)
 *
 * imagick 3.7 版本
 * 文档 https://www.php.net/imagick
 */
class ImagickDrawTool
{
    private ImagickDraw $draw;

    public function __construct()
    {
        if (!extension_loaded('imagick')) {
            throw new Exception('未加载 imagick 扩展.');
        }
        $this->draw = new ImagickDraw();
    }

    /**
     * 设置描边的颜色
     *
     * @param ImagickPixel $strokeColor 描边的颜色
     *
     * @return bool 成功时返回 TRUE
     */
    public function setStrokeColor(ImagickPixel $strokeColor): bool
    {
        return $this->draw->setStrokeColor($strokeColor);
    }

    /**
     * 设置填充的颜色
     *
     * @param ImagickPixel $fillColor 填充的颜色
     *
     * @return bool 成功时返回 TRUE
     */
    public function setFillColor(ImagickPixel $fillColor): bool
    {
        return $this->draw->setFillColor($fillColor);
    }

    /**
     * 对绘图进行仿射变换
     *
     * @param array $affine 指定仿射变换的参数
     *
     * @return bool 成功时返回 TRUE
     */
    public function affine(array $affine): bool
    {
        return $this->draw->affine($affine);
    }

    /**
     * 在指定位置添加注释
     *
     * @param float  $x    注释的 x 坐标
     * @param float  $y    注释的 y 坐标
     * @param string $text 注释的文本内容
     *
     * @return bool 成功时返回 TRUE
     */
    public function annotation(float $x, float $y, string $text): bool
    {
        return $this->draw->annotation($x, $y, $text);
    }

    /**
     * 通过指定的参数绘制弧线
     *
     * @param float $sx 弧线的起始 x 坐标
     * @param float $sy 弧线的起始 y 坐标
     * @param float $ex 弧线的结束 x 坐标
     * @param float $ey 弧线的结束 y 坐标
     * @param float $sd 弧线的起始角度
     * @param float $ed 弧线的结束角度
     *
     * @return bool 成功时返回 TRUE
     */
    public function arc(float $sx, float $sy, float $ex, float $ey, float $sd, float $ed): bool
    {
        return $this->draw->arc($sx, $sy, $ex, $ey, $sd, $ed);
    }

    /**
     * 通过指定的参数绘制贝塞尔曲线
     *
     * @param array $coordinateArray 贝塞尔曲线的坐标数组
     *
     * @return bool 成功时返回 TRUE
     */
    public function bezier(array $coordinateArray): bool
    {
        return $this->draw->bezier($coordinateArray);
    }

    /**
     * 在给定的位置绘制一个圆
     *
     * @param float $ox 圆心的 x 坐标
     * @param float $oy 圆心的 y 坐标
     * @param float $px 圆上一点的 x 坐标
     * @param float $py 圆上一点的 y 坐标
     *
     * @return bool 成功时返回 TRUE
     */
    public function circle(float $ox, float $oy, float $px, float $py): bool
    {
        return $this->draw->circle($ox, $oy, $px, $py);
    }

    /**
     * 清除所有设置的绘图
     *
     * @return bool 成功时返回 TRUE
     */
    public function clear(): bool
    {
        return $this->draw->clear();
    }

    /**
     * 克隆当前的 ImagickDraw 对象
     *
     * @return ImagickDraw 克隆后的 ImagickDraw 对象
     */
    public function clone(): ImagickDraw
    {
        return $this->draw->clone();
    }

    /**
     * 设置描边或填充的颜色
     *
     * @param ImagickPixel $color 设置的颜色
     *
     * @return bool 成功时返回 TRUE
     */
    public function color(ImagickPixel $color): bool
    {
        return $this->draw->color($color);
    }

    /**
     * 在图像中添加注释
     *
     * @param string $comment 注释的内容
     *
     * @return bool 成功时返回 TRUE
     */
    public function comment(string $comment): bool
    {
        return $this->draw->comment($comment);
    }

    /**
     * 将图像组合到 ImagickDraw 对象中
     *
     * @param float $x       组合图像的 x 坐标
     * @param float $y       组合图像的 y 坐标
     * @param int   $compose 组合的模式
     * @param float $scale   缩放比例
     *
     * @return bool 成功时返回 TRUE
     */
    public function composite(float $x, float $y, int $compose, float $scale): bool
    {
        return $this->draw->composite($x, $y, $compose, $scale);
    }

    /**
     * 销毁 ImagickDraw 对象
     *
     * @return void
     */
    public function destroy(): void
    {
        $this->draw->destroy();
    }

    /**
     * 在指定位置绘制椭圆
     *
     * @param float $ox    椭圆中心的 x 坐标
     * @param float $oy    椭圆中心的 y 坐标
     * @param float $rx    椭圆的 x 半轴长度
     * @param float $ry    椭圆的 y 半轴长度
     * @param float $start 椭圆的起始角度
     * @param float $end   椭圆的结束角度
     *
     * @return bool 成功时返回 TRUE
     */
    public function ellipse(float $ox, float $oy, float $rx, float $ry, float $start, float $end): bool
    {
        return $this->draw->ellipse($ox, $oy, $rx, $ry, $start, $end);
    }

    /**
     * 获取剪辑路径
     *
     * @return string 当前的剪辑路径
     */
    public function getClipPath(): string
    {
        return $this->draw->getClipPath();
    }

    /**
     * 获取剪辑规则
     *
     * @return int 当前的剪辑规则
     */
    public function getClipRule(): int
    {
        return $this->draw->getClipRule();
    }

    /**
     * 获取剪辑单元
     *
     * @return int 当前的剪辑单元
     */
    public function getClipUnits(): int
    {
        return $this->draw->getClipUnits();
    }

    /**
     * 获取填充颜色
     *
     * @return ImagickPixel 当前的填充颜色
     */
    public function getFillColor(): ImagickPixel
    {
        return $this->draw->getFillColor();
    }

    /**
     * 获取填充的透明度
     *
     * @return float 当前的填充透明度
     */
    public function getFillOpacity(): float
    {
        return $this->draw->getFillOpacity();
    }

    /**
     * 获取填充规则
     *
     * @return int 当前的填充规则
     */
    public function getFillRule(): int
    {
        return $this->draw->getFillRule();
    }

    /**
     * 获取当前的字体设置
     *
     * @return string 当前的字体设置
     */
    public function getFont(): string
    {
        return $this->draw->getFont();
    }

    /**
     * 获取当前的字体家族
     *
     * @return string 当前的字体家族
     */
    public function getFontFamily(): string
    {
        return $this->draw->getFontFamily();
    }

    /**
     * 获取当前的字体大小
     *
     * @return float 当前的字体大小
     */
    public function getFontSize(): float
    {
        return $this->draw->getFontSize();
    }

    /**
     * 获取当前的字体伸展
     *
     * @return int 当前的字体伸展
     */
    public function getFontStretch(): int
    {
        return $this->draw->getFontStretch();
    }

    /**
     * 获取当前的字体样式
     *
     * @return int 当前的字体样式
     */
    public function getFontStyle(): int
    {
        return $this->draw->getFontStyle();
    }

    /**
     * 获取当前的字体粗细
     *
     * @return int 当前的字体粗细
     */
    public function getFontWeight(): int
    {
        return $this->draw->getFontWeight();
    }

    /**
     * 获取当前的文本对齐方式
     *
     * @return int 当前的文本对齐方式
     */
    public function getGravity(): int
    {
        return $this->draw->getGravity();
    }

    /**
     * 获取当前的描边抗锯齿设置
     *
     * @return bool 当前的描边抗锯齿设置
     */
    public function getStrokeAntialias(): bool
    {
        return $this->draw->getStrokeAntialias();
    }

    /**
     * 获取当前的描边颜色
     *
     * @return ImagickPixel 当前的描边颜色
     */
    public function getStrokeColor(): ImagickPixel
    {
        return $this->draw->getStrokeColor();
    }

    /**
     * 获取当前的描边虚线设置
     *
     * @return array 当前的描边虚线设置
     */
    public function getStrokeDashArray(): array
    {
        return $this->draw->getStrokeDashArray();
    }

    /**
     * 获取当前的描边虚线偏移量
     *
     * @return float 当前的描边虚线偏移量
     */
    public function getStrokeDashOffset(): float
    {
        return $this->draw->getStrokeDashOffset();
    }

    /**
     * 获取当前的描边线端点样式
     *
     * @return int 当前的描边线端点样式
     */
    public function getStrokeLineCap(): int
    {
        return $this->draw->getStrokeLineCap();
    }

    /**
     * 获取当前的描边线连接样式
     *
     * @return int 当前的描边线连接样式
     */
    public function getStrokeLineJoin(): int
    {
        return $this->draw->getStrokeLineJoin();
    }

    /**
     * 获取当前的描边斜接限制
     *
     * @return float 当前的描边斜接限制
     */
    public function getStrokeMiterLimit(): float
    {
        return $this->draw->getStrokeMiterLimit();
    }

    /**
     * 获取当前的描边透明度
     *
     * @return float 当前的描边透明度
     */
    public function getStrokeOpacity(): float
    {
        return $this->draw->getStrokeOpacity();
    }

    /**
     * 获取当前的描边宽度
     *
     * @return float 当前的描边宽度
     */
    public function getStrokeWidth(): float
    {
        return $this->draw->getStrokeWidth();
    }

    /**
     * 获取当前的文本对齐方式
     *
     * @return int 当前的文本对齐方式
     */
    public function getTextAlignment(): int
    {
        return $this->draw->getTextAlignment();
    }

    /**
     * 获取当前的文本抗锯齿设置
     *
     * @return bool 当前的文本抗锯齿设置
     */
    public function getTextAntialias(): bool
    {
        return $this->draw->getTextAntialias();
    }

    /**
     * 获取当前的文本装饰设置
     *
     * @return int 当前的文本装饰设置
     */
    public function getTextDecoration(): int
    {
        return $this->draw->getTextDecoration();
    }

    /**
     * 获取当前的文本编码
     *
     * @return string 当前的文本编码
     */
    public function getTextEncoding(): string
    {
        return $this->draw->getTextEncoding();
    }

    /**
     * 获取当前的文本行间距
     *
     * @return float 当前的文本行间距
     */
    public function getTextInterlineSpacing(): float
    {
        return $this->draw->getTextInterlineSpacing();
    }

    /**
     * 获取当前的文本字间距
     *
     * @return float 当前的文本字间距
     */
    public function getTextInterwordSpacing(): float
    {
        return $this->draw->getTextInterwordSpacing();
    }

    /**
     * 获取当前的文本字距
     *
     * @return float 当前的文本字距
     */
    public function getTextKerning(): float
    {
        return $this->draw->getTextKerning();
    }

    /**
     * 获取当前的文本下划线颜色
     *
     * @return ImagickPixel 当前的文本下划线颜色
     */
    public function getTextUnderColor(): ImagickPixel
    {
        return $this->draw->getTextUnderColor();
    }

    /**
     * 获取当前的矢量图形设置
     *
     * @return string 当前的矢量图形设置
     */
    public function getVectorGraphics(): string
    {
        return $this->draw->getVectorGraphics();
    }

    /**
     * 绘制一条直线
     *
     * @param float $sx 起始点的 x 坐标
     * @param float $sy 起始点的 y 坐标
     * @param float $ex 结束点的 x 坐标
     * @param float $ey 结束点的 y 坐标
     *
     * @return bool 成功时返回 TRUE
     */
    public function line(float $sx, float $sy, float $ex, float $ey): bool
    {
        return $this->draw->line($sx, $sy, $ex, $ey);
    }

    /**
     * 通过指定的参数设置图像遮罩
     *
     * @param float $x           设置遮罩的 x 坐标
     * @param float $y           设置遮罩的 y 坐标
     * @param int   $paintMethod 遮罩的绘制方法
     *
     * @return bool 成功时返回 TRUE
     */
    public function matte(float $x, float $y, int $paintMethod): bool
    {
        return $this->draw->matte($x, $y, $paintMethod);
    }

    /**
     * 关闭当前的路径
     *
     * @return bool 成功时返回 TRUE
     */
    public function pathClose(): bool
    {
        return $this->draw->pathClose();
    }

    /**
     * 从当前点绘制二次贝塞尔曲线到指定点
     *
     * @param float $x1 控制点的 x 坐标
     * @param float $y1 控制点的 y 坐标
     * @param float $x  控制点后终点的 x 坐标
     * @param float $y  控制点后终点的 y 坐标
     *
     * @return bool 成功时返回 TRUE
     */
    public function pathCurveToAbsolute(float $x1, float $y1, float $x, float $y): bool
    {
        return $this->draw->pathCurveToAbsolute($x1, $y1, $x, $y);
    }

    /**
     * 从当前点绘制二次贝塞尔曲线到指定点（绝对坐标）
     *
     * @param float $x 控制点后终点的 x 坐标
     * @param float $y 控制点后终点的 y 坐标
     *
     * @return bool 成功时返回 TRUE
     */
    public function pathCurveToQuadraticBezierAbsolute(float $x, float $y): bool
    {
        return $this->draw->pathCurveToQuadraticBezierAbsolute($x, $y);
    }

    /**
     * 从当前点绘制二次贝塞尔曲线到指定点（相对坐标）
     *
     * @param float $x 控制点后终点的 x 坐标
     * @param float $y 控制点后终点的 y 坐标
     *
     * @return bool 成功时返回 TRUE
     */
    public function pathCurveToQuadraticBezierRelative(float $x, float $y): bool
    {
        return $this->draw->pathCurveToQuadraticBezierRelative($x, $y);
    }

    /**
     * 从当前点绘制平滑的二次贝塞尔曲线到指定点（绝对坐标）
     *
     * @param float $x 控制点后终点的 x 坐标
     * @param float $y 控制点后终点的 y 坐标
     *
     * @return bool 成功时返回 TRUE
     */
    public function pathCurveToQuadraticBezierSmoothAbsolute(float $x, float $y): bool
    {
        return $this->draw->pathCurveToQuadraticBezierSmoothAbsolute($x, $y);
    }

    /**
     * 从当前点绘制平滑的二次贝塞尔曲线到指定点（相对坐标）
     *
     * @param float $x 控制点后终点的 x 坐标
     * @param float $y 控制点后终点的 y 坐标
     *
     * @return bool 成功时返回 TRUE
     */
    public function pathCurveToQuadraticBezierSmoothRelative(float $x, float $y): bool
    {
        return $this->draw->pathCurveToQuadraticBezierSmoothRelative($x, $y);
    }

    /**
     * 从当前点绘制贝塞尔曲线到指定点（相对坐标）
     *
     * @param array $coordinateArray 贝塞尔曲线的坐标数组
     *
     * @return bool 成功时返回 TRUE
     */
    public function pathCurveToRelative(array $coordinateArray): bool
    {
        return $this->draw->pathCurveToRelative($coordinateArray);
    }

    /**
     * 从当前点绘制平滑的贝塞尔曲线到指定点（绝对坐标）
     *
     * @param float $x2 控制点的 x 坐标
     * @param float $y2 控制点的 y 坐标
     * @param float $x  控制点后终点的 x 坐标
     * @param float $y  控制点后终点的 y 坐标
     *
     * @return bool 成功时返回 TRUE
     */
    public function pathCurveToSmoothAbsolute(float $x2, float $y2, float $x, float $y): bool
    {
        return $this->draw->pathCurveToSmoothAbsolute($x2, $y2, $x, $y);
    }

    /**
     * 从当前点绘制平滑的贝塞尔曲线到指定点（相对坐标）
     *
     * @param float $x2 控制点的 x 坐标
     * @param float $y2 控制点的 y 坐标
     * @param float $x  控制点后终点的 x 坐标
     * @param float $y  控制点后终点的 y 坐标
     *
     * @return bool 成功时返回 TRUE
     */
    public function pathCurveToSmoothRelative(float $x2, float $y2, float $x, float $y): bool
    {
        return $this->draw->pathCurveToSmoothRelative($x2, $y2, $x, $y);
    }

    /**
     * 通过指定参数绘制椭圆弧线（相对坐标）
     *
     * @param float $rx              x 轴半径长度
     * @param float $ry              y 轴半径长度
     * @param float $x_axis_rotation x 轴旋转角度
     * @param bool  $large_arc_flag  大弧标志
     * @param bool  $sweep_flag      扫描标志
     * @param float $x               结束点的 x 坐标
     * @param float $y               结束点的 y 坐标
     *
     * @return bool 成功时返回 TRUE
     */
    public function pathEllipticArcRelative(float $rx, float $ry, float $x_axis_rotation, bool $large_arc_flag, bool $sweep_flag, float $x, float $y): bool
    {
        return $this->draw->pathEllipticArcRelative($rx, $ry, $x_axis_rotation, $large_arc_flag, $sweep_flag, $x, $y);
    }

    /**
     * 完成当前的路径
     *
     * @return bool 成功时返回 TRUE
     */
    public function pathFinish(): bool
    {
        return $this->draw->pathFinish();
    }

    /**
     * 从当前点绘制直线到指定点（绝对坐标）
     *
     * @param float $x 结束点的 x 坐标
     * @param float $y 结束点的 y 坐标
     *
     * @return bool 成功时返回 TRUE
     */
    public function pathLineToAbsolute(float $x, float $y): bool
    {
        return $this->draw->pathLineToAbsolute($x, $y);
    }

    /**
     * 从当前点绘制直线到指定点（相对坐标）
     *
     * @param float $x 结束点的 x 坐标
     * @param float $y 结束点的 y 坐标
     *
     * @return bool 成功时返回 TRUE
     */
    public function pathLineToRelative(float $x, float $y): bool
    {
        return $this->draw->pathLineToRelative($x, $y);
    }

    /**
     * 从当前点绘制垂直直线到指定的 y 坐标（绝对坐标）
     *
     * @param float $y 结束点的 y 坐标
     *
     * @return bool 成功时返回 TRUE
     */
    public function pathLineToVerticalAbsolute(float $y): bool
    {
        return $this->draw->pathLineToVerticalAbsolute($y);
    }

    /**
     * 从当前点绘制垂直直线到指定的 y 坐标（相对坐标）
     *
     * @param float $y 结束点的 y 坐标
     *
     * @return bool 成功时返回 TRUE
     */
    public function pathLineToVerticalRelative(float $y): bool
    {
        return $this->draw->pathLineToVerticalRelative($y);
    }

    /**
     * 将当前点移动到指定的点（绝对坐标）
     *
     * @param float $x 结束点的 x 坐标
     * @param float $y 结束点的 y 坐标
     *
     * @return bool 成功时返回 TRUE
     */
    public function pathMoveToAbsolute(float $x, float $y): bool
    {
        return $this->draw->pathMoveToAbsolute($x, $y);
    }

    /**
     * 将当前点移动到指定的点（相对坐标）
     *
     * @param float $x 结束点的 x 坐标
     * @param float $y 结束点的 y 坐标
     *
     * @return bool 成功时返回 TRUE
     */
    public function pathMoveToRelative(float $x, float $y): bool
    {
        return $this->draw->pathMoveToRelative($x, $y);
    }

    /**
     * 开始新的路径
     *
     * @return bool 成功时返回 TRUE
     */
    public function pathStart(): bool
    {
        return $this->draw->pathStart();
    }

    /**
     * 在指定的坐标处绘制一个点
     *
     * @param float $x 点的 x 坐标
     * @param float $y 点的 y 坐标
     *
     * @return bool 成功时返回 TRUE
     */
    public function point(float $x, float $y): bool
    {
        return $this->draw->point($x, $y);
    }

    /**
     * 绘制多边形
     *
     * @param array $coordinates 多边形的坐标数组
     *
     * @return bool 成功时返回 TRUE
     */
    public function polygon(array $coordinates): bool
    {
        return $this->draw->polygon($coordinates);
    }

    /**
     * 绘制折线
     *
     * @param array $coordinates 折线的坐标数组
     *
     * @return bool 成功时返回 TRUE
     */
    public function polyline(array $coordinates): bool
    {
        return $this->draw->polyline($coordinates);
    }

    /**
     * 弹出堆栈顶部的设置
     *
     * @return bool 成功时返回 TRUE
     */
    public function pop(): bool
    {
        return $this->draw->pop();
    }

    /**
     * 弹出剪辑路径的堆栈
     *
     * @return bool 成功时返回 TRUE
     */
    public function popClipPath(): bool
    {
        return $this->draw->popClipPath();
    }

    /**
     * 弹出定义的堆栈
     *
     * @return bool 成功时返回 TRUE
     */
    public function popDefs(): bool
    {
        return $this->draw->popDefs();
    }

    /**
     * 弹出图案的堆栈
     *
     * @return bool 成功时返回 TRUE
     */
    public function popPattern(): bool
    {
        return $this->draw->popPattern();
    }

    /**
     * 推入堆栈一个新的设置
     *
     * @return bool 成功时返回 TRUE
     */
    public function push(): bool
    {
        return $this->draw->push();
    }

    /**
     * 推入一个剪辑路径到堆栈
     *
     * @param string $clipMaskId 剪辑路径的 ID
     *
     * @return bool 成功时返回 TRUE
     */
    public function pushClipPath(string $clipMaskId): bool
    {
        return $this->draw->pushClipPath($clipMaskId);
    }

    /**
     * 推入定义到堆栈
     *
     * @param string $id         定义的 ID
     * @param string $definition 定义的内容
     *
     * @return bool 成功时返回 TRUE
     */
    public function pushDefs(string $id, string $definition): bool
    {
        return $this->draw->pushDefs($id, $definition);
    }

    /**
     * 推入图案到堆栈
     *
     * @param string $patternId 图案的 ID
     * @param float  $x         x 轴的偏移量
     * @param float  $y         y 轴的偏移量
     * @param float  $width     图案的宽度
     * @param float  $height    图案的高度
     *
     * @return bool 成功时返回 TRUE
     */
    public function pushPattern(string $patternId, float $x, float $y, float $width, float $height): bool
    {
        return $this->draw->pushPattern($patternId, $x, $y, $width, $height);
    }

    /**
     * 绘制矩形
     *
     * @param float $x1 第一个顶点的 x 坐标
     * @param float $y1 第一个顶点的 y 坐标
     * @param float $x2 第二个顶点的 x 坐标
     * @param float $y2 第二个顶点的 y 坐标
     *
     * @return bool 成功时返回 TRUE
     */
    public function rectangle(float $x1, float $y1, float $x2, float $y2): bool
    {
        return $this->draw->rectangle($x1, $y1, $x2, $y2);
    }

    /**
     * 渲染当前的矢量图形
     *
     * @return bool 成功时返回 TRUE
     */
    public function render(): bool
    {
        return $this->draw->render();
    }

    /**
     * 重置矢量图形
     *
     * @return bool 成功时返回 TRUE
     */
    public function resetVectorGraphics(): bool
    {
        return $this->draw->resetVectorGraphics();
    }

    /**
     * 设置旋转角度
     *
     * @param float $angle 旋转角度
     *
     * @return bool 成功时返回 TRUE
     */
    public function rotate(float $angle): bool
    {
        return $this->draw->rotate($angle);
    }

    /**
     * 绘制圆角矩形
     *
     * @param float $x1 左上角的 x 坐标
     * @param float $y1 左上角的 y 坐标
     * @param float $x2 右下角的 x 坐标
     * @param float $y2 右下角的 y 坐标
     * @param float $rx x 轴的半径
     * @param float $ry y 轴的半径
     *
     * @return bool 成功时返回 TRUE
     */
    public function roundRectangle(float $x1, float $y1, float $x2, float $y2, float $rx, float $ry): bool
    {
        return $this->draw->roundRectangle($x1, $y1, $x2, $y2, $rx, $ry);
    }

    /**
     * 设置比例
     *
     * @param float $x 水平方向的比例
     * @param float $y 垂直方向的比例
     *
     * @return bool 成功时返回 TRUE
     */
    public function scale(float $x, float $y): bool
    {
        return $this->draw->scale($x, $y);
    }

    /**
     * 设置剪辑路径
     *
     * @param string $clipMask 剪辑路径
     *
     * @return bool 成功时返回 TRUE
     */
    public function setClipPath(string $clipMask): bool
    {
        return $this->draw->setClipPath($clipMask);
    }

    /**
     * 设置剪辑规则
     *
     * @param int $fillRule 剪辑规则
     *
     * @return bool 成功时返回 TRUE
     */
    public function setClipRule(int $fillRule): bool
    {
        return $this->draw->setClipRule($fillRule);
    }

    /**
     * 设置剪辑单元
     *
     * @param int $clipUnits 剪辑单元
     *
     * @return bool 成功时返回 TRUE
     */
    public function setClipUnits(int $clipUnits): bool
    {
        return $this->draw->setClipUnits($clipUnits);
    }

    /**
     * 设置填充透明度
     *
     * @param float $alpha 透明度
     *
     * @return bool 成功时返回 TRUE
     */
    public function setFillAlpha(float $alpha): bool
    {
        return $this->draw->setFillAlpha($alpha);
    }

    /**
     * 设置填充透明度
     *
     * @param float $opacity 透明度
     *
     * @return bool 成功时返回 TRUE
     */
    public function setFillOpacity(float $opacity): bool
    {
        return $this->draw->setFillOpacity($opacity);
    }

    /**
     * 设置填充图案的URL
     *
     * @param string $fillUrl 填充图案的URL
     *
     * @return bool 成功时返回 TRUE
     */
    public function setFillPatternURL(string $fillUrl): bool
    {
        return $this->draw->setFillPatternURL($fillUrl);
    }

    /**
     * 设置填充规则
     *
     * @param int $fillRule 填充规则
     *
     * @return bool 成功时返回 TRUE
     */
    public function setFillRule(int $fillRule): bool
    {
        return $this->draw->setFillRule($fillRule);
    }

    /**
     * 设置字体
     *
     * @param string $font 字体
     *
     * @return bool 成功时返回 TRUE
     */
    public function setFont(string $font): bool
    {
        return $this->draw->setFont($font);
    }

    /**
     * 设置字体家族
     *
     * @param string $fontFamily 字体家族
     *
     * @return bool 成功时返回 TRUE
     */
    public function setFontFamily(string $fontFamily): bool
    {
        return $this->draw->setFontFamily($fontFamily);
    }

    /**
     * 设置字体大小
     *
     * @param float $fontSize 字体大小
     *
     * @return bool 成功时返回 TRUE
     */
    public function setFontSize(float $fontSize): bool
    {
        return $this->draw->setFontSize($fontSize);
    }

    /**
     * 设置字体拉伸
     *
     * @param int $fontStretch 字体拉伸
     *
     * @return bool 成功时返回 TRUE
     */
    public function setFontStretch(int $fontStretch): bool
    {
        return $this->draw->setFontStretch($fontStretch);
    }

    /**
     * 设置字体样式
     *
     * @param int $fontStyle 字体样式
     *
     * @return bool 成功时返回 TRUE
     */
    public function setFontStyle(int $fontStyle): bool
    {
        return $this->draw->setFontStyle($fontStyle);
    }

    /**
     * 设置字体粗细
     *
     * @param int $fontWeight 字体粗细
     *
     * @return bool 成功时返回 TRUE
     */
    public function setFontWeight(int $fontWeight): bool
    {
        return $this->draw->setFontWeight($fontWeight);
    }

    /**
     * 设置重心
     *
     * @param int $gravity 重心
     *
     * @return bool 成功时返回 TRUE
     */
    public function setGravity(int $gravity): bool
    {
        return $this->draw->setGravity($gravity);
    }

    /**
     * 设置分辨率
     *
     * @param float $x 分辨率的 x 值
     * @param float $y 分辨率的 y 值
     *
     * @return bool 成功时返回 TRUE
     */
    public function setResolution(float $x, float $y): bool
    {
        return $this->draw->setResolution($x, $y);
    }

    /**
     * 设置描边透明度
     *
     * @param float $alpha 透明度
     *
     * @return bool 成功时返回 TRUE
     */
    public function setStrokeAlpha(float $alpha): bool
    {
        return $this->draw->setStrokeAlpha($alpha);
    }

    /**
     * 设置描边的抗锯齿设置
     *
     * @param bool $strokeAntialias 描边的抗锯齿设置
     *
     * @return bool 成功时返回 TRUE
     */
    public function setStrokeAntialias(bool $strokeAntialias): bool
    {
        return $this->draw->setStrokeAntialias($strokeAntialias);
    }


    /**
     * 设置描边的虚线模式
     *
     * @param array $dashArray 描边的虚线模式数组
     *
     * @return bool 成功时返回 TRUE
     */
    public function setStrokeDashArray(array $dashArray): bool
    {
        return $this->draw->setStrokeDashArray($dashArray);
    }

    /**
     * 设置描边的虚线偏移量
     *
     * @param float $dashOffset 虚线偏移量
     *
     * @return bool 成功时返回 TRUE
     */
    public function setStrokeDashOffset(float $dashOffset): bool
    {
        return $this->draw->setStrokeDashOffset($dashOffset);
    }

    /**
     * 设置描边的线端点样式
     *
     * @param int $lineCap 线端点样式
     *
     * @return bool 成功时返回 TRUE
     */
    public function setStrokeLineCap(int $lineCap): bool
    {
        return $this->draw->setStrokeLineCap($lineCap);
    }

    /**
     * 设置描边的线连接样式
     *
     * @param int $lineJoin 线连接样式
     *
     * @return bool 成功时返回 TRUE
     */
    public function setStrokeLineJoin(int $lineJoin): bool
    {
        return $this->draw->setStrokeLineJoin($lineJoin);
    }

    /**
     * 设置描边的斜接限制
     *
     * @param int $miterLimit 斜接限制
     *
     * @return bool 成功时返回 TRUE
     */
    public function setStrokeMiterLimit(int $miterLimit): bool
    {
        return $this->draw->setStrokeMiterLimit($miterLimit);
    }

    /**
     * 设置描边的透明度
     *
     * @param float $opacity 透明度
     *
     * @return bool 成功时返回 TRUE
     */
    public function setStrokeOpacity(float $opacity): bool
    {
        return $this->draw->setStrokeOpacity($opacity);
    }

    /**
     * 设置描边的图案的 URL
     *
     * @param string $url 图案的 URL
     *
     * @return bool 成功时返回 TRUE
     */
    public function setStrokePatternURL(string $url): bool
    {
        return $this->draw->setStrokePatternURL($url);
    }

    /**
     * 设置描边的宽度
     *
     * @param float $width 宽度
     *
     * @return bool 成功时返回 TRUE
     */
    public function setStrokeWidth(float $width): bool
    {
        return $this->draw->setStrokeWidth($width);
    }

    /**
     * 设置文本对齐方式
     *
     * @param int $alignment 对齐方式
     *
     * @return bool 成功时返回 TRUE
     */
    public function setTextAlignment(int $alignment): bool
    {
        return $this->draw->setTextAlignment($alignment);
    }

    /**
     * 设置文本抗锯齿
     *
     * @param bool $antialias 文本抗锯齿
     *
     * @return bool 成功时返回 TRUE
     */
    public function setTextAntialias(bool $antialias): bool
    {
        return $this->draw->setTextAntialias($antialias);
    }

    /**
     * 设置文本装饰
     *
     * @param int $decoration 文本装饰
     *
     * @return bool 成功时返回 TRUE
     */
    public function setTextDecoration(int $decoration): bool
    {
        return $this->draw->setTextDecoration($decoration);
    }

    /**
     * 设置文本编码
     *
     * @param string $encoding 文本编码
     *
     * @return bool 成功时返回 TRUE
     */
    public function setTextEncoding(string $encoding): bool
    {
        return $this->draw->setTextEncoding($encoding);
    }

    /**
     * 设置文本行间距
     *
     * @param float $spacing 行间距
     *
     * @return bool 成功时返回 TRUE
     */
    public function setTextInterlineSpacing(float $spacing): bool
    {
        return $this->draw->setTextInterlineSpacing($spacing);
    }

    /**
     * 设置文本字间距
     *
     * @param float $spacing 字间距
     *
     * @return bool 成功时返回 TRUE
     */
    public function setTextInterwordSpacing(float $spacing): bool
    {
        return $this->draw->setTextInterwordSpacing($spacing);
    }

    /**
     * 设置文本字距
     *
     * @param float $kerning 字距
     *
     * @return bool 成功时返回 TRUE
     */
    public function setTextKerning(float $kerning): bool
    {
        return $this->draw->setTextKerning($kerning);
    }

    /**
     * 设置文本下划线颜色
     *
     * @param ImagickPixel $underColor 下划线颜色
     *
     * @return bool 成功时返回 TRUE
     */
    public function setTextUnderColor(ImagickPixel $underColor): bool
    {
        return $this->draw->setTextUnderColor($underColor);
    }

    /**
     * 设置矢量图形
     *
     * @param string $xml 矢量图形的 XML
     *
     * @return bool 成功时返回 TRUE
     */
    public function setVectorGraphics(string $xml): bool
    {
        return $this->draw->setVectorGraphics($xml);
    }

    /**
     * 设置视图框
     *
     * @param float $x1 视图框左上角的 x 坐标
     * @param float $y1 视图框左上角的 y 坐标
     * @param float $x2 视图框右下角的 x 坐标
     * @param float $y2 视图框右下角的 y 坐标
     *
     * @return bool 成功时返回 TRUE
     */
    public function setViewbox(float $x1, float $y1, float $x2, float $y2): bool
    {
        return $this->draw->setViewbox($x1, $y1, $x2, $y2);
    }

    /**
     * 对 x 轴进行斜切变换
     *
     * @param float $angle 角度
     *
     * @return bool 成功时返回 TRUE
     */
    public function skewX(float $angle): bool
    {
        return $this->draw->skewX($angle);
    }

    /**
     * 对 y 轴进行斜切变换
     *
     * @param float $angle 角度
     *
     * @return bool 成功时返回 TRUE
     */
    public function skewY(float $angle): bool
    {
        return $this->draw->skewY($angle);
    }

    /**
     * 对图像进行平移
     *
     * @param float $x x 轴的平移量
     * @param float $y y 轴的平移量
     *
     * @return bool 成功时返回 TRUE
     */
    public function translate(float $x, float $y): bool
    {
        return $this->draw->translate($x, $y);
    }

    public function __call($method, $arg)
    {
        if (method_exists($this, $method)) {
            return call_user_func_array(array($this, $method), $arg);
        }

        return call_user_func_array(array($this->draw, $method), $arg);
    }

}