<?php
/**
* Gmail sends email using PHPMailer + Gmail oAuth. It is wrapper around PHPMailer
* It was created to have different email processors with same interface
*
* LICENSE: MIT
*
* @category   Mail
* @package    Gelembjuk/Mail
* @copyright  Copyright (c) 2015-2024 Roman Gelembjuk. (http://gelembjuk.com)
* @version    2.0
* @link       https://github.com/Gelembjuk/mail
*/

namespace Gelembjuk\Mail;

use PHPMailer\PHPMailer\PHPMailer;
use League\OAuth2\Client\Provider\Google;
use PHPMailer\PHPMailer\OAuth;
use PHPMailer\PHPMailer\SMTP;
use PHPMailer\PHPMailer\Exception;

class Gmail extends PHPMailerWrapper {
    protected function buildMailerObj()
	{
		// create PHPMailer object
		$mail = new PHPMailer();

		$mail->SMTPDebug  = 0; 
		$mail->CharSet = 'UTF-8';
				
		$mail->IsSMTP();
		
		$this->logQ("Sending with SMTP/Gmail",'mailsend|phpmailer');
		
        $mail->SMTPAuth = true;
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_SMTPS;
		$mail->Host = 'smtp.gmail.com';
		$mail->Port = 465;

        $mail->AuthType = 'XOAUTH2';
		
		$this->logQ("SMTP host ".$mail->Host.":".$mail->Port,'mailsend|phpmailer');

        $provider = new Google(
            [
                'clientId' => $this->options['google_client_id'],
                'clientSecret' => $this->options['google_client_secret'],
            ]
        );

        $mail->setOAuth(
            new OAuth(
                [
                    'provider' => $provider,
                    'clientId' => $this->options['google_client_id'],
                    'clientSecret' => $this->options['google_client_secret'],
                    'refreshToken' => $this->options['refresh_token'],
                    'userName' => $this->options['email'],
                ]
            )
        );

		return $mail;
	}
}