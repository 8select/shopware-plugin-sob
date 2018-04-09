<?php

use EightSelect\Models\Attribute;

class Shopware_Controllers_Backend_EightSelect extends \Shopware_Controllers_Backend_Application
{
    protected $model = Attribute::class;
    protected $alias = 'attribute';

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
                    'shopwareAttibuteName' => 'Attribute',
                    'shopwareAttibute'     => 'attr1'
                ],
                [
                    'eightSelectAttribute' => 'name1',
                    'shopwareAttibuteName' => 'Attribute',
                    'shopwareAttibute'     => 'attr1'
                ],
                [
                    'eightSelectAttribute' => 'name2',
                    'shopwareAttibuteName' => 'Attribute',
                    'shopwareAttibute'     => 'attr1'
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
                    'shopwareAttibuteName' => 'Attribute',
                    'shopwareAttibute'     => 'attr1'
                ],
                [
                    'eightSelectAttribute' => 'angebots_preis',
                    'shopwareAttibuteName' => 'Attribute',
                    'shopwareAttibute'     => 'attr1'
                ],
                [
                    'eightSelectAttribute' => 'groesse',
                    'shopwareAttibuteName' => 'Attribute',
                    'shopwareAttibute'     => 'attr1'
                ],
                [
                    'eightSelectAttribute' => 'marke',
                    'shopwareAttibuteName' => 'Attribute',
                    'shopwareAttibute'     => 'attr1'
                ],
                [
                    'eightSelectAttribute' => 'bereich',
                    'shopwareAttibuteName' => 'Attribute',
                    'shopwareAttibute'     => 'attr1'
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
                    'shopwareAttibuteName' => 'Attribute',
                    'shopwareAttibute'     => 'attr1'
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
                    'shopwareAttibuteName' => 'Attribute',
                    'shopwareAttibute'     => 'attr1'
                ],
                [
                    'eightSelectAttribute' => 'beschreibung1',
                    'shopwareAttibuteName' => '',
                    'shopwareAttibute'     => ''
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
