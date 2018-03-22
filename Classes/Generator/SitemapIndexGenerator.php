<?php
/***************************************************************
 *  Copyright notice
 *
 *  (c) 2015 Tim Riemenschneider <t.riemenschneider@detco.de>
 *  All rights reserved
 *
 *  This script is part of the TYPO3 project. The TYPO3 project is
 *  free software; you can redistribute it and/or modify
 *  it under the terms of the GNU General Public License as published by
 *  the Free Software Foundation; either version 2 of the License, or
 *  (at your option) any later version.
 *
 *  The GNU General Public License can be found at
 *  http://www.gnu.org/copyleft/gpl.html.
 *
 *  This script is distributed in the hope that it will be useful,
 *  but WITHOUT ANY WARRANTY; without even the implied warranty of
 *  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 *  GNU General Public License for more details.
 *
 *  This copyright notice MUST APPEAR in all copies of the script!
 ***************************************************************/

namespace DmitryDulepov\DdGooglesitemap\Generator;

use DmitryDulepov\DdGooglesitemap\Renderers\AbstractSitemapRenderer;
use TYPO3\CMS\Core\Utility\GeneralUtility;

/**
 * This class produces sitemap for pages
 *
 * @author        Tim Riemenschneider <t.riemenschneider@detco.de>
 * @package       TYPO3
 * @subpackage    tx_ddgooglesitemap
 */
class SitemapIndexGenerator extends AbstractSitemapGenerator
{

    /**
     * A sitemap renderer
     *
     * @var    AbstractSitemapRenderer
     */
    protected $renderer;

    /**
     * Initializes the instance of this class. This constructir sets starting
     * point for the sitemap to the current page id
     */
    public function __construct()
    {
        $this->renderer = GeneralUtility::makeInstance('DmitryDulepov\\DdGooglesitemap\\Renderers\\SitemapIndexRenderer');

        $link = \TYPO3\CMS\Core\Utility\ExtensionManagementUtility::siteRelPath('dd_googlesitemap') . 'Resources/Public/sitemap.xsl';
        $link = GeneralUtility::locationHeaderUrl($link);
        $this->renderer->additionalHeader = '<?xml-stylesheet type="text/xsl" href="' . $link . '"?>';
    }

    /**
     * Generates sitemap for pages (<url> entries in the sitemap)
     *
     * @return    void
     */
    protected function generateSitemapContent()
    {
        if (is_array($GLOBALS['TYPO3_CONF_VARS']['EXTCONF']['dd_googlesitemap']['sitemap'])) {
            foreach ($GLOBALS['TYPO3_CONF_VARS']['EXTCONF']['dd_googlesitemap']['sitemap'] as $sitemaptype => $config) {
                if (isset($GLOBALS['TSFE']->tmpl->setup['tx_ddgooglesitemap.']['indexoptions.'][ $sitemaptype ]) &&
                    $GLOBALS['TSFE']->tmpl->setup['tx_ddgooglesitemap.']['indexoptions.'][ $sitemaptype ] == 'skip'
                ) {
                    continue;
                }
                $options = $GLOBALS['TSFE']->tmpl->setup['tx_ddgooglesitemap.']['indexoptions.'][ $sitemaptype . '.' ];

                if (is_array($options)) {
                    $first = reset($options);
                    if (is_array($first)) {
                        foreach ($options as $entry) {
                            $this->renderSingleIndexEntry($sitemaptype, $entry);
                        }
                        continue;
                    }
                }

                $this->renderSingleIndexEntry($sitemaptype, $GLOBALS['TSFE']->tmpl->setup['tx_ddgooglesitemap.']['indexoptions.'][ $sitemaptype . '.' ]);
            }
        }
    }

    /**
     * @param string $sitemaptype
     * @param array  $conf
     */
    protected function renderSingleIndexEntry($sitemaptype, $conf)
    {
        $params            = $_GET;
        $params['sitemap'] = $sitemaptype;

        if (is_array($conf)) {
            $params = array_merge($params, $conf);
        }
        $pstr = array();
        foreach ($params as $key => $value) {
            $pstr[] = implode('=', array($key, $value));
        }
        echo $this->renderer->renderEntry(GeneralUtility::locationHeaderUrl($_SERVER['SCRIPT_NAME'] . '?' . implode('&amp;', $pstr)), '');
    }
}
