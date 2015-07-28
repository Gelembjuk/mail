<?php

/**
* Base class for mail processors
* The class sets up some base interface for mailers and does initial checks for an email
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

abstract class MailerBase {
	// inlude logger trait to have logging functionality
	use \Gelembjuk\Logger\ApplicationLogger;

	/**
	 * Mailer options
	 * 
	 * @var array
	 */
	protected $options;
	/**
	 * Email formater options
	 * 
	 * @var array
	 */
	protected $formatoptions = array();
	/**
	 * Init mailer. Set uo options
	 * 
	 * @param array $options List of mailer options
	 */
	public function initMailer($options) {
		$this->options = $options;
		
		if (isset($this->options['format'])) {
			$this->formatoptions = $this->options['format'];
			unset($this->options['format']);
		}
		
		if (isset($this->options['logger'])) {
			$this->setLogger($this->options['logger']);
			
			if (!isset($this->formatoptions['logger'])) {
				$this->formatoptions['logger'] = $this->options['logger'];
			}
			
			unset($this->options['logger']);
		}
		
		
	}
	/**
	 * Set formatter option 
	 * 
	 * @param string $key Option key
	 * @param mixed $val Option value
	 */
	public function setFormatterOption($key,$val) {
		$this->formatoptions[$key] = $val;
	}
	/**
	 * Send email function. It must be defined in child classes
	 */
	public function formatAndSendEmail($template,$data,$email,$from,
		$replyto='',$ccemail='',$bccemail='',$outtemplate = null,$textemail=false) {
		
		$this->logQ($this->formatoptions['locale'],'formatter');
		
		$formatter = new EmailFormat($this->formatoptions);
		
		$templatedata = $formatter->fetchTemplate($template,$data,$outtemplate);
		
		return $this->sendEmail(
			$email,
			$templatedata['subject'],
			$templatedata['body'],
			$from,
			$replyto,
			$ccemail,
			$bccemail,
			$textemail);
	}
	/**
	 * Send email function. It must be defined in child classes
	 */
	abstract public function sendEmail($email,$subject,$body,$from,
		$replyto='',$ccemail='',$bccemail='',$textemail=false);
	/**
	 * Check if all data are present and are in correct format
	 * All email addresses can be in format: just email, array with `name` and `email` keys.
	 * 
	 * @param strintg $email Email address to send to
	 * @param strintg $subject EMail subject
	 * @param strintg $body EMail body
	 * @param strintg $from From email address
	 * @param strintg $replyto Reply to email address
	 * @param strintg $ccemail CC email address
	 * @param strintg $bccemail BCC email address
	 */
	protected function checkArguments($email,$subject,$body,$from,$replyto,$ccemail,$bccemail) {
		// do checks of email body and subject
		if (trim($subject) == '') {
			throw new \Exception('EMail subject can not be empty');
		}
		
		if (trim($body) == '') {
			throw new \Exception('EMail body can not be empty');
		}
		
		// check if all addresses are correct
		$this->isCorrectEmailAddress($email,'Email');
		
		$this->isCorrectEmailAddress($from,'From Email');
		
		if ($replyto != '') {
			$this->isCorrectEmailAddress($replyto,'ReplyTo Email');
		}
		
		if ($ccemail != '') {
			$this->isCorrectEmailAddress($ccemail,'CC Email');
		}
		
		if ($bccemail != '') {
			$this->isCorrectEmailAddress($bccemail,'BCC Email');
		}
		
		return true;
	}
	/**
	 * Checks email address
	 * 
	 * @param string|array $email Email address or array with keys `name` and `email`
	 * @param string $title Title of email address , used for error message
	 * 
	 * @return boolean Success or not
	 */
	protected function isCorrectEmailAddress($email,$title='') {
		if (is_array($email)) {
			$email = $email['address'];
		}
		
		if ($title == '') {
			$title = 'Email';
		}
		
		if (trim($email) == '') {
			throw new \Exception($title.' address can not be empty');
		}
		
		// email can be in breakets
		if (preg_match('!<.+>!',$email,$f)) {
			$email = $f[1];
		}
		
		// check against regular expression
		$regexp = '!^[_a-z0-9-+]+(\\.[_a-z0-9-+]+)*@[a-z0-9-]+(\\.[a-z0-9-]+)*(\\.[a-z]{2,3})$!i';
		
		if (strpos($email,',') > 0) {
			// can be list of meails
			$emails = explode(',',$email);
			
			foreach ($emails as $email) {
				if (trim($email) == '') {
					return false;
				}
				if (!preg_match($regexp, $email)) {
					throw new \Exception($title.' address has wrong format');
				}
			}
			return true;
		}
		
		if (!preg_match($regexp, $email)) {
			throw new \Exception($title.' address has wrong format');
		}
		return true;
	}
}
