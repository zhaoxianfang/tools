<?php
// +---------------------------------------------------------------------
// | 图片处理
// +---------------------------------------------------------------------
// | Licensed   | http://www.apache.org/licenses/LICENSE-2.0 )
// +---------------------------------------------------------------------
// | Author     | ZhaoXianFang <1748331509@qq.com>
// +---------------------------------------------------------------------
// | 版权       | http://www.itzxf.com
// +---------------------------------------------------------------------
// | Date       | 2019-07-30
// +---------------------------------------------------------------------
namespace zxf\tools;

class Img
{
    /**
     * @var object 对象实例
     */
    protected static $instance;

    /**
     * 初始化
     * @access public
     * @param array $options 参数
     * @return Tree
     */
    public static function instance($options = [])
    {
        if (is_null(self::$instance)) {
            self::$instance = new static($options);
        }

        return self::$instance;
    }

    /**
     * 将图片处理为圆角
     * @Author    ZhaoXianFang
     * @DateTime  2020-04-05
     * @param     [type]       $imgPath     [要处理的图片路径]
     * @param boolean $newFilePath [可选][保存路径]
     *                                 [false默认不保存,true直接修改原图,设置路径则保存到设置路径]
     * @return    [type]                    [description]
     * demo1:     header("Content-Type: image/png");
     *            $img = Img::changeCircularImg('./1.jpg');
     *            imagepng($img,$img_path='./test101.png');
     *            imagedestroy($img);
     * demo2:     $img = Img::changeCircularImg('./1.jpg',true);
     * demo3:     $img = Img::changeCircularImg('./1.jpg','./img/temp.jpg');
     * @copyright [copyright]
     * @license   [license]
     * @version   [version]
     */
    public function changeCircularImg($imgPath, $newFilePath = false)
    {
        // $ext     = pathinfo($imgPath);
        $ename = getimagesize($imgPath);
        $ext   = explode('/', $ename['mime'])[1];

        $src_img = null;
        // switch ($ext['extension']) {
        switch ($ext) {
            case 'jpg':
            case 'jpeg':
                $src_img = imagecreatefromjpeg($imgPath);
                break;
            case 'png':
                $src_img = imagecreatefrompng($imgPath);
                break;
        }
        $wh  = getimagesize($imgPath);
        $w   = $wh[0];
        $h   = $wh[1];
        $w   = min($w, $h);
        $h   = $w;
        $img = imagecreatetruecolor($w, $h);
        //这一句一定要有
        imagesavealpha($img, true);
        //拾取一个完全透明的颜色,最后一个参数127为全透明
        $bg = imagecolorallocatealpha($img, 255, 255, 255, 127);
        imagefill($img, 0, 0, $bg);
        $r   = $w / 2; //圆半径
        $y_x = $r; //圆心X坐标
        $y_y = $r; //圆心Y坐标
        for ($x = 0; $x < $w; $x++) {
            for ($y = 0; $y < $h; $y++) {
                $rgbColor = imagecolorat($src_img, $x, $y);
                if (((($x - $r) * ($x - $r) + ($y - $r) * ($y - $r)) < ($r * $r))) {
                    imagesetpixel($img, $x, $y, $rgbColor);
                }
            }
        }
        if ($newFilePath === true) {
            $newFilePath = $imgPath;
        }
        $ext2 = explode(".", $newFilePath);
        $ext2 = $ext2[count($ext2) - 1];

        if (!is_dir(dirname($newFilePath))) {
            //检查是否有该文件夹，如果没有就创建，并给予最高权限
            mkdir(dirname($newFilePath), 0755, true);
        }
        // dirname

        if ($newFilePath) {
            // switch ($ext['extension']) {
            switch ($ext2) {
                case 'jpg':
                case 'jpeg':
                    imagejpeg($img, $newFilePath);
                    break;
                case 'png':
                    imagepng($img, $newFilePath);
                    break;
            }
            imagedestroy($img);
        }
        return $img;
    }

