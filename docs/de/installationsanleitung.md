##  8select Curated Shopping Engine für Shopware
# Installationsanleitung

## System Anforderungen
- ab Shopware 5.2.17
- Shop muss aus dem Internet erreichbar sein

## Vorbereitung
- Wählen Sie ein **Service Paket** auf [8select.com](https://www.8select.com/cse-pricing) und vervollständigen Sie Ihre Registrierung. Nach erfolgreicher Registrierung erhalten Sie eine E-Mail mit **zwei Freischaltcodes** für das 8select Plugin für Shopware und einen **weiteren Aktivierungslink** für Ihre **Zugangsdaten zu der 8select Management Console (MCON)**.
- Aktivieren Sie Ihren **8select Management Console user account**, indem Sie auf den Link in der E-Mail klicken. Sie erhalten im Anschluss Ihre Zugangsdaten. 
- In der Management Console (MCON) werden nach der Plugin Installation in Shopware alle weiteren Einstellungen am 8select CSE Plugin vorgenommen.

## Installation des 8select Plugins

1. **Download des 8select Plugins aus dem Shopware Store und Aktivierung**  
  
    Sie können das Plugin [hier](https://store.shopware.com/detail/index/sArticle/164960) herunterladen. Bitte aktivieren Sie im Anschluss das Plugin in Ihrem Shopware Backend Plugin Manager, bevor Sie fortfahren.

2. **Einfügen der Freischaltcodes und Aktivierung der Konfiguration**
    
    Sie finden die Freischaltcodes in der MCON unter [Settings -> Plugin](https://console.8select.io/settings/plugin)

    Gehen Sie in den Konfiguration Tab in Ihrem Shopware Plugin Manager und **tragen Sie die Freischaltcodes** in die dafür vorgesehenen Felder ein. Setzen Sie **„Aktiviert“** auf **„ja“** und speichern Sie Ihre Eingaben.

    ![Plugin Konfiguration](https://d3b0t4f30thpgq.cloudfront.net/plugins/shopware/de/config-de.png)

3. **Konfiguration der CSE**
    
    Bevor die CSE Sets ausspielt, müssen sie je nach gewähltem Service Paket noch Einstellungen in der MCON vornehmen.

    Sie können hier auch das Aussehen der Widgets anpassen.

    Sie finden die Konfiguration in der MCON unter [Settings](https://console.8select.io/settings).
    
4. **Sie haben die 8select Curated Shopping Engine erfolgreich installiert**  
    
    Sie können verifizieren, ob Ihre Installation erfolgreich war, indem Sie den Vorschaumodus im 8select Plugin aufrufen.
    - Rufen Sie eine Produktdetailseite in Ihrem Shop auf
    - Fügen Sie `?preview=1&8s_demo=1` ans Ende der URL hinzu.   
    Zum Beispiel: `http://meinshop.de/fashion/145/mein-produkt?preview=1&8s_demo=1`
        - `preview=1` zeigt das Widget an, wenn das Plugin im Preview-Modus ist
        - `8s_demo=1` zeigt das Demoset, solange ihre Produkte verarbeitet werden und noch nicht verfügbar sind
    - Das Widget sollte nun im Standard Design angezeigt werden.
    - Das Design können Sie in der MCON anpassen.
    - Um das Widget in Ihrem Shop „live“ zu stellen, **deaktivieren Sie bitte den Vorschaumodus**.

5. **Vorschaumodus deaktivieren**

    - In Ihrem Shopware Backend gehen Sie auf "Einstellungen > Plugin Manager" und wählen Sie das 8select CSE Plugin unter „installiert“ aus.
    - Scrollen Sie zum Tab „Konfiguration“ und setzen Sie den **„Vorschaumodus an“** auf **„Nein“**.
