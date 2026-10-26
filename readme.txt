=== Pasporto - Digital Product Passport (DPP) for WooCommerce ===
Contributors: motylanogha
Tags: woocommerce, digital product passport, dpp, espr, circularity
Requires at least: 6.5
Tested up to: 7.1
Requires PHP: 8.1
Stable tag: 1.0.0
License: GPLv2 or later
License URI: https://www.gnu.org/licenses/gpl-2.0.html

Statutory Digital Product Passport (DPP) display, material composition, circularity, carbon footprint (GWP), and JSON-LD schema markup under Ecodesign Regulation (EU) 2024/1781 (ESPR).

== Description ==

Under the Ecodesign for Sustainable Products Regulation (Regulation (EU) 2024/1781 - ESPR) and the EU Batteries Regulation (Regulation (EU) 2023/1542 Article 77), manufacturers and retailers selling products into the European Union must provide consumers with access to a Digital Product Passport (DPP) prior to purchase. Under Articles 14 and 29 of Regulation (EU) 2024/1781, online marketplaces and ecommerce webshops are legally required to make the passport data carrier (link, identifier, or QR code) and essential sustainability attributes visibly available on product pages.

Covered priority product categories rolling out across 2026-2027 include industrial and EV batteries, textiles, garments, footwear, electronics, furniture, tires, and detergents.

Pasporto equips WooCommerce stores with an automated, lightweight, zero-dependency implementation of the EU Digital Product Passport framework:

* **Statutory DPP Display**: Renders an interactive Digital Product Passport badge on product pages, either under the single product summary or in a dedicated "Digital Product Passport" tab.
* **Essential Compliance Attributes**: Manage Unique Product Identifiers (UPI / GTIN / GS1 Digital Link), primary material composition, certified recycled content percentage, life-cycle carbon footprint (GWP in kg CO2e), circularity/recyclability class (A to E), and Declaration of Conformity document links.
* **Data Carrier Mobile Link**: Provides a direct link and visual scan prompt for shoppers to access the official digital product passport on their smartphones.
* **JSON-LD Schema Markup**: Automatically injects structured Schema.org `Product` metadata with `additionalProperty` specifications for EU market surveillance and search engines.
* **Variation Parent Inheritance**: Product variations automatically inherit parent passport attributes unless specific variation data is defined.
* **Zero External Dependencies**: All calculations and HTML generation occur locally on your server. Zero tracking, zero third-party cloud subscriptions, and zero external script calls.
* **WooCommerce Standards**: Built natively for WooCommerce with full support for High-Performance Order Storage (HPOS) and Cart/Checkout Blocks.

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
2. Install Pasporto and activate it.
3. Open WooCommerce > Product Passport (DPP) to configure display location.
4. Edit products to enter their Unique Product ID, primary material, recycled content percentage, carbon footprint, circularity class, or passport URL.

== Frequently Asked Questions ==

= What is a Digital Product Passport (DPP)? =
A Digital Product Passport is a mandatory digital identity under Regulation (EU) 2024/1781 that provides information about a product's sustainability, environmental footprint, recycled content, and circularity across its life cycle.

= Who is required to provide a DPP? =
Manufacturers, importers, and online retailers offering covered goods (such as batteries under Regulation (EU) 2023/1542 and textiles/apparel under delegated acts of Regulation (EU) 2024/1781) to consumers in the European Union.

= Does Pasporto require an external monthly subscription? =
No. Pasporto operates entirely on your WordPress server. You can link to your own passport documents or enter statutory data attributes directly in the product editor.

= Does Pasporto work with product variations? =
Yes. Product variations inherit the parent product's passport data by default, saving time when managing multiple sizes or colors.

== Changelog ==

= 1.0.0 =
* Initial release: Statutory EU Digital Product Passport (DPP) display, material composition, circularity, carbon footprint, and JSON-LD schema markup.
