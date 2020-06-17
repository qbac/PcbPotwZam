<?php

class OrdersFromCustomers {
    
    private $idCechaNaglConfirm = ID_CECHA_NAGL_CONFIRM; //id header attributes Orders from recipients
    private $idGrupaDok = 80; // Group Orders from recipients
    private $toConfirmation = VALUE_CHECHA_NAGL_TO_CONFIRM;
    private $idPriorytetConfirmation = ID_NAGL_PRIORYTET_TO_CONFIRM;
    private $idKontrahToConfirm = ID_KONTRAH_TO_CONFIRM;
    private $ignoreIdKartoteka = IGNORE_ID_KARTOTEKA;
    private $totalLines = 0;
    private $lpPozConfirmed = '';
    private $nrDokWew ='';
    private $defaultDeliveryPointIln = DEFAULT_DELIVERY_POINT_ILN;
    public $locationXmlFile = LOCATION_XML_FILES ;
    public $data = array();
    public $XmlData;
    public $conn ;

    public function getHeaderOrder($idNagl) {
        $query = "SELECT NRDOKWEW, cast('Now' as date) as OrderResponseDate, NAPODSTAWIE, CAST(DATADOK as date) as DATADOK FROM nagl
        WHERE id_nagl='{$idNagl}'";
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        $result = $stmt->fetchAll(PDO::FETCH_ASSOC);
        $this->nrDokWew = $result[0]['NRDOKWEW'];
        $this->data['OrderResponse-Header']['OrderResponseType'] = '231';
        $this->data['OrderResponse-Header']['OrderResponseNumber'] = $result[0]['NRDOKWEW'];
        $this->data['OrderResponse-Header']['OrderResponseDate'] = $result[0]['ORDERRESPONSEDATE'];
        $this->data['OrderResponse-Header']['OrderNumber'] = $result[0]['NAPODSTAWIE'];
        $this->data['OrderResponse-Header']['OrderDate'] = $result[0]['DATADOK'];
        $this->data['OrderResponse-Header']['DocumentFunctionCode'] = 'O';
        //$this->addToXmlData($data, $this->XmlData);

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

        $this->data['OrderResponse-Parties']['Buyer']['ILN'] = $result[0]['GLOBALNRLOKALIZ'];
        $this->data['OrderResponse-Parties']['Buyer']['PartyName'] = $result[0]['NAZWADL'];
        if ($result[0]['NRLOKALU']) {
            $this->data['OrderResponse-Parties']['Buyer']['StreetAndNumber'] = $result[0]['ULICA'].' '.$result[0]['NRDOMU'].'/'.$result[0]['NRLOKALU'];
        } else {
            $this->data['OrderResponse-Parties']['Buyer']['StreetAndNumber'] = $result[0]['ULICA'].' '.$result[0]['NRDOMU'];
        }
        $this->data['OrderResponse-Parties']['Buyer']['CityName'] = $result[0]['MIEJSCOWOSC'];
        $this->data['OrderResponse-Parties']['Buyer']['PostCode'] = $result[0]['KODPOCZTOWY'];
        $this->data['OrderResponse-Parties']['Buyer']['Country'] = $result[0]['KODKRAJU'];
        //$this->addToXmlData($data, $this->XmlData);
    }

