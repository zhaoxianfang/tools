<?php

namespace zxf\PHPMailer;
/**
 * 发送邮件类
 *      支持故障转移(default 参数中传入 "fail_over" 标识)
 * eg:  $mail = Mail::instance()
 *          ->title('Title')
 *          ->content('Content')
 *          ->to('mail','name')
 *          ->cc('mail','name')
 *          ->bcc('mail','name')
 *          ->attachment('xxx.csv','xxx报表');
 *          ->send();
 *
 * @package zxf\PHPMailer
 */
class Mail
{
    protected PHPMailer $mailObj;

    protected string $mailer       = 'smtp';
    protected string $lang         = 'zh_cn'; //选择语言包
    protected array  $config       = []; // 当前正在发送邮件的邮件配置
    protected array  $failOverKeys = []; // 故障转移的keys,逐个尝试发送
    protected bool   $openDebug    = false; // 是否开启调试模式

    private string|null $sendMailer = ''; // 运行中的mailer
    private array       $errors     = []; // 运行中的错误信息

    // 发送邮件的[临时]数据，发送完毕后自动清空
    private array $sendData = [];

    public function __construct()
    {
        $this->init();
    }

    /**
     * 初始化
     */
    public static function instance()
    {
        return new static();
    }

    private function init()
    {
        // 实例化PHPMailer核心类
        $this->mailObj          = new PHPMailer(true);
        $this->mailObj->CharSet = "utf-8";
        $this->mailObj->isHTML(true);

        $this->mailer   = config('tools_mail.default', 'smtp');
        $this->lang     = 'zh_cn';
        $this->config   = [];
        $this->sendData = [
            'title'      => '', // 邮件标题
            'content'    => '', // 邮件内容
            'to'         => [], // 接收人列表
            'cc'         => [], // “抄送”地址列表
            'bcc'        => [], // “密件抄送”地址列表
            'reply_to'   => [], // 回复地址列表
            'attachment' => [], // 发送的附件列表
        ];
        $this->initFailOver();
        return $this;
    }

    private function initFailOver()
    {
        $this->failOverKeys = $this->mailer == 'fail_over' ? config('tools_mail.fail_over') : [$this->mailer];
        return $this;
    }

    // 获取当前邮件配置
    private function getConfig(string $driver = '')
    {
        try {
            $this->config = config("tools_mail.fail_over.{$driver}");
        } catch (\Exception $e) {
            throw new \Exception("邮件配置不存在:" . $driver);
        }
        return $this;
    }

    // 修改邮件配置为下一个配置项
    private function next()
    {
        if (!empty($this->failOverKeys)) {
            $this->sendMailer = array_shift($this->failOverKeys);
            $this->getConfig($this->sendMailer);
        }
        return $this;
    }

    /**
     * 设置邮件发送特定通道
     *
     * @param string $mailer
     *
     * @return $this
     */
    public function mailer(string $mailer = 'default')
    {
        $this->mailer = $mailer;
        return $this;
    }

    // 邮件标题
    public function title(string $title = 'Title')
    {
        $this->sendData['title'] = $title;
        return $this;
    }

    // 邮件内容
    public function content(string $content = 'Content')
    {
        $this->sendData['content'] = $content;
        return $this;
    }

    // 邮件接收人,可多次调用
    public function to(string $mail, string $name = '')
    {
        $this->sendData['to'][] = ['mail' => $mail, 'name' => $name];
        return $this;
    }

    // 邮件回复地址，一般与来源保持一致
    public function replyTo(string $mail, string $name = '')
    {
        $this->sendData['reply_to'] = ['mail' => $mail, 'name' => $name];
        return $this;
    }

    // 添加“抄送”地址,可多次调用
    public function cc(string $mail, string $name = '')
    {
        $this->sendData['cc'][] = ['mail' => $mail, 'name' => $name];
        return $this;
    }

    // 添加“密件抄送”地址,可多次调用
    public function bcc(string $mail, string $name = '')
    {
        $this->sendData['bcc'][] = ['mail' => $mail, 'name' => $name];
        return $this;
    }

