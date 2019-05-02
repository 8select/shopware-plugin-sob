##  8select Curated Shopping Engine for Shopware
#  Installation manual

## System requirements

- Shopware version 5.2.17 or higher
- Your shop has to be reachable from the internet


## Before getting started

- Choose an **8select service package** on the [8select website](https://www.8select.com/cse-pricing) and complete your registration. After registration, you receive an email with **two registration keys for the 8select plugin for Shopware** and an **activation link** for your **8select Management Console (MCON) user credentials**.
- Activate your **8select Management Console user account** by clicking on the link in the email. You will receive your login credentials. These give you access to the Management Console, where you can make further customizations later.
- In the Management Console (MCON) all further settings of the 8select CSE Plugin are done after the plugin installation in Shopware is completed.


## Installing the 8select plugin

1. **Download the 8select plugin from the Shopware Store and activate it**  
   You can download the plugin [here](https://store.shopware.com/detail/index/sArticle/164960). Please activate it in your Shopware Backend Plugin Manager before continuing.

2. **Insert your registration keys and activate the configuration**  
   
   You can find the unlock codes in the MCON under [Settings -> Plugin](https://console.8select.io/settings/plugin)

   Go to the configuration tab in the Shopware Plugin Manager and **insert the keys** in the corresponding fields. Set **"Aktiviert"** to **"Ja"**. Save your settings.

   ![Plugin Configuration](https://d3b0t4f30thpgq.cloudfront.net/plugins/shopware/en/config-en.png)

3. **Configure the CSE**
    
    Before the CSE will display sets, you have to make settings in the MCON, depending on the service package selected.

    You can also change the appearance of the widgets here.

    You can find the configuration in the MCON under [Settings](https://console.8select.io/settings).

4. **You have now successfully installed the 8select Curated Shopping Engine**  

    You can check if your installation was successful through the default preview mode of the 8select CSE plugin.
    - Go to a page in your online shop where the widget should be shown.
    - Add `?preview=1&8s_demo=1` to the end of the URL. For example: `http://meinshop.de/fashion/145/mein-produkt?preview=1&8s_demo=1`
        - `preview=1` will show the widget while the plugin is in preview mode
        - `8s_demo=1` will show our demo set while your products are processed and not available yet
    - The widget should now be visible in its default design and a demo set. 
    - To customize the design, please read our [guide on customizing the 8select CSE Widget](./customization.md) or check our [Knowledge Base](https://knowledge.8select.com). 
    - To **set the widget "live"** in your shop you need to **deactivate the preview mode**. 
  
5. **Deactivate the preview mode** 

    - In your Shopware Backend go to Configuration > Plugin Manager and select the 8select CSE plugin under "installed". 
    - Scroll down to the tab "Konfiguration" and set **"Vorschaumodus an"** to **"Nein"**.
