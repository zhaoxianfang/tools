<?php

namespace zxf\PHPMailer;

class Factory
{
    protected $mailObj;

    protected string $driver = 'default';
    protected array  $config = [];

    public function __construct($driver = 'default')
    {
        $this->setDriver($driver);

        $this->mailObj = new PHPMailer(true);
    }

    public function setDriver($driver = 'default')
    {
        $this->driver = $driver;
        $this->config = config('tools_other.mail.' . $this->driver);
        return $this;
    }

    //标题
    public function setSubject(string $title)
    {
        $this->mailObj->Subject = $title;
        return $this;
    }

    //正文
    public function setContent(string $content)
    {
        $this->mailObj->Body = $content ?? 'This is the HTML message body <b>in bold!</b>';
        return $this;
    }

    public function addAddress(string $mail, string $name = '')
    {
        $this->mailObj->addAddress($mail, $name ?? "");     //添加收件人
        return $this;
    }

    // 设置回复地址，一般与来源保持一直
    public function addReplyTo(string $mail, string $name = '')
    {
        $this->mailObj->addReplyTo($mail, $name ?? ""); // 设置回复地址，一般与来源保持一直
        return $this;
    }

    // 添加一个“抄送”地址。
    public function addCC(string $mail, string $name = '')
    {
        $this->mailObj->addCC($mail, $name ?? "");                       //添加一个“抄送”地址。
        return $this;
    }

    //添加“密件抄送”地址。
    public function addBCC(string $mail, string $name = '')
    {
        $this->mailObj->addBCC($mail, $name ?? "");                       //添加“密件抄送”地址。
        return $this;
    }

    // 添加附件
    public function addAttachment(string $filePath, string $fileName = '')
    {
        $this->mailObj->addAttachment(realpath($filePath), $fileName ?? "");    //可选名称
        return $this;
    }


    /**
     * 初始化
     *
     * @access public
     *
     * @param string $driver
     *
     * @return Factory
     */
    public static function instance(string $driver = 'default')
    {
        return new static($driver);
    }

    public function send(...$args)
    {
        //创建一个实例；传递“true”将启用异常
        //调试时候传递“true”，其他时候建议为空

        try {
            //Server settings
            $this->mailObj->SMTPDebug = SMTP::DEBUG_SERVER;                      //启用详细调试输出
            // $this->mailObj->isSMTP();                                            //使用SMTP发送
            $this->mailObj->Mailer     = $this->config['mailer'];                                          //使用SMTP发送
            $this->mailObj->Host       = $this->config['host'];                     //将SMTP服务器设置为通过发送 例如：smtp.qq.com
            $this->mailObj->SMTPAuth   = true;                                   //启用SMTP身份验证
            $this->mailObj->Username   = $this->config['username'];                     //SMTP username 例如：123456@qq.com
            $this->mailObj->Password   = $this->config['password'];                               //SMTP password //客户端授权密码，注意不是登录密码
            $this->mailObj->SMTPSecure = PHPMailer::ENCRYPTION_SMTPS;            //使用ssl协议 - 启用隐式TLS加密
            $this->mailObj->Port       = $this->config['port'] ?? 465;                                    //要连接的TCP端口；如果已设置，请使用587 `SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS`

            // 发件人
            $this->mailObj->setFrom($this->config['username'], $this->config['form'] ?? 'System');         //设置邮箱的来源，邮箱与$this->mailObj->Username一致，名称随意

            //收件人
            // $this->mailObj->addAddress('joe@example.net', 'Joe User');     //添加收件人
            // $this->mailObj->addAddress('ellen@example.com');               //名称是可选的
            // $this->mailObj->addReplyTo('info@example.com', 'Information'); // 设置回复地址，一般与来源保持一直
            // $this->mailObj->addCC('cc@example.com');                       //添加一个“抄送”地址。
            // $this->mailObj->addBCC('bcc@example.com');                     //添加“密件抄送”地址。

            //附件
            // $this->mailObj->addAttachment('/var/tmp/file.tar.gz');         //添加附件
            // $this->mailObj->addAttachment('/tmp/image.jpg', 'new.jpg');    //可选名称

            $this->mailObj->CharSet = "utf-8";                              //字符集设置，防止中文乱码

            //内容
            $this->mailObj->isHTML(true);                                  //将电子邮件格式设置为HTML

            // $this->mailObj->AltBody = '这是非HTML邮件客户端的纯文本正文';

            return $this->mailObj->send();
            // echo '消息已发送';
        } catch (Exception $e) {
            // echo "无法发送消息。Mailer错误: {$this->mailObj->ErrorInfo}";
            throw new \Exception("无法发送消息。Mailer错误: {$this->mailObj->ErrorInfo}");
        }
    }
}