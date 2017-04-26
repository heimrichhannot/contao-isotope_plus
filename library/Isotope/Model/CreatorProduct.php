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

namespace Isotope\Model;

use Contao\DC_Table;
use Contao\FilesModel;
use Isotope\Backend;
use Isotope\Backend\Product\Category;
use Isotope\Backend\Product\Price;
use HeimrichHannot\IsotopePlus\CreatorProductHelper;
use PHPExif\Reader\Reader;

class CreatorProduct extends \Model
{
    // exif_read_data only reads jpeg and tiff
    const ALLOWED_IMAGE_EXTENSIONS = array('jpg', 'jpeg', 'tiff', 'png');

    protected static $strTable = 'tl_iso_product';

    protected $arrCreatorData = array();

    protected $arrExifData = array();

    protected $objFile;

    protected function create($arrData = array())
    {
        if (!empty($arrData))
        {
            $this->setRow($arrData);
        }

        return $this->save();
    }


    /**
     * @param string|FilesModel           $varFile path to file | uuid of file | file model
     * @param \DataContainer|\ModuleModel $varData
     *
     * @return CreatorProduct $this
     */
    public function createImageProduct($varFile, $varData)
    {
        if (!$this->checkFile($varFile))
        {
            return null;
        }

        if ($varData instanceof \DataContainer)
        {
            $objModule = $varData->objModule;
        }
        else
        {
            $objModule = $varData;
        }

        $this->getImageData();

        $this->setBasicData();

        $this->setDataFromModule($objModule);

        $this->setDataFromExifData($objModule);

        $this->setAdditionalData($objModule);

        $this->create($this->arrCreatorData);

        $this->additionalTasks($objModule);

        return $this;
    }

    /**
     * Check varFile input for allowed exif extensions
     *
     * @param $varFile
     *
     * @return bool
     */
    protected function checkFile($varFile)
    {
        if ($varFile != '' && $varFile !== null)
        {
            // check if input is a FilesModel, an uuid or a path
            if (!($varFile instanceof \FilesModel))
            {
                if (\Validator::isUuid($varFile))
                {
                    if (($objFile = FilesModel::findByUuid($varFile)) === null)
                    {
                        return false;
                    }
                }
                else
                {
                    if (($objFile = FilesModel::findByPath($varFile)) === null)
                    {
                        return false;
                    }
                }
            }
            else
            {
                $objFile = $varFile;
            }

            $arrPathInfo = pathinfo(TL_ROOT . '/' . $objFile->path);
            if (in_array($arrPathInfo['extension'], self::ALLOWED_IMAGE_EXTENSIONS))
            {
                $this->objFile = $objFile;

                return true;
            }
        }

        return false;
    }


    /**
     * Get exif/iptc data from image
     */
    protected function getImageData()
    {
        $objExifReader = Reader::factory(Reader::TYPE_NATIVE);
        $objExifData   = $objExifReader->read(TL_ROOT . '/' . $this->objFile->path);

        if ($objExifData === null || $objExifData === false)
        {
            return;
        }

        $this->arrExifData = $objExifData->getData();
    }

    protected function setBasicData()
    {
        $this->arrCreatorData['dateAdded'] = time();
        $this->arrCreatorData['tstamp']    = time();
    }

    protected function setDataFromModule($objModule)
    {
        $this->arrCreatorData['type']       = $objModule->iso_productType;
        $this->arrCreatorData['orderPages'] = $objModule->iso_productCategory;

        $this->formatOrderPages();

        if ($objModule->formHybridAddDefaultValues)
        {
            $this->setDataFromDefaultValues($objModule->formHybridDefaultValues);
        }
    }

    /**
     * orderPages page ids are needed as string
     */
    protected function formatOrderPages()
    {
        $arrOrderPages = deserialize($this->arrCreatorData['orderPages'], true);

        foreach ($arrOrderPages as $key => $value)
        {
            $arrOrderPages[$key] = (string) $value;
        }

        $this->arrCreatorData['orderPages'] = serialize($arrOrderPages);
    }

    protected function setDataFromDefaultValues($arrDefaultValues)
    {
        $arrDcaFields     = \HeimrichHannot\Haste\Dca\General::getFields(static::$strTable, false);
        $arrDefaultValues = deserialize($arrDefaultValues, true);

        foreach ($arrDefaultValues as $arrValue)
        {
            if (in_array($arrValue['field'], $arrDcaFields))
            {
                $this->arrCreatorData[$arrValue['field']] = $arrValue['value'];
            }
        }
    }

