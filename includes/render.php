<?php 
function superlist_get_xml_for_order($order_data)
{
	ob_start(); 
echo "<?xml version=\"1.0\" encoding=\"UTF-8\" standalone=\"no\"?>";
?>
<OrderList>
	<Order currency="BRL" type="Local" id="<?= $order_data['@attributes']['id']?>">
		<Time><?= $order_data['Time']?></Time>
		<NumericTime><?= $order_data['NumericTime']?></NumericTime>
		<Origin>Local</Origin>
		<customerID><?= $order_data['customerID']?></customerID>
		<customerName><?= $order_data['customerName']?></customerName>
		<AddressInfo type="shipping">
			<Name>
				<First><?= $order_data['AddressInfo'][0]['Name']['First']?></First>
				<Last><?= $order_data['AddressInfo'][0]['Name']['Last']?></Last>
				<Full><?= $order_data['AddressInfo'][0]['Name']['Full']?></Full>
			</Name>
			<Address1><?= $order_data['AddressInfo'][0]['Address1']?></Address1>
			<Address2><?= $order_data['AddressInfo'][0]['Address2']?></Address2>
			<Number><?= $order_data['AddressInfo'][0]['Number']?></Number>
			<Neighborhood><?= $order_data['AddressInfo'][0]['Neighborhood']?></Neighborhood>
			<City><?= $order_data['AddressInfo'][0]['City']?></City>
			<State><?= $order_data['AddressInfo'][0]['State']?></State>
			<Country><?= $order_data['AddressInfo'][0]['Country']?></Country>
			<Zip><?= $order_data['AddressInfo'][0]['Zip']?></Zip>
			<Phone><?= $order_data['AddressInfo'][0]['Phone']?></Phone>
			<CellPhone><?= $order_data['AddressInfo'][0]['CellPhone']?></CellPhone>
			<Email><?= $order_data['AddressInfo'][0]['Email']?></Email>
			<reference><?= $order_data['AddressInfo'][0]['reference']?></reference>
		</AddressInfo>
		<AddressInfo type="billing">
			<Name>
				<First><?= $order_data['AddressInfo'][1]['Name']['First']?></First>
				<Last><?= $order_data['AddressInfo'][1]['Name']['Last']?></Last>
				<Full><?= $order_data['AddressInfo'][1]['Name']['Full']?></Full>
				<CPF><?= $order_data['AddressInfo'][1]['Name']['CPF']?></CPF>
				<CNPJ><?= $order_data['AddressInfo'][1]['Name']['CNPJ']?></CNPJ>
				<IE><?= $order_data['AddressInfo'][1]['Name']['IE']?></IE>
				<Company><?= $order_data['AddressInfo'][1]['Name']['Company']?></Company>
			</Name>
			<Address1><?= $order_data['AddressInfo'][1]['Address1']?></Address1>
			<Address2><?= $order_data['AddressInfo'][1]['Address2']?></Address2>
			<Number><?= $order_data['AddressInfo'][1]['Number']?></Number>
			<Neighborhood><?= $order_data['AddressInfo'][1]['Neighborhood']?></Neighborhood>
			<City><?= $order_data['AddressInfo'][1]['City']?></City>
			<State><?= $order_data['AddressInfo'][1]['State']?></State>
			<Country><?= $order_data['AddressInfo'][1]['Country']?></Country>
			<Zip><?= $order_data['AddressInfo'][1]['Zip']?></Zip>
			<Phone><?= $order_data['AddressInfo'][1]['Phone']?></Phone>
			<CellPhone><?= $order_data['AddressInfo'][1]['CellPhone']?></CellPhone>
			<Email><?= $order_data['AddressInfo'][1]['Email']?></Email>
			<reference><?= $order_data['AddressInfo'][1]['reference']?></reference>
		</AddressInfo>
		<Shipping><?= $order_data['Shipping']?></Shipping>
		<DeliveryPreference><?= $order_data['DeliveryPreference']?></DeliveryPreference>
		<DeliveryDate><?= $order_data['DeliveryDate']?></DeliveryDate>
		<isAutoship><?= $order_data['isAutoship']?></isAutoship>
<?php	foreach ($order_data['Items'] as $item) { ?>
		<Items>
			<Id><?= $item['Id']?></Id>
			<Quantity><?= $item['Quantity']?></Quantity>
			<Unit-Price><?= $item['Unit-Price']?></Unit-Price>
			<Line-Price><?= $item['Line-Price']?></Line-Price>
			<Description><![CDATA[<?= $item['Description']?>]]></Description>
			<Recurrency><?= $item['Recurrency']?></Recurrency>
			<AllowChanges><?= $item['AllowChanges']?></AllowChanges>
			<Url><?= $item['Url']?></Url>
			<Taxable><?= $item['Taxable']?></Taxable>
			<EAN><?= $item['EAN']?></EAN>
		</Items>
<?php } ?>
		<Total>
			<Line type="Coupon" name="Discount"><?= $order_data['Total']['Line'][0]['@value'] ?></Line>
			<Line type="Shipping" name="Shipping"><?= $order_data['Total']['Line'][1]['@value'] ?></Line>
			<Line type="Tax" name="Tax"><?= $order_data['Total']['Line'][2]['@value'] ?></Line>
			<Line type="Total" name="Total"><?= $order_data['Total']['Line'][3]['@value'] ?></Line>
		</Total>
	</Order>
</OrderList>
<?php
	try{
	$xml_string=ob_get_clean();
	//now validates by creating a simplexml object
	$xml_object=simplexml_load_string($xml_string);
	return $xml_object;
	}
	catch (Exception $e)
	{
		var_dump($e->get_message);
		return false;
	}
}