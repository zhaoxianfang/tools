# PHPMailer

> https://github.com/PHPMailer/PHPMailer/

update_at:2024-01-11

## 最小安装

虽然手动或使用Composer安装整个软件包简单、方便、可靠，但您可能希望在项目中只包含重要文件。至少你需要src/PHPMail.php。 如果你使用SMTP，你需要src/SMTP.php，
如果你在SMTP之前使用POP（非常不可能！），你需要src=POP3.php。 如果你没有向用户显示错误，你可以跳过语言文件夹，只处理英语错误。
如果您使用XOAUTH2，则需要src/OAuth.php以及要进行身份验证的服务的Composer依赖项。 真的，使用Composer要容易得多！

## 简单示例

### 方式一(推荐)

> 支持故障转移(default 参数中传入 "fail_over" 标识)

```
use zxf\PHPMailer\Mail;

$mail = Mail::instance();
$mail->title('Title')
->content('Content')
->to('mail','name')
->cc('mail','name')
->bcc('mail','name')
->attachment('xxx.csv','xxx报表');
->send();
```

### 方式二

```
<?php
//将PHPMailer类导入全局命名空间
//这些必须位于脚本的顶部，而不是函数内部

use zxf\PHPMailer\PHPMailer;
use zxf\PHPMailer\SMTP;
use zxf\PHPMailer\Exception;

//创建一个实例；传递“true”将启用异常
//调试时候传递“true”，其他时候建议为空
$mail = new PHPMailer(true);

try {
    //Server settings
    $mail->SMTPDebug = SMTP::DEBUG_SERVER;                      //启用详细调试输出
    $mail->isSMTP();                                            //使用SMTP发送
    $mail->Host       = 'smtp.example.com';                     //将SMTP服务器设置为通过发送 例如：smtp.qq.com
    $mail->SMTPAuth   = true;                                   //启用SMTP身份验证
    $mail->Username   = 'user@example.com';                     //SMTP username 例如：123456@qq.com
    $mail->Password   = 'secret';                               //SMTP password //客户端授权密码，注意不是登录密码
    $mail->SMTPSecure = PHPMailer::ENCRYPTION_SMTPS;            //使用ssl协议 - 启用隐式TLS加密
    $mail->Port       = 465;                                    //要连接的TCP端口；如果已设置，请使用587 `SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS`

    //收件人
    $mail->setFrom('from@example.com', 'Mailer');         //设置邮箱的来源，邮箱与$mail->Username一致，名称随意
    $mail->addAddress('joe@example.net', 'Joe User');     //添加收件人
    $mail->addAddress('ellen@example.com');               //名称是可选的
    $mail->addReplyTo('info@example.com', 'Information'); // 设置回复地址，一般与来源保持一直
    $mail->addCC('cc@example.com');                       //添加一个“抄送”地址。
    $mail->addBCC('bcc@example.com');                     //添加“密件抄送”地址。

    //附件
    $mail->addAttachment('/var/tmp/file.tar.gz');         //添加附件
    $mail->addAttachment('/tmp/image.jpg', 'new.jpg');    //可选名称

    $mail->CharSet = "utf-8";                              //字符集设置，防止中文乱码

    //内容
    $mail->isHTML(true);                                  //将电子邮件格式设置为HTML
    $mail->Subject = 'Here is the subject';                 //标题
    $mail->Body    = 'This is the HTML message body <b>in bold!</b>'; //正文
    $mail->AltBody = '这是非HTML邮件客户端的纯文本正文';

    $mail->send();
    echo '消息已发送';
} catch (Exception $e) {
    echo "无法发送消息。Mailer错误: {$mail->ErrorInfo}";
}
```

PHPMailer默认为英语，可以设置语言

```
//加载中文
$mail->setLanguage('zh_cn');
// 或者使用自定义的文件路径
$mail->setLanguage('zh_cn', '/optional/path/to/language/directory/');
```

其他方法

```
$mail->getLastMessageID()
$mail->postSend()
验证邮箱号
PHPMailer::validateAddress('user@example.com', function($address) {
    return (strpos($address, '@') !== false);
});
```