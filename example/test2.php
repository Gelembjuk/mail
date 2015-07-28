<?php 

/**
 * Example. Usage of Gelembjuk/Mail . Send simple contact email.
 * The example shows how to easy create wel formatted emails and send them to users
 * The package allows to change sending engine easy without many changes in your PHP code.
 * Different mail systems have same interface, change only mailer clas name.
 * 
 * This example is part of gelembjuk/templating package by Roman Gelembjuk (@gelembjuk)
 */

// ===================== INIT SECTION =================================
// path to your composer autoloader
require ('vendor/autoload.php');

$thisdir = dirname(__FILE__).'/';

// create logger object
$logger1 = new Gelembjuk\Logger\FileLogger(
	array('logfile' => $thisdir.'/logs/log.txt','groupfilter' => 'all'));
	
if (!$logger1->logFileIsWritable()) {
	echo '<font color="red">No access to write to log file </font>';
	exit;
}

// choose what mailer type do you want to use (null,phpmailer,phpnative)
$mailertype = 'null';

// it is really used only if  $mailertype is 'phpmailer'
$maileroptions = array(
	'logger' => $logger1,
	'format' => array( // email formater options
		'locale' => '', // no any locale for init
		'deflocale' => 'en',
		'templateprocessorclass' => null, // create default Smarty
		'templatecompiledir' => $thisdir.'/email_tmp/',
		'templatespath' => $thisdir.'/email_intern_templates/'
		),
	// other options related to specified email sending class
	// next options are only for PHPMailer
	'mailsystem' => 'smtp', // for phpmailer it can be smtp or mail
	'smtp_host' => 'smtp_host', 	// aka smtp.gmail.com
	'smtp_port' => 25,		// aka 587
	'smtp_secure' => false,		// or true in case of ssl/tls
	'smtp_auth' => true,		// usually true
	'smtp_user' => 'smtp user',	// aka your gmail account
	'smtp_password' => 'smtp password', // your smtp password (gmail etc)
);

// make email sending object
switch ($mailertype) {
	case 'phpmailer':
		$mailer = new \Gelembjuk\Mail\PHPMailer();
		break;
	case 'phpnative':
		$mailer = new \Gelembjuk\Mail\PHPNative();
		break;
	default:
		$mailer = new \Gelembjuk\Mail\NullMail();
		// check if log file is writable as 
}

// init email processor
$mailer->initMailer($maileroptions);

// ===================== TEST SECTION =================================

$contactdata = array(
	'name' => 'John Smith',
	'message' => 'This is my contact message'
);

// SEND CONTACT EMAIL in default locale (en)

$mailer->formatAndSendEmail(
	'contact',  // template name
	$contactdata,
	'target_email@gmail.com',
	'from_email@gmail.com'
	);
	
// now set `de` locale and send again German copy of email
$mailer->setFormatterOption('locale','de');

// send same email but now templates must be from different locale
$mailer->formatAndSendEmail(
	'contact',  // template name
	$contactdata,
	'target_email@gmail.com',
	'from_email@gmail.com'
	);
	
// now set `fr` locale and send again French copy of email
$mailer->setFormatterOption('locale','fr');

// send same email but now templates must be from different locale
$mailer->formatAndSendEmail(
	'contact',  // template name
	$contactdata,
	'target_email@gmail.com',
	'from_email@gmail.com'
	);
	
echo 'All emails were sent. ';

if ($mailertype == 'null') {
	echo 'Check log file to see results';
}
