<?php

class OrdersFromCustomers {
    
    private $idCechaNaglConfirm = 10019; //id header attributes Orders from recipients
    private $idGrupaDok = 80; // Group Orders from recipients
    private $toConfirmation = 'Do potwierdzenia';
    public $data = array();
    public $conn = '';

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
        return $result;
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
        return $result;
    }

    private function getOrdersToConfirmed () {

        $query = "SELECT WC.ID_NAGL
        FROM WYSTCECHNAGL WC
        INNER JOIN CECHADOKK CD  ON (WC.ID_CECHADOKK = CD.ID_CECHADOKK)
        inner JOIN NAGL NA ON (WC.id_nagl = NA.id_nagl)
        WHERE
        WC.id_cechadokk = {$this->idCechaNaglConfirm}
        AND NA.id_grupadok = {$this->idGrupaDok}
        AND WC.wartosc = '{$this->toConfirmation}'";
        
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        $result = $stmt->fetchAll(PDO::FETCH_ASSOC);
        return $result;
    }

    private function getPositionToConfirmed ($idNagl) {

    $query = "SELECT p.id_poz, pzs.termindost, pzs.datawysylki FROM poz p
    INNER join pozzamwsp pzs ON p.id_poz = pzs.id_poz
    where
    p.id_nagl = {$idNagl}
    AND pzs.termindost is not null
    AND pzs.termindost <> '30.12.1899'
    AND (iif(cast(pzs.termindost as timestamp) <> iif(pzs.datawysylki is null, '30.12.1899',cast(pzs.datawysylki as timestamp)), 1, 0) = 1)";
    
    $stmt = $this->conn->prepare($query);
    $stmt->execute();
    $result = $stmt->fetchAll(PDO::FETCH_ASSOC);
    return $result;
}

    private function setStatusConfirmed () {

    }

    private function setLogConfirmed () {

    }

    private function generateXmlFile() {

        //$this->data = '';
    }

    public function checkToConfirmedAndGenXML () {

        foreach ($this->getOrdersToConfirmed() as $value)
        {
            //$this->getBuyer($value["ID_NAGL"]);
            //$this->getSeller();
            $this->getPositionToConfirmed($value["ID_NAGL"]);
            //$this->generateXmlFile();
            //$this->setStatusConfirmed();
            //$this->setLogConfirmed();
        }
    }
}

?>