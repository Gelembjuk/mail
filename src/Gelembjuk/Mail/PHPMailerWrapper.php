<?php
/**
* PHPMailer sends email using PHPMailer. It is wrapper around PHPMailer
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

use \PHPMailer\PHPMailer\PHPMailer;
use \PHPMailer\PHPMailer\SMTP;
use \PHPMailer\PHPMailer\Exception;

class PHPMailerWrapper extends MailerBase {
	/**
	 * Sends email with PHPMailer
	 * All email addresses can be in format: just email, array with `name` and `email` keys.
	 * 
	 * @param strintg $email Email address to send to
	 * @param strintg $subject EMail subject
	 * @param strintg $body EMail body
	 * @param strintg $from From email address
	 * @param strintg $replyto Reply to email address
	 * @param strintg $ccemail CC email address
	 * @param strintg $bccemail BCC email address
	 * 
	 * @return boolean Success or not
	 */
	public function sendEmail($email,$subject,$body,$from,$replyto='',$ccemail='',$bccemail='',$textemail=false) {
		$this->checkArguments($email,$subject,$body,$from,$replyto,$ccemail,$bccemail);
		
		$mail = $this->buildMailerObj();

		// set up email credentials

		if (is_array($replyto)) {
			$mail->AddReplyTo($replyto['address'],$replyto['name']);
		} elseif ($replyto != '') {
			$mail->AddReplyTo($replyto,'');
		}
                
        if (is_array($from)) {
			$mail->Sender=$from['address'];
			$mail->SetFrom($from['address'],$from['name']);
			$mail->From = $from['address'];
			$mail->FromName = $from['name'];
		} else {
			$mail->Sender=$from;
			$mail->SetFrom($from);
			$mail->From = $from;
		}

        $mail->Subject    = $subject;
               
		if($textemail){
			$mail->Body = $body;
		}else{
			$mail->MsgHTML($body);

			// prepare text version automatically
			$h2t = new \Html2Text\Html2Text($body);
			$mail->AltBody    = $h2t->getText();
		}

		if (is_array($email)) {
			$mail->AddAddress($email['address'],$email['name']);
		} else {
			$mail->AddAddress($email);
		}
		
		if (is_array($ccemail)) {
			$mail->AddCC($ccemail['address'],$ccemail['name']);
		} elseif ($ccemail != '') {
			$mail->AddCC($ccemail);
		}
		
		if (is_array($bccemail)) {
			$mail->AddCC($bccemail['address'],$bccemail['name']);
		} elseif ($bccemail != '') {
			$mail->AddCC($bccemail);
		}

		$att=1;
		$success=false;
                
		// try to send 5 times. for case when SMTP is not very stable
		do {
			if(!$mail->Send()) {
				$att++;
				sleep(1);
			}else{
				$success=true;
			}
		} while (!$success && $att < 5);
		
		if (!$success) {
			$this->logQ('EMail sending error: '.$mail->ErrorInfo,'mailererror');
			
			throw new \Exception(sprintf('EMail sending error: %s',$mail->ErrorInfo));
		}
		return true;
	}
	protected function buildMailerObj()
	{
		// create PHPMailer object
		$mail = new \PHPMailer();

		$mail->SMTPDebug  = 0; 
		$mail->CharSet = 'UTF-8';
				
				// select mailsystem, get it from options
		if ($this->options['mailsystem'] == 'mail') {
			$mail->IsMail();
			$this->logQ("Sending with mail",'mailsend|phpmailer');

			return $mail;
		}
		$mail->IsSMTP();
		
		$this->logQ("Sending with SMTP",'mailsend|phpmailer');
		
		if ($this->options['smtp_auth'] === 'no' ||
			$this->options['smtp_auth'] === 'n' ||
			(is_bool($this->options['smtp_auth']) && !$this->options['smtp_auth'])) {
			$mail->SMTPAuth   = false;
			
			$this->logQ("SMTP Auth is Off",'mailsend|phpmailer');
		} else {
			$mail->SMTPAuth   = true;
			$mail->Username   = $this->options['smtp_user'];
			$mail->Password   = $this->options['smtp_password'];
			
			$this->logQ("SMTP Auth is On and user ".$mail->Username,'mailsend|phpmailer');
		}
		
		if(isset($this->options['smtp_secure']) && 
			($this->options['smtp_secure'] && is_bool($this->options['smtp_secure']) 
			||
			$this->options['smtp_secure'] === 'y'
			||
			$this->options['smtp_secure'] === 'yes')) {
		
			$mail->SMTPSecure = ($this->options['smtp_secure_proto'] != '') ? $this->options['smtp_secure_proto'] : "tls";
			$this->logQ("SMTP secure is On with proto ".$mail->SMTPSecure,'mailsend|phpmailer');
		}
		$mail->Host = $this->options['smtp_host'];
		$mail->Port = $this->options['smtp_port'];
		
		$this->logQ("SMTP host ".$mail->Host.":".$mail->Port,'mailsend|phpmailer');

		return $mail;
	}
}