    /**
     * 修改图片尺寸[支持透明图片修改]
     * @Author    ZhaoXianFang
     * @DateTime  2020-04-05
     * @param     [type]       $filePath    [原图路径]
     * @param boolean $newFilePath [可选][保存路径]
     *                                 [false默认不保存,true直接修改原图,设置路径则保存到设置路径]
     * @param     [type]       $xmax        [设置新图宽度]
     * @param     [type]       $ymax        [设置新图高度]
     * @return    [type]                    [description]
     * @version   [version]
     * @copyright [copyright]
     * @license   [license]
     */
    public function resizeImage($filePath, $newFilePath = false, $xmax, $ymax)
    {
        // $ext = explode(".", $filePath);
        // $ext = $ext[count($ext)-1];
        // 用 getimagesize 不要用  explode(".", $filePath) 判断文件类型
        $ename = getimagesize($filePath);
        $ext   = explode('/', $ename['mime'])[1];
        //获取源图gd图像标识符
        // $srcImg = imagecreatefrompng($filePath);
        if ($ext == "jpg" || $ext == "jpeg") {
            $srcImg = imagecreatefromjpeg($filePath);
        } elseif ($ext == "png") {
            $srcImg = imagecreatefrompng($filePath);
        } elseif ($ext == "gif") {
            $srcImg = imagecreatefromgif($filePath);
        }

        $srcWidth  = imagesx($srcImg);
        $srcHeight = imagesy($srcImg);

        //创建新图
        // $newWidth = round($srcWidth / 2);
        // $newHeight = round($srcHeight / 2);
        $newWidth  = $xmax;
        $newHeight = $ymax;

        $newImg = imagecreatetruecolor($newWidth, $newHeight);
        //分配颜色 + alpha，将颜色填充到新图上
        $alpha = imagecolorallocatealpha($newImg, 0, 0, 0, 127);
        imagefill($newImg, 0, 0, $alpha);

        //将源图拷贝到新图上，并设置在保存 PNG 图像时保存完整的 alpha 通道信息
        imagecopyresampled($newImg, $srcImg, 0, 0, 0, 0, $newWidth, $newHeight, $srcWidth, $srcHeight);
        imagesavealpha($newImg, true);
        // 修改原图
        if ($newFilePath === true) {
            $newFilePath = $filePath;
        }
        if (!is_dir(dirname($newFilePath))) {
            //检查是否有该文件夹，如果没有就创建，并给予最高权限
            mkdir(dirname($newFilePath), 0755, true);
        }
        if ($newFilePath) {
            if ($ext == "jpg" || $ext == "jpeg") {
                imagejpeg($newImg, $newFilePath);
            } elseif ($ext == "png") {
                imagepng($newImg, $newFilePath);
            } elseif ($ext == "gif") {
                imagegif($newImg, $newFilePath);
            }

        }
        return $newImg;
    }

    /**
     * 解决imagecopymerge 函数背景黑色问题,参数与 imagecopymerge 保持一直
     * @Author    ZhaoXianFang
     * @DateTime  2020-04-05
     * @param     [type]       $dst_im [目标图像]
     * @param     [type]       $src_im [被拷贝的源图像]
     * @param     [type]       $dst_x  [目标图像开始 x 坐标]
     * @param     [type]       $dst_y  [目标图像开始 y 坐标，x,y同为 0 则从左上角开始]
     * @param     [type]       $src_x  [拷贝图像开始 x 坐标]
     * @param     [type]       $src_y  [拷贝图像开始 y 坐标，x,y同为 0 则从左上角开始拷贝]
     * @param     [type]       $src_w  [从 src_x 开始）拷贝的宽度]
     * @param     [type]       $src_h  [从 src_y 开始）拷贝的高度]
     * @param     [type]       $pct    [图像合并程度，取值 0-100 ，当 pct=0 时，实际上什么也没做，反之完全合并。]
     * @return    [type]               [description]
     * @copyright [copyright]
     * @license   [license]
     * @version   [version]
     */
    public function imagecopymerge_alpha($dst_im, $src_im, $dst_x, $dst_y, $src_x, $src_y, $src_w, $src_h, $pct)
    {
        $opacity = $pct;
        // getting the watermark width
        $w = imagesx($src_im);
        // getting the watermark height
        $h = imagesy($src_im);

        // creating a cut resource
        $cut = imagecreatetruecolor($src_w, $src_h);
        // copying that section of the background to the cut
        imagecopy($cut, $dst_im, 0, 0, $dst_x, $dst_y, $src_w, $src_h);
        // inverting the opacity
        //$opacity = 100 - $opacity;

        // placing the watermark now
        imagecopy($cut, $src_im, 0, 0, $src_x, $src_y, $src_w, $src_h);
        imagecopymerge($dst_im, $cut, $dst_x, $dst_y, $src_x, $src_y, $src_w, $src_h, $opacity);
    }

