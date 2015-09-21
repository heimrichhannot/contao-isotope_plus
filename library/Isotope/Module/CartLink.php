<?php

namespace HeimrichHannot\IsotopePlus\Module;

use Isotope\Module\Module;

/**
 * Class CartLink
 *
 * Copyright (c) 2015 Heimrich & Hannot GmbH
 *
 * @package isotope_plus
 * @author  Dennis Patzer <d.patzer@heimrich-hannot.de>
 * @license http://www.gnu.org/licences/lgpl-3.0.html LGPL
 */
class CartLink extends Module
{

	/**
	 * Template
	 *
	 * @var string
	 */
	protected $strTemplate = 'mod_iso_cart_link';

	/**
	 * Display a wildcard in the back end
	 *
	 * @return string
	 */
	public function generate()
	{
		if (TL_MODE == 'BE') {
			$objTemplate = new \BackendTemplate('be_wildcard');

			$objTemplate->wildcard = '### ISOTOPE ECOMMERCE: CART LINK ###';

			$objTemplate->title = $this->headline;
			$objTemplate->id = $this->id;
			$objTemplate->link = $this->name;
			$objTemplate->href = 'contao/main.php?do=themes&amp;table=tl_module&amp;act=edit&amp;id=' . $this->id;

			return $objTemplate->parse();
		}

		return parent::generate();
	}

	protected function compile()
	{
		global $objPage;

		$this->Template->href = \Controller::generateFrontendUrl(\PageModel::findByPk($this->jumpTo)->row());
		if ($objPage->id == $this->jumpTo) {
			$this->Template->active = true;
		}
	}

}
