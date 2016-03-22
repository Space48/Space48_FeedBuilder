#Space48_FeedBuilder

##Summary
This module aims to be the foundation module for creating feeds in asn optimised and extensible way.

## IMPORTANT!! Development Priciples
* This module should contain code which is generic to all projects. Customisation should take place in a module which extends it.
* All configuration of feeds should be done in XML.
* Attribute Models (or any code to do with a feed) should **NEVER** load a full Magento model as this is detrimental to performance
e.g. Mage::getModel('catalog/product')->load(1) 
* A feed makes use of:
    * *Data Model (One per Feed)* - This wraps a Magento collection and is used to extract the base data from Magento.
    * *Attribute Model (Multiple per feed)* - These are used to extend the Data Model by adding attributes to the 
    collection, adding joins, calculating and formatting values etc.
    * *Filter Model (any filters required, can be none or many)* - These filter the collection e.g. to only show products
    for a specific store or product type.
    * *Writer Model (One per feed)* - This processes the data from the model and outputs appropriately. All logic 

##Adding this module to a project repository (using Modman)
* Add this as a submodule of the project and create the relevant symbolic links by doing the following in shell :
```sh
cd <root folder of web directory>;
modman init
modman clone git@github.com:Space48/Space48_FeedBuilder.git
```

##Creating a feed
* Create a module in the local code pool e.g. Acme_Feeds
* In the module's 'etc' directory create 'config.xml' using the below as a reference:
```xml
<config>
    <modules>
        <Acme_Feeds>
            <version>0.0.1</version>
        </Acme_Feeds>
    </modules>
    <global>
        <models>
            <acme_feeds>
                <class>Acme_Feeds_Model</class>
            </acme_feeds>
        </models>
    </global>
    <space48_feedbuilder> <!-- DO NOT CHANGE THIS -->
        <feeds>
            <google_base_feed> <!-- THIS IS A UNIQUE FEED REFERENCE -->
                <!-- THIS FEED IS THE TEMPLATE FOR OTHER FEEDS, SO IS DISABLED, SEE NEXT FEED -->
                <status>disabled</status> <!-- ANYTHING EXCEPT 'disabled' WILL ENABLE THE FEED -->
                <name>Google Feed Configuration</name>
                <!-- WHEN SHOULD THE FEED RUN? -->
                <schedule>
                    <cron_expr>0 1 * * *</cron_expr>
                </schedule>
                <!-- THE WRITER MODEL - USED FOR CREATING THE FEED OUTPUT -->
                <writer_model>
                    <class>Space48_FeedBuilder_Model_Writer_Csv</class>
                </writer_model>
                <!-- THE DATA MODEL - PROVIDES THE BASE MAGENTO COLLECTION -->
                <data_model>
                    <class>Space48_FeedBuilder_Model_Data_ProductExtensible</class>
                </data_model>
                <!-- THESE ARE THE FIELDS WHICH WILL APPEAR IN THE FEED. THE NODE NAME WILL BE THE FEED FIELD NAME, THE 
                VALUE CONTROLS ATTRIBUTE JOINING AND WHAT DATA IS OUTPUT. -->
                <fields>
                    <!-- A FIELD WHICH IS IN THE COLLECTION BY DEFAULT -->
                    <id>sku</id>
                    <!-- ADD AN ATTRIBUTE TO THE COLLECTION -->
                    <brand>
                        <class>Space48_FeedBuilder_Model_Data_Attribute_Additional</class>
                        <args>
                            <!-- THE ATTRIBUTE CODE TO BE ADDED -->
                            <attribute_code>manufacturer</attribute_code>
                            <!-- ADD THIS NODE TO GET THE VALUE FROM A SELECT LIST -->
                            <is_select>true</is_select>
                        </args>
                    </brand>
                    <!-- A CUSTOM DATA ATTRIBUTE TO ALLOW FOR SPECIFIC DATA LOGIC -->
                    <title>
                        <class>Acme_Feeds_Model_Data_Attribute_GoogleProductName</class>
                    </title>
                    <!-- THE PRODUCT 'small_image' URL -->
                    <image_link>
                        <class>Space48_FeedBuilder_Model_Data_Attribute_ProductImage</class>
                    </image_link>
                    <!-- PRODUCT FINAL PRICE i.e. PURCHASE PRICE -->
                    <price>
                        <class>Space48_FeedBuilder_Model_Data_Attribute_ProductFinalPrice</class>
                    </price>
                    <!-- STOCK AVAILABILTY (EXAMPLE OF A JOIN) -->
                    <availability>
                        <class>Space48_FeedBuilder_Model_Data_Attribute_ProductIsInStock</class>
                    </availability>
                    <!-- THE SAME VALUE APPLIES TO ALL ITEMS -->
                    <condition>
                        <class>Space48_FeedBuilder_Model_Data_Attribute_StaticValue</class>
                        <args>
                            <!-- THIS IS THE VALUE WHICH WILL BE IN THE FEED -->
                            <static_value>New</static_value>
                        </args>
                    </condition>
                </fields>
            </google_base_feed>
            <!-- A FEED FOR THE 'default' STORE -->
            <acme_foodstore_google_feed>
                <inherit>google_base_feed</inherit> <!-- INHERIT CONFIGURATION FROM THIS FEED -->
                <!-- ANYTHING ELSE IN THIS SECTION WILL OVERRIDE INHERITED CONFIGURATION -->
                <status>enabled</status> <!-- ENABLE THE FEED -->
                <file_name>s48_feedbuilder/googlefeed_acmefoods.csv</file_name>
                <filters>
                    <!-- FILTER THE DATA COLLECTION BY STORE -->
                    <store_name>
                        <class>Space48_FeedBuilder_Model_Data_Filter_Store</class>
                        <args>
                            <!-- USE DATA FROM THIS STORE VIEW -->
                            <store_code>default</store_code>
                        </args>
                    </store_name>
                </filters>
            </acme_foodstore_google_feed>
            <acme_hardwarestore_google_feed>
                <inherit>google_base_feed</inherit>
                <status>enabled</status>
                <file_name>s48_feedbuilder/googlefeed_acmehardware.csv</file_name>
                <filters>
                    <store_name>
                        <class>Space48_FeedBuilder_Model_Data_Filter_Store</class>
                        <args>
                            <store_code>acmehardware</store_code>
                        </args>
                    </store_name>
                </filters>
            </acme_hardwarestore_google_feed>
        </feeds>
    </space48_feedbuilder>
</config>
```
* To add custom data attributes e.g. Acme_Feeds_Model_Data_Attribute_GoogleProductName
**IMPORTANT** : Consider if the functionality already available can meet your needs. If not, can the required
functionality be abstracted and added to the community module?
    * Create the following folder structure in the local feeds module directory:
    Model/Data/Attribute
    * Create the custom Data Attribute as below (GoogleProductName.php):
    **WHAT GOES IN HERE ? : For examples look at the community data attributes or at the abstract class**
    
    
```php
<?php

/* 
 * This field in the feed will contain:
 * 'Google Feed Description' if it is populated
 * OR
 * fall back to the standard 'Short Description'.
 */
class Acme_Feeds_Model_Data_Attribute_GoogleProductDescription 
    extends Space48_FeedBuilder_Model_Data_Attribute_Abstract
{
    public function addCollectionAttribute(Varien_Data_Collection $collection)
    {
        return $collection->addAttributeToSelect(array('google_feed_description', 'short_description'));
    }

    public function getValue(Mage_Core_Model_Abstract $model)
    {
        return $model->getGoogleFeedDescription() ?
            $model->getGoogleFeedDescription() :
            $model->getShortDescription();
    }
}

```

##Running the feeds
The feeds will run in cron as per the defined schedule for each feed.
A shell script is availble to generate the feeds on demand if required :
```sh
cd <magento base directory>/app/code/community/Space48/FeedBuilder/shell;
php feedBuilder.php --all-feeds
```
