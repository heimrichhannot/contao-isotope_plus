<?php

/**
 * Created by PhpStorm.
 * User: mkunitzsch
 * Date: 26.09.17
 * Time: 09:03
 */
namespace HeimrichHannot\IsotopePlus;

use Contao\FilesModel;
use Haste\Util\Format;
use Isotope\Model\Product;
use Isotope\Model\ProductPrice;
use Isotope\Model\ProductType;

class Backend
{
	public function getProductCreatorLabel($row, $label, $dc, $args)
	{
		$objProduct = Product::findByPk($row['id']);
		
		foreach ($GLOBALS['TL_DCA'][$dc->table]['list']['label']['fields'] as $i => $field) {
			switch ($field) {
				
				// Add an image
				case 'images':
					$arrImages = deserialize($objProduct->images);
					$args[$i]  = '&nbsp;';
					
					if (is_array($arrImages) && !empty($arrImages)) {
						foreach ($arrImages as $image) {
							$strImage = 'isotope/' . strtolower(substr($image['src'], 0, 1)) . '/' . $image['src'];
							
							if (!is_file(TL_ROOT . '/' . $strImage)) {
								continue;
							}
							
							$size = @getimagesize(TL_ROOT . '/' . $strImage);
							
							$args[$i] = sprintf(
								'<a href="s%s" onclick="Backend.openModalImage({\'width\':%s,\'title\':\'%s\',\'url\':\'%s\'});return false"><img src="%s" alt="%s" align="left"></a>',
								TL_FILES_URL . $strImage,
								$size[0],
								str_replace("'", "\\'", $objProduct->name),
								TL_FILES_URL . $strImage,
								TL_ASSETS_URL . \Image::get($strImage, 50, 50, 'proportional'),
								$image['alt']
							);
							break;
						}
					}
					break;
				case 'uploadedFiles':
					if(is_array($uploadedFiles = unserialize($row['uploadedFiles'])))
					{
						$row['uploadedFiles'] = $uploadedFiles[0];
					}
					
					if(\Validator::isUuid($row['uploadedFiles']))
					{
						$image = FilesModel::findByUuid($row['uploadedFiles']);
						$size = @getimagesize(TL_ROOT . '/' . $image->path);
						
						$args[$i] = sprintf(
							'<a href="%s" onclick="Backend.openModalImage({\'width\':%s,\'title\':\'%s\',\'url\':\'%s\'});return false"><img src="%s" alt="%s" align="left"></a>',
							TL_FILES_URL . $image->path,
							$size[0],
							str_replace("'", "\\'", $objProduct->name),
							TL_FILES_URL . $image->path,
							TL_ASSETS_URL . \Image::get($image->path, 50, 50, 'proportional'),
							$image->alt
						);
					}
					break;
				case 'name':
					$args[$i] = $objProduct->name;
					/** @var \Isotope\Model\ProductType $objProductType */
					if ($row['pid'] == 0 && ($objProductType = ProductType::findByPk($row['type'])) !== null && $objProductType->hasVariants()) {
						// Add a variants link
						$args[$i] = sprintf('<a href="%s" title="%s">%s</a>', ampersand(\Environment::get('request')) . '&amp;id=' . $row['id'], specialchars($GLOBALS['TL_LANG'][$dc->table]['showVariants']), $args[$i]);
					}
					break;
				
				case 'price':
					$objPrice = ProductPrice::findPrimaryByProductId($row['id']);
					
					if (null !== $objPrice) {
						/** @var \Isotope\Model\TaxClass $objTax */
						$objTax = $objPrice->getRelated('tax_class');
						$strTax = (null === $objTax ? '' : ' (' . $objTax->getName() . ')');
						
						$args[$i] = $objPrice->getValueForTier(1) . $strTax;
					}
					break;
				
				case 'variantFields':
					$attributes = array();
					foreach ($GLOBALS['TL_DCA'][$dc->table]['list']['label']['variantFields'] as $variantField) {
						$attributes[] = '<strong>' . Format::dcaLabel($dc->table, $variantField) . ':</strong>&nbsp;' . Format::dcaValue($dc->table, $variantField, $objProduct->$variantField);
					}
					
					$args[$i] = ($args[$i] ? $args[$i] . '<br>' : '') . implode(', ', $attributes);
					break;
			}
		}
		
		return $args;
	}
}