<?php
/**
* PHPNative sends email using PHP mail() function.
* It was created to have different email processors with same interface
*
* LICENSE: MIT
*
* @category   Mail
* @package    Gelembjuk/Mail
* @copyright  Copyright (c) 2015 Roman Gelembjuk. (http://gelembjuk.com)
* @version    1.0
* @link       https://github.com/Gelembjuk/mail
*/
namespace Gelembjuk\Mail;

class PHPNative extends MailerBase {
	/**
	 * Sends email with mail() function
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
		
                $headers = 'From: '.$this->getEmailHeader($from). "\r\n";
                
                $headers .= 'Reply-To: '.$this->getEmailHeader($replyto). "\r\n";
                
                $headers .= 'Cc: '.$this->getEmailHeader($ccemail). "\r\n";
                
                $headers .= 'Bcc: '.$this->getEmailHeader($bccemail). "\r\n";
		
                
		$headers .= 'Content-Type: text/html; charset=utf-8'. "\r\n";
		$headers .= 'X-Mailer: '. (($this->options['mailername'] != '')?$this->options['mailername']:'Gelembjuk-Mail Mailer');

		if (is_array($from)) {
			$from = $from['address'];
		}
		
		if (is_array($email)) {
			$email = $email['address'];
		}
		
		// call mail() function, hide errors
		$result = @mail($email,$subject,$body,$headers,"-f".$from);
		
                return $result;
	}
	/**
	 * prepares email address header. An address can contain only email or name also
	 * 
	 * @param string|array $email EMail address
	 */
	protected function getEmailHeader($email) {
		if (is_array($email)) {
			return $email['name'].'<'.$email['address'].'>';
		}
		return '<'.$email.'>';
	}
}
