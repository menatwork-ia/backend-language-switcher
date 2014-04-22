<?php

/**
 * Contao Open Source CMS
 *
 * @copyright  MEN AT WORK 2014
 * @package    backend-language-switcher
 * @license    GNU/LGPL
 * @filesource
 */

$GLOBALS['TL_DCA']['tl_page']['config']['onload_callback'][] = array('BackendLanguageSwitcher\LanguageSwitcher', 'addPageTranslationLinks');

/**
 * Fields
 */
$GLOBALS['TL_DCA']['tl_page']['fields']['page_links'] = array
(
    'label'                          => &$GLOBALS['TL_LANG']['tl_page']['page_links'],
    'inputType'                      => 'multiColumnWizard',
    'load_callback'                  => array
    (
        array('BackendLanguageSwitcher\LanguageSwitcher', 'getLinkedPages')
    ),
    'save_callback'                  => array
    (
        array('BackendLanguageSwitcher\LanguageSwitcher', 'returnNull')
    ),
    'eval' => array
    (
        'style'                      => 'width:100%;',
        'columnFields' => array
        (
            'linkedPages' => array
            (
                'label'              => null,
                'exclude'            => true,
                'inputType'          => 'justtextoption',
                'options_callback'   => array('BackendLanguageSwitcher\LanguageSwitcher', 'getTranslationPages'),
                'eval' => array
                (
                    'hideHead'       => true,
                    'hideBody'       => false,
                    'doNotSaveEmpty' => true,
                )
            ),
        ),
        'buttons' => array
        (
            'copy'                   => false,
            'delete'                 => false,
            'up'                     => false,
            'down'                   => false
        ),
        'doNotSaveEmpty'             => true,
        'tl_class'                   => 'be-language-switch'
    )
);
