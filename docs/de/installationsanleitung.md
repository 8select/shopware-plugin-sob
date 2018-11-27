##  8select Curated Shopping Engine für Shopware
# Installationsanleitung

## System Anforderungen
- Systemvoraussetzung: ab Shopware 5.2.17; 
- Shopware-Standard-PlugIn "Cron"; 
- Crontab-Eintrag auf dem Server, um regelmäßig Shopware-Cronjobs auszuführen.

## Vorbereitung
- Wählen Sie ein **8select service package** auf der [8select website](https://www.8select.com/cse-pricing) und vervollständigen Sie Ihre Registrierung. Nach erfolgreicher Registrierung erhalten Sie eine E-Mail mit **zwei Freischaltcodes** für das 8select plugin für Shopware und einen **weiteren Aktivierungslink** für Ihre Zugangsdaten zu der 8select Management Console.
- Aktivieren Sie Ihren **8select Management Console user account**, indem Sie auf den Link in der E-Mail klicken. Sie erhalten im Anschluss Ihre Zugangsdaten. Diese ermöglichen ihnen den Zugang zur Management Console, wo Sie weiterführende Einstellungen an dem 8select CSE Plugin vornehmen können.
- Stellen Sie sicher, dass Sie die **Freischaltcodes** während des nachfolgendem Installationsprozess zur Hand haben.

## Installation des 8select Plugin

1. **Download des 8select Plugin aus dem Shopware Store und Aktivierung**  
  Sie können das Plugin [hier](https://store.shopware.com/detail/index/sArticle/164960) herunterladen. Bitte aktivieren Sie im Anschluss den Plugin in Ihrem Shopware Backend Plugin Manager, bevor Sie fortfahren.

2. **Einfügen der Freischaltcodes und Aktivierung der Konfiguration**
Sie haben die Freischaltcodes bereits per E-Mail erhalten, nachdem Sie erfolgreich Ihr Service Paket auf der [8select website](https://www.8select.com/cse-pricing) bestellt haben.  

    Sie haben erhalten:
    - **API ID** - zur sicheren Übertragung von Produktvorschlägen und Leistungstracking
    - **Feed ID** - zur sichern Übertragung des Produkt Daten Feed  

    Gehen Sie in den Konfiguration Tab in Ihrem Shopware Plugin Manager und **tragen Sie die Freischaltcodes** in die dafür vorgesehenen Felder ein. Setzen Sie **„Aktiviert“** auf **„ja“** und speichern Sie Ihre Eingaben.

[insert image: 02_insert_keys]

3. **Definieren der Größenrelevanten Attributegruppen für den Produktdaten Feed**
    - In Ihrem Shopware Backend gehen Sie auf „Artikel“ > „Übersicht“
    - Wählen Sie ein Produkt aus und gehen Sie zu „Varianten“ > „Konfiguration“
    - Klicken Sie auf das Editieren Icon einer relevanten Gruppe wie bspw. „Größe“ und aktivieren Sie „definiert Größe“
    - Klicken Sie auf „Speichern“ und wiederholen Sie diesen Schritt für alle relevanten Attributgruppen dieses Produktes. Das können Sie bei allen Attributen wie „Gefäßgröße“, „Skigröße“ oder „Stocklänge“ machen.
    - Sie müssen diese Änderungen nur exemplarisch für ein Produkt vornehmen. Ihre Änderungen werden in den allgemeinen Einstellungen gespeichert und für alle Produktvarianten automatisch übernommen.

4. **Auswahl der Attribute für den Export Ihrer Produktdaten**
    - Im Shopware Backen gehen Sie auf „Einstellungen“ > „8select“ > „Export Einstellungen“
    - Doppelklicken Sie auf die Reihe mit „Farbe“ in der Spalte 8select Attribute Mapping
    - Innerhalb dieser Reihe gehen Sie auf das Feld „Shopware Attribute“ und wählen Sie „Konfigurator-Gruppe: Farbvariante“ aus dem Dropdown Menü.
    - Klicken Sie auf „update“ um Ihre Einstellungen zu speichern.

     [insert video: eng-export-settings-farbvariant]

5. **Start der Feed Verarbeitung**
    Um den Datenfeed erfolgreich verarbeiten zu können, muss das Shopware Cron Plugin aktivert sein und ein Crontab Eintrag auf Ihrem Server hinterlegt sein der die Shopware Cronjobs in regelmäßigen Abständen ausführt. Bitte stellen Sie sicher, dass der Crontab Eintrag ordnungsgemäß eingerichtet ist bevor Sie mit der Installation fortfahren.
    
    Sie können einen vollständigen Export aller Produkte manuell anstoßen:
    - Gehen Sie auf „Einstellungen“ > „8select“ > „Manueller Export“.
    - Wählen Sie „Produkt Voll-Export anstoßen.
    
    [insert image: 05_insert_keys]
       
    > **Hinweis für Testshops:** Wenn Sie das 8select CSE Plugin in Ihrem Testshop 	testen und kein Cron Plugin aktiviert haben können Sie den 8select-Cronjob auf Ihrem Server mit folgendem Befehl ausführen:  
    `Php bin/console sw:cron:run Shopware_CronJob_CseEightselectBasicArticleExport -f`

6. **Sie haben die 8select Curated Shopping Engine erfolgreich installiert**  
    Sie können verifizieren ob Ihre Installation Erfolgreich war indem Sie den Vorschaumodus im 8select Plugin aufrufen.
    - Rufen Sie eine Seite in Ihrem Shop auf wo das Widget angezeigt werden soll
    - Fügen Sie `?preview=1` ans Ende der URL hinzu.   
    Zum Beispiel: `http://meinshop.de/fashion/145/mein-produkt?preview=1`  
    - Das Widget sollte nun im standard Design angezeigt werden.
    - Um das Design anzupassen lesen Sie bitte unseren [konifgurationsanleitung](./customization.md) oder schauen Sie in unserer [Knowledge Base](https://knowledge.8select.com) nach.
    - Um das Widget in Ihrem Shop „live“ zu stellen **deaktivieren Sie bitte den Vorschaumodus**.

7. **Vorschaumodus deaktivieren**
    - In Ihrem Shopware Backend gehen Sie auf Einstellungen > Plugin Manager und wählen Sie das 8select CSE Plugin unter „installiert“ aus.
    - Scrollen Sie zu dem Tab „Konfiguration“ und setzen Sie den **„Vorschaumodus an“** auf **„Nein“**.
