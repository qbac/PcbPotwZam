<?php
$test_array = array (
    'bla' => 'blub1',
    'foo' => 'bar',
    'another_array' => array (
      'stack' => 'overflow',
    ),
  );

$data['OrderResponse-Header']['OrderResponseType'] = '231';
$data['OrderResponse-Header']['OrderResponseNumber'] = '342/C/2020';
$data['OrderResponse-Header']['OrderResponseDate'] = '1999-09-09';
$data['OrderResponse-Header']['OrderNumber'] = 'OrderNumber_1';
$data['OrderResponse-Header']['OrderDate'] = '1999-09-09';
$data['OrderResponse-Header']['DocumentFunctionCode'] = 'O';

$data['OrderResponse-Parties']['Buyer']['ILN'] = '1111111111111';
$data['OrderResponse-Parties']['Buyer']['PartyName'] = 'PartyName_1';
$data['OrderResponse-Parties']['Buyer']['StreetAndNumber'] = 'StreetAndNumber_1';
$data['OrderResponse-Parties']['Buyer']['CityName'] = 'CityName_1';
$data['OrderResponse-Parties']['Buyer']['PostCode'] = '99-999';
$data['OrderResponse-Parties']['Buyer']['Country'] = 'PL';

$data['OrderResponse-Parties']['Seller']['ILN'] = '1111111111111';
$data['OrderResponse-Parties']['Seller']['PartyName'] = 'PartyName_2';
$data['OrderResponse-Parties']['Seller']['StreetAndNumber'] = 'StreetAndNumber_2';
$data['OrderResponse-Parties']['Seller']['CityName'] = 'CityName_2';
$data['OrderResponse-Parties']['Seller']['PostCode'] = '99-999';
$data['OrderResponse-Parties']['Seller']['Country'] = 'PL';

$data['OrderResponse-Parties']['DeliveryPoint']= ' ';

$data['OrderResponse-Lines']['Line']['Line-Item']['LineNumber'] = '1';
$data['OrderResponse-Lines']['Line']['Line-Item']['OrderedQuantity'] = '3';
$data['OrderResponse-Lines']['Line']['Line-Item']['QuantityToBeDelivered'] = '3';
$data['OrderResponse-Lines']['Line']['Line-Item']['UnitOfMeasure'] = 'PCE';
$data['OrderResponse-Lines']['Line']['Line-Item']['OrderedUnitNetPrice'] = '23.45';
$data['OrderResponse-Lines']['Line']['Line-Item']['ExpectedDeliveryDate'] = '1999-09-09';

function array_to_xml( $data, &$xml_data ) {
    foreach( $data as $key => $value ) {
        if( is_array($value) ) {
            if( is_numeric($key) ){
                $key = 'item'.$key; //dealing with <0/>..<n/> issues
            }
            $subnode = $xml_data->addChild($key);
            array_to_xml($value, $subnode);
        } else {
            $xml_data->addChild("$key",htmlspecialchars("$value"));
        }
     }
}

// initializing or creating array
//$data = array('total_stud' => 500);

// creating object of SimpleXMLElement
$xml_data = new SimpleXMLElement('<?xml version="1.0"?><Document-OrderResponse></Document-OrderResponse>');

// function call to convert array to xml
array_to_xml($data,$xml_data);

//saving generated xml file; 
$result = $xml_data->asXML('name.xml');
print_r ($xml_data->asXML());
?>