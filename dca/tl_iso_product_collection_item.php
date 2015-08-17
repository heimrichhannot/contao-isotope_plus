<?php



/**
 * Add fields to tl_iso_product_collection_item
 */
$GLOBALS['TL_DCA']['tl_iso_product_collection_item']['fields'] += array(

    'has_bookings' => array
    (
        'sql'                   => "int(1) unsigned NOT NULL default '0'",
    ),
    'booking_start' => array
    (
        'sql'                   => "int(10) unsigned NOT NULL default '0'",
    ),
    'booking_stop' => array
    (
        'sql'                   => "int(10) unsigned NOT NULL default '0'",
    ),
);