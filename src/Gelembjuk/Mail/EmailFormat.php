<?php

/**
* EmailFormat class helps to prepare email message from a template.
* The class supports internationalization, selectes a template based on a locale. 
* EmailFormat uses an object with the Gelembjuk/Templating/TemplatingInterface interface to fetch templates.
* One of templating engine (Smarty etc) must be installed to use this class .
* By default , Gelembjuk\Templating\SmartyTemplating is used as templating engine
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

class EmailFormat {
	// inlude logger trait to have logging functionality
	use \Gelembjuk\Logger\ApplicationLogger;
	
	/**
	* Templates directory path. A directory where email templates are stored
	*
	* @var string
	*/
	protected $templatespath;
	/**
	* Locale. 2 symbols identifying a language. 
	*
	* @var string
	*/
	protected $locale = '';
	/**
	* Default Locale. 2 symbols identifying a language.
	* Is used when no template for required locale is found 
	*
	* @var string
	*/
	protected $deflocale = '';
	/**
	* Prefix for out template file, common template used for all emails 
	*
	* @var string
	*/
	protected $outtemplateprefix;
	/**
	* Templates fetching class. Must be a class implementing the Gelembjuk\Templating\TemplatingInterface intetraface
	* If not provided with options then Gelembjuk\Templating\SmartyTemplating is used
	*
	* @var string
	*/
	protected $templateprocessorclass;
	/**
	* Templates fetching class options. Is related to what class is used to fetch templates
	*
	* @var array
	*/
	protected $tepmlateprocessorinitoptions;
	/**
	 * Constructor
	 * Accepts array of options to init an object
	 * 
	 * templatespath	- Path to a directory with emails templates
	 * locale			- 2 symbols language identifier
	 * deflocale		- Default locale. It will be used if provided locale has no template
	 * outtemplateprefix - Prefix for `out` template files used for all emails (common header, footer)
	 * procoptions		- Template engine options
	 * templatecompiledir - If procoptions is not provided then this value is set as the only option compiledir
	 * templateprocessorclass - Class of templating engine based on Gelembjuk\Templating\TemplatingInterface interface
	 * logger		- Logger object. Optional
	 * 
	 * @param array $options Options
	 */
	public function __construct($options = array()) {
		if (isset($options['templatespath'])) {
			$this->templatespath = $options['templatespath']; 
		}
		
		if ($this->templatespath == '' || !is_dir($this->templatespath)) {
			throw new \Exception('Templates path is not set in EmailFormat');
		}
		
		if ($options['locale'] != '') {
			$this->setLocale($options['locale']);
		}
		
		if (isset($options['deflocale'])) {
			$this->deflocale = $options['deflocale']; 
		}
		
		$this->outtemplateprefix = 'out_';
		
		if (isset($options['outtemplateprefix'])) {
			$this->outtemplateprefix = $options['outtemplateprefix']; 
		}
		
		if (is_array($options['procoptions'])) {
			$this->tepmlateprocessorinitoptions = $options['procoptions']; 
		} elseif ($options['templatecompiledir'] != '') {
			$this->tepmlateprocessorinitoptions = array(
				'compiledir' => $options['templatecompiledir'],
				'extension' => 'htm' // we use htm extension for template files
				);
		} else {
			throw new \Exception('No config options for template pocessor in EmailFormat class');
		}
		
		$this->tepmlateprocessorinitoptions['templatepath'] = $this->templatespath;		
		$this->tepmlateprocessorinitoptions['extension'] = 'htm';
		
		if ($options['templateprocessorclass']) {
			$this->templateprocessorclass = $options['templateprocessorclass'];
		} else {
			$this->templateprocessorclass = '\\Gelembjuk\\Templating\\SmartyTemplating';
		}
		
		if (is_object($options['logger'])) {
			$this->setLogger($options['logger']);
		}
		
		if (!class_exists($this->templateprocessorclass)) {
			throw new \Exception(sprintf('Temlating class %s not found',$this->templateprocessorclass));
		}
	}
	/**
	 * Set new locale 
	 */
	public function setLocale($locale) {
		$this->locale = $locale;
	}
	/**
	 * Generate email body and subject based on templates and options
	 * 
	 * @param string $template Email template file
	 * @param array $data Template data to fetch (insert in a template)
	 * @param string $outtemplate Out template to use for email. Default is `default` (plus prefix). Set to empty string to skip out template
	 * 
	 * @return array Array with 2 keys: body and subject
	 */
	public function fetchTemplate($template,$data = array(),$outtemplate = null) {
		// template is required
		if (trim($template) == '') {
			throw new \Exception('No template to format email');
		}
		
		$templatedata = $this->getTemplateData($template);
		
		// use default out template if is not provided
		if ($outtemplate === null) {
			$outtemplate = 'default';
		}
		
		// template is not found in any locale
		if (!is_array($templatedata)) {
			throw new \Exception(sprintf('Email template %s not found',$template));
		}
		
		// if email template provided.
		if ($outtemplate != '') {
			$outtemplate_real = $this->getOutTemplate($outtemplate);
			
			if (!$outtemplate_real) {
				throw new \Exception(sprintf('Email template %s not found',$outtemplate));
			}
			
			$outtemplate = $outtemplate_real;
		}
		
		// create template processor object
		$class = $this->templateprocessorclass;
		$templating = new $class();
		
		// init template processor
		$templating->init($this->tepmlateprocessorinitoptions);
		
		// check if template processor can access a template
		if (!$templating->checkTemplateExists($templatedata['file'])) {
			throw new \Exception(sprintf('Email template %s not found',$template));
		}
		
		// set data to fetch with the template
		$templating->setVars($data);
		
		// fetch subject template (it also can contains some wildcards)
		$subject = $templating->fetchString($templatedata['subject']);
		
		$templating->setTemplate($templatedata['file']);
		
		// generate email body
		$emailhtml = $templating->fetchTemplate();
		
		// insert a body to a common outer template
		if ($outtemplate != '') {
			$templating->setVar('EMAILCONTENT',$emailhtml);
			
			$templating->setTemplate($outtemplate);
			
			$emailhtml = $templating->fetchTemplate();
		}
		
		// return body and subject
		return array('body'=>$emailhtml,'subject'=>$subject);
	}
	/**
	 * Returns template data according to locale settings
	 * 
	 * @param string $template Template file
	 */
	protected function getTemplateData($template) {
		// firstly, check for current locale, if it was set
		if ($this->locale != '') {
			$tdata = $this->getTemplateDataForLocale($template,$this->locale);
			
			// found for this locale. return
			if (is_array($tdata)) {
				return $tdata;
			}
		}
		
		// check for default locale
		// for example, didn't find for locale `ge` but found for default `en`
		if ($this->deflocale != '') {
			$tdata = $this->getTemplateDataForLocale($template,$this->deflocale);
			
			if (is_array($tdata)) {
				return $tdata;
			}
		}
		
		// if nothing found for both locales then check in root templates folder
		// this is normal case for non-internation application where 
		// locales are not used
		$tdata = $this->getTemplateDataForLocale($template,'');
			
		return $tdata;
	}
	/**
	 * Get template information for specified locale
	 * 
	 * @param string $template Template file
	 * @param string $locale Locale or empty string
	 * 
	 * @return array Template subject and file path
	 */
	protected function getTemplateDataForLocale($template,$locale) {
		// path to document with subjects
		$metafile = $this->templatespath.'/'.$locale.'/subjects.xml';
		
		if (!file_exists($metafile)) {
			// file with subjects not found
			return null;
		}
		
		// load and parse document with subjects
		$xml = @file_get_contents($metafile);
		
		if (!$xml || $xml == '') {
			return null;
		}
		
		$array = \LSS\XML2Array::createArray($xml);
		
		// our template is not found in this document so it is not there
		if (!isset($array['templates'][$template])) {
			return null;
		}
		
		$templatefile = $this->templatespath.'/'.$locale.'/'.$template.'.htm';
		
		// check if template file exists for this locale
		if (!file_exists($templatefile)) {
			return null;
		}
		
		$tmpl = ($locale != '') ? $locale.'/':'';
		
		$tmpl .= $template;
		
		// all was fine. return found template
		return array('subject'=>$array['templates'][$template]['subject'],
			'file'=>$tmpl);
	}
	/**
	 * Return template file for out template
	 * 
	 * @param string $template Out template name without a prefix
	 * 
	 * @return string Template file relative path
	 */
	protected function getOutTemplate($template = 'default') {
		// check for current locale
		if ($this->locale != '') {
			$templatefile = $this->templatespath.'/'.$this->locale.'/'.$this->outtemplateprefix.$template.'.htm';
			
			if (file_exists($templatefile)) {
				return $this->locale.'/'.$this->outtemplateprefix.$template;
			}
		}
		
		// if not found, check for default locale
		if ($this->deflocale != '') {
			$templatefile = $this->templatespath.'/'.$this->deflocale.'/'.$this->outtemplateprefix.$template.'.htm';
			
			if (file_exists($templatefile)) {
				return $this->deflocale.'/'.$this->outtemplateprefix.$template;
			}
		}
		
		// if not found then check in root
		$templatefile = $this->templatespath.'/'.$this->outtemplateprefix.$template.'.htm';
			
		if (file_exists($templatefile)) {
			return $this->outtemplateprefix.$template;
		}
		
		// out template not found
		return null;
	}
}
