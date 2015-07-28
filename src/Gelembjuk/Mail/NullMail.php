<?php

/**
* NullMail is presude Mailer processor. It doesn't really send email
* just logs it. 
* The class can be used for debugging and testing
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

class NullMail extends MailerBase {
	/**
	 * Emulate email sending. Just log everything
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
		$this->logQ('============ NEW EMAIL ====================','mailsend');
		
               	if (is_array($email)) {
               		$this->logQ('To : '.$email['name'].'<'.$email['address'].'>','mailsend');
                } else {
                	$this->logQ('To : '.$email,'mailsend');
                }
                
               	if (is_array($from)) {
               		$this->logQ('From : '.$from['name'].'<'.$from['address'].'>','mailsend');
                } else {
                	$this->logQ('From : '.$from,'mailsend');
                }
		
		if (is_array($replyto)) {
			$this->logQ('ReplyTo : '.$replyto['name'].'<'.$replyto['address'].'>','mailsend');
                } elseif ($replyto != '') {
                	$this->logQ('ReplyTo : '.$replyto,'mailsend');
                }
                
                if (is_array($ccemail)) {
			$this->logQ('CC : '.$ccemail['name'].'<'.$ccemail['address'].'>','mailsend');
                } elseif ($ccemail != '') {
                	$this->logQ('CC : '.$ccemail,'mailsend');
                }
                
                if (is_array($bccemail)) {
			$this->logQ('BCC : '.$bccemail['name'].'<'.$bccemail['address'].'>','mailsend');
                } elseif ($bccemail != '') {
                	$this->logQ('BCC : '.$bccemail,'mailsend');
                }
                
                $this->logQ('Subject: '.$subject,'mailsend');
                
                $this->logQ($body,'mailsend');
                
                return true;
	}
}
