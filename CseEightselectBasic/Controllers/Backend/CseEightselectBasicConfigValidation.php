<?php

use CseEightselectBasic\Components\ConfigValidator;

class Shopware_Controllers_Backend_CseEightselectBasicConfigValidation extends \Shopware_Controllers_Backend_ExtJs
{
    public function validateAction()
    {
        $isValid = true;
        $messages = array();

        $isActive = ConfigValidator::isPluginActive();
        $apiId = ConfigValidator::getApiId();
        $feedId = ConfigValidator::getFeedId();
        $HtmlContainer = ConfigValidator::getHtmlContainer();
        $sizeDefinitions = ConfigValidator::hasSizeDefinitions();

        if ( !$isActive ) {
            $isValid = false;
            array_push($messages, "Plugin ist nicht aktiv");
        }

        if ( !$apiId ) {
            $isValid = false;
            array_push($messages, "Keine API ID hinterlegt");
        }

        if ( $apiId && strlen($apiId) !== 36 ) {
            $isValid = false;
            array_push($messages, "Die hinterlegte API ID ist ungültig");
        }

        if ( !$feedId ) {
            $isValid = false;
            array_push($messages, "Keine Feed ID hinterlegt");
        }

        if ( $feedId && strlen($feedId) !== 36 ) {
            $isValid = false;
            array_push($messages, "Die hinterlegte Feed ID ist ungültig");
        }

        if ( strlen($HtmlContainer) === 0 || strpos($HtmlContainer, 'CSE_SYS') === false ) {
            $isValid = false;
            array_push($messages, "Kein Widget-Platzhalter (CSE_SYS) im HTML-Container");
        }

        if ( !$sizeDefinitions ) {
          $isValid = false;
          $noSizesMessage = "Keine Attributgruppe als Größe definiert. Mehr Infos finden Sie in der " . 
          "<a href='https://www.8select.com/8select-cse-installationsanleitung-shopware#5-konfiguration-attributfelder' target='_blank'>Installationsanleitung</a>";
          array_push($messages, $noSizesMessage);
        }

        $validationResult = [
            'isValid' => $isValid,
            'messages' => $messages
        ];

        $this->View()->assign(['validationResult' => $validationResult]);
    }
}
