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
use HeimrichHannot\Ajax\Response\ResponseData;
use HeimrichHannot\Ajax\Response\ResponseSuccess;
use HeimrichHannot\Haste\Dca\General;
use HeimrichHannot\Haste\Util\FormSubmission;
use Isotope\Backend;
use Isotope\Backend\Product\Category;
use Isotope\Backend\Product\Price;
use HeimrichHannot\IsotopePlus\ProductHelper;
use PHPExif\Reader\Reader;

class ProductModel extends \Model
{
	protected static $strTable = 'tl_iso_product';
}
