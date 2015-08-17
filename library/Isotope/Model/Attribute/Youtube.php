<?php

/**
 * Isotope eCommerce for Contao Open Source CMS
 *
 * Copyright (C) 2009-2014 terminal42 gmbh & Isotope eCommerce Workgroup
 *
 * @package    Isotope
 * @link       http://isotopeecommerce.org
 * @license    http://opensource.org/licenses/lgpl-3.0.html
 */

namespace Isotope\Model\Attribute;

use Isotope\Interfaces\IsotopeAttribute;
use Isotope\Interfaces\IsotopeProduct;
use Isotope\Model\Attribute;


/**
 * Attribute to provide an audio/video player in the product details
 *
 * @copyright  Isotope eCommerce Workgroup 2009-2014
 * @author     Christoph Wiechert <cw@4wardmedia.de>
 */
class Youtube extends Attribute implements IsotopeAttribute
{
    /**
     * Return class name for the backend widget or false if none should be available
     * @return    string
     */
    public function getBackendWidget()
    {
        return $GLOBALS['BE_FFL']['text'];
    }


    /**
     * Generate youtube attribute
     *
     * @param \Isotope\Interfaces\IsotopeProduct $objProduct
     * @param array $arrOptions
     * @return string
     */
    public function generate(IsotopeProduct $objProduct, array $arrOptions = array())
    {
        $strPoster = null;
        $arrAttribtues = deserialize($objProduct->{$this->field_name}, true);

        // Return if there is no video
        if (empty($arrAttribtues) || !is_array($arrAttribtues)) {
            return '';
        }

        $objContentModel = new \ContentModel();
        $objContentModel->type = 'youtube';
        $objContentModel->cssID = serialize(array('', $this->field_name));

        // Vorschaubild TODO
        /*
        $arrImages = deserialize($objProduct->images, true);
        if( is_array($arrImages) && !empty($arrImages[0])) {
            $objContentModel->addPreviewImage = true;
            $objContentModel->addPlayButton = true;

            echo '<pre>';
            $pth = array("files/markenportal/images/filme/".$arrImages[0]['src']);
            $objFiles = \FilesModel::findMultipleByPaths( $pth);


            var_dump($objFiles->first());
            //$objProduct->images
            echo '</pre>';

            $objContentModel->posterSRC = $objFiles->path;
        } //*/



        //$objContentModel->playerSRC = serialize($arrFiles);
        // $objContentModel->posterSRC = $strPoster;
        $objContentModel->youtube = $arrAttribtues[0];

        // TODO use default values
        if ($arrOptions['autoplay']) {
            $objContentModel->autoplay = '1';
        }

        if ($arrOptions['width'] || $arrOptions['height']) {
            $objContentModel->playerSize = serialize(array($arrOptions['width'], $arrOptions['height']));
        }

        $objElement = new \ContentResponsiveYouTubeVideo($objContentModel);

        return $objElement->generate();
    }
}