    /**
     * 图片水印 和 拼接合并
     * @Author    ZhaoXianFang
     * @DateTime  2020-04-05
     * @param string $bigImgPath [description]
     * @param string $smallImgPath [description]
     * @param string $saveToPath [description]
     * @param string $option 数组参数[0：x偏移；1：y偏移；2：是否使用透明底色]
     * @param string $type [设置方式 water:水印;splice:拼接]
     * @return    [type]                     [description]
     * @copyright [copyright]
     * @license   [license]
     * @version   [version]
     */
    public function imageMerge($bigImgPath = '', $smallImgPath = '', $saveToPath = '', $type = 'water', $option = [])
    {

        // 用 getimagesize 不要用  explode(".", $filePath) 判断文件类型
        $ename1 = getimagesize($bigImgPath);
        $ext1   = explode('/', $ename1['mime'])[1];

        $ename2 = getimagesize($smallImgPath);
        $ext2   = explode('/', $ename2['mime'])[1];

        // $ename3 = getimagesize($saveToPath);
        // $ext3   = explode('/', $ename3['mime'])[1];
        $ext3 = explode(".", $saveToPath);
        $ext3 = $ext3[count($ext3) - 1];

        //获取源图gd图像标识符
        // $srcImg = imagecreatefrompng($filePath);
        if ($ext1 == "jpg" || $ext1 == "jpeg") {
            $srcImg1 = imagecreatefromjpeg($bigImgPath);
        } elseif ($ext1 == "png") {
            try {
                $srcImg1 = imagecreatefrompng($bigImgPath);
            } catch (Exception $e) {
                // var_dump(pathinfo($bigImgPath));
                // die;
                $srcImg1 = imagecreatefromjpeg($bigImgPath);
            }
        } elseif ($ext1 == "gif") {
            $srcImg1 = imagecreatefromgif($bigImgPath);
        }

        if ($ext2 == "jpg" || $ext2 == "jpeg") {
            $srcImg2 = imagecreatefromjpeg($smallImgPath);
        } elseif ($ext2 == "png") {
            $srcImg2 = imagecreatefrompng($smallImgPath);
        } elseif ($ext2 == "gif") {
            $srcImg2 = imagecreatefromgif($smallImgPath);
        }

        // getting the watermark width
        $w1        = imagesx($srcImg1); // 1图宽度
        $h1        = imagesy($srcImg1); // 1图高度
        $w2        = imagesx($srcImg2); // 1图宽度
        $h2        = imagesy($srcImg2); // 2图高度
        $pathInfo1 = pathinfo($bigImgPath); // 1图信息
        $pathInfo2 = pathinfo($smallImgPath); // 2图信息

        $dealType = 0; //处理类型 0未知；1：水印；2x：拼接
        switch ($type) {
            case 'water': // 自定义水印位置
                $dealType = 1;
                $dst_x    = $option['0']; // 自定义的水印x轴开始坐标 目标图像开始 x 坐标
                $dst_y    = $option['1']; // 自定义的水印x轴开始坐标 目标图像开始 y 坐标
                $src_x    = $option['2']; // 拷贝图像开始 x 坐标
                $src_y    = $option['3']; // 拷贝图像开始 y 坐标，x,y同为 0 则从左上角开始拷贝
                break;
            case 'left-top': // 左上水印位置
                $dealType = 1;
                $dst_x    = 0; // 自定义的水印x轴开始坐标 目标图像开始 x 坐标
                $dst_y    = 0; // 自定义的水印x轴开始坐标 目标图像开始 y 坐标
                $src_x    = 0; // 拷贝图像开始 x 坐标
                $src_y    = 0; // 拷贝图像开始 y 坐标，x,y同为 0 则从左上角开始拷贝
                break;
            case 'right-top': // 右上水印位置
                $dealType = 1;
                $dst_x    = $w2 < $w1 ? $w1 - $w2 : 0; // 自定义的水印x轴开始坐标 目标图像开始 x 坐标
                $dst_y    = 0; // 自定义的水印x轴开始坐标 目标图像开始 y 坐标
                $src_x    = 0; // 拷贝图像开始 x 坐标
                $src_y    = 0; // 拷贝图像开始 y 坐标，x,y同为 0 则从左上角开始拷贝
            case 'left-bottom': // 左下水印位置
                $dealType = 1;
                $dst_x    = 0; // 自定义的水印x轴开始坐标 目标图像开始 x 坐标
                $dst_y    = $h2 < $h1 ? $h1 - $h2 : 0; // 自定义的水印x轴开始坐标 目标图像开始 y 坐标
                $src_x    = 0; // 拷贝图像开始 x 坐标
                $src_y    = 0; // 拷贝图像开始 y 坐标，x,y同为 0 则从左上角开始拷贝
                break;
            case 'right-bottom': // 右下水印位置
                $dealType = 1;
                $dst_x    = $w2 < $w1 ? $w1 - $w2 : 0; // 自定义的水印x轴开始坐标 目标图像开始 x 坐标
                $dst_y    = $h2 < $h1 ? $h1 - $h2 : 0; // 自定义的水印x轴开始坐标 目标图像开始 y 坐标
                $src_x    = 0; // 拷贝图像开始 x 坐标
                $src_y    = 0; // 拷贝图像开始 y 坐标，x,y同为 0 则从左上角开始拷贝
                break;
            case 'conter-bottom': // 居中水印位置
                $dealType = 1;
                $dst_x    = $w2 < $w1 ? ($w1 - $w2) / 2 : 0; // 自定义的水印x轴开始坐标 目标图像开始 x 坐标
                $dst_y    = $h2 < $h1 ? ($h1 - $h2) / 2 : 0; // 自定义的水印x轴开始坐标 目标图像开始 y 坐标
                $src_x    = 0; // 拷贝图像开始 x 坐标
                $src_y    = 0; // 拷贝图像开始 y 坐标，x,y同为 0 则从左上角开始拷贝
                break;

            case 'bottom-splice': // 下侧拼接位置
                $dealType = 20;
                break;
            case 'right-splice': // 右侧拼接位置
                $dealType = 21;
                break;
            default:
                break;
        }

        if (!is_dir(dirname($saveToPath))) {
            //检查是否有该文件夹，如果没有就创建，并给予最高权限
            mkdir(dirname($saveToPath), 0755, true);
        }

        // 水印
        if ($dealType == 1) {
            $bigImg   = imagecreatefromstring(file_get_contents($bigImgPath));
            $smallImg = imagecreatefromstring(file_get_contents($smallImgPath));
            list($qCodeWidth, $qCodeHight, $qCodeType) = getimagesize($smallImgPath);
            // imagecopymerge($bigImg, $qCodeImg, 300, 400, 0, 0, $qCodeWidth, $qCodeHight, 100);
            $this->imagecopymerge_alpha($bigImg, $smallImg, $dst_x, $dst_y, $src_x, $src_y, $qCodeWidth, $qCodeHight, 100);
            list($bigWidth, $bigHight, $bigType) = getimagesize($bigImgPath);
            imagejpeg($bigImg, $saveToPath);
            return $bigImg;
        }
        // 图片拼接
        if ($dealType == 20 || $dealType == 21) {
            if ($dealType == 20) {
                $background = imagecreatetruecolor($w1, $h1 + $h2); // 背景图片
            }
            if ($dealType == 21) {
                $background = imagecreatetruecolor($w1 + $w2, $h1); // 背景图片
            }
            // if(isset($option['2']) && $option['2']=== true){
            // $color = imagecolorallocate($background, 0, 0, 0); // 为真彩色画布创建白色背景，再设置为透明
            // imagefill($background, 0, 0, $color);
            // imageColorTransparent($background, $color);
            $white = imagecolorallocate($background, 255, 255, 255);
            imagefill($background, 0, 0, $white);
            // imageColorTransparent($background, $white);
            // }else{
            //     $color = imagecolorallocate($background, 202, 201, 201); // 为真彩色画布创建白色背景，再设置为透明
            //     imagefill($background, 0, 0, $color);
            //     imageColorTransparent($background, $color);
            // }

            switch (strtolower($ext1)) {
                case 'jpg':
                case 'jpeg':
                    $imagecreatefromjpeg = 'imagecreatefromjpeg';
                    break;
                case 'png':
                    $imagecreatefromjpeg = 'imagecreatefrompng';
                    break;
                case 'gif':
                default:
                    $imagecreatefromjpeg = 'imagecreatefromstring';
                    $bigImgPath          = file_get_contents($bigImgPath);
                    break;
            }
            $resource = $imagecreatefromjpeg($bigImgPath);
            // $start_x,$start_y copy图片在背景中的位置
            // 0,0 被copy图片的位置
            // $pic_w,$pic_h copy后的高度和宽度
            imagecopyresized($background, $resource, $start_x = 0, $start_y = 0, 0, 0, $w1, $h1, imagesx($resource), imagesy($resource)); // 最后两个参数为原始图片宽度和高度，倒数两个参数为copy时的图片宽度和高度

            // 图二

            // switch (strtolower($ext2)) {
            switch ($ext2) {
                case 'jpg':
                case 'jpeg':
                    $imagecreatefromjpeg = 'imagecreatefromjpeg';
                    break;
                case 'png':
                    $imagecreatefromjpeg = 'imagecreatefrompng';
                    break;
                case 'gif':
                default:
                    $imagecreatefromjpeg = 'imagecreatefromstring';
                    $smallImgPath        = file_get_contents($smallImgPath);
                    break;
            }
            // var_dump($w2);
            // var_dump($h2);
            // die;
            $resource = $imagecreatefromjpeg($smallImgPath);
            // $start_x,$start_y copy图片在背景中的位置
            // 0,0 被copy图片的位置
            // $pic_w,$pic_h copy后的高度和宽度
            if ($dealType == 20) {
                // 上下
                // imagecopyresized($background, $resource, 0, $h1, 0, 0, $w2, $h2, imagesx($resource), imagesy($resource)); // 最后两个参数为原始图片宽度和高度，倒数两个参数为copy时的图片宽度和高度
                imagecopyresized($background, $resource, 0 + $option['0'], $h1 + $option['1'], 0, 0, $w2, $h2, imagesx($resource), imagesy($resource)); // 最后两个参数为原始图片宽度和高度，倒数两个参数为copy时的图片宽度和高度
            }
            if ($dealType == 21) {
                // 左右
                imagecopyresized($background, $resource, $w1, 0, 0, 0, $w2, $h2, imagesx($resource), imagesy($resource)); // 最后两个参数为原始图片宽度和高度，倒数两个参数为copy时的图片宽度和高度
            }

            switch (strtolower($ext3)) {
                case 'jpg':
                case 'jpeg':
                    header("Content-type: image/jpg");
                    imagejpeg($background, $saveToPath);
                    break;
                case 'gif':
                    header("Content-type: image/gif");
                    imagegif($background, $saveToPath);
                    break;
                case 'png':
                default:
                    header("Content-type: image/png");
                    imagepng($background, $saveToPath);
                    break;
            }
            return $background;
        }
    }