    public function getSeller() {
        $query = "select FIRST 1 df.globalnrlokaliz, df.nazwa_firmy, Df.kodpocztowy, Df.ulica, Df.nrdomu, Df.nrlokalu, Df.miejscowosc, kr.kodkraju
        FROM danefirmy df
        LEFT OUTER join kraj KR ON (Df.id_kraj = kr.id_kraj)";
        
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        $result = $stmt->fetchAll(PDO::FETCH_ASSOC);

        $this->data['OrderResponse-Parties']['Seller']['ILN'] = $result[0]['GLOBALNRLOKALIZ'];
        $this->data['OrderResponse-Parties']['Seller']['PartyName'] = $result[0]['NAZWA_FIRMY'];
        if ($result[0]['NRLOKALU']) {
            $this->data['OrderResponse-Parties']['Seller']['StreetAndNumber'] = $result[0]['ULICA'].' '.$result[0]['NRDOMU'].'/'.$result[0]['NRLOKALU'];
        } else {
            $this->data['OrderResponse-Parties']['Seller']['StreetAndNumber'] = $result[0]['ULICA'].' '.$result[0]['NRDOMU'];
        }
        $this->data['OrderResponse-Parties']['Seller']['CityName'] = $result[0]['MIEJSCOWOSC'];
        $this->data['OrderResponse-Parties']['Seller']['PostCode'] = $result[0]['KODPOCZTOWY'];
        $this->data['OrderResponse-Parties']['Seller']['Country'] = $result[0]['KODKRAJU'];
    }

    public function getDeliveryPoint() {
        $this->data['OrderResponse-Parties']['DeliveryPoint']['ILN']= $this->defaultDeliveryPointIln;
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
        AND NA.ID_KONTRAH = {$this->idKontrahToConfirm}
        AND ((WC.wartosc = '{$this->toConfirmation}') OR (ND.ID_NAGL_PRIORYTET = {$this->idPriorytetConfirmation}))
        AND NA.status = 0";
        
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        $result = $stmt->fetchAll(PDO::FETCH_ASSOC);
        return $result;
    }

    private function getPositionToConfirmed ($idNagl) {

    $query = "SELECT p.lp, k.indeks, p.kart_nazwadl, p.ilosc, p.jm, p.cenanetto, CAST(pzs.termindost as date) as termindost, pzs.datawysylki, p.id_poz FROM poz p
    INNER join pozzamwsp pzs ON p.id_poz = pzs.id_poz
    INNER join kartoteka k ON p.id_kartoteka = k.id_kartoteka
    where
    p.id_nagl = {$idNagl}
    AND pzs.termindost is not null
    AND pzs.termindost <> '30.12.1899'
    AND (iif(cast(pzs.termindost as timestamp) <> iif(pzs.datawysylki is null, '30.12.1899',cast(pzs.datawysylki as timestamp)), 1, 0) = 1)";
    if($this->ignoreIdKartoteka <>''){
        $query .= " AND p.id_kartoteka not in ({$this->ignoreIdKartoteka})";
    }
    
    $stmt = $this->conn->prepare($query);
    $stmt->execute();
    $result = $stmt->fetchAll(PDO::FETCH_ASSOC);
    if ($result) {
        $data['OrderResponse-Lines']= array();    
    foreach ($result as $value)
        {
            // $data['OrderResponse-Lines']['Line']['Line-Item']['LineNumber'] = $value["LP"];
            // $data['OrderResponse-Lines']['Line']['Line-Item']['SupplierItemCode'] = $value["INDEKS"];
            // $data['OrderResponse-Lines']['Line']['Line-Item']['OrderedQuantity'] = $value["ILOSC"];
            // $data['OrderResponse-Lines']['Line']['Line-Item']['QuantityToBeDelivered'] = $value["ILOSC"];
            // $data['OrderResponse-Lines']['Line']['Line-Item']['UnitOfMeasure'] = Schema::$jm[$value["JM"]];
            // $data['OrderResponse-Lines']['Line']['Line-Item']['OrderedUnitNetPrice'] = $value["CENANETTO"];
            // $data['OrderResponse-Lines']['Line']['Line-Item']['ExpectedDeliveryDate'] = $value["TERMINDOST"];

            $dataLine['Line-Item']['LineNumber'] = $value["LP"];
            $dataLine['Line-Item']['SupplierItemCode'] = $value["INDEKS"];
            $dataLine['Line-Item']['OrderedQuantity'] = number_format($value["ILOSC"],3);
            $dataLine['Line-Item']['QuantityToBeDelivered'] = number_format($value["ILOSC"],3);
            $dataLine['Line-Item']['UnitOfMeasure'] = Schema::$jm[$value["JM"]];
            $dataLine['Line-Item']['OrderedUnitNetPrice'] = number_format($value["CENANETTO"],2);
            $dataLine['Line-Item']['ExpectedDeliveryDate'] = $value["TERMINDOST"];
            
            array_push($data['OrderResponse-Lines'],$dataLine);
            $this->lpPozConfirmed = $this->lpPozConfirmed.' '.$value["LP"];
            
                $query = "UPDATE pozzamwsp pzs SET pzs.datawysylki = '{$value["TERMINDOST"]}' WHERE pzs.id_poz={$value["ID_POZ"]}";
                $stmt = $this->conn->prepare($query);
                $stmt->execute();

            //$data = array();
            $dataLine = array();
            $this->totalLines++;
        }
        $this->addToXmlData($data, $this->XmlData);
    }

    //return $result;
}

