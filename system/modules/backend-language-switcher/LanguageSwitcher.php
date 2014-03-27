<?php

/**
 * Contao Open Source CMS
 *
 * @copyright  MEN AT WORK 2014
 * @package    backend-language-switcher
 * @license    GNU/LGPL 
 * @filesource
 */

namespace BackendLanguageSwitcher;

use ContaoCommunityAlliance\Contao\LanguageRelations\LanguageRelations;
use PageModel;

/**
 * Class LanguageSwitcher
 * @package BackendLanguageSwitcher
 */
class LanguageSwitcher extends \Backend
{

    private $strTemplate = 'be_language_switcher';

    static public $arrPageCache = array();

    public function returnNull($varValue)
    {
        return '';
    }

    /**
     * @param $dc
     */
    public function addPageTranslationLinks($dc)
    {
        if (\Input::get('act') == edit)
        {
            $GLOBALS['TL_CSS'][] = 'system/modules/backend-language-switcher/assets/style.css';
            foreach ($GLOBALS['TL_DCA']['tl_page']['palettes'] as $key => $strPallett)
            {
                //skip '__selector__
                if ($key == '__selector__') continue;
                $GLOBALS['TL_DCA']['tl_page']['palettes'][$key] = '{belanguage_legend},language_links;' . $GLOBALS['TL_DCA']['tl_page']['palettes'][$key];
            }
        }
    }

    /**
     * @return array
     */
    public function getTranslationPages()
    {
        $arrReturn = array();
        $arrIds = LanguageRelations::getRelations(\Input::get('id'));
        $arrIds[] = \Input::get('id');
        foreach ($arrIds as $value)
        {
            $objTemplate = new \BackendTemplate('be_language_switcher');
            $arrPage = \PageModel::findWithDetails($value)->row();
            $arrPage['href'] = TL_PATH . '/contao/main.php?do=page&act=edit&id=' . $value . '&rt=' . REQUEST_TOKEN;
            if (\Input::get('id') == $value)
                $arrPage['isActive'] = true;
            $objTemplate->page = $arrPage;
            $arrReturn[$value] = $objTemplate->parse();
        }
        return $arrReturn;
    }

    /**
     * Compare current page language against the stored once.
     *
     * @param array $varValue
     * @return array
     */
    public function getLinkedPages($varValue)
    {
        //get the related languaged
        $arrPages = LanguageRelations::getRelations(\Input::get('id'));
        //add the curent id

        $arrPages[] = \Input::get('id');
        //get page details and sorting info
        $this->collectPageDetails($arrPages);

        usort($arrPages, function($a, $b){
            return (LanguageSwitcher::$arrPageCache[$a]['rootIdSorting'] < LanguageSwitcher::$arrPageCache[$b]['rootIdSorting']) ? -1 : 1;});

        //build return array
        foreach ($arrPages as $value)
        {
            $newValues[] = array(
                'linkedPages'	 => $value,
                'value'		 => '',
            );
        }

        return serialize($newValues);
    }


    /**
     * @param $arrPages
     */
    protected function collectPageDetails($arrPages)
    {
        foreach ($arrPages as $value)
        {
            //update cache if necessary
            if (!LanguageSwitcher::$arrPageCache[$value])
            {
                //store pageDetails in cache
                LanguageSwitcher::$arrPageCache[$value] = \PageModel::findWithDetails($value)->row();
                //add sorting value of the root page
                LanguageSwitcher::$arrPageCache[$value]['rootIdSorting'] = \Database::getInstance()->prepare('SELECT sorting FROM tl_page WHERE id = ?')->execute(LanguageSwitcher::$arrPageCache[$value]['rootId'])->sorting;
            }
        }
        return;
    }
}