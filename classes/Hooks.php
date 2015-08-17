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


class Hooks extends \Isotope\Isotope
{
    private $intStart = 0;
    private $intStop = 0;


	public function generateProductHook(&$objTemplate, $objProduct)
	{
        DownloadHelper::addDownloadsFromProductDownloadsToTemplate($objTemplate);
        BookingHelper::addBookingsFromProductToTemplate($objTemplate, $objProduct);
	}


    /**
     * Validierung im Warenkorb
     */
    public function addProductToCollectionHook($objProduct, $intQuantity)
    {
        if($objProduct->bookings) {
            if(!$this->isBookingValid($objProduct)) {
                return 0;
            } else {
                unset($_SESSION['ISO_ERROR']);
            }
        }

		if( !$this->isOrderSizeValid($objProduct, $intQuantity) )
		{
			return 0;
		} else {
			unset($_SESSION['ISO_ERROR']);
		}

        return $intQuantity;
    }

	/**
	 * Belegung im Warenkorb speichern
	 *
	 * @param $objItem
	 * @param $intQuantity
	 * @param $objProductCollection
	 * @return bool
	 */
	public function postAddProductToCollectionHook($objItem, $intQuantity, &$objProductCollection)
	{
		$objProduct = \Isotope\Model\Product\Standard::findPublishedByPk($objItem->product_id);

        if(!$objProduct->bookings) return;
        if(!$this->isBookingValid($objProduct)) return false;

		// Add booking to item
        $objItem->has_bookings = 1;
		$objItem->booking_start = $this->intStart;
		$objItem->booking_stop = $this->intStop;
		$objItem->save();
	}

    /**
     * Belegung für Produkt
     *
     * Am Ende der Bestellung wird der Belegungsplan gespeichert (neuer Eintrag). Im Fehlerfall, falls das Produkt
     * bereits vergriffen ist, erfolgt eine Umleitung auf die aktuelle Übersicht inkl. Fehlermeldung.
     */
    public function preCheckoutHook( $objOrder)
    {
        $arrItems = $objOrder->getItems();
        $arrBookings = array();
		$arrOrders = array();

        foreach($arrItems as $objItem)
        {
            $objProduct = $objItem->getProduct();

            // get Item with bookings
            if($objProduct->bookings) {
                // validate TODO message
                // TODO passende Fehlermeldung (z.B. via ?reason=meldung
                if(!BookingHelper::isProductBookingPossible( $objProduct->bookings,$objItem->booking_start,$objItem->booking_stop)) {
                    // das gewünschte Produkt ist inzwischen bereits belegt
                    return false;
                }
                $arrBookings[] = $objItem;
            }

			if($objProduct->stock) {
				if( !static::isOrderSizeValid($objProduct, $objItem->quantity) ) return false;
				$arrOrders[] = $objItem;
			}
        }

        // save booking data to tl_belegungsplan_data & include name in comment
        $objMember = $objOrder->getRelated('member');
        foreach($arrBookings as $objItem)
        {
            $booking = new BookingDataModel();
            $booking->pid = $objItem->getProduct()->bookings;
            $booking->start = $objItem->booking_start;
            $booking->stop = $objItem->booking_stop;
            $booking->comment = "Bestellung #". $objOrder->id ." von ". $objMember->firstname ." ". $objMember->lastname;
            $booking->save();
        }

		// save new stock and if stock empty block shipping
		foreach($arrOrders as $objItem)
		{
			$objProduct = $objItem->getProduct();
			$objProduct->stock -= $objItem->quantity;
			if($objProduct->stock <= 0) {
				$objProduct->shipping_exempt = '1';
			}
			$objProduct->save();
		}

		return true;
    }

	/**
	 * Check for updated quantity
	 *
	 * @param $objItem
	 * @param $arrSet
	 * @param $objCollection
	 * @return $arrSet
	 */
	public function updateItemInCollectionHook($objItem, $arrSet, $objCollection)
	{
		$objProduct = \Isotope\Model\Product::findPublishedByPk($objItem->product_id);
		if( !static::isOrderSizeValid($objProduct, $arrSet['quantity']) ) {
			\Controller::reload();
		}
		return $arrSet;
	}

    protected function isBookingValid($objProduct)
    {
        if($objProduct === null) return false;

        $this->intStart = ($objProduct->bookings && \Input::post('booking_start_requested')) ? strtotime(\Input::post('booking_start_requested')) : time();
        $this->intStop = ($objProduct->bookings && \Input::post('booking_stop_requested')) ? strtotime(\Input::post('booking_stop_requested')) : $this->intStart;

		if( !$this->isDateValid($this->intStart) || !$this->isDateValid($this->intStop) ) {
			$_SESSION['ISO_ERROR'][] = "Kein gültiges Datumsformat";
			return false;
		}

		if($this->intStart > $this->intStop) {
			$_SESSION['ISO_ERROR'][] = "Das Enddatum liegt vor dem Startdatum";
			return false;
		}

		$blnBooking = BookingHelper::isProductBookingPossible($objProduct->bookings, $this->intStart, $this->intStop);
		if(!$blnBooking) {
			$_SESSION['ISO_ERROR'][] = "Das gewünschte Produkt ist für diesen Zeitraum bereits belegt.";
			return false;
		}

        return $blnBooking;
    }

	public static function isOrderSizeValid($objProduct, $intQuantity)
	{
		if($objProduct->stock <= 0 || $objProduct->stock == '' || $objProduct->stock == null || $intQuantity > $objProduct->stock) {
			$_SESSION['ISO_ERROR'][] = 'Ihre Bestellmenge liegt über der noch vorhandenen Menge.';
		} elseif($intQuantity <= $objProduct->max_order_size || $objProduct->max_order_size == '') {
			return true;
		} else {
			$_SESSION['ISO_ERROR'][] = 'Ihre Bestellmenge liegt über der maximal zulässigen Menge.';
		}
		return false;
	}

	public static function isDateValid($intDate)
	{
		if($intDate <= PHP_INT_MAX  && $intDate >= ~PHP_INT_MAX)
		{
			$strDate = date("d.m.Y", $intDate);

			if( strpos ($strDate, '.') )
			{
				$values = explode('.', $strDate);
				$day = $values[0];
				$month = $values[1];
				$year  = $values[2];

				if(checkdate($month, $day ,$year))
				{
					return true;
				}
			}
		}
		return false;
	}
}