<?php

namespace zxf\Facade;

/**
 * Mail 发送邮件类
 *
 * @link http://github.com/zhaoxianfang/tools
 *
 * @method Mail reset()
 * @method Mail mailer(string $mailer = 'default') // 手动设置邮件发送特定通道
 * @method Mail title(string $title = 'Title') // 邮件标题
 * @method Mail content(string $content = 'Content') // 邮件内容
 * @method Mail to(string $mail, string $name = '') // 邮件接收人,可多次调用
 * @method Mail replyTo(string $mail, string $name = '') // 邮件回复地址，一般与来源保持一致
 * @method Mail cc(string $mail, string $name = '') // 添加“抄送”地址,可多次调用
 * @method Mail bcc(string $mail, string $name = '') // 添加“密件抄送”地址,可多次调用
 * @method Mail attachment(string $filePath, string $fileName = '') // 发送邮件附件,可多次调用
 * @method bool send(): bool // 发送邮件
 * @method array getErrors(): array // 获取错误信息
 */
class Mail extends FacadeBase implements FacadeInterface
{
    public static function getFacadeAccessor()
    {
        return \zxf\PHPMailer\Mail::class;
    }
}