    private function getTotalLines ($tl) {
        $data['OrderResponse-Summary']['TotalLines'] = $tl;
        $this->addToXmlData($data, $this->XmlData);
        $data = array();
    }

    private function setStatusConfirmed ($idNagl) {
        $query = "UPDATE WYSTCECHNAGL W Set W.WARTOSC = 'Potwierdzone'
        Where (W.Id_Nagl = {$idNagl}) and (W.Id_CechaDokK = {$this->idCechaNaglConfirm})";
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        $query = "UPDATE NAGLDANE N Set ID_NAGL_PRIORYTET = 0
        Where (N.Id_Nagl = {$idNagl})";
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
    }

    private function setLogConfirmed ($idNagl, $idTresc, $trescLog = '') {
        $tresc[1]='Poprawnie wygenerowano potwierdzenie zamówienia';
        $tresc[2]='Błąd. Nie można wygenerować potwierdzenia zamówienia.';
        if ($idTresc == 0) {
            $query = "EXECUTE PROCEDURE rejestroper_add(136,1,{$idNagl},'{$trescLog}')";
        }else {
            $tr = $tresc[$idTresc].' '.$trescLog;
            $query = "EXECUTE PROCEDURE rejestroper_add(136,1,{$idNagl},'{$tr}')";
        }       
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
    }

    private function addToXmlData($data, &$xml_data){
        foreach( $data as $key => $value ) {
            if( is_array($value) ) {
                if( is_numeric($key) ){
                    $key = 'Line';
                }
                $subnode = $xml_data->addChild($key);
                $this->addToXmlData($value, $subnode);
            } else {
                $xml_data->addChild("$key",htmlspecialchars("$value"));
            }
         }
    }
    private function generateXmlFile($idNagl) {
        $mydate=getdate();
        $md = date("Y-m-d_H-i");
        $nrdok = str_replace("/","-",$this->nrDokWew);
        $fileName = $idNagl."_".$nrdok."_".$md.'.xml';
        $this->XmlData->asXML($this->locationXmlFile.''.$fileName);
        return $fileName;
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
                $this->getDeliveryPoint();
                $this->addToXmlData($this->data, $this->XmlData);
                $this->getPositionToConfirmed($value["ID_NAGL"]);
                $this->getTotalLines($this->totalLines);
                if ($this->totalLines > 0) {
                    $fileName = $this->generateXmlFile($value["ID_NAGL"]);
                    $this->setLogConfirmed($value["ID_NAGL"],1, "Lp: ".$this->lpPozConfirmed." Plik: ".$fileName);
                    $this->setStatusConfirmed($value["ID_NAGL"]);
                } else {
                    $this->setLogConfirmed($value["ID_NAGL"],2,'Brak pozycji do potwierdzenia');
                    $this->setStatusConfirmed($value["ID_NAGL"]);
                }
                //$this->setStatusConfirmed();
                //$this->setLogConfirmed();
                //return $r;
                $this->XmlData = null;
                $this->totalLines = 0;
                $this->lpPozConfirmed = '';
                $this->nrDokWew = '';
            }
        }
    }
}

?>