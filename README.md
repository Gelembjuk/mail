## Gelembjuk/Mail

PHP Package to send email with different mailers without changes in the PHP code and to format email messages using templater (Smarty, Twig). 


### Installation
Using composer: [gelembjuk/mail](http://packagist.org/packages/gelembjuk/mail) ``` require: {"gelembjuk/mail": "*"} ```

### Configuration

```php

$formatteroptions = array( 
	'locale' => '', 
		// optional, add if you need international support
	'deflocale' => 'en', 
		// default locale. Optional as well, template from it are used if no in a `locale`
	'templateprocessorclass' => null, 
		// if not provided then smarty is used. this is one of classes from Gelembjuk/Templating
	'templatecompiledir' => $thisdir.'/email_tmp/',  
		// directory used to store tempopary files of cache engine . It i reuired for Smarty but not needed for Twig
	'templatespath' => $thisdir.'/email_templates/'
		// a base path where email templates and files with subjects are stored
	);
	
$maileroptions = array(
	'logger' => $logger,
		// (optional) Logger, instance of Gelembjuk\Logger\FileLogger, 
	'format' => $formatteroptions,
		// formatter options
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

```

### Usage

```php

$mailer = new \Gelembjuk\Mail\PHPMailer();

// OR
// $mailer = new \Gelembjuk\Mail\PHPNative(); // uses mail()
// OR 
// $mailer = new \Gelembjuk\Mail\NullMail(); // is only for testing, doesn't send only log

$mailer->initMailer($maileroptions);

$email_data = array(
	'user' => 'John Smith',
	'activationlink' => 'http://our_site.com/activateaccount/code'
);

$mailer->formatAndSendEmail(
	'activate',  // template name
	$email_data,
	'john_smith@gmail.com', // send to email
	'from_email@gmail.com'  // send from email
	);

```

#### Templates

In this example a `templatespath` must contain 3 files: 

activate.htm - template for this email, 

```html

<h3>Hello {$user}</h3>

<p>Click the link <a href="{$activationlink}">{$activationlink}</a></p>

```

out_default.htm - common `out` template used fro all emails (aka common header/footer for all emails)

```html

<table>
<tr><td>Header. Company name, logo etc</td></tr>

<tr><td>{$EMAILCONTENT}</td></tr>

<tr><td>Footer. Contacts, signature etc</td></tr>
</table>

```

subjects.xml - contains text of email subjects for templates

```xml

<?xml version="1.0" encoding="utf-8"?>
<templates>
	<activate>
		<subject>Activate your new account</subject>
		<description>This email is sent to a user when new account is registered</description>
	</activate>
</templates>

```

### Different template engines

Now Smarty and Twig can be used. See https://github.com/Gelembjuk/templating for more information. More supported engines can be added later.

### Internationalization

See the test2.php in examples folder in this project. It shows how to organize structure of locales folders.

```php

// send email with german template
$mailer->setFormatterOption('locale','de');

$mailer->formatAndSendEmail(
	'activate',  // template name
	$email_data,
	'john_smith@gmail.com', // send to email
	'from_email@gmail.com'  // send from email
	);
	
// send same email with french template
$mailer->setFormatterOption('locale','fr');

$mailer->formatAndSendEmail(
	'activate',  // template name
	$email_data,
	'john_smith@gmail.com', // send to email
	'from_email@gmail.com'  // send from email
	);

```

### Author

Roman Gelembjuk (@gelembjuk)

