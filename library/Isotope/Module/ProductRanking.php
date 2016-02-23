<?php

namespace Isotope\Module;

use HeimrichHannot\Haste\Util\Url;

class ProductRanking extends Module
{
	protected $strTemplate = 'mod_iso_product_ranking';

	public function generate()
	{
		if (TL_MODE == 'BE')
		{
			$objTemplate = new \BackendTemplate('be_wildcard');
			$objTemplate->wildcard = '### ISOTOPE ECOMMERCE: PRODUCT RANKING ###';
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
		// items
		$arrProducts = array();
		$arrRanking = array();
			
		$strQuery = '
			SELECT
			p.name,
			t.name AS type,
			SUM(quantity) as count,
			p.id,
			p.setQuantity,
			MONTH(FROM_UNIXTIME(o.locked)) as month
			FROM tl_iso_product p
			INNER JOIN tl_iso_product_collection_item oi ON oi.product_id = p.id
			LEFT JOIN tl_iso_product_collection o ON o.id = oi.pid
			INNER JOIN tl_iso_producttype t ON p.type = t.id
			WHERE DATE(FROM_UNIXTIME(o.locked)) BETWEEN DATE_SUB(CURDATE(), INTERVAL 2 MONTH) AND DATE(CURDATE())
			AND o.type = "order" AND o.locked > 0
			GROUP BY p.id, month ORDER BY month DESC';

		$objProducts = $this->Database->prepare($strQuery)->execute();

		if ($objProducts->numRows > 0)
		{
			while($objProducts->next())
			{
				$arrProducts[$objProducts->id] = $objProducts->row();
				$arrRanking[$objProducts->id][$objProducts->month] = $objProducts->count;
			}
		}

		$this->Template->products = $arrProducts;
		$this->Template->ranking = $arrRanking;
		$this->Template->months = array(
			date('n', strtotime("-2 month")),
			date('n', strtotime("-1 month")), 
			date('n', time())
		);
	}
}