    /**
     * Map the exif tags to database fields
     *
     * @param $objModule
     */
    protected function setDataFromExifData($objModule)
    {
        $arrMappings = deserialize($objModule->iso_exifMapping, true);

        foreach ($arrMappings as $arrMapping)
        {
            $arrTableFields = explode('.', $arrMapping['tableField']);
            $strValue       = '';

            if (!empty($arrTableFields) && ($strTableField = array_pop($arrTableFields)) != '')
            {
                switch ($arrMapping['exifTag'])
                {
                    case \PHPExif\Exif::CREATION_DATE :
                        $strValue = CreatorProductHelper::prepareExifDataForSave(\PHPExif\Exif::CREATION_DATE, $this->arrExifData);
                        break;
                    case \PHPExif\Exif::KEYWORDS :
                        $strValue = CreatorProductHelper::prepareExifDataForSave(\PHPExif\Exif::KEYWORDS, $this->arrExifData);
                        break;
                    case 'custom' :
                        $strValue = $this->arrExifData[$arrMapping['customTag']];
                        break;

                    case \PHPExif\Exif::APERTURE :
                    case \PHPExif\Exif::AUTHOR :
                    case \PHPExif\Exif::CAMERA :
                    case \PHPExif\Exif::CAPTION :
                    case \PHPExif\Exif::COLORSPACE :
                    case \PHPExif\Exif::COPYRIGHT :
                    case \PHPExif\Exif::CREDIT :
                    case \PHPExif\Exif::EXPOSURE :
                    case \PHPExif\Exif::FILESIZE :
                    case \PHPExif\Exif::FOCAL_LENGTH :
                    case \PHPExif\Exif::FOCAL_DISTANCE :
                    case \PHPExif\Exif::HEADLINE :
                    case \PHPExif\Exif::HEIGHT :
                    case \PHPExif\Exif::HORIZONTAL_RESOLUTION :
                    case \PHPExif\Exif::ISO :
                    case \PHPExif\Exif::JOB_TITLE :
                    case \PHPExif\Exif::MIMETYPE :
                    case \PHPExif\Exif::ORIENTATION :
                    case \PHPExif\Exif::SOFTWARE :
                    case \PHPExif\Exif::SOURCE :
                    case \PHPExif\Exif::TITLE :
                    case \PHPExif\Exif::VERTICAL_RESOLUTION :
                    case \PHPExif\Exif::WIDTH :
                    case \PHPExif\Exif::GPS :
                        $strValue = $this->arrExifData[$arrMapping['exifTag']];
                        break;

                    default :
                        break;
                }

                // Hook : handle exif tags
                if (isset($GLOBALS['TL_HOOKS']['creatorProduct']['handleExifTags']) && is_array($GLOBALS['TL_HOOKS']['creatorProduct']['handleExifTags']))
                {
                    foreach ($GLOBALS['TL_HOOKS']['creatorProduct']['handleExifTags'] as $arrCallback)
                    {
                        $objClass = \Controller::importStatic($arrCallback[0]);
                        $strValue = $objClass->{$arrCallback[1]}($arrMapping['exifTag'], $arrMapping, $strValue);
                    }
                }

                if ($strValue !== null)
                {
                    $this->arrCreatorData[$strTableField] = $strValue;
                }
            }
        }
    }

    protected function setAdditionalData($objModule)
    {
        $this->arrCreatorData['alias'] = CreatorProductHelper::generateAliasFromTitleOrFilename($this->arrExifData, $this->objFile);
        $this->arrCreatorData['sku']   = $this->arrCreatorData['alias'];

        // create image in isotope folder and save data in product
        if (($strImage = CreatorProductHelper::importImageToIsotopeMediamanager($this->objFile, $this->arrExifData)) !== null)
        {
            $this->arrCreatorData['images'] = $strImage;
        }

        // Hook : modify the product data
        if (isset($GLOBALS['TL_HOOKS']['creatorProduct']['modifyData']) && is_array($GLOBALS['TL_HOOKS']['creatorProduct']['modifyData']))
        {
            foreach ($GLOBALS['TL_HOOKS']['creatorProduct']['modifyData'] as $arrCallback)
            {
                $objClass = \Controller::importStatic($arrCallback[0]);
                $objClass->{$arrCallback[1]}($objModule, $this->arrCreatorData, $this);
            }
        }
    }

    protected function additionalTasks($objModule)
    {
        // save exif data in tl_files
        if (!empty($this->arrExifData))
        {
            $this->objFile->exif = $this->arrExifData;
            $this->objFile->save();
        }

        // create Isotope download
        $objDownload            = new Download();
        $objDownload->pid       = $this->id;
        $objDownload->tstamp    = time();
        $objDownload->title     = $this->title ?: $this->objFile->name;
        $objDownload->singleSRC = $this->objFile->uuid;
        $objDownload->published = 1;
        $objDownload->save();

        // add product categories to isotope category table
        $objDc        = new DC_Table('tl_iso_product', $objModule);
        $objDc->intId = $this->id;
        Category::save(deserialize($this->orderPages), $objDc);

        // add price to product and isotope price table
        Price::save(array('value' => '0.00', 'unit' => 0), $objDc);

        // clear product cache
        Backend::truncateProductCache();
    }
}
