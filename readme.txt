=== Pasporto - Digital Product Passport (DPP) ===
Contributors: motylanogha
Tags: woocommerce, digital product passport, dpp, espr, circularity
Requires at least: 6.5
Tested up to: 7.1
Requires PHP: 8.1
Stable tag: 1.1.0
License: GPLv2 or later
License URI: https://www.gnu.org/licenses/gpl-2.0.html

Show a Digital Product Passport (DPP) link, product identifier and sustainability data on WooCommerce product pages.

== Description ==

Pasporto adds a Digital Product Passport (DPP) section to WooCommerce product pages: a link to the product's passport, its unique product identifier and the sustainability data you enter for each product.

Why a shop may need this: under Regulation (EU) 2024/1781 (ESPR), a product needs a digital product passport where a delegated act for its product group requires one (Article 9(1)). Dealers, which includes shops selling at a distance (Article 2, point 55), shall ensure that the passport is easily accessible for customers and potential customers, as set out in Article 9(2), point (e), and specified in the applicable delegated acts (Article 31(2)). The economic operator placing the product on the market provides dealers with a digital copy of the data carrier or the unique product identifier (Article 10(3), point (a)), or a webpage link on request (Article 10(3), point (b)). Which data a passport holds is also set in the delegated acts (Article 9(2), point (a)).

For batteries, Regulation (EU) 2023/1542 requires a battery passport from 18 February 2027 for LMT batteries, industrial batteries with a capacity above 2 kWh and electric vehicle batteries (Article 77(1)).

Pasporto does not create a passport, and it does not check your data against any delegated act. It shows the link and data you enter.

* **Passport section on the product page**: shown either under the product summary or in its own "Digital Product Passport (DPP)" tab.
* **Product fields**: unique product identifier (GTIN, GS1 Digital Link or other), passport URL, primary material, recycled content percentage, carbon footprint (kg CO2e), a manufacturer-declared circularity class and a link to the EU declaration of conformity.
* **Passport link**: a button that opens the product's passport in a new tab.
* **Structured data**: the same data is added as `additionalProperty` entries to the Product markup WooCommerce already prints, so search engines read one Product, not two.
* **No external calls**: everything runs on your server. No tracking, no third-party service, no external scripts.
* **WooCommerce compatibility**: declares compatibility with High-Performance Order Storage (HPOS) and the Cart and Checkout blocks. Pasporto does not touch orders or checkout.

= Documentation and links =

* **Documentation** - [plogins.com/pasporto/docs/](https://plogins.com/pasporto/docs/)
* **Plugin page** - [plogins.com/pasporto/](https://plogins.com/pasporto/)
* **Source code** - [github.com/wppoland/pasporto](https://github.com/wppoland/pasporto)
* **Bug reports and feature requests** - [GitHub issues](https://github.com/wppoland/pasporto/issues)

Reporting a security issue: email hello@wppoland.com under our coordinated disclosure policy. We confirm within two business days, assess within five, and patch a critical issue within seven days of confirming it.

== Translations ==

Pasporto is fully translatable and ships the `pasporto.pot` template. Translations are delivered by WordPress.org language packs from translate.wordpress.org.

== Installation ==

1. Ensure WooCommerce 8.0 or higher is installed and active.
2. Install Pasporto and activate it. The passport section is switched on and shown in its own product tab straight away.
3. Optional: open WooCommerce > Product Passport (DPP) to move it under the product summary or switch it off.
4. Edit a product and fill in the Digital Product Passport fields in the General tab of the product data box.

== Frequently Asked Questions ==

= What is a Digital Product Passport (DPP)? =
Regulation (EU) 2024/1781 defines it as a set of data specific to a product that includes the information specified in the applicable delegated act and that is accessible via electronic means through a data carrier (Article 2, point 28). A product needs one where a delegated act for its product group requires it (Article 9(1)).

= Who has to provide the passport? =
Under Regulation (EU) 2024/1781, manufacturers (Article 27(1), point (c)) and importers (Article 29(2), point (c)) ensure that a passport is available. Dealers, including online shops, ensure that customers can easily access it (Article 31(2)). For batteries, the economic operator placing the battery on the market ensures that the passport information is accurate, complete and up to date (Regulation (EU) 2023/1542, Article 77(4)).

= Does Pasporto show a QR code? =
No. It shows a link to the passport and the identifier you enter. A data carrier is a linear barcode symbol, a two-dimensional symbol or another medium read by a device (Regulation (EU) 2024/1781, Article 2, point 29), and it belongs on the product, its packaging or accompanying documentation as the delegated act specifies (Article 10(1), point (b)).

= Does Pasporto work with product variations? =
Passport data is entered once per product and shown for all of its variations. There are no per-variation fields.

= Where are the product fields? =
In the General tab of the classic product editor. The new block-based product editor is not supported yet.

= What happens when I duplicate a product? =
The copy keeps the material and sustainability data, but its unique product identifier and passport URL are cleared, because they belong to the original product.

= Does Pasporto require an external monthly subscription? =
No. Pasporto runs entirely on your WordPress server. You link to your own passport and enter the data directly in the product editor.

== Changelog ==

= 1.1.0 =
* Fixed: on a fresh install the passport section is shown without saving the settings first.
* Fixed: passport data is added to WooCommerce's own Product structured data instead of a second Product without a name or offer.
* Fixed: duplicating a product no longer copies its unique product identifier and passport URL.
* Fixed: a product with only a declaration of conformity link now shows its passport section.
* Fixed: product fields stay editable while the storefront display is switched off.
* Fixed: the settings screen no longer fails on a malformed saved value.
* Changed: the declaration of conformity link no longer claims to be a PDF, and the passport button no longer suggests scanning.
* Changed: the circularity class is shown as manufacturer-declared, without a regulation badge.
* Changed: legal statements in the readme now cite the articles of Regulation (EU) 2024/1781 and 2023/1542 they come from.

= 1.0.1 =
* Fixed: deleting the plugin on a multisite network now removes its settings from every site, not only the main one.

= 1.0.0 =
* Initial release: Digital Product Passport (DPP) display, material composition, circularity, carbon footprint, and JSON-LD schema markup.
