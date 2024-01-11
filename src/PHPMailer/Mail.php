<?php

namespace zxf\PHPMailer;

class Mail
{
    protected $mailObj;

    protected string $driver        = 'default';
    protected string $lang          = 'zh_cn'; //选择语言包
    private string   $mailConfigKey = 'tools_other.mail'; // 获取配置文件的key 名称，多级用.分割
    protected array  $config        = []; // 当前正在发送邮件的邮件配置
    protected array  $failOverKeys  = []; // 故障转移的keys,逐个尝试发送

    // 发送邮件的临时数据，发送完毕后自动清空
    private array $sendData = [
        'title'      => '', // 邮件标题
        'content'    => '', // 邮件内容
        'alt_body'   => '', // 邮件内容
        'address'    => [], // 接收人列表
        'cc'         => [], // “抄送”地址列表
        'bcc'        => [], // “密件抄送”地址列表
        'reply_to'   => [], // 回复地址列表
        'attachment' => [], // 发送的附件列表
    ];

    public function __construct($driver = 'default')
    {
        $this->initDriver($driver);
        $this->mailObj = new PHPMailer(true);
        $this->setDebug(false);
        $this->setAuth(true);
    }

    public function initDriver($driver = 'default')
    {
        $this->driver = $driver;
        // 使用 故障转移 逐个发送
        if ($this->driver == 'failover') {
            $configList         = config($this->mailConfigKey);
            $this->failOverKeys = array_diff(array_keys($configList), ['failover']);
        } else {
            $this->failOverKeys = [$driver];
        }
        $this->next();
        return $this;
    }

    // 获取当前邮件配置
    private function getCurrentConfig(string $driver = 'default')
    {
        try {
            $this->config = config("{$this->mailConfigKey}.{$driver}");
        } catch (\Exception $e) {
            throw new \Exception("邮件配置不存在:" . $driver);
        }
        return $this;
    }

    // 修改邮件配置为下一个配置项
    private function next()
    {
        if (!empty($this->failOverKeys)) {
            $this->getCurrentConfig(array_shift($this->failOverKeys));
        }
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

    // 非HTML邮件客户端的纯文本正文
    public function setAltBody(string $content)
    {
        $this->mailObj->AltBody = $content ?? 'This is the body in plain text for non-HTML mail clients';
        return $this;
    }

    //添加收件人
    public function addAddress(string $mail, string $name = '')
    {
        $this->mailObj->addAddress($mail, $name ?? "");
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

    // 设置debug模式
    // DEBUG_LOWLEVEL:启用详细调试输出;DEBUG_OFF关闭调试模式
    public function setDebug(bool $debug = false)
    {
        $this->mailObj->SMTPDebug = $debug ? SMTP::DEBUG_LOWLEVEL : SMTP::DEBUG_OFF;
        return $this;
    }

    // 启用SMTP身份验证
    public function setAuth(bool $auth = true)
    {
        $this->mailObj->SMTPAuth = $auth;
        return $this;
    }

    // 邮件发送方式
    protected function sendType(string $type = 'smtp')
    {
        switch ($type) {
            case 'mail':
                $this->mailObj->isMail();
                break;
            case 'sendmail':
                $this->mailObj->isSendmail();
                break;
            case 'qmail':
                $this->mailObj->isQmail();
                break;
            case 'smtp':
            default:
                $this->mailObj->isSMTP();
                break;
        }
        return $this;
    }


    /**
     * 初始化
     *
     * @access public
     *
     * @param string $driver
     *
     * @return Mail
     */
    public static function instance(string $driver = 'default')
    {
        return new static($driver);
    }

    public function send()
    {
        //创建一个实例；传递“true”将启用异常
        //调试时候传递“true”，其他时候建议为空

        try {
            //Server settings
            $this->sendType($this->config['mailer'] ?? 'smtp');
            // $this->mailObj->SMTPDebug = SMTP::DEBUG_OFF;                         // DEBUG_SERVER:启用详细调试输出;DEBUG_OFF关闭调试模式

            $this->mailObj->Host       = $this->config['host'];                     //将SMTP服务器设置为通过发送 例如：smtp.qq.com
            $this->mailObj->SMTPAuth   = !isset($this->config['auth']) || (bool)$this->config['auth'];    //启用SMTP身份验证
            $this->mailObj->Username   = $this->config['username'];                 //SMTP username 例如：123456@qq.com
            $this->mailObj->Password   = $this->config['password'];                 //SMTP password //客户端授权密码，注意不是登录密码
            $this->mailObj->SMTPSecure = $this->config['secure'] ?? PHPMailer::ENCRYPTION_SMTPS;               //使用ssl协议 - 启用隐式TLS加密
            $this->mailObj->Port       = $this->config['port'] ?? 465;              //要连接的TCP端口；如果已设置，请使用587 `SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS`

            // 发件人
            $this->mailObj->setFrom($this->config['username'], $this->config['form'] ?? 'Mail');         //设置邮箱的来源，邮箱与$this->mailObj->Username一致，名称随意

            $this->mailObj->CharSet = "utf-8";                              //字符集设置，防止中文乱码

            //内容
            $this->mailObj->isHTML(true);                                  //将电子邮件格式设置为HTML
            $this->mailObj->setLanguage($this->lang);                     //设置语言，zh_cn为中文

            return $this->mailObj->send();
            // echo '消息已发送';
        } catch (Exception $e) {

            // echo "无法发送消息，Mailer错误: {$this->mailObj->ErrorInfo}";
            throw new \Exception("无法发送消息,Mailer错误: {$this->mailObj->ErrorInfo}");
        }
    }

    public function __call($method, $args)
    {
        if (method_exists($this, $method)) {
            return call_user_func_array(array($this, $method), $args);
        }
        if (empty($this->mailObj)) {
            return $this;
        }

        return call_user_func_array(array($this->mailObj, $method), $args);
    }
}