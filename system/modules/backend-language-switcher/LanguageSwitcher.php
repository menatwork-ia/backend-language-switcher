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

    static public $arrArticleCache = array();

    /* ---- Common functions ---- */
    /**
     * @param $varValue
     *
     * @return string returns an empty string.
     */
    public function returnNull($varValue)
    {
        return '';
    }

    /* ---- Pages ---- */
    /**
     * @param $dc
     */
    public function addPageTranslationLinks($dc)
    {
        if (\Input::get('act') == edit && LanguageRelations::getRelations(\Input::get('id')))
        {
            $GLOBALS['TL_CSS'][] = 'system/modules/backend-language-switcher/assets/edit.css';
            foreach ($GLOBALS['TL_DCA']['tl_page']['palettes'] as $key => $strPallett)
            {
                //skip '__selector__
                if ($key == '__selector__') continue;
                $GLOBALS['TL_DCA']['tl_page']['palettes'][$key] = '{belanguage_legend},page_links;' . $GLOBALS['TL_DCA']['tl_page']['palettes'][$key];
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
            $objTemplate = new \BackendTemplate('be_language_switcher_page');
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

    /* ---- Articles ---- */

    /**
     * @param $dc
     */
    public function addArticleTranslationLinks($dc)
    {
        if (\Input::get('act') == edit)
        {
            $objArticle = \ArticleModel::findByPk(\Input::get('id'));
            if (!LanguageRelations::getRelations($objArticle->pid)) return;

            $GLOBALS['TL_CSS'][] = 'system/modules/backend-language-switcher/assets/edit.css';
            foreach ($GLOBALS['TL_DCA']['tl_article']['palettes'] as $key => $strPallett)
            {
                //skip '__selector__
                if ($key == '__selector__') continue;
                $GLOBALS['TL_DCA']['tl_article']['palettes'][$key] = '{belanguage_legend},article_links;' . $GLOBALS['TL_DCA']['tl_article']['palettes'][$key];
            }
        }
    }

    /**
     * @param $dc
     */
    public function addArticleTranslationHeaderCss($dc)
    {
        if (\Input::get('act') != edit)
        {
            $GLOBALS['TL_CSS'][] = 'system/modules/backend-language-switcher/assets/header.css';
        }
    }

    /**
     * @return array
     */
    public function getTranslationArticles()
    {
        $arrReturn = array();

        $objArticle = \ArticleModel::findByPk(\Input::get('id'));

        $intArticlePosition = $this->getArticlePosition($objArticle);
        if ($intArticlePosition === null) return $arrReturn;

        //get the related pages
        $arrIds = LanguageRelations::getRelations($objArticle->pid);
        $arrIds[] = $objArticle->pid;

        foreach ($arrIds as $value)
        {
            //try to load article if not in cache
            if (!LanguageSwitcher::$arrArticleCache[$value])
                $this->collectArticlesFromPages(array($value));

            //skip this page if no matching article is found
            if (!LanguageSwitcher::$arrArticleCache[$value][$intArticlePosition])
                continue;

            $objTemplate = new \BackendTemplate('be_language_switcher_article');
            $arrArticle = LanguageSwitcher::$arrArticleCache[$value][$intArticlePosition]->row();
            $arrArticle['language'] = LanguageSwitcher::$arrArticleCache[$value]['rootIdLanguage'];
            $arrArticle['href'] = TL_PATH . '/contao/main.php?do=article&act=edit&id=' . $arrArticle['id'] . '&rt=' . REQUEST_TOKEN;
            if (\Input::get('id') == $arrArticle['id'])
                $arrArticle['isActive'] = true;
            $objTemplate->article = $arrArticle;
            $arrReturn[$arrArticle['id']] = $objTemplate->parse();
        }
        return $arrReturn;
    }

    /**
     * Compare current page language against the stored once.
     *
     * @param array $varValue
     * @return array
     */
    public function getLinkedArticles($varValue)
    {

        $objArticle = \ArticleModel::findByPk(\Input::get('id'));

        //get the related pages
        $arrPages = LanguageRelations::getRelations($objArticle->pid);

        //add the curent pid
        $arrPages[] = $objArticle->pid;

        //get the articles of the related pages
        $this->collectArticlesFromPages($arrPages);

        //find the position of the current article
        $intArticlePosition = $this->getArticlePosition($objArticle);

        //sort the pages
        usort($arrPages, function($a, $b){
            return (LanguageSwitcher::$arrArticleCache[$a]['rootIdSorting'] < LanguageSwitcher::$arrArticleCache[$b]['rootIdSorting']) ? -1 : 1;});

        //build return array
        foreach ($arrPages as $value)
        {
            $newValues[] = array(
                'linkedArticles'	 => LanguageSwitcher::$arrArticleCache[$value][$intArticlePosition]->id,
                'value'		 => '',
            );
        }
        return serialize($newValues);
    }

    public function addArticleTranslations($add, $dc)
    {

        if (\Input::get('do') != 'article') return $add;

        $arrArticles = array();
        //find the current article
        if (\Input::get('act') == 'paste' && (\Input::get('mode') == 'copy' || \Input::get('mode') == 'cut'))
        {
            $intCePid = \Database::getInstance()->prepare('Select pid FROM tl_content WHERE id = ?')->execute(\Input::get('id'))->pid;
            $objArticle = \ArticleModel::findByPk($intCePid);
        }
        else
        {
            $objArticle = \ArticleModel::findByPk(\Input::get('id'));
        }


        //get the related pages
        $arrIds = LanguageRelations::getRelations($objArticle->pid);
        //return if no related pages are found
        if (empty($arrIds)) return $add;

        $arrIds[] = $objArticle->pid;

        //get the articles of the related pages
        $this->collectArticlesFromPages($arrIds);
        $intArticlePosition = $this->getArticlePosition($objArticle);
        if ($intArticlePosition === null) return $add;

        //sort the pages
        usort($arrIds, function($a, $b){
            return (LanguageSwitcher::$arrArticleCache[$a]['rootIdSorting'] < LanguageSwitcher::$arrArticleCache[$b]['rootIdSorting']) ? -1 : 1;});

        foreach ($arrIds as $value)
        {
            if (!LanguageSwitcher::$arrArticleCache[$value])
                $this->collectArticlesFromPages(array($value));

            //skip this page if no matching article is found
            if (!LanguageSwitcher::$arrArticleCache[$value][$intArticlePosition])
                continue;

            $arrArticle = LanguageSwitcher::$arrArticleCache[$value][$intArticlePosition]->row();
            $arrArticle['language'] = LanguageSwitcher::$arrArticleCache[$value]['rootIdLanguage'];
            $arrArticle['href'] = TL_PATH . '/contao/main.php?do=article&table=tl_content&id=' . $arrArticle['id'] . '&rt=' . REQUEST_TOKEN;
            if (\Input::get('id') == $arrArticle['id'])
                $arrArticle['isActive'] = true;
            $arrArticles[] = $arrArticle;
        }

        //return if no realted articles could be found
        if (count($arrArticles) <= 1) return $add;
        $objTemplate = new \BackendTemplate('be_language_switcher_article_header');
        $objTemplate->arrArticles = $arrArticles;

        $add[$GLOBALS['TL_LANG']['tl_content']['belanguage_header']] = $objTemplate->parse();

        return ($add);
    }

    /**
     * @param $arrPages
     */
    protected function collectArticlesFromPages($arrPages)
    {
        foreach ($arrPages as $value)
        {
            //update cache if necessary
            if (!LanguageSwitcher::$arrArticleCache[$value])
            {
                $objArticle = \ArticleModel::findBy('pid', $value, array('order' => 'sorting ASC'));
                if ($objArticle === null) continue;
                //store pageDetails in cache
                LanguageSwitcher::$arrArticleCache[$value] = $objArticle->getModels();
                //add sorting value of the root page
                $objPage = \Database::getInstance()->prepare('SELECT * FROM tl_page WHERE id = (SELECT cca_rr_root FROM tl_page WHERE id = ?)')->execute($value);
                LanguageSwitcher::$arrArticleCache[$value]['rootIdSorting'] = $objPage->sorting;
                LanguageSwitcher::$arrArticleCache[$value]['rootIdLanguage'] = $objPage->language;
            }
        }
        return;
    }

    /**
     * @param $objArticle
     */
    protected function getArticlePosition($objArticle)
    {
        if (!LanguageSwitcher::$arrArticleCache[$objArticle->pid])
            $this->collectArticlesFromPages(array($objArticle->pid));

        $intArticlePosition = null;
        foreach (LanguageSwitcher::$arrArticleCache[$objArticle->pid] as $key => $article)
        {
            if ($article->id == $objArticle->id) $intArticlePosition = $key;
        }
        return $intArticlePosition;
    }
}