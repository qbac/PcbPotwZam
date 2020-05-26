<?php

class OrdersFromCustomers {
    
    private $idCechaNaglConfirm = 10019; //id header attributes Orders from recipients
    private $idGrupaDok = 80; // Group Orders from recipients
    private $toConfirmation = 'Do potwierdzenia';
    private $totalLines = 0;
    private $lpPozConfirmed = '';
    public $locationXmlFile = "xml/";
    //public $data = array();
    public $XmlData;
    public $conn = '';

    public function getHeaderOrder($idNagl) {
        $query = "SELECT NRDOKWEW, cast('Now' as date) as OrderResponseDate, NAPODSTAWIE, CAST(DATADOK as date) as DATADOK FROM nagl
        WHERE id_nagl='{$idNagl}'";
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        $result = $stmt->fetchAll(PDO::FETCH_ASSOC);

        $data['OrderResponse-Header']['OrderResponseType'] = '231';
        $data['OrderResponse-Header']['OrderResponseNumber'] = $result[0]['NRDOKWEW'];
        $data['OrderResponse-Header']['OrderResponseDate'] = $result[0]['ORDERRESPONSEDATE'];
        $data['OrderResponse-Header']['OrderNumber'] = $result[0]['NAPODSTAWIE'];
        $data['OrderResponse-Header']['OrderDate'] = $result[0]['DATADOK'];
        $data['OrderResponse-Header']['DocumentFunctionCode'] = 'O';
        $this->addToXmlData($data, $this->XmlData);

    }

    public function getBuyer($idNagl) {
        $query = "select DK.id_danekontrah, K.globalnrlokaliz, DK.nazwadl, DK.kodpocztowy, DK.ulica, DK.nrdomu, DK.nrlokalu, DK.miejscowosc, KR.kodkraju FROM nagl n
        LEFT OUTER JOIN KONTRAH K ON (N.ID_KONTRAH = K.ID_KONTRAH)  
        LEFT OUTER JOIN DANEKONTRAH DK ON (N.ID_DANEKONTRAH = DK.ID_DANEKONTRAH)
        LEFT OUTER join kraj KR ON (DK.id_kraj = kr.id_kraj)
        WHERE n.id_nagl='{$idNagl}'";
        
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        $result = $stmt->fetchAll(PDO::FETCH_ASSOC);

        $data['OrderResponse-Parties']['Buyer']['ILN'] = $result[0]['GLOBALNRLOKALIZ'];
        $data['OrderResponse-Parties']['Buyer']['PartyName'] = $result[0]['NAZWADL'];
        if ($result[0]['NRLOKALU']) {
            $data['OrderResponse-Parties']['Buyer']['StreetAndNumber'] = $result[0]['ULICA'].' '.$result[0]['NRDOMU'].'/'.$result[0]['NRLOKALU'];
        } else {
            $data['OrderResponse-Parties']['Buyer']['StreetAndNumber'] = $result[0]['ULICA'].' '.$result[0]['NRDOMU'];
        }
        $data['OrderResponse-Parties']['Buyer']['CityName'] = $result[0]['MIEJSCOWOSC'];
        $data['OrderResponse-Parties']['Buyer']['PostCode'] = $result[0]['KODPOCZTOWY'];
        $data['OrderResponse-Parties']['Buyer']['Country'] = $result[0]['KODKRAJU'];
        $this->addToXmlData($data, $this->XmlData);
    }

    public function getSeller() {
        $query = "select FIRST 1 df.globalnrlokaliz, df.nazwa_firmy, Df.kodpocztowy, Df.ulica, Df.nrdomu, Df.nrlokalu, Df.miejscowosc, kr.kodkraju
        FROM danefirmy df
        LEFT OUTER join kraj KR ON (Df.id_kraj = kr.id_kraj)";
        
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        $result = $stmt->fetchAll(PDO::FETCH_ASSOC);

        $data['OrderResponse-Parties']['Seller']['ILN'] = $result[0]['GLOBALNRLOKALIZ'];
        $data['OrderResponse-Parties']['Seller']['PartyName'] = $result[0]['NAZWA_FIRMY'];
        if ($result[0]['NRLOKALU']) {
            $data['OrderResponse-Parties']['Seller']['StreetAndNumber'] = $result[0]['ULICA'].' '.$result[0]['NRDOMU'].'/'.$result[0]['NRLOKALU'];
        } else {
            $data['OrderResponse-Parties']['Seller']['StreetAndNumber'] = $result[0]['ULICA'].' '.$result[0]['NRDOMU'];
        }
        $data['OrderResponse-Parties']['Seller']['CityName'] = $result[0]['MIEJSCOWOSC'];
        $data['OrderResponse-Parties']['Seller']['PostCode'] = $result[0]['KODPOCZTOWY'];
        $data['OrderResponse-Parties']['Seller']['Country'] = $result[0]['KODKRAJU'];
        $this->addToXmlData($data, $this->XmlData);
    }

    public function getDeliveryPoint() {
        $this->data['OrderResponse-Parties']['DeliveryPoint']= ' ';
    }

