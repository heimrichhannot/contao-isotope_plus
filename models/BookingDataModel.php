<?php
/**
 * Created by PhpStorm.
 * User: mschulz
 * Date: 16.04.15
 * Time: 10:45
 */

namespace HeimrichHannot\Isotope;


class BookingDataModel extends \Model
{

    protected static $strTable = 'tl_belegungsplan_data';

    public static function findByPidAndRange($intId, $intStart, $intStop, $arrOptions = array())
    {
        $t = static::$strTable;

        $arrColumns = array("$t.pid=? AND (($t.start>=$intStart AND $t.start<=$intStop) OR ($t.stop>=$intStart AND $t.stop<=$intStop) OR ($t.start<=$intStart AND $t.stop>=$intStop))");

        $arrValues = array($intId);

        return static::findBy($arrColumns, $arrValues, $arrOptions);
    }
}