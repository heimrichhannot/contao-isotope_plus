<?php
/**
 * Contao Open Source CMS
 *
 * Copyright (c) 2015 Heimrich & Hannot GmbH
 *
 * @package isotope_plus
 * @author  Oliver Janke <o.janke@heimrich-hannot.de>
 * @license http://www.gnu.org/licences/lgpl-3.0.html LGPL
 */

namespace HeimrichHannot\IsotopePlus;

use HeimrichHannot\Haste\Dca\General;

class CreatorProductHelper
{
    const IMAGE_RESIZE_MAX_HEIGHT = 1600;
    const IMAGE_RESIZE_MAX_WIDTH = 1920;

    public static function prepareExifDataForSave($strExifTag, $arrExifData)
    {
        switch ($strExifTag)
        {
            case \PHPExif\Exif::CREATION_DATE :
                $strValue = static::prepareDateTimes($arrExifData);
                break;
            case \PHPExif\Exif::KEYWORDS :
                $strValue = static::prepareKeywords($arrExifData);
                break;
            default :
                $strValue = null;
        }

        return $strValue;
    }

    protected static function prepareDateTimes($arrExifData)
    {
        $objCreationDate = $arrExifData[\PHPExif\Exif::CREATION_DATE];

        if ($objCreationDate === null)
        {
            return null;
        }

        return $objCreationDate->getTimestamp();
    }

    protected static function prepareKeywords($arrExifData)
    {
        if(is_array($arrExifData[\PHPExif\Exif::KEYWORDS]))
        {
            $strKeywords = implode(', ', $arrExifData[\PHPExif\Exif::KEYWORDS]);
        }

        if (empty($strKeywords))
        {
            return null;
        }

        return '<p>' . $strKeywords . '</p>';
    }

    /**
     * @param $objFile
     * @param $arrExifData
     *
     * @return string serialized multidimensional array
     */
    public static function importImageToIsotopeMediamanager($objFile, $arrExifData)
    {
        if (self::createResizedImage($objFile, $arrExifData) === null)
        {
            return null;
        }

        $arrFile = array(
            'src'  => strtolower($objFile->name),
            'alt'  => '',
            'link' => '',
            'desc' => '',
        );

        return serialize(array($arrFile));
    }


    /**
     * Calculate new dimensions and create image
     *
     * @param $objFile
     * @param $arrExifData
     *
     * @return null|string
     */
    protected static function createResizedImage($objFile, $arrExifData)
    {
        $strTarget = 'isotope/' . strtolower(substr($objFile->name, 0, 1)) . '/' . $objFile->name;

        if ($arrExifData['width'] >= $arrExifData['height'] && $arrExifData['width'] > self::IMAGE_RESIZE_MAX_WIDTH)
        {
            // landscape
            $intHeight = round(self::IMAGE_RESIZE_MAX_WIDTH * ($arrExifData['height']/$arrExifData['width']), 0);
            $intWidth = self::IMAGE_RESIZE_MAX_WIDTH;
        }
        elseif ($arrExifData['height'] > $arrExifData['width'] && $arrExifData['height'] > self::IMAGE_RESIZE_MAX_HEIGHT)
        {
            // portrait
            $intHeight = self::IMAGE_RESIZE_MAX_HEIGHT;
            $intWidth = round(self::IMAGE_RESIZE_MAX_HEIGHT * ($arrExifData['width']/$arrExifData['height']), 0);
        }
        else {
            // no resize needed
            $intHeight = $arrExifData['height'];
            $intWidth = $arrExifData['width'];
        }

        // create a resized copy of the uploaded image
        return \Image::get($objFile->path, $intWidth, $intHeight, 'center_center', $strTarget, true);
    }

    public static function generateAliasFromTitleOrFilename($arrExif, $objFile)
    {
        if ($arrExif['title'])
        {
            $strAlias = $arrExif['title'];
        }
        else
        {
            $strAlias = str_replace('.' . $objFile->extension, '', $objFile->name);
        }

        return General::generateAlias('', $objFile->id, 'tl_iso_product', $strAlias);
    }
}