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
use Isotope\Model\FrontendProduct;
use Isotope\Model\ProductModel;

class Callbacks
{
    protected static $strProductTable        = 'tl_iso_product';

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
    public function getDefaultValueFields()
    {
        return \HeimrichHannot\Haste\Dca\General::getFields(static::$strProductTable, false);
    }
	
	/**
	 * upload path callback
	 *
	 * @return string
	 */
	public function getUploadFolder(\DataContainer $dc)
	{
		$uploadFolder = \FilesModel::findByUuid($dc->objModule->iso_uploadFolder)->path;
		
		if (FE_USER_LOGGED_IN)
		{
			if (($objUser = \FrontendUser::getInstance()) === null)
			{
				return $uploadFolder;
			}
			
			return $uploadFolder . '/' . $objUser->username;
		}
		
		return $uploadFolder;
	}
}