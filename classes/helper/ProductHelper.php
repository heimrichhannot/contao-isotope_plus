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

use Isotope\Model\Download;
use Isotope\Model\ProductType;

class ProductHelper
{
	public static function prepareExifDataForSave($strExifTag, $arrExifData)
	{
		switch ($strExifTag) {
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
		
		if ($objCreationDate === null) {
			return null;
		}
		
		return $objCreationDate->getTimestamp();
	}
	
	protected static function prepareKeywords($arrExifData)
	{
		if (is_array($arrExifData[\PHPExif\Exif::KEYWORDS])) {
			$strKeywords = implode(', ', $arrExifData[\PHPExif\Exif::KEYWORDS]);
		}
		
		if (empty($strKeywords)) {
			return null;
		}
		
		return '<p>' . $strKeywords . '</p>';
	}
	
	
	/**
	 * create download element for each set size of isotope product image
	 *
	 * @param $id   int
	 * @param $file object
	 * @param $size array
	 */
	public static function createDownloadItem($id, $file, $size, $pdf = false)
	{
		if (!$pdf) {
			$name = str_replace('.' . $file->extension, ProductHelper::getReplacer($file, $size), ltrim($file->name, '_'));
			$path = $downloadPath = str_replace($file->name, $name, $file->path);
		} else {
			$path = $downloadPath = $pdf->path;
		}
		
		if (!file_exists($path)) {
			$downloadPath = \Image::get($file->path, $size['size'][0], $size['size'][1], $size['size'][2], $path);
		}
		
		// TODO check if file exists
		
		if (($downloadFile = \FilesModel::findByPath($path)) === null) {
			$downloadFile = \Dbafs::addResource(urldecode($downloadPath));
		}
		
		// create Isotope download
		$objDownload            = new Download();
		$objDownload->pid       = $id;
		$objDownload->tstamp    = time();
		$objDownload->title     = $size['name'];
		$objDownload->singleSRC = $downloadFile->uuid;
		$objDownload->published = 1;
		
		$objDownload->save();
	}
	
	/**
	 * @param $file object
	 * @param $size array
	 *
	 * @return string
	 */
	protected static function getReplacer($file, $size)
	{
		$suffix = '';
		if ($size['name'] != $GLOBALS['TL_LANG']['MSC']['originalSize']) {
			$suffix = '_' . $size['size'][0];
		}
		
		return $suffix . '.' . $file->extension;
		
	}
	
	/**
	 * return all product groups that are defined as editable in module
	 *
	 * @param $module object
	 *
	 * @return array
	 */
	public function getEditableCategories($module)
	{
		if (!$module->iso_editableCategories) {
			return [];
		}
		
		$categories = [];
		
		foreach (deserialize($module->iso_editableCategories, true) as $cat) {
			$categories[$cat] = ProductType::findByPk($cat)->name;
		}
		
		asort($categories);
		
		return $categories;
	}
	
	public static function addPdfViewerToTemplate($template, $item, $module)
	{
		if (!$item->isPdfProduct)
			return;
		
		if(($downloads = Download::findBy('pid', $item->id)) === null)
			return;
			
		
		$downloads = $downloads->fetchAll();
		$viewer    = [];
		
		foreach ($downloads as $download) {
			$pdfViewer       = new \FrontendTemplate('iso_pdf_viewer');
			$pdfViewer->href = \FilesModel::findByUuid($download['singleSRC'])->path;
			$pdfViewer->id   = $download['id'];
			
			$viewer[] = $pdfViewer->parse();
		}
		
		$pdfViewerWrapper         = new \FrontendTemplate('iso_pdf_viewer_wrapper');
		$pdfViewerWrapper->items  = $downloads;
		$pdfViewerWrapper->panels = $viewer;
		
		
		$template->pdfViewer = $pdfViewerWrapper->parse();
	}
}