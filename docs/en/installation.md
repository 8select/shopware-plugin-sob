##  8select Curated Shopping Engine for Shopware
#  Installation Manual

## System requirements

- Shopware version 5.2.17 or higher
- Your shop has to be reachable from the internet


## Before Getting Started

- Choose an **8select service package** on the [8select website](https://www.8select.com/cse-pricing) and complete your registration. After registration, you receive an email with **two registration keys for the 8select plugin for Shopware** and an **activation link** for your **8select Management Console user credentials**.
- Activate your **8select Management Console user account** by clicking on the link in the email. You will receive your login credentials. These give you access to the Management Console, where you can make further customizations later.
- Make sure you have the **registration keys** at hand during the following installation process.


## Installing the 8select plugin

1. **Download the 8select plugin from the Shopware Store and activate it**  
   You can download the plugin [here](https://store.shopware.com/detail/index/sArticle/164960). Please activate it in your Shopware Backend Plugin Manager before continuing.

2. **Insert your registration keys and activate the configuration**  
   You have already received these registration keys by e-mail after the purchase of your service package on the [8select website](https://www.8select.com/cse-pricing).

   You received:
   - **API ID** key - for secure transmission of product recommendations and performance tracking
   - **Feed ID** key - for secure transmission of the product data feed

   Go to the configuration tab in the Shopware Plugin Manager and **insert the keys** in the corresponding fields. Set **"Aktiviert"** to **"Ja"**. Save your settings.

   ![Plugin Configuration](https://d3b0t4f30thpgq.cloudfront.net/plugins/shopware/en/config-en.png)

3. **Define size relevant attribute groups for the product data feed** 
    - In your Shopware Backend menu go to "Items" > "Overview"
    - Choose a product and go to "Variants" > "Configuration" 
    - Click the edit icon of a size relevant attribute group like "Größe" and activate "Definiert Größe". 
    - Click "Save" and repeat this for every size relevant attribute group of this product. This could also be attributes like "Gefäßgröße", "Skigröße" or "Stocklänge".
    - You only need to make these settings for one product. Your changes will be applied to the general settings for product variants automatically.

    [Define Variant Attributes for size Video](https://d3b0t4f30thpgq.cloudfront.net/plugins/shopware/en/eng-installation-size-attributes.mp4)

4. **Select attributes for the export of your product data**
    - In your Shopware Backend menu go to "Configuration" > "8select" > "Export Settings"
    - Double click on the row "FARBE" in the column 8select Attribute Mapping
    - Within this row, go to the field "Shopware Attribute" and select "Konfigurator-Gruppe: Farbvariante" from the dropdown menu.
    - Click "Update" to save your settings.

    [Define Attribute Mapping](https://d3b0t4f30thpgq.cloudfront.net/plugins/shopware/en/eng-export-settings-farbvariant.mp4)

5. **Start processing your feed**

If you have configured the plugin successfully (2. and 3.) product data will be pulled automatically.

    - 01:00 UTC a full product export
    - 01:30 UTC a full property export 
    - every 15 minutes a status and price export of all changed products since the last export

6. **You have now successfully installed the 8select Curated Shopping Engine**  
    You can check if your installation was successful through the default preview mode of the 8select CSE plugin.
    - Go to a page in your online shop where the widget should be shown.
    - Add `?preview=1&8s_demo=1` to the end of the URL. For example: `http://meinshop.de/fashion/145/mein-produkt?preview=1&8s_demo=1`
        - `preview=1` will show the widget while the plugin is in preview mode
        - `8s_demo=1` will show our demo set while your products are processed and not available yet
    - The widget should now be visible in its default design and a demo set. 
    - To customize the design, please read our [guide on customizing the 8select CSE Widget](./customization.md) or check our [Knowledge Base](https://knowledge.8select.com). 
    - To **set the widget "live"** in your shop you need to **deactivate the preview mode**. 
  
7. **Deactivate the preview mode** 
    - In your Shopware Backend go to Configuration > Plugin Manager and select the 8select CSE plugin under "installed". 
    - Scroll down to the tab "Konfiguration" and set **"Vorschaumodus an"** to **"Nein"**.
