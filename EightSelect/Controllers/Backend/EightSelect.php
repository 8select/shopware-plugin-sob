<?php

use EightSelect\Models\EightSelectAttribute;

class Shopware_Controllers_Backend_EightSelect extends \Shopware_Controllers_Backend_Application
{
    protected $model = EightSelectAttribute::class;
    protected $alias = 'eightSelectAttribute';

    public function getAttributeListAction()
    {
        $this->View()->assign($this->getAttributeList());
    }

    protected function getAttributeList()
    {
        return [
            'success' => true,
            'data' => [
                [
                    'eightSelectAttribute' => 'ean',
                    'shopwareAttibuteName' => 'ean',
                    'shopwareAttibute'     => 'ean'
                ],
                [
                    'eightSelectAttribute' => 'name1',
                    'shopwareAttibuteName' => 'articleName',
                    'shopwareAttibute'     => 'articleName'
                ],
                [
                    'eightSelectAttribute' => 'name2',
                    'shopwareAttibuteName' => '',
                    'shopwareAttibute'     => ''
                ],
                [
                    'eightSelectAttribute' => 'kategorie1',
                    'shopwareAttibuteName' => '',
                    'shopwareAttibute'     => ''
                ],
                [
                    'eightSelectAttribute' => 'kategorie2',
                    'shopwareAttibuteName' => '',
                    'shopwareAttibute'     => ''
                ],
                [
                    'eightSelectAttribute' => 'kategorie3',
                    'shopwareAttibuteName' => '',
                    'shopwareAttibute'     => ''
                ],
                [
                    'eightSelectAttribute' => 'streich_preis',
                    'shopwareAttibuteName' => '',
                    'shopwareAttibute'     => ''
                ],
                [
                    'eightSelectAttribute' => 'angebots_preis',
                    'shopwareAttibuteName' => '',
                    'shopwareAttibute'     => ''
                ],
                [
                    'eightSelectAttribute' => 'groesse',
                    'shopwareAttibuteName' => 'Attribute',
                    'shopwareAttibute'     => 'attr1'
                ],
                [
                    'eightSelectAttribute' => 'marke',
                    'shopwareAttibuteName' => 'Herstellername',
                    'shopwareAttibute'     => 'supplierName'
                ],
                [
                    'eightSelectAttribute' => 'bereich',
                    'shopwareAttibuteName' => '',
                    'shopwareAttibute'     => ''
                ],
                [
                    'eightSelectAttribute' => 'rubrik',
                    'shopwareAttibuteName' => '',
                    'shopwareAttibute'     => ''
                ],
                [
                    'eightSelectAttribute' => 'abteilung',
                    'shopwareAttibuteName' => '',
                    'shopwareAttibute'     => ''
                ],
                [
                    'eightSelectAttribute' => 'kiko',
                    'shopwareAttibuteName' => '',
                    'shopwareAttibute'     => ''
                ],
                [
                    'eightSelectAttribute' => 'typ',
                    'shopwareAttibuteName' => '',
                    'shopwareAttibute'     => ''
                ],
                [
                    'eightSelectAttribute' => 'farbe',
                    'shopwareAttibuteName' => '',
                    'shopwareAttibute'     => ''
                ],
                [
                    'eightSelectAttribute' => 'farbspektrum',
                    'shopwareAttibuteName' => '',
                    'shopwareAttibute'     => ''
                ],
                [
                    'eightSelectAttribute' => 'absatzhoehe',
                    'shopwareAttibuteName' => '',
                    'shopwareAttibute'     => ''
                ],
                [
                    'eightSelectAttribute' => 'muster',
                    'shopwareAttibuteName' => '',
                    'shopwareAttibute'     => ''
                ],
                [
                    'eightSelectAttribute' => 'aermellaenge',
                    'shopwareAttibuteName' => '',
                    'shopwareAttibute'     => ''
                ],
                [
                    'eightSelectAttribute' => 'kragenform',
                    'shopwareAttibuteName' => '',
                    'shopwareAttibute'     => ''
                ],
                [
                    'eightSelectAttribute' => 'obermaterial',
                    'shopwareAttibuteName' => '',
                    'shopwareAttibute'     => ''
                ],
                [
                    'eightSelectAttribute' => 'passform',
                    'shopwareAttibuteName' => '',
                    'shopwareAttibute'     => ''
                ],
                [
                    'eightSelectAttribute' => 'schnitt',
                    'shopwareAttibuteName' => '',
                    'shopwareAttibute'     => ''
                ],
                [
                    'eightSelectAttribute' => 'waschung',
                    'shopwareAttibuteName' => '',
                    'shopwareAttibute'     => ''
                ],
                [
                    'eightSelectAttribute' => 'stil',
                    'shopwareAttibuteName' => '',
                    'shopwareAttibute'     => ''
                ],
                [
                    'eightSelectAttribute' => 'sportart',
                    'shopwareAttibuteName' => '',
                    'shopwareAttibute'     => ''
                ],
                [
                    'eightSelectAttribute' => 'detail',
                    'shopwareAttibuteName' => '',
                    'shopwareAttibute'     => ''
                ],
                [
                    'eightSelectAttribute' => 'auspraegung',
                    'shopwareAttibuteName' => '',
                    'shopwareAttibute'     => ''
                ],
                [
                    'eightSelectAttribute' => 'baukasten',
                    'shopwareAttibuteName' => '',
                    'shopwareAttibute'     => ''
                ],
                [
                    'eightSelectAttribute' => 'eigenschaft',
                    'shopwareAttibuteName' => '',
                    'shopwareAttibute'     => ''
                ],
                [
                    'eightSelectAttribute' => 'fuellmenge',
                    'shopwareAttibuteName' => 'weight',
                    'shopwareAttibute'     => 'weight'
                ],
                [
                    'eightSelectAttribute' => 'funktion',
                    'shopwareAttibuteName' => '',
                    'shopwareAttibute'     => ''
                ],
                [
                    'eightSelectAttribute' => 'gruppe',
                    'shopwareAttibuteName' => '',
                    'shopwareAttibute'     => ''
                ],
                [
                    'eightSelectAttribute' => 'material',
                    'shopwareAttibuteName' => '',
                    'shopwareAttibute'     => ''
                ],
                [
                    'eightSelectAttribute' => 'saison',
                    'shopwareAttibuteName' => '',
                    'shopwareAttibute'     => ''
                ],
                [
                    'eightSelectAttribute' => 'serie',
                    'shopwareAttibuteName' => '',
                    'shopwareAttibute'     => ''
                ],
                [
                    'eightSelectAttribute' => 'beschreibung',
                    'shopwareAttibuteName' => 'description',
                    'shopwareAttibute'     => 'description'
                ],
                [
                    'eightSelectAttribute' => 'beschreibung1',
                    'shopwareAttibuteName' => 'description_long',
                    'shopwareAttibute'     => 'description_long'
                ],
                [
                    'eightSelectAttribute' => 'beschreibung2',
                    'shopwareAttibuteName' => '',
                    'shopwareAttibute'     => ''
                ]
            ]
        ];
    }
}
