<?php

$test_array = array (
    'bla' => 'blub1',
    'foo' => 'bar',
    'another_array' => array (
      'stack' => 'overflow',
    ),
  );

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

$data['OrderResponse-Header']['OrderResponseType'] = '231';
$data['OrderResponse-Header']['OrderResponseNumber'] = '342/C/2020';
$data['OrderResponse-Header']['OrderResponseDate'] = '1999-09-09';
$data['OrderResponse-Header']['OrderNumber'] = 'OrderNumber_1';
$data['OrderResponse-Header']['OrderDate'] = '1999-09-09';
$data['OrderResponse-Header']['DocumentFunctionCode'] = 'O';

$data['OrderResponse-Parties']['Buyer']['ILN'] = '1111111111111';
$data['OrderResponse-Parties']['Buyer']['PartyName'] = 'PartyName_1';
$data['OrderResponse-Parties']['Buyer']['StreetAndNumber'] = 'StreetAndNumber_1';
$data['OrderResponse-Parties']['Buyer']['CityName'] = 'Inowrocław_1';
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
$data['OrderResponse-Lines']['Line']['Line-Item']['SupplierItemCode'] = 'EC3234534';
$data['OrderResponse-Lines']['Line']['Line-Item']['OrderedQuantity'] = '3';
$data['OrderResponse-Lines']['Line']['Line-Item']['QuantityToBeDelivered'] = '3';
$data['OrderResponse-Lines']['Line']['Line-Item']['UnitOfMeasure'] = 'PCE';
$data['OrderResponse-Lines']['Line']['Line-Item']['OrderedUnitNetPrice'] = '23.45';
$data['OrderResponse-Lines']['Line']['Line-Item']['ExpectedDeliveryDate'] = '1999-09-09';

// initializing or creating array
//$data = array('total_stud' => 500);

// creating object of SimpleXMLElement

$xml_data = new SimpleXMLElement('<?xml version="1.0" encoding="UTF-8"?><Document-OrderResponse></Document-OrderResponse>');


// function call to convert array to xml
array_to_xml($data,$xml_data);

$data2['OrderResponse-Lines']['Line']['Line-Item']['LineNumber'] = '2';
$data2['OrderResponse-Lines']['Line']['Line-Item']['SupplierItemCode'] = 'EC3234534';
$data2['OrderResponse-Lines']['Line']['Line-Item']['OrderedQuantity'] = '3';
$data2['OrderResponse-Lines']['Line']['Line-Item']['QuantityToBeDelivered'] = '3';
$data2['OrderResponse-Lines']['Line']['Line-Item']['UnitOfMeasure'] = 'PCE';
$data2['OrderResponse-Lines']['Line']['Line-Item']['OrderedUnitNetPrice'] = '23.45';
$data2['OrderResponse-Lines']['Line']['Line-Item']['ExpectedDeliveryDate'] = '1999-09-09';

array_to_xml($data2,$xml_data);
//saving generated xml file; 
$result = $xml_data->asXML('name.xml');
print_r ($xml_data->asXML());

$users_array = array(
    "total_users" => 3,
    "users" => array(
        array(
            "id" => 1,
            "name" => "Smith",
            "address" => array(
                "country" => "United Kingdom",
                "city" => "London",
                "zip" => 56789,
            )
        ),
        array(
            "id" => 2,
            "name" => "John",
            "address" => array(
                "country" => "USA",
                "city" => "Newyork",
                "zip" => "NY1234",
            ) 
        ),
        array(
            "id" => 3,
            "name" => "Viktor",
            "address" => array(
                "country" => "Australia",
                "city" => "Sydney",
                "zip" => 123456,
            ) 
        ),
    )
);

$xml_data2 = new SimpleXMLElement('<?xml version="1.0" encoding="UTF-8"?><Document-OrderResponse></Document-OrderResponse>');
// function call to convert array to xml
array_to_xml($users_array,$xml_data2);
echo "<br><br>";
$xml_data2->asXML('users_array.xml');
print_r ($xml_data2->asXML());

$user1 = array(
    "id" => 1,
    "name" => "Smith",
    "address" => array(
        "country" => "United Kingdom",
        "city" => "London",
        "zip" => 56789,
    )
    );

$user2 =  array(
    "id" => 2,
    "name" => "John",
    "address" => array(
        "country" => "USA",
        "city" => "Newyork",
        "zip" => "NY1234",
    ) 
    );

$users["total_users"] = 3;
$users["users"] = array();
array_push($users["users"], $user1, $user2);
//$users["users"] = $user1;
//$users["users"] = $user2;
$xml_data3 = new SimpleXMLElement('<?xml version="1.0" encoding="UTF-8"?><Document-OrderResponse></Document-OrderResponse>');
// function call to convert array to xml
array_to_xml($users,$xml_data3);
echo "<br><br>";
$xml_data3->asXML('users.xml');
print_r ($xml_data3->asXML());

// XML to Array
echo "<br><br>";
$path = "users.xml"; 
// Read entire file into string 
$xmlfile = file_get_contents($path); 
// Convert xml string into an object 
$new = simplexml_load_string($xmlfile); 
// Convert into json 
$con = json_encode($new); 
// Convert into associative array 
$newArr = json_decode($con, true); 
print_r($newArr); 

?>