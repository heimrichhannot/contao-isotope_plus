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

use HeimrichHannot\Haste\Dca\General;
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
	 * @param $objFile
	 * @param $arrExifData
	 *
	 * @return string serialized multidimensional array
	 */
	public static function importImageToIsotopeMediamanager($objFile, $arrExifData, $imgSize = null)
	{
		if (($strSrc = self::createResizedImage($objFile, $arrExifData, $imgSize)) === null) {
			return null;
		}
		
		$arrFile = [
			'src'       => $strSrc,
			'alt'       => '',
			'link'      => '',
			'desc'      => '',
			'translate' => 'none'
		];
		
		return serialize([$arrFile]);
	}
	
	/**
	 * Calculate new dimensions and create image
	 *
	 * @param $objFile
	 * @param $arrExifData
	 *
	 * @return null|string
	 */
	
	protected static function createResizedImage($objFile, $arrExifData, $imgSize = null)
	{
		list($target, $size, $name) = ProductHelper::prepareImageStore($objFile, $arrExifData, $imgSize);
		
		$newImage = \Image::get($objFile->path, $size['size'][0], $size['size'][1], $size['size'][2], $target);
		
		$objNewFile = \Dbafs::addResource(urldecode($newImage));
		
		
		return $name;
	}
	
	
	protected function prepareImageStore($objFile, $arrExifData, $imgSize)
	{
		$name = ltrim($objFile->name, '_');
		$path = $objFile->path;
		
		if (!empty($imgSize)) {
			$name = str_replace('.' . $objFile->extension, '_' . $imgSize['size'][0] . '.' . $objFile->extension, $name);
			$path = str_replace($objFile->name, $name, $objFile->path);
		} else {
			$imgSize = ['size' => [$arrExifData['width'], $arrExifData['height'], 'center-center']];
		}
		
		return [$path, $imgSize, $name];
	}
	
	/**
	 * create download element for each set size of isotope product image
	 *
	 * @param $id   int
	 * @param $file object
	 * @param $size array
	 */
	public static function createDownloadItem($id, $file, $size)
	{
		$name = str_replace('.' . $file->extension, ProductHelper::getReplacer($file, $size), ltrim($file->name, '_'));
		$path = str_replace($file->name, $name, $file->path);
		
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
	 * @param $arrExif
	 * @param $objFile
	 *
	 * @return string
	 */
	public static function generateAliasFromTitleOrFilename($arrExif, $objFile)
	{
		if ($arrExif['title']) {
			$strAlias = $arrExif['title'];
		} else {
			$strAlias = str_replace('.' . $objFile->extension, '', $objFile->name);
		}
		
		return General::generateAlias('', $objFile->id, 'tl_iso_product', $strAlias);
	}
	
	/**
	 * @param $src string
	 *
	 * @return int|string    filesize in readable form
	 */
	public function getFileSize($src)
	{
		$bytes = filesize($src);
		
		if ($bytes >= 1073741824) {
			$bytes = number_format($bytes / 1073741824, 2) . ' GB';
		} elseif ($bytes >= 1048576) {
			$bytes = number_format($bytes / 1048576, 2) . ' MB';
		} elseif ($bytes >= 1024) {
			$bytes = number_format($bytes / 1024, 2) . ' KB';
		} elseif ($bytes > 1) {
			$bytes = $bytes . ' bytes';
		} elseif ($bytes == 1) {
			$bytes = $bytes . ' byte';
		} else {
			$bytes = '0 bytes';
		}
		
		return $bytes;
	}
	
	public static function getFileTitle($uuid)
	{
		$file = Download::findBy('singleSRC', $uuid);
		
		return $file->title;
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
		if ($module->iso_editableCategories) {
			$categories = [];
			
			foreach (deserialize($module->iso_editableCategories) as $cat) {
				$categories[$cat] = ProductType::findByPk($cat)->name;
			}
			
			return $categories;
		}
	}
}