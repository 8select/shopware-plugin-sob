## 8select Curated Shopping Engine für Shopware
#  Anpassen des CSE Shopware Plugin

Sie können das 8select CSE Shopware Plugin in ihrem Shopware Backend anpassen: Dazu gehen sie unter Konfiguration > Plugin Manager und wählen hier das 8select CSE Plugin unter „installiert“ aus.

## Konfigurierbare Felder
### Aktiviert: „Ja“
Mit dieser Einstellung können sie das Plugin aktivieren oder deaktivieren. „Ja“ bedeutet, dass der Produktexport aktiv ist und das Modul befindet sich im Vorschau-Modus (standard) oder „live“ in ihrem Shop, je nach der Einstellung.

### API ID: 
Die API ID ist ihr Freischaltcode für das CSE-Modul. Sie erhalten den Code per E-Mail, nachdem sie ihr 8select Service Paket auf der [8select Website](https://www.8select.com/cse-pricing) gebucht haben.

### Feed ID: 
Freischaltcode für die sichere Übertragung des Produkt-Feeds. Sie erhalten den Code per E-Mail, nachdem sie ihr 8select Service Paket auf der  [8select Website](https://www.8select.com/cse-pricing) gebucht haben.

### Position SYS-Widget auf der Artikeldetailseite festlegen:
Hier können sie die Position des SYS-Widget auf ihrer Shop-Seite definieren. Sie haben die Auswahl zwischen:
- **Produkttabs (empfohlene Einstellung):** ein neuer TAB wird erstellt.
- **Produkttitel:** oberhalb oder unterhalb des Produkttitels/Artikelbezeichnung positionieren.
- **Produktbeschreibung/Bewertungen:** oberhalb oder unterhalb des Produktbeschreibungstextes platzieren.
- **Cross Selling:** oberhalb oder unterhalb des Blocks "Cross Selling" positonieren.
- **Nicht automatisch einfügen:** manuelle Positionierung; z.B. Einkaufswelten (verfügbare 8select-Elemente: "Set für Produkt"; "Liste von Sets"; "Bestimmtes Set").

### Widget am Block-Anfang oder Ende einfügen 
Empfohlene Einstellung: "Anfang", z. B. bei  Auswahl "Produkttabs" wird ein neuer Tab erstellt und dieser nach vorne platziert.

### Eigenes CSS: 
Voreinstellung: leer/nicht genutzt. Sollte nur durch erfahrene Entwickler genutzt werden.

### HTML-Container (CSE_SYS):
 Hier können sie dem Container eigene HTML Elemente hinzufügen. Der Tag CSE_SYS repräsentiert dabei das Widget. Zum Beispiel können sie ein HTML Header Element oberhalb des CSE_SYS einfügen. Ermöglicht es, im Widget eigene HTML-Elemente zu platzieren. Diese Einstellung sollte nur durch erfahrene Entwickler genutzt werden.
 
 ### Zeige SYS-ACC Widget 
 Emphohlene Einstellung: “Ja“. Falls Auswahl "Ja", wird das SYS-ACC-Widget auf dem AddToCart-Confirmation-Layer angezeigt.

 ### Vorschau Modus an: 
- "Nein": die Widgets spielen Ergebnisse im Shop sichtbar aus; 
- "Ja": Die Widgets werden im Preview Modus angezeigt. Fügen sie `?preview=1` am Ende der URL einer Produkt Detail Seite hinzu. Das Widget ist jetzt nur lokal bei ihnen sichtbar. Nutzen sie den Vorschau Modus um Änderungen am Design zu testen. Vergewissern sie dich dass die Option „Aktiviert“ auf „ja“ gestellt ist.

### Weitere Anpassungen mit der 8select Management Console (MCON)

Sie können Farben, Schriftarten, Buttontexte, Links und viele andere Einstellungen der 8select CSE-Widgets in der [8select Management Console](https://console.8select.io) anpassen.

- Um sich anmelden zu können, müssen Sie Ihr **8select Management Console-Benutzerkonto aktivieren**. Klicken Sie dazu auf den Link in der E-Mail, die Sie bei der Registrierung erhalten haben. Ihre Zugangsdaten werden Ihnen kurz darauf in einer zweiten E-Mail zugesandt.
- Melden Sie sich bei der Management Console an. Gehen Sie im Menü zu "Widget-Manager" > "Product Set Presenter".
- Über den Tabs "Setup", "Behaviour" und "Design" können Sie alle Anpassungen vornehmen, um das Widget nahtlos an das Erscheinungsbild Ihres Shops anzupassen.

Weitere Informationen bezüglich Konfigurationsmöglichkeiten und die Funktionalität der Management Console finden Sie in unserer [Knowledge Base](https://knowledge.8select.com).
