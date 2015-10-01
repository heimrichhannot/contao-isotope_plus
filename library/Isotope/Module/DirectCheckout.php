<?php

namespace Isotope\Module;
use Isotope\Form\DirectCheckoutForm;

/**
 * Class DirectCheckout
 *
 * Copyright (c) 2015 Heimrich & Hannot GmbH
 *
 * @package isotope_plus
 * @author  Dennis Patzer <d.patzer@heimrich-hannot.de>
 * @license http://www.gnu.org/licences/lgpl-3.0.html LGPL
 */
class DirectCheckout extends Checkout
{

	protected $strTemplate = 'mod_iso_direct_checkout';

	public function generate()
	{
		if (TL_MODE == 'BE') {
			$objTemplate = new \BackendTemplate('be_wildcard');

			$objTemplate->wildcard = '### ISOTOPE ECOMMERCE: DIRECT CHECKOUT ###';

			$objTemplate->title = $this->headline;
			$objTemplate->id = $this->id;
			$objTemplate->link = $this->name;
			$objTemplate->href = 'contao/main.php?do=themes&amp;table=tl_module&amp;act=edit&amp;id=' . $this->id;

			return $objTemplate->parse();
		}

		return Module::generate();
	}

	protected function compile()
	{
		$this->objModel->formHybridDataContainer = 'tl_iso_product_collection';

		$objForm = new DirectCheckoutForm($this, $this->objModel);
		$this->Template->checkoutForm = $objForm->generate();
	}

}
