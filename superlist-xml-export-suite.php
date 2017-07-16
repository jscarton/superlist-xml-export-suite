<?php
use Roots\Sage\Extras; 
/**
 * Plugin Name: Superlist XML Export Suite
 * Plugin URI: http://www.superlist.com
 * Description: Allow export orders to XML and send them via ftp to SAP
 * Author: Juan Scarton
 * Author URI: http://thegeeks.rocks
 * Version: 1.0
 *
 * Copyright: (c) 2017 Juan Scarton. (jscarton@gmail.com)
 *
 * License: GNU General Public License v3.0
 * License URI: http://www.gnu.org/licenses/gpl-3.0.html
 *
 * @package   SUPERLIST-XML-Export-Suite
 * @author    Juan Scarton
 * @category  Custom
 * @copyright Copyright (c) 2017, Superlist.
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

define( 'SUPERLIST_XML_EXPORT_SUITE_VERSION', '1.0.0' );
define( 'SUPERLIST_XML_EXPORT_SUITE_ROOT', plugin_dir_path( __FILE__ ) );
define( 'SUPERLIST_XML_EXPORT_SUITE_ROOT_URL', plugin_dir_url( __FILE__ ) );
define( 'SUPERLIST_XML_EXPORT_SUITE_ROOT_FILE', __FILE__) ;
include_once ABSPATH . 'wp-admin/includes/plugin.php';
require_once SUPERLIST_XML_EXPORT_SUITE_ROOT."includes/functions.php";
require_once SUPERLIST_XML_EXPORT_SUITE_ROOT."includes/render.php";
require_once SUPERLIST_XML_EXPORT_SUITE_ROOT."includes/ftp.php";


function superlist_process_xml_export()
{
	//get orders to export
	$orders= superlist_get_orders_to_export();
	if (is_array($orders) && count($orders)>0)
	{
		$results=[];
		foreach ($orders as $order) {
			echo "Processing order: ",$order->ID,"\n";
			$order_data=superlist_get_order($order->ID);
			if (!is_null($order_data))
			{
				$order_xml=superlist_get_xml_for_order($order_data);	
				if ($order_xml!==FALSE)
				{			
					$result=superlist_export_xml_to_SAP($order_xml,$order->ID);
				}
				else
					$result=FALSE;
				$results[$order->ID]=$result;
			}
		}
		//inform about the XML
		superlist_after_xml_export_to_SAP($orders,$results);
	}
}

add_shortcode('superlist-xml-export-suite-send-to-sap','superlist_process_xml_export');