    /**
     * 在图片对象上 添加文字
     * @Author   ZhaoXianFang
     * @DateTime 2020-04-07
     * @param    [type]       $image      [图片对象]
     * @param int $size [文字大小]
     * @param    [type]       $rot        [旋转角度]
     * @param    [type]       $offset_x   [写入的X坐标]
     * @param    [type]       $offset_y   [写入的Y坐标]
     * @param    [type]       $foreground [背景：例如 ImageColorAllocate($image, 255,255,255)]
     * @param    [type]       $fontFile   [ttf字体 路径]
     * @param    [type]       $text       [文本]
     * @param string $saveToPath [保存路径]
     * @return   [type]                   [description]
     * demo:         $white = ImageColorAllocate($image, 255,255,255);
     *               $imgTool->drawTextImg($image, 15, 0, 50, 900, $white = false, "E:/www/itzxf.com/public/static/font/lishu.ttf", "手机号:xxx", 'save_ttf.png');
     */
    public function drawTextImg($image, int $size, $rot, $offset_x, $offset_y, $foreground, $fontFile, $text, $saveToPath = '')
    {
        $width  = imagesx($image); // 图宽
        $height = imagesy($image); // 图高

        // $ename = getimagesize($saveToPath);
        // $ext   = explode('/', $ename['mime'])[1];
        $ext = explode(".", $saveToPath);
        $ext = $ext[count($ext) - 1];

        if ($offset_y > $height) {

            $background = imagecreatetruecolor($width, $offset_y + 30); // 背景图片

            $color = imagecolorallocate($background, 202, 201, 201); // 为真彩色画布创建白色背景，再设置为透明
            imagefill($background, 0, 0, $color);
            imageColorTransparent($background, $color);

            imagecopyresized($background, $image, $start_x = 0, $start_y = 0, 0, 0, $width, $height, imagesx($image), imagesy($image)); //

            $image = $background;
        }
        if ($offset_x > $width) {

            $background = imagecreatetruecolor($offset_x + 10, $height); // 背景图片

            $color = imagecolorallocate($background, 202, 201, 201); // 为真彩色画布创建白色背景，再设置为透明
            imagefill($background, 0, 0, $color);
            imageColorTransparent($background, $color);

            imagecopyresized($background, $image, $start_x = 0, $start_y = 0, 0, 0, $width, $height, imagesx($image), imagesy($image)); //

            $image = $background;
        }

        imagettftext($image, $size, $rot, $offset_x, $offset_y, $foreground, $fontFile, $text);

        if ($saveToPath) {

            if (!is_dir(dirname($saveToPath))) {
                //检查是否有该文件夹，如果没有就创建，并给予最高权限
                mkdir(dirname($saveToPath), 0755, true);
            }

            switch (strtolower($ext)) {
                case 'jpg':
                case 'jpeg':
                    header("Content-type: image/jpg");
                    imagejpeg($image, $saveToPath);
                    break;
                case 'gif':
                    header("Content-type: image/gif");
                    imagegif($image, $saveToPath);
                    break;
                case 'png':
                default:
                    header("Content-type: image/png");
                    imagepng($image, $saveToPath);
                    break;
            }
        }
        return $image;
    }

