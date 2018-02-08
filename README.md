# shopware-plugin-sob

## 1. Installation des 8select CSE Plugins

Um das Plugin in Shopware zu laden, führen Sie folgende Schritte aus:

* Öffnen Sie den **Plugin Manager** unter **"Einstellungen -> Plugin Manager".**
* Navigieren Sie im Plugin Manager zum Menüpunkt **"Installiert"**.
* Suchen Sie in der Kopfleiste nach dem Button **"Plugin hochladen"**.

![](Documentation/plugin-upload_de.png)

* Wählen Sie die Zip-Datei aus und laden sie hoch. Zum Beispiel `8select_CSE_Shopware-5.2_1.0.0.zip`
* Finden Sie das Plugin unter **"Deinstalliert"**, dann gehen Sie wie folgt vor:
    * Klicken Sie auf das **grüne Pluszeichen** rechts neben dem Modul, um es zu installieren. Sollte rechts davon ein blauer, kreisrunder Pfeil angezeigt werden, klicken Sie diesen vor der Installation, um das Modul upzudaten.    
    * Es öffnet sich ein Modulfenster. Klicken Sie darin auf den Button **"Aktivieren".**
   
* Wenn sich das Plugin nicht unter **"Deinstalliert"** befindet, sondern unter **"Deaktiviert"**, dann klicken Sie auf das **rote X** in der Spalte **"Aktiviert"**, um das Plugin zu aktivieren.

![](Documentation/plugin-not-installed_de.png)

* Wenn das Plugin sowohl installiert als auch aktiviert ist, fahren Sie bei **2.** fort.


### **WICHTIGER HINWEIS:** Änderungen an den Plugin-, als auch Widget-Einstellungen erfordern ein erneutes [Kompilieren der Themes](#22-themes-neu-kompilieren)!


## 2. Konfiguration

Nach erfolgreichem Upload und Installation folgt nun die Konfiguration des 8select CSE Plugins.

### 2.1. Konfigurieren des Moduls
Klicken Sie im **Plugin Manager** bei dem 8select Modul auf den **Stift**, um das Modul zu konfigurieren.

#### 2.1.1 Notwendige Einstellungen
* Tragen Sie unter **"Händler-Id"** ihre Händler-Id ein, die Ihnen von 8select zugewiesen wurde.
 
* **"Widget-Plazierung(1)"**: Legen Sie Fest, wo das 8select CSE Plugin auf der Produktdetail-Seite plaziert wird.
    * Wählen Sie bei **"Position, an der das SYS-widget platziert wird"** aus, an welcher Position (innerhalb welchen Blocks) das Widget platziert wird. Mögliche Positionen sind:
        
        * bei **Produktbeschreibung / Bewertungen**
        * bei **Produkttitel**
        * bei **Cross selling**
        * als **Produkttab**
        * gar nicht
        
    * Wählen Sie bei **"Widget am Block-Anfang oder -Ende einfügen"** aus, ob das Widget am Anfang oder am Ende des ausgewählten Blocks platziert werden soll.
    
* **"HTML-Container"**: Geben Sie eigenen HTML-Code an, um das Widget in gewünschten Containern zu platzieren. **CSE_SYS** muss vorhanden sein und wird durch den tatsächlichen Widget-Code ersetzt. Zum Beispiel:

```
	<div class="your-container">
	    <div class="title">8select CSE Widget</div>
	    <div class="your-widget">CSE_SYS</div>
	</div>
```

Klicken Sie auf **Speichern.** Alle weiteren Einstellungen sind optional.

![](Documentation/plugin-config_de.png)

#### 2.1.2 Optionale Einstellungen

* **"Eigenes CSS"**: Geben Sie hier eigenen CSS-Code an, um Anpassungen an Ihrem HTML-Container vorzunehmen. Zum Beispiel:

```
.your-container {
    padding: 10px;
}
.your-container .title {
    font-size: 32px;
}
.your-container .your-widget {
    background-color: #ccc;
}
```

#### 2.2 Themes neu kompilieren

Da das Plugin eine JavaScript Datei hinzufügt, müssen sie ggf. den Theme Cache invalidieren und Themes neu kompilieren um das JavaScript Bundling erneut anzustoßen.

### 3. 8select Einkaufswelten-Widgets

Mit Installation und Konfiguration des Moduls sind automatisch Widgets zur Integration für Einkaufswelten verfügbar.
 
### 3.1 Einbindung der Einkaufswelten-Widgets

Um Einkaufswelten Widgets verwenden zu können, führen Sie folgende Schritte aus:
* Öffnen Sie **Einkaufswelten** unter **"Marketing -> Einkaufswelten".**
* Wählen Sie die gewünschte Einkaufswelt zur Konfiguration aus, indem Sie auf den **Stift** klicken, oder erstellen Sie eine neue.
* Es öffnet sich das Fenster **Designer**, in dem verschiedene Widgets platziert werden können.

![](Documentation/plugin-shoppingworlds-slots_de.png)


* Standardmäßig ist der Reiter **Einstellungen** geöffnet. Wechseln Sie zum Reiter **Elemente** und scrollen Sie zum Abschnitt **Weitere Elemente**

![](Documentation/plugin-shoppingworlds-components_de.png)

* Hier finden Sie drei Widgets, die vom Modul zur Verfügung gestellt werden:
    * **Set für Produkt** zeigt ein Set für ein bestimmtes Produkt an
    * **Liste von Sets** zeigt eine Liste von Sets für einen bestimmten Stylefactor
    * **Bestimmtes Set** zeigt ein Set für eine bestimmte Set-ID an

* Ziehen Sie das gewünschte Widget in einen freien Widget-Slot. Drücken Sie nach der Konfiguration (siehe **3.2.**) auf **Einkaufswelt speichern**

![](Documentation/plugin-shoppingworlds-slots-filled_de.png)

### 3.2 Konfiguration der Einkaufswelten-Widgets

Um ein Widget zu konfigurieren, drücken Sie den **Stift** im jeweiligen Widget-Slot. Für die drei Widgets gibt es folgende Konfigurationsmöglichkeiten:

### 3.2.1 Element-Einstellungen der Einkaufswelten-Widgets

* **Set für Produkt**: Geben Sie über ein Suchfeld die **Bestellnummer** des Produkts an, für welches ein Set dargestellt werden soll.
![](Documentation/plugin-setforproduct_de.png)

* **Liste von Sets**: Geben Sie den **Stylefactor** an, über den eine Liste von Sets dargestellt werden soll.
![](Documentation/plugin-listofsets_de.png)
* **Bestimmtes Set**: Geben Sie die **Set-ID** an, über die ein bestimmtes Set dargestellt werden soll.
![](Documentation/plugin-certainset_de.png)

### 3.2.2 Globale Element-Einstellungen

Für alle Widgets lassen sich noch folgende globale Element-Einstellungen festlegen: 

* **Lazy Loading Distance Factor**: Definieren Si einen Faktor auf Basis der Fensterhöhe, ab dem das Widget unterhalb des sichtbaren Scrollbereiches vorgeladen werden soll ("lazy loading"). 

**Beispiel:** 

* 0 = Laden, sobald sich das Widget direkt unterhalb des sichtbaren Bereiches befindet
* 1 = Laden, sobald sich das Widget eine Fensterhöhe weit unterhalb des sichtbaren Bereich befindet

![](Documentation/plugin-lazyloaddistance_de.png)

* **CSS Klasse**: Geben Sie mit Leerzeichen getrennt mehrere Klassennamen an, die hinzugefügt werden sollen.
![](Documentation/plugin-globalcss_de.png)