    private function getOrdersToConfirmed () {

        $query = "SELECT WC.ID_NAGL
        FROM WYSTCECHNAGL WC
        INNER JOIN CECHADOKK CD  ON (WC.ID_CECHADOKK = CD.ID_CECHADOKK)
        inner JOIN NAGL NA ON (WC.id_nagl = NA.id_nagl)
        LEFT JOIN NAGLDANE ND ON (WC.id_nagl = ND.id_nagl)
        WHERE
        WC.id_cechadokk = {$this->idCechaNaglConfirm} 
        AND NA.id_grupadok = {$this->idGrupaDok}
        AND ((WC.wartosc = '{$this->toConfirmation}') OR (ND.ID_NAGL_PRIORYTET = 5))
        AND NA.status = 0";
        
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        $result = $stmt->fetchAll(PDO::FETCH_ASSOC);
        return $result;
    }

    private function getPositionToConfirmed ($idNagl) {

    $query = "SELECT p.lp, k.indeks, p.kart_nazwadl, p.ilosc, p.jm, p.cenanetto, CAST(pzs.termindost as date) as termindost, pzs.datawysylki FROM poz p
    INNER join pozzamwsp pzs ON p.id_poz = pzs.id_poz
    INNER join kartoteka k ON p.id_kartoteka = k.id_kartoteka
    where
    p.id_nagl = {$idNagl}
    AND pzs.termindost is not null
    AND pzs.termindost <> '30.12.1899'
    AND (iif(cast(pzs.termindost as timestamp) <> iif(pzs.datawysylki is null, '30.12.1899',cast(pzs.datawysylki as timestamp)), 1, 0) = 1)";
    
    $stmt = $this->conn->prepare($query);
    $stmt->execute();
    $result = $stmt->fetchAll(PDO::FETCH_ASSOC);
    if ($result) {
    foreach ($result as $value)
        {
            $data['OrderResponse-Lines']['Line']['Line-Item']['LineNumber'] = $value["LP"];
            $data['OrderResponse-Lines']['Line']['Line-Item']['SupplierItemCode'] = $value["INDEKS"];
            $data['OrderResponse-Lines']['Line']['Line-Item']['OrderedQuantity'] = $value["ILOSC"];
            $data['OrderResponse-Lines']['Line']['Line-Item']['QuantityToBeDelivered'] = $value["ILOSC"];
            $data['OrderResponse-Lines']['Line']['Line-Item']['UnitOfMeasure'] = $value["JM"];
            $data['OrderResponse-Lines']['Line']['Line-Item']['OrderedUnitNetPrice'] = $value["CENANETTO"];
            $data['OrderResponse-Lines']['Line']['Line-Item']['ExpectedDeliveryDate'] = $value["TERMINDOST"];
            $this->addToXmlData($data, $this->XmlData);
            $this->lpPozConfirmed = $this->lpPozConfirmed.' '.$value["LP"];
            $data = array();
            $this->totalLines++;
        }
    }

    //return $result;
}

    private function getTotalLines ($tl) {
        $data['OrderResponse-Summary']['TotalLines'] = $tl;
        $this->addToXmlData($data, $this->XmlData);
        $data = array();
    }

    private function setStatusConfirmed () {

    }

    private function setLogConfirmed ($idNagl, $idTresc) {
        $tresc[0]='Poprawnie wygenerowano potwierdzenie zamówienia';
        $tresc[1]='Błąd. Nie można wygenerować potwierdzenia zamówienia. Brak pozycji do potwierdzenia';
        $query = "EXECUTE PROCEDURE rejestroper_add(136,1,{$idNagl},'{$tresc[$idTresc]}')";       
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
    }

    private function addToXmlData($data, &$xml_data){
        foreach( $data as $key => $value ) {
            if( is_array($value) ) {
                if( is_numeric($key) ){
                    $key = 'item'.$key; //dealing with <0/>..<n/> issues
                }
                $subnode = $xml_data->addChild($key);
                $this->addToXmlData($value, $subnode);
            } else {
                $xml_data->addChild("$key",htmlspecialchars("$value"));
            }
         }
    }
    private function generateXmlFile($idNagl) {

        $this->XmlData->asXML($this->locationXmlFile.''.$idNagl.'.xml');
    }

    public function checkToConfirmedAndGenXML () {
        $result = $this->getOrdersToConfirmed();
        if ($result) {
            foreach ($result as $value)
            {
                $this->XmlData = new SimpleXMLElement('<?xml version="1.0" encoding="UTF-8"?><Document-OrderResponse></Document-OrderResponse>');
                echo "NAGŁÓWEK DOKUMENTU: ".$value["ID_NAGL"]."\n";
                $this->getHeaderOrder($value["ID_NAGL"]);
                $this->getBuyer($value["ID_NAGL"]);
                $this->getSeller();
                //$this->getDeliveryPoint();
                $this->getPositionToConfirmed($value["ID_NAGL"]);
                $this->getTotalLines($this->totalLines);
                if ($this->totalLines > 0) {
                    $this->generateXmlFile($value["ID_NAGL"]);
                    
                } else {$this->setLogConfirmed($value["ID_NAGL"],1);}
                //$this->setStatusConfirmed();
                //$this->setLogConfirmed();
                //return $r;
                $this->XmlData = null;
                $this->totalLines = 0;
                $this->lpPozConfirmed = '';
            }
        }
    }
}

?>