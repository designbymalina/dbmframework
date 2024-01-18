<?php
/*
 * DbM Framework
 * All code copyright Design by Malina
 * DbM: www.dbm.org.pl
 */

declare(strict_types=1);

namespace App\Service;

use PHPMailer\PHPMailer\PHPMailer;
// use PHPMailer\PHPMailer\Exception; // as PHPMailerException; // Not used
// use PHPMailer\PHPMailer\SMTP; // Not used
use Exception;

class MailerService
{
    public function sendMessage(array $params): void
    {
        // Variables
        $error = false;
        !empty($params['subject']) ? $subject = $params['subject'] : $subject = APP_NAME;
        !empty($params['sender_name']) ? $sender_name = $params['sender_name'] : $sender_name = APP_NAME;
        !empty($params['sender_email']) ? $sender_email = $params['sender_email'] : $sender_email = APP_EMAIL;
        !empty($params['page_address']) ? $page_address = $params['page_address'] : $page_address = APP_PATH;
        !empty($params['recipient_name']) ? $recipient_name = $params['recipient_name'] : $recipient_name = null;
        !empty($params['recipient_email']) ? $recipient_email = $params['recipient_email'] : $recipient_email = null;
        !empty($params['message_template']) ? $message_template = $params['message_template'] : $message_template = null;
        !empty($params['attachment_filename']) ? $attachment_filename = $params['attachment_filename'] : $attachment_filename = null;
        !empty($params['attachment_rename']) ? $attachment_rename = $params['attachment_rename'] : $attachment_rename = null;
        !empty($params['token']) ? $token = $params['token'] : $token = null;

        try {
            // Path to template message and filename attachment
            $path_message = BASE_DIRECTORY . 'data/message/' . $message_template;
            $path_attachment = BASE_DIRECTORY . 'data/attachment/' . $attachment_filename;

            // Message subject
            //$subject = "$subject ($statement)";

            // Message content, with $this->replaceContent()
            $content = file_get_contents($path_message);
            $replace = array($subject, $sender_name, $recipient_name, $page_address, null, $token);
            $content = $this->replaceContent($content, $replace);

            // PHPMailer
            $mail = new PHPMailer(); // Passing true enables exceptions
            $mail->CharSet = "UTF-8";

            // PHPMailer optional SMTP
            if (MAIL_SMTP === true) {
                $mail->IsSMTP(); // telling the class to use SMTP
                $mail->Host = MAIL_HOST; // SMTP server
                $mail->SMTPAuth = true; // enable SMTP authentication
                $mail->Username = MAIL_USERNAME; // SMTP account username
                $mail->Password = MAIL_PASSWORD; // SMTP account password
            }

            // PHPMailer c.d.
            $mail->SetFrom($sender_email, $sender_name);
            $mail->AddAddress($recipient_email, $recipient_name); // optional Name
            $mail->Subject = $subject;
            //$mail->IsHTML(true); // option for Body with HTML
            //$mail->Body = "$content"; // option
            $mail->MsgHTML($content);

            // Add attachment
            if (is_file($path_attachment)) {
                $mail->addAttachment($path_attachment, $attachment_rename);
            }

            // Send message
            if (!$mail->send()) {
                $error = "[PHPMailer] " . $mail->ErrorInfo;
            }
        } catch (Exception $exception) {
            $error = "[Exception] " . $exception->getMessage();
        }

        ### Error logger
        if ($error !== false) {
            $this->errorLogger($error);
        }
    }

    /*
     * Replace message code: subject, sender_name, recipient_name, page_address, attachment, token
     */
    private function replaceContent(string $content, array $replace = []): string
    {
        $string = array('{subject}', '{sender_name}', '{recipient_name}', '{page_address}', '{attachment}', '{token}');
        $result = str_replace($string, $replace, $content);

        return $result;
    }

    private function errorLogger(mixed $error): void
    {
        $dir = BASE_DIRECTORY . 'var' . DS . 'log' . DS . 'mailer' . DS;

        if (!is_dir($dir)) {
            mkdir($dir, 0755, true);
        }

        if (is_array($error)) {
            $error = json_encode($error);
        }

        $path = $dir . date('ym') . '_error.log';

        $data = "TIME: " . date('Y-m-d H:i:s');
        $data .= "\r\n    REQUEST: " . json_encode($_REQUEST);
        $data .= "\r\n    ERROR: " . $error . "\r\n";

        $file = fopen($path, 'a');
        fwrite($file, $data);
        fclose($file);
    }
}
