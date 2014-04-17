<?php

/**
 * Contao Open Source CMS
 *
 * @copyright  MEN AT WORK 2014
 * @package    backend-language-switcher
 * @license    GNU/LGPL
 * @filesource
 */

$GLOBALS['TL_DCA']['tl_article']['config']['onload_callback'][] = array('BackendLanguageSwitcher\LanguageSwitcher', 'addArticleTranslationLinks');

/**
 * Fields
 */
$GLOBALS['TL_DCA']['tl_article']['fields']['article_links'] = array
(
    'label'                          => &$GLOBALS['TL_LANG']['tl_article']['article_links'],
    'inputType'                      => 'multiColumnWizard',
    'load_callback'                  => array
    (
        array('BackendLanguageSwitcher\LanguageSwitcher', 'getLinkedArticles')
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
            'linkedArticles' => array
            (
                'label'              => null,
                'exclude'            => true,
                'inputType'          => 'justtextoption',
                'options_callback'   => array('BackendLanguageSwitcher\LanguageSwitcher', 'getTranslationArticles'),
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
