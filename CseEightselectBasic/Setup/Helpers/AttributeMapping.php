<?php

namespace CseEightselectBasic\Setup\Helpers;

use Doctrine\DBAL\Connection;

class AttributeMapping
{

    /**
     * @var Connection
     */
    private $connection;

    /**
     * @param Connection $connection
     */
    public function __construct(Connection $connection)
    {
        $this->connection = $connection;
    }

    /**
     * @throws \Zend_Db_Adapter_Exception
     */
    public function initAttributes()
    {
        $attributeList = [
            [
                'eightselectAttribute' => 'ean',
                'eightselectAttributeLabel' => 'EAN-CODE',
                'eightselectAttributeLabelDescr' => 'Standardisierte eindeutige Materialnummer nach EAN (European Article Number) oder UPC (Unified Product Code).',
                'shopwareAttribute' => 's_articles_details.ean',
            ],
            [
                'eightselectAttribute' => 'name1',
                'eightselectAttributeLabel' => 'ARTIKELBEZEICHNUNG',
                'eightselectAttributeLabelDescr' => 'Standardbezeichnung für den Artikel so wie er normalerweise in der Artikeldetailansicht genutzt wird (z.B. Sportliches Herren-Hemd "Arie")',
                'shopwareAttribute' => 's_articles.name',
            ],
            [
                'eightselectAttribute' => 'name2',
                'eightselectAttributeLabel' => 'ALTERNATIVE ARTIKELBEZEICHNUNG',
                'eightselectAttributeLabelDescr' => 'Oft als Kurzbezeichnung in Listenansichten verwendet (z.B. "Freizeit-Hemd") oder für Google mit mehr Infos zur besseren Suche',
                'shopwareAttribute' => '-',
            ],
            [
                'eightselectAttribute' => 'beschreibung',
                'eightselectAttributeLabel' => 'BESCHREIBUNGSTEXT HTML',
                'eightselectAttributeLabelDescr' => 'Der Beschreibungstext zum Artikel, auch "description long" genannt, im HTML-Format z.B. "<p>Federleichte Regenhose! </ br> ...</p>"',
                'shopwareAttribute' => 's_articles.description_long',
            ],
            [
                'eightselectAttribute' => 'beschreibung2',
                'eightselectAttributeLabel' => 'ALTERNATIVER BESCHREIBUNGSTEXT',
                'eightselectAttributeLabelDescr' => 'zusätzliche Informationen zum Produkt, technische Beschreibung, Kurzbeschreibung oder auch Keywords',
                'shopwareAttribute' => 's_articles.keywords',
            ],
            [
                'eightselectAttribute' => 'rubrik',
                'eightselectAttributeLabel' => 'PRODUKTKATEGORIE / -RUBRIK',
                'eightselectAttributeLabelDescr' => 'bezeichnet spezielle Artikelgruppen, die als Filter oder Shop-Navigation genutzt werden (z.B. Große Größen, Umstandsmode, Stillmode)',
                'shopwareAttribute' => '-',
            ],
            [
                'eightselectAttribute' => 'typ',
                'eightselectAttributeLabel' => 'PRODUKTTYP / UNTERKATEGORIE',
                'eightselectAttributeLabelDescr' => 'verfeinerte Shop-Navigation oder Unterkategorie (z.B. Lederjacke, Blouson, Parka)',
                'shopwareAttribute' => '-',
            ],
            [
                'eightselectAttribute' => 'abteilung',
                'eightselectAttributeLabel' => 'ABTEILUNG',
                'eightselectAttributeLabelDescr' => 'Einteilung der Sortimente nach Zielgruppen  (z.B. Damen, Herren, Kinder)',
                'shopwareAttribute' => '-',
            ],
            [
                'eightselectAttribute' => 'kiko',
                'eightselectAttributeLabel' => 'KIKO',
                'eightselectAttributeLabelDescr' => 'Speziell für Kindersortimente: Einteilung nach Zielgruppen (z.B. Mädchen, Jungen, Baby)',
                'shopwareAttribute' => '-',
            ],
            [
                'eightselectAttribute' => 'bereich',
                'eightselectAttributeLabel' => 'BEREICH',
                'eightselectAttributeLabelDescr' => 'Damit können Teilsortimente bezeichnet sein (z.B. Outdoor; Kosmetik; Trachten; Lifestyle)',
                'shopwareAttribute' => '-',
            ],
            [
                'eightselectAttribute' => 'sportart',
                'eightselectAttributeLabel' => 'SPORTART',
                'eightselectAttributeLabelDescr' => 'speziell bei Sportartikeln (z.B. Handball, Bike, Bergsteigen)',
                'shopwareAttribute' => '-',
            ],
            [
                'eightselectAttribute' => 'serie',
                'eightselectAttributeLabel' => 'SERIE',
                'eightselectAttributeLabelDescr' => 'Hier können Bezeichnungen für Serien übergeben werden, um Artikelfamilien oder Sondereditionen zu kennzeichnen (z.B. Expert Line, Mountain Professional)',
                'shopwareAttribute' => '-',
            ],
            [
                'eightselectAttribute' => 'gruppe',
                'eightselectAttributeLabel' => 'GRUPPE / BAUKAUSTEN',
                'eightselectAttributeLabelDescr' => 'bezeichnet direkt zusammengehörige Artikel (z.B. Bikini-Oberteil "Aloha" und Bikini-Unterteil "Aloha" = Gruppe 1002918; Baukasten-Sakko "Ernie" und Baukasten-Hose "Bert" = Gruppe "E&B"). Dabei können auch mehr als 2 Artikel eine Gruppe bilden (z.B. Mix & Match: Gruppe "Hawaii" = 3 Bikini-Oberteile können mit 2 Bikini-Unterteilen frei kombiniert werden) . Die ID für eine Gruppe kann eine Nummer oder ein Name sein.',
                'shopwareAttribute' => '-',
            ],
            [
                'eightselectAttribute' => 'saison',
                'eightselectAttributeLabel' => 'SAISON',
                'eightselectAttributeLabelDescr' => 'Beschreibt zu welcher Saison bzw. saisonalen Kollektion der Artikel gehört (z.B. HW18/19; Sommer 2018; Winter)',
                'shopwareAttribute' => '-',
            ],
            [
                'eightselectAttribute' => 'farbe',
                'eightselectAttributeLabel' => 'FARBE',
                'eightselectAttributeLabelDescr' => 'Die exakte Farbbezeichnung des Artikels (z.B. Gelb; Himbeerrot; Rosenrot)',
                'shopwareAttribute' => '-',
            ],
            [
                'eightselectAttribute' => 'farbspektrum',
                'eightselectAttributeLabel' => 'FARBSPEKTRUM',
                'eightselectAttributeLabelDescr' => 'Farben sind einem Farbspektrum zugeordnet (z.B. Farbe: Himbeerrot > Farbspektrum: Rot)',
                'shopwareAttribute' => '-',
            ],
            [
                'eightselectAttribute' => 'muster',
                'eightselectAttributeLabel' => 'MUSTER',
                'eightselectAttributeLabelDescr' => 'Farbmuster des Artikels (z.B. uni, einfarbig,  kariert, gestreift, Blumenmuster, einfarbig-strukturiert)',
                'shopwareAttribute' => '-',
            ],
            [
                'eightselectAttribute' => 'waschung',
                'eightselectAttributeLabel' => 'WASCHUNG',
                'eightselectAttributeLabelDescr' => 'optische Wirkung des Materials (bei Jeans z.B.  used, destroyed, bleached, vintage)',
                'shopwareAttribute' => '-',
            ],
            [
                'eightselectAttribute' => 'stil',
                'eightselectAttributeLabel' => 'STIL',
                'eightselectAttributeLabelDescr' => 'Stilrichtung des Artikels (z.B.  Business, Casual,  Ethno, Retro)',
                'shopwareAttribute' => '-',
            ],
            [
                'eightselectAttribute' => 'detail',
                'eightselectAttributeLabel' => 'DETAIL',
                'eightselectAttributeLabelDescr' => 'erwähnenswerte Details an Artikeln (z.B. Reißverschluss seitlich am Saum, Brusttasche, Volants, Netzeinsatz, Kragen in Kontrastfarbe)',
                'shopwareAttribute' => '-',
            ],
            [
                'eightselectAttribute' => 'passform',
                'eightselectAttributeLabel' => 'PASSFORM',
                'eightselectAttributeLabelDescr' => 'in Bezug auf die Körperform, wird häufig für  Hemden, Sakkos und Anzüge verwendet (z.B. schmal, bequeme Weite, slim-fit, regular-fit, comfort-fit, körpernah)',
                'shopwareAttribute' => '-',
            ],
            [
                'eightselectAttribute' => 'schnitt',
                'eightselectAttributeLabel' => 'SCHNITT',
                'eightselectAttributeLabelDescr' => 'in Bezug auf die Form des Artikels  (z.B. Bootcut, gerades Bein, Oversized, spitzer Schuh)',
                'shopwareAttribute' => '-',
            ],
            [
                'eightselectAttribute' => 'aermellaenge',
                'eightselectAttributeLabel' => 'ÄRMELLÄNGE',
                'eightselectAttributeLabelDescr' => 'speziell bei Oberbekleidung: Länge der Ärmel (z.B. normal, extra-lange Ärmel, ärmellos, 3/4 Arm)',
                'shopwareAttribute' => '-',
            ],
            [
                'eightselectAttribute' => 'kragenform',
                'eightselectAttributeLabel' => 'KRAGENFORM',
                'eightselectAttributeLabelDescr' => 'speziell bei Oberbekleidung: Beschreibung des Kragens  oder Ausschnitts (z.B. Rollkragen, V-Ausschnitt, Blusenkragen, Haifischkragen)',
                'shopwareAttribute' => '-',
            ],
            [
                'eightselectAttribute' => 'verschluss',
                'eightselectAttributeLabel' => 'VERSCHLUSS',
                'eightselectAttributeLabelDescr' => 'beschreibt Verschlussarten (z.B: geknöpft, Reißverschluss,  Druckknöpfe, Klettverschluss; Haken&Öse)',
                'shopwareAttribute' => '-',
            ],
            [
                'eightselectAttribute' => 'obermaterial',
                'eightselectAttributeLabel' => 'ART OBERMATERIAL',
                'eightselectAttributeLabelDescr' => 'wesentliches Material des Artikels (z.B. Wildleder, Denim,  Edelstahl, Gewebe, Strick, Jersey, Sweat, Crash)',
                'shopwareAttribute' => '-',
            ],
            [
                'eightselectAttribute' => 'material',
                'eightselectAttributeLabel' => 'MATERIAL',
                'eightselectAttributeLabelDescr' => 'bezeichnet die genaue Materialzusammensetzung (z.B. 98% Baumwolle, 2% Elasthan)',
                'shopwareAttribute' => '-',
            ],
            [
                'eightselectAttribute' => 'funktion',
                'eightselectAttributeLabel' => 'FUNKTION',
                'eightselectAttributeLabelDescr' => 'beschreibt Materialfunktionen und -eigenschaften (z.b. schnelltrocknend, atmungsaktiv, 100% UV-Schutz; pflegeleicht, bügelleicht, körperformend)',
                'shopwareAttribute' => '-',
            ],
            [
                'eightselectAttribute' => 'eigenschaft',
                'eightselectAttributeLabel' => 'EIGENSCHAFT / EINSATZBEREICH',
                'eightselectAttributeLabelDescr' => 'speziell für Sport und Outdoor. Hinweise zum Einsatzbereich (Bsp. Schlafsack geeignet für Temparaturbereich 1 °C bis -16 °C, kratzfest, wasserdicht)',
                'shopwareAttribute' => '-',
            ],
            [
                'eightselectAttribute' => 'auspraegung',
                'eightselectAttributeLabel' => 'AUSFÜHRUNG & MAßANGABEN',
                'eightselectAttributeLabelDescr' => 'speziell für Sport und Outdoor. Wichtige Informationen,  die helfen, den Artikel in das Sortiment einzuordnen (Beispiele: bei Rucksäcken: Volumen "30-55 Liter"; bei Skistöcken: Größenangaben in Maßeinheit "Körpergröße 160 bis 175cm";  Sonderausführungen: "Linkshänder")',
                'shopwareAttribute' => '-',
            ],
            [
                'eightselectAttribute' => 'fuellmenge',
                'eightselectAttributeLabel' => 'FUELLMENGE',
                'eightselectAttributeLabelDescr' => 'bezieht sich auf die Menge des Inhalts des Artikels (z.B. 200ml; 0,5 Liter, 3kg, 150 Stück)',
                'shopwareAttribute' => '-',
            ],
            [
                'eightselectAttribute' => 'absatzhoehe',
                'eightselectAttributeLabel' => 'ABSATZHÖHE',
                'eightselectAttributeLabelDescr' => 'speziell bei Schuhen: Höhe des Absatzes (Format mit oder ohne Maßeinheit z.B. 5,5 cm oder 5,5)',
                'shopwareAttribute' => '-',
            ],
            [
                'eightselectAttribute' => 'sonstiges',
                'eightselectAttributeLabel' => 'SONSTIGES',
                'eightselectAttributeLabelDescr' => 'zusätzliche Artikelinformationen, die keinem spezifischen Attribut zugeordnet werden können',
                'shopwareAttribute' => '-',
            ],
        ];

        foreach ($attributeList as $attributeEntry) {
            $sql = 'INSERT INTO 8s_attribute_mapping (eightselectAttribute, eightselectAttributeLabel, eightselectAttributeLabelDescr, shopwareAttribute) VALUES (?, ?, ?, ?)';
            $this->connection->executeUpdate(
                $sql,
                [
                    $attributeEntry['eightselectAttribute'],
                    $attributeEntry['eightselectAttributeLabel'],
                    $attributeEntry['eightselectAttributeLabelDescr'],
                    $attributeEntry['shopwareAttribute'],
                ]
            );
        }
    }
}
