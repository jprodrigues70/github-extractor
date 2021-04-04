<?php

namespace App;

use Exception;
use PHPMailer\PHPMailer\PHPMailer;

class Mailer
{
    private $subject = '';
    private $message = '';
    private $senderEmail = '';
    private $senderName = '';
    private $recipientEmail = '';
    private $recipientName = '';

    private $protocol = 'smtp';
    private $charSet = 'UTF-8';
    private $smtpSecure = 'tls';
    private $smtpDebug = 1;
    private $smtpAuth = TRUE;
    private $port = 587;
    private $host = 'smtp.gmail.com';

    public function __construct(string $subject = '', string $message = '', string $login = '', string $password = '', $config = [])
    {

        $this->subject = $subject ? $subject : env('MAIL_SUBJECT');
        $this->message = $message ? $message : env('MAIL_MESSAGE');
        $message = $this->parseMessage();

        foreach ($config as $key => $value) {
            if (isset($this->{$key})) {
                $this->{$key} = $value;
            }
        }
    }

    public function from(Sender $sender): Mailer
    {
        $this->senderEmail = $sender->email;
        $this->senderName = $sender->name;
        return $this;
    }

    public function to(Recipient $recipient): Mailer
    {
        $this->recipientEmail = $recipient->email;
        $this->recipientName = $recipient->name;
        return $this;
    }

    public function validateConfig()
    {
        if (empty(env('MAIL_LOGIN', $this->login)) || empty(env('MAIL_PASSWORD', $this->password))) {
            throw new Exception("You will need to set the credentials");
            die();
        }
        $senderEmail = $this->senderEmail ? $this->senderEmail : env('MAIL_SENDER');

        if (empty($senderEmail) || empty($this->recipientEmail)) {
            throw new Exception("You will need to set the Sender and the Recipient");
            die();
        }
    }

    public function send($verbose = false, $logFail = true)
    {
        $this->validateConfig();
        $message = $this->parseMessage();

        $mail = new PHPMailer();

        $mail->IsSMTP();
        $mail->Mailer = $this->protocol;

        $mail->CharSet = env('MAIL_CHARSET', $this->charSet);
        $mail->SMTPSecure = env('MAIL_SECURE', $this->smtpSecure);
        $mail->Host       = env('MAIL_HOST', $this->host);
        $mail->Port       = env('MAIL_PORT', $this->port);

        $mail->Username   = env('MAIL_LOGIN', $this->login);
        $mail->Password   = env('MAIL_PASSWORD', $this->password);
        $mail->SMTPAuth   = env('MAIL_SMTP_AUTH', $this->smtpAuth);

        if ($verbose) {
            $mail->SMTPDebug  = env('MAIL_SMTP_DEBUG', $this->smtpDebug);
        }

        $mail->IsHTML(true);

        $mail->AddAddress($this->recipientEmail, $this->recipientName);

        $mail->SetFrom($this->senderEmail ? $this->senderEmail : env('MAIL_SENDER'), $this->senderName ? $this->senderName : env('MAIL_SENDER_NAME'));

        $mail->Subject = $this->subject;

        $message = $this->parseMessage();

        $mail->MsgHTML($message);
        echo "     ";
        if (!$mail->Send()) {
            echo "\033[31mFAIL!\033[0m\n" . "      $mail->ErrorInfo";
            $file = __DIR__ . '/../log/' . date('Y-m-d') . '.csv';
            $fp = fopen($file, 'a');
            fputcsv($fp, ["[" . date('H:i:s') . "]...", $this->recipientName, $this->recipientEmail, $mail->ErrorInfo]);
            fclose($fp);
        } else {
            echo "\033[32mOK!\033[0m\n";
        }
    }

    public function parseMessage()
    {
        $message = preg_replace(["/{{ name }}/", "/{{name}}/"], $this->recipientName, $this->message);
        $message = preg_replace(["/{{ email }}/", "/{{email}}/"], $this->recipientEmail, $message);

        return $message;
    }
}