    // 发送邮件附件,可多次调用
    public function attachment(string $filePath, string $fileName = '')
    {
        $this->sendData['attachment'][] = ['path' => realpath($filePath), 'name' => $fileName];
        return $this;
    }

    // 是否开启调试模式
    public function debug(bool $status = false)
    {
        $this->openDebug = $status;
        return $this;
    }

    // 选择邮件发送方式
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

    // 邮件发送
    public function send(): bool
    {
        try {
            $this->paramParsing();

            // 发送邮件
            if ($this->mailObj->send()) {
                // 发送成功，清空数据
                $this->init();
                return true;
            }
            // echo '消息已发送';
        } catch (Exception $e) {
            // 发送失败，重试
            $this->errors[] = ['mailer' => $this->sendMailer, 'error' => $this->mailObj->ErrorInfo];
            if (!empty($this->failOverKeys)) {
                return $this->send();
            }
            // "发送失败，Mailer错误: {$this->mailObj->ErrorInfo}";
            throw new \Exception("发送失败: {$this->mailObj->ErrorInfo}");
        }
    }

    // 获取错误信息
    public function getErrors(): array
    {
        return $this->errors;
    }

    // 参数解析
    private function paramParsing()
    {

        $this->next();
        // 邮件发送语言包
        $this->mailObj->setLanguage($this->lang);
        // 邮件发送方式
        $this->sendType($this->config['mailer'] ?? 'smtp');
        // 是否调试模式
        $this->mailObj->SMTPDebug = $this->openDebug ? SMTP::DEBUG_CONNECTION : SMTP::DEBUG_OFF;

        $this->mailObj->Host       = $this->config['host'];                     //将SMTP服务器设置为通过发送 例如：smtp.qq.com
        $this->mailObj->SMTPAuth   = !isset($this->config['auth']) || (bool)$this->config['auth'];    //启用SMTP身份验证
        $this->mailObj->Username   = $this->config['username'];                 //SMTP username 例如：123456@qq.com
        $this->mailObj->Password   = $this->config['password'];                 //SMTP password //客户端授权密码，注意不是登录密码
        $this->mailObj->SMTPSecure = $this->config['secure'] ?? PHPMailer::ENCRYPTION_SMTPS;               //使用ssl协议 - 启用隐式TLS加密
        $this->mailObj->Port       = $this->config['port'] ?? 465;              //要连接的TCP端口；如果已设置，请使用587 `SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS`

        // 发件人
        $form = config("tools_mail.from");
        $this->mailObj->setFrom($form['address'], $form['name']);         //设置邮箱的来源，邮箱与$this->mailObj->Username一致，名称随意

        // 内容
        $this->mailObj->Subject = $this->sendData['title'];              //邮件标题
        $this->mailObj->Body    = $this->sendData['content'];              //邮件内容

        // 收件人
        foreach ($this->sendData['to'] as $user) {
            $this->mailObj->addAddress($user['mail'], $user['name']);     //设置收件人邮箱和名称，可以多次调用，设置多个收件人
        }

        // 抄送人
        foreach ($this->sendData['cc'] as $user) {
            $this->mailObj->addCC($user['mail'], $user['name']);     //设置抄送人邮箱和名称，可以多次调用，设置多个抄送人
        }

        // 密送人
        foreach ($this->sendData['bcc'] as $user) {
            $this->mailObj->addBCC($user['mail'], $user['name']);     //设置密送人邮箱和名称，可以多次调用，设置多个密送人
        }

        // 回复地址
        $replyTo = $this->sendData['reply_to'];
        !empty($replyTo) && $this->mailObj->addReplyTo($replyTo['mail'], $replyTo['name']);

        // 附件
        foreach ($this->sendData['attachment'] as $file) {
            $this->mailObj->addAttachment($file['path'], $file['name']);    // 添加附件
        }
        return $this;
    }

    /**
     * 调用PHPMailer类的方法
     * eg: $mail->clearAddresses();
     *     $mail->clearCCs();
     *     $mail->clearBCCs();
     *     ...
     */
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

    public function __get($name)
    {
        return $this->mailObj->$name;
    }

    public function __set($name, $value)
    {
        $this->mailObj->$name = $value;
        return $this;
    }
}