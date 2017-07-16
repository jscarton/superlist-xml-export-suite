<?php

function superlist_get_orders_to_export()
{
	global $wpdb;
	$result = $wpdb->get_results( "SELECT p.ID FROM wp_posts p, wp_postmeta pm
		            WHERE p.post_type = 'shop_order'
		            AND p.post_status IN ('wc-processing')
		            and pm.post_id=p.ID
		            AND pm.meta_key ='_superlist_exported_order'
		            AND pm.meta_value='0';
		        ");
	return $result;
}

function superlist_get_order($order_id)
{
	WC();
	$order= new WC_Order($order_id);
	$data=null;
	if (is_object($order))
		$data=superlist_get_order_data($order);
	return $data;
}

function superlist_get_order_data( $order ) {

	//preformat complemento and referencia

	if (strpos($order->shipping_address_2, "Ref:")!==FALSE)
		list($shipping_complemento,$shipping_referencia)=explode("Ref:",$order->shipping_address_2);
	else
		list($shipping_complemento,$shipping_referencia)=[$order->shipping_address_2,""];

	if (strpos($order->billing_address_2, "Ref:")!==FALSE)
		list($billing_complemento,$billing_referencia)=explode("Ref:",$order->billing_address_2);
	else
		list($billing_complemento,$billing_referencia)=[$order->billing_address_2,""];

	$isAutoship='Não';
	if(get_post_meta($order->id,'_wc_autoship_order',true)!=null)
		$isAutoship='Sim';

	$customerIDV = -1;
	if(!empty(get_user_meta($order->get_user_id(),'_superlist_custom_sap_user_id',true)))
		$customerIDV = get_user_meta($order->get_user_id(),'_superlist_custom_sap_user_id',true);
	else
		$customerIDV = $order->get_user_id();

	$dateD = '';

	if(!empty(get_post_meta($order->id,'superlist_delivery_date',true)))
	{
		$dateD = get_post_meta($order->id,'superlist_delivery_date',true);
	}else{
		$dateD = Extras\wc_superlist_xml_get_delivery_date(date('d-m-Y',date_timestamp_get(date_create_from_format('Y-m-d H:i:s',$order->order_date))), get_post_meta($order->id,'_wc_autoship_order',true));
	}

	return array(
		'@attributes' => array( 'currency' => 'BRL', 'type' => 'Local', 'id' => $order->id ),
		'Time'        => date( 'Y-m-d H:i:s', strtotime( $order->order_date ) ),
		'NumericTime' => strtotime( $order->order_date ),
		'Origin'      => 'Local',
		'customerID'	=> $customerIDV,
		'customerName'=>$order->billing_first_name . ' ' . $order->billing_last_name,
		'AddressInfo' => array(
			0 => array(
				'@attributes' => array( 'type' => 'shipping' ),				
				'Name'        => array(
					'First' => $order->shipping_first_name,
					'Last'  => $order->shipping_last_name,
					'Full'  => $order->shipping_first_name . ' ' . $order->shipping_last_name,
				),
				'Address1'    => $order->shipping_address_1,
				'Address2'    => $shipping_complemento,
				'Number'	  => $order->shipping_number,
				'Neighborhood'=> $order->shipping_neighborhood,
				'City'        => $order->shipping_city,
				'State'       => $order->shipping_state,
				'Country'     => $order->shipping_country,
				'Zip'         => $order->shipping_postcode,
				'Phone'       => empty($order->billing_phone)?$order->billing_cellphone:$order->billing_phone,
				'CellPhone'   => $order->billing_cellphone,
				'Email'       => $order->billing_email,
				'reference'	  => $shipping_referencia
			),
			1 => array(
				'@attributes' => array( 'type' => 'billing' ),
				'Name'        => array(
					'First' 	=> $order->billing_first_name,
					'Last'  	=> $order->billing_last_name,
					'Full'  	=> $order->billing_first_name . ' ' . $order->billing_last_name,
					'CPF'		=> ($order->billing_persontype==1)?$order->billing_cpf:"",
					'CNPJ'		=> ($order->billing_persontype==2)?$order->billing_cnpj:"",
					'IE'		=> $order->billing_ie != 'ISENTO'?$order->billing_ie:'',
					'Company'	=> $order->billing_company,
				),
				'Address1'    => $order->billing_address_1,
				'Address2'    => $billing_complemento,
				'Number'	  => $order->billing_number,
				'Neighborhood'=> $order->billing_neighborhood,
				'City'        => $order->billing_city,
				'State'       => $order->billing_state,
				'Country'     => $order->billing_country,
				'Zip'         => $order->billing_postcode,
				'Phone'       => empty($order->billing_phone)?$order->billing_cellphone:$order->billing_phone,
				'CellPhone'   => $order->billing_cellphone,
				'Email'       => $order->billing_email,
				'reference'	  => $billing_referencia
			),
		),
		'Shipping'    => $order->get_shipping_method(),
		'DeliveryPreference'    => $order->customer_note,
		'DeliveryDate' => $dateD, 
		'isAutoship'=>$isAutoship,
		'Items'       => wc_superlist_xml_get_line_items( $order ),
		'Total'       => array(
			'Line' => array(
				0 => array(
					'@attributes' => array( 'type' => 'Coupon', 'name' => 'Discount' ),
					'@value'      => $order->get_total_discount()
				),
				1 => array(
					'@attributes' => array( 'type' => 'Shipping', 'name' => 'Shipping' ),
					'@value'      => $order->get_total_shipping()
				),
				2 => array(
					'@attributes' => array( 'type' => 'Tax', 'name' => 'Tax' ),
					'@value'      => $order->get_total_tax()
				),
				3 => array(
					'@attributes' => array( 'type' => 'Total', 'name' => 'Total' ),
					'@value'      => $order->get_total()
				)
			),
		)		
	);
}
/**
 * Adjust the individual line item format
 *
 * @since 1.0
 * @param object $order \WC_Order instance
 * @return array
 */
function superlist_xml_get_line_items( $order ) {
	foreach( $order->get_items() as $item_id => $item_data ) {
		$product = $order->get_product_from_item( $item_data );
		$heart = woocommerce_get_order_item_meta( $item_id, '_superlist_choosen_product', true );
		
		if ($heart=='yes')
			$allow_changes="no";
		else
			$allow_changes="yes";

		if(intval($item_data['qty'])>0 )
		$items[] = array(
			'Id'				=> $product->get_sku(),
			'Quantity'			=> $item_data['qty'],
			'Unit-Price'		=> number_format($order->get_item_subtotal( $item_data ), 2,'.',''),
			'Line-Price'		=> number_format($item_data['line_total'], 2,'.',''),
			'Description'		=> $product->get_title(),
			'Recurrency' 		=> woocommerce_get_order_item_meta( $item_id, '_wc_autoship_frequency', true ),
			'AllowChanges' 		=> $allow_changes=='yes'?'YES':'NO',
			'Url'				=> set_url_scheme( get_permalink( $product->id ), 'https' ),
			'Taxable'			=> ( $product->is_taxable() ) ? 'YES' : 'NO',
			'EAN'				=> get_post_meta($product->get_id(),'_superlist_code_ean',true)
		);
	}

	return $items;
}

/**
* estimate the delivery date
* @since 1.0
* @param object $date
* @return date
*/
function superlist_after_xml_export_to_SAP($orders,$results)
{	
	$content="Orders exported to SAP\n";
	foreach ($orders as $order) {		
		$result=json_encode($results[$order->ID]);
		$content.="ID:{$order->ID}\n";
		$content.="Method : FTP\n";
		$content.="timestamp:".date("d-m-Y H:i:s");
		$content.="\ntransfered:".$result;
		$content.="\n(times are in UTC)\n";
	}

	wp_mail( "juan.scarton@superlist.com", "[Superlist] Export From Woo to SAP Notification", $content, [], []);
}

function save_exported_meta($order_id) {
    update_post_meta($order_id, '_superlist_exported_order', '0');
      
}

add_action('woocommerce_order_status_processing', 'save_exported_meta');

