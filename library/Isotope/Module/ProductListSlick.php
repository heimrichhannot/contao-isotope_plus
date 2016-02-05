<?php

namespace Isotope\Module;

use HeimrichHannot\Slick\SlickConfig;
use HeimrichHannot\Slick\SlickConfigModel;

/**
 * Class ProductListPlus
 *
 * Copyright (c) 2015 Heimrich & Hannot GmbH
 * @package isotope_plus
 * @author Dennis Patzer <digitales@heimrich-hannot.de>
 * @license http://www.gnu.org/licences/lgpl-3.0.html LGPL
 */

class ProductListSlick extends ProductListPlus
{

	protected $blnCacheProducts = false;

	public function generate()
	{
		if (TL_MODE == 'BE') {
			$objTemplate = new \BackendTemplate('be_wildcard');

			$objTemplate->wildcard = '### ISOTOPE ECOMMERCE: PRODUCT LIST SLICK ###';

			$objTemplate->title = $this->headline;
			$objTemplate->id    = $this->id;
			$objTemplate->link  = $this->name;
			$objTemplate->href  = 'contao/main.php?do=themes&amp;table=tl_module&amp;act=edit&amp;id=' . $this->id;

			return $objTemplate->parse();
		}

		parent::generate();

		$objConfig = SlickConfigModel::findByPk($this->slickConfig);

		if($objConfig !== null)
		{
			SlickConfig::createConfigJs($objConfig);
			$this->Template->class .= ' ' . SlickConfig::getCssClassFromModel($objConfig) . ' slick';
		}

		return $this->Template->parse();
	}

	protected function compile()
	{
		parent::compile();
	}

}
