##  8select Curated Shopping Engine for Shopware
#  Customize the CSE Shopware Plugin 

You can customize the 8select CSE Shopware plugin in your Shopware Backend: go to Configuration > Plugin Manager and select the 8select CSE plugin under "installed". 

## Fields to configure

### Activated: "Ja" (Yes)
With this configuration setting, you can activate or deactivate the plugin.
"Ja" means that the product export is active and the module is shown in preview mode (default) or "live" in your shop, according to your preview mode settings. 

### API ID
Registration key for the CSE-module. You receive this key by e-mail, after purchasing your **8select service package** on the [8select website](https://www.8select.com/cse-pricing).


### Feed ID
Registration key for secure transmission of the product data feed. You receive this key by e-mail, after purchasing your **8select service package** on the [8select website](https://www.8select.com/cse-pricing).

### Position SYS-Widget
Here you can define the position of the SYS-Widget on your web pages. You can choose between:
- **Product tabs (recommended):** a new tab is created on the product detail page.
- **Product title:** positioning above or below the product title or product name.
- **Product description/rating:** positioning above or below the product description text
- **Cross Selling:** positioning above or below the "Cross Selling" block.
- **Do not insert automatically**: manual positioning. For example positioning at Shopware "Einkaufswelten". The available 8select elements here are: "set for product", "list of sets", "defined set".

### Add Widget to block top or bottom  
Recommended setting is "Anfang" which means top. Combined with the SYS-Widget position "Product tabs" a new tab is created and set to the front per default.

### Custom CSS  
This field is empty per default. Recommended to be used only by experienced developers.  

### HTML Container (CSE_SYS)  
Here you can add custom HTML elements to a container that wraps the widget. The tag CSE_SYS represents the widget. For example, you can add an HTML header element above "CSE_SYS". Recommended to be used only by experienced developers. 

### Show SYS-ACC Widget  
Recommended setting: "Ja" (Yes). In this case, the SYS-ACC-Widget is added to the AddToCart-Confirmation-Layer.

### Preview mode activated  
- "Nein" (No): the widget is live in your shop.
- "Ja" (Yes):  the widget is shown only in preview mode. Add `?preview=1` to the end of the URL of a product detail page. The widget is now visible on your local computer. Use the preview mode URL to check the current design settings of the widget. Please make sure the option "Activated" is set to "Ja".


### Further customizations with the 8select Management Console (MCON)
You can customize colors, fonts, button texts, links and many other settings of the 8select CSE Widgets in the [8select Management Console](https://console.8select.io). 

- To be able to login you need to activate your **8select Management Console user account** by clicking on the link in the email you received at registration. Your login credentials will be sent to you in a second email shortly after.
- Log in to the Management Console. In the menu go to "Widget-Manager" > "Product Set Presenter".
- At the tabs "Setup", "Behaviour" and "Design" you can make all the customizations to make the widget fit the look and feel of your shop seamlessly.

More information about customizing and the functionality of the Management Console you find in our [Knowledge Base](https://knowledge.8select.com). 