    /**
     * @desc Base64生成图片文件,自动解析格式
     * @param $base64 可以转成图片的base64字符串
     * @param $path 绝对路径
     * @param $filename 生成的文件名
     * @return array 返回的数据，当返回status==1时，代表base64生成图片成功，其他则表示失败
     */
    public static function base64ToImage($base64, $path, $filename)
    {

        $res = array();
        //匹配base64字符串格式
        if (preg_match('/^(data:\s*image\/(\w+);base64,)/', $base64, $result)) {
            //保存最终的图片格式
            $postfix  = $result[2];
            $base64   = base64_decode(substr(strstr($base64, ','), 1));
            $filename .= '.' . $postfix;
            $path     .= $filename;
            //创建图片
            if (file_put_contents($path, $base64)) {
                $res['status']   = 1;
                $res['filename'] = $filename;
            } else {
                $res['status'] = 2;
                $res['err']    = 'Create img failed!';
            }
        } else {
            $res['status'] = 2;
            $res['err']    = 'Not base64 char!';
        }
        return $res;

    }

    /**
     * @desc 将图片转成base64字符串
     * @param string $filename 图片地址
     * @return string
     */
    public static function imageToBase64($filename = '')
    {
        $base64 = '';
        if (file_exists($filename)) {
            if ($fp = fopen($filename, "rb", 0)) {
                $img = fread($fp, filesize($filename));
                fclose($fp);
                $base64 = 'data:image/jpg/png/gif;base64,' . chunk_split(base64_encode($img));
            }
        }
        return $base64;
    }
}
