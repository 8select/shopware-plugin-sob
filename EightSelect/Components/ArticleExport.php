<?php
namespace EightSelect\Components;

use Shopware\Models\Article\Article;
use Shopware\Models\Article\Detail;

class ArticleExport {

    CONST STORAGE = 'files/export/';

    public $fields = [
        'sku'                => 'sku',
        'mastersku'          => 'mastersku',
        'status'             => 'status',
        'warenkorb_id'       => 'warenkorb_id',
        'ean'                => 'ean',
        'name1'              => 'name1',
        'name2'              => 'name2',
        'kategorie1'         => 'kategorie1',
        'kategorie2'         => 'kategorie2',
        'kategorie3'         => 'kategorie3',
        'streich_preis'      => 'streich_preis',
        'angebots_preis'     => 'angebots_preis',
        'groesse'            => 'groesse',
        'marke'              => 'marke',
        'bereich'            => 'bereich',
        'rubrik'             => 'rubrik',
        'abteilung'          => 'abteilung',
        'kiko'               => 'kiko',
        'typ'                => 'typ',
        'farbe'              => 'farbe',
        'farbspektrum'       => 'farbspektrum',
        'absatzhoehe'        => 'absatzhoehe',
        'muster'             => 'muster',
        'aermellaenge'       => 'aermellaenge',
        'kragenform'         => 'kragenform',
        'obermaterial'       => 'obermaterial',
        'passform'           => 'passform',
        'schnitt'            => 'schnitt',
        'waschung'           => 'waschung',
        'stil'               => 'stil',
        'sportart'           => 'sportart',
        'detail'             => 'detail',
        'auspraegung'        => 'auspraegung',
        'baukasten'          => 'baukasten',
        'eigenschaft'        => 'eigenschaft',
        'fuellmenge'         => 'fuellmenge',
        'funktion'           => 'funktion',
        'gruppe'             => 'gruppe',
        'material'           => 'material',
        'saison'             => 'saison',
        'serie'              => 'serie',
        'produkt_url'        => 'produkt_url',
        'bilder'             => 'bilder',
        'beschreibung'       => 'beschreibung',
        'beschreibung1'      => 'beschreibung1',
        'beschreibung2'      => 'beschreibung2',
    ];

    public function doCron() {
        $date = strtotime(date('Y-m-d'));

        /** @var \Shopware\Components\Model\ModelManager $em */
        $em = Shopware()->Container()->get('models');

        $details = $em->getRepository(Detail::class)->findAll();

        if (!is_dir(self::STORAGE)) {
            mkdir(self::STORAGE, 775, true);
        }

        $fp = fopen(self::STORAGE . 'products_full_' . date('Ymdhi') . '.csv', 'a');

        $header = [];
        foreach ($this->fields as $key => $field) {
            if (is_array($field)) {
                $header = array_merge($header, $field);
                continue;
            }
            $header[] = $field;
        }

        fputcsv($fp, $header, ';');


        /** @var Detail $detail */
        foreach ($details as $detail) {
            /** @var Article $article */
            $article = $detail->getArticle();
            $line = [];
            foreach ($this->fields as $key => $field) {
                $class = 'EightSelect\\Service\\' . $key;
                if (class_exists($class)) {
                    $class::generate($article, $detail, $line, $date);
                } elseif (method_exists($detail, $key)) {
                    $line[] = $detail->$key() . '';
                } elseif (method_exists($article, $key)) {
                    $line[] = $article->$key() . '';
                } else {
                    $line[] = $key;
                }
            }

            fputcsv($fp, $line, ';');
        }

        fclose($fp);

        $em->flush();
    }
}
