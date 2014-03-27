<?php

/**
 * Contao Open Source CMS
 *
 * @copyright  MEN AT WORK 2014
 * @package    backend-language-switcher
 * @license    GNU/LGPL 
 * @filesource
 */

/**
 * Register the namespaces
 */
ClassLoader::addNamespaces(array
(
	'BackendLanguageSwitcher',
));


/**
 * Register the classes
 */
ClassLoader::addClasses(array
(
	'BackendLanguageSwitcher\LanguageSwitcher' => 'system/modules/backend-language-switcher/LanguageSwitcher.php',
));

/**
 * Register the templates
 */
TemplateLoader::addFiles(array
(
    'be_language_switcher'    => 'system/modules/backend-language-switcher/templates',
));