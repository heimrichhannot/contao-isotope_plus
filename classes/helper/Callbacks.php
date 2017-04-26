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

use Contao\ModuleModel;
use HeimrichHannot\Haste\Util\Files;
use Isotope\Model\CreatorProduct;

class Callbacks
{
    protected static $strProductTable        = 'tl_iso_product';
    protected static $strProductCreatorTable = 'tl_iso_product_creator';

    /**
     * option callback
     *
     * @return array
     */
    public function getProductTableFieldsAsOptions()
    {
        $arrOptions = array();

        \Controller::loadDataContainer(static::$strProductTable);

        $arrFields = $GLOBALS['TL_DCA'][static::$strProductTable]['fields'];

        if (!is_array($arrFields) || empty($arrFields))
        {
            return $arrOptions;
        }

        foreach ($arrFields as $strField => $arrData)
        {
            $arrOptions[static::$strProductTable . '.' . $strField] = $strField;
        }

        asort($arrOptions);

        return $arrOptions;
    }

    /**
     * option callback
     *
     * @return array
     */
    public function getProductCreatorFields()
    {
        return \HeimrichHannot\Haste\Dca\General::getFields(static::$strProductCreatorTable, false);
    }

    /**
     * option callback
     *
     * @return array
     */
    public function getDefaultValueFields()
    {
        return \HeimrichHannot\Haste\Dca\General::getFields(static::$strProductTable, false);
    }


    /**
     * upload path callback
     *
     * @return string
     */
    public function getUploadFolder()
    {
        $strDefaultPath = 'files/isotope-creator/uploads';

        if (FE_USER_LOGGED_IN)
        {
            if (($objUser = \FrontendUser::getInstance()) === null)
            {
                return $strDefaultPath;
            }

            $path = Files::getPathFromUuid($objUser->homeDir) . '/uploads/' . date("Y") . '/' . date("m");

            return $path;
        }

        return $strDefaultPath;
    }

    /**
     * onsubmit_callback
     *
     * @param \DataContainer $objDc
     */
    public function createProductsFromFrontendCreator(\DataContainer $objDc)
    {
        $arrImageUploads = deserialize($objDc->activeRecord->uploadedFiles, true);
        $arrProducts     = array();

        if (empty($arrImageUploads))
        {
            return;
        }

        foreach ($arrImageUploads as $strUuid)
        {
            $objProduct    = new CreatorProduct();
            $objProduct    = $objProduct->createImageProduct($strUuid, $objDc);
            $arrProducts[] = $objProduct->id;
        }

        \Database::getInstance()->execute(
            "UPDATE " . self::$strProductCreatorTable . " SET createdProducts='" . serialize($arrProducts) . "' WHERE id=" . intval($objDc->activeRecord->id)
        );
    }


    /**
     * post upload callback
     *
     * @param array $arrFiles
     */
    public function createProductsFromBackendUpload(array $arrFiles = array())
    {
        if (TL_MODE == 'BE')
        {
            $objModule     = null;
            $strParentUuid = '';

            if (empty($arrFiles))
            {
                return;
            }

            foreach ($arrFiles as $strPath)
            {
                if ($strParentUuid == '')
                {
                    if (($strParentUuid = \FilesModel::findByPath($strPath)->pid) === null
                        || ($objModule = ModuleModel::findById(\FilesModel::findByUuid($strParentUuid)->addProductCreation)) === null
                    )
                    {
                        return;
                    }
                }

                $objProduct = new CreatorProduct();
                $objProduct->createImageProduct($strPath, $objModule);
            }
        }
    }
}