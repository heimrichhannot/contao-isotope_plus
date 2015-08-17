<?php
/**
 * Contao Open Source CMS
 * 
 * Copyright (c) 2015 Heimrich & Hannot GmbH
 * @package isotope_plus
 * @author Rico Kaltofen <r.kaltofen@heimrich-hannot.de>
 * @license http://www.gnu.org/licences/lgpl-3.0.html LGPL
 */

namespace HeimrichHannot\Isotope;


class BookingHelper extends \Isotope\Isotope
{
    public static function addBookingsFromProductToTemplate($objTemplate, $objProduct)
	{
        $objModule = \ModuleModel::findByPk($objTemplate->module_id);
        if($objModule === null) return '';

        // Add calendar
        if ($objProduct->bookings > 0)
        {
            $objModule->bp_plan = $objProduct->bookings;
            $objModuleBooking = new \ModuleBelegungsplan($objModule);

            $objTemplate->bookings = $objModuleBooking->generate();
        }
	}

    public static function isProductBookingPossible($bookingId, $intStart, $intEnd)
    {
        $intStart = intval($intStart);
        $intEnd = intval($intEnd);

        $objBookingData = BookingDataModel::findByPidAndRange($bookingId, $intStart, $intEnd);

        if($objBookingData === null) return true;

        return false;
    }

}