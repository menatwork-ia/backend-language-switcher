<?php

/**
 * Contao Open Source CMS
 *
 * @copyright  MEN AT WORK 2014
 * @package    backend-language-switcher
 * @license    GNU/LGPL
 * @filesource
 */

$GLOBALS['TL_DCA']['tl_content']['list']['sorting']['header_callback'] = array('BackendLanguageSwitcher\LanguageSwitcher', 'addArticleTranslations');
$GLOBALS['TL_DCA']['tl_content']['config']['onload_callback'][] = array('BackendLanguageSwitcher\LanguageSwitcher', 'addArticleTranslationHeaderCss');