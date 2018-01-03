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


class ProductModel extends \Model
{
	protected static $strTable = 'tl_iso_product';
	
	public static function getCopyrights()
	{
		if(null !== ($copyrights = \Database::getInstance()->prepare("SELECT * FROM tl_iso_product WHERE copyright IS NOT NULL AND copyright != ''")->execute()))
		{
			return array_unique($copyrights->fetchEach('copyright'));
		}
		
		return [];
	}
}
