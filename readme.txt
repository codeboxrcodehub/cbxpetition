=== CBX Petition ===
Contributors: codeboxr, manchumahara
Tags: petition,activism,signature,change,campaign
Requires at least: 5.3
Tested up to: 6.7.2
Stable tag: 2.0.0
License: GPLv2 or later
License URI: http://www.gnu.org/licenses/gpl-2.0.html

A plugin to create, manage petition and collect signatures for petition

== Description ==

A plugin to create, manage petition and collect signatures for petition. Plugin is created with extensive settings and hooks/filters as necessary.
Frontend and backend views are loaded as templates which can be override from theme.


### CBX Petition by [Codeboxr](https://codeboxr.com/product/cbx-petition-for-wordpress/)

>📋 [Documentation](https://codeboxr.com/doc/cbxpetition-doc/) | 🌟 [Upgrade to PRO](https://codeboxr.com/product/cbx-petition-for-wordpress/) |  👨‍💻 [Free Support](https://wordpress.org/support/plugin/cbxpetition/) | 🤴 [Pro Support](https://codeboxr.com/contact-us) | 📱 [Contact](https://codeboxr.com/contact-us/)


**If you think any necessary feature is missing contact with us, we will add in new release. Best way to check the feature is install the free core version in any dev site and explore**

= Core Plugin Features =

**Petition Backend/Petition Properties**

-  Create Petition from admin panel
-  Custom category adn tag taxonomy with petition
-  Petition Title and Description using wordpress core
-  *Petition Meta fields*
-- Signature Target (Required)
-- Expire Date
-- Petition Photos (Drag and drop photo upload to custom uploads dir, no wordpress media manager used)
-- Petition Banner (Drag and drop photo/banner upload to custom uploads dir, no wordpress media manager used)
-- Youtube Video url, title, mini description
-- Petition Letter/Letter Text Field
-- Petition Recipients (Name, Designation, Email)

**Petition Frontend**

-  Petition Title, Description Using WordPress Theme Core feature
-  *Extra information using Hooks(Configurable from Settings):*
-- Petition Video, Video Title, Video Text
-- Petition Photos
-- Petition Banner
-- Letter Text
-- Letter Recipients
-- Petition Sign Form
-- Petition Listing
-- Petition Statistics(Total Target, Sign Collected, Ratio/Bars)
-- Most of these features are available via shortcodes


**Petition Display Shortcodes**

1. Petition signature form [cbxpetition_signform]
2. Petition video [cbxpetition_video]
3. Petition photos [cbxpetition_photos]
4. Petition letter [cbxpetition_letter]
5. Petition signature listing [cbxpetition_signatures]
6. Petition banner [cbxpetition_banner]
7. Petition statistics [cbxpetition_stat]
8. Petition Details [cbxpetition]  (New in V1.0.1) to display full petition inside any page or post
9. Petition Summary [cbxpetition_summary]  (New in V1.0.1) to display petition summary inside any page or post
10. Any shortcode should have but missing? let us know

**Classic Widgets**

1. Petition Summary Widget [New in V1.0.2]
1. Petition Sign Form Widget [New in V1.0.2]

**Elementor Widgets**

Elementor widget addeds in V1.0.4

1. Petition Details widget compatible with shortcode [cbxpetition]
9. Petition Summary widget compatible with shortcode [cbxpetition_summary]
2. Petition signature form widget compatible with shortcode [cbxpetition_signform]
3. Petition video widget compatible with shortcode [cbxpetition_video]
4. Petition photos widget compatible with shortcode  [cbxpetition_photos]
5. Petition letter widget compatible with shortcode [cbxpetition_letter]
6. Petition signature listing widget compatible with shortcode [cbxpetition_signatures]
7. Petition banner widget compatible with shortcode [cbxpetition_banner]
8. Petition statistics widget compatible with shortcode [cbxpetition_stat]



**Petition Backend Settings**

-  **Basic Setting**
-  Enable Auto Integration
-  Auto Integration Before Content
-  Auto Integration After Content
-  Default Sign Status(Unverified, Pending, Approved, Unapproved), possible to extend
-  Guest Email Activation(Guest signature approval can be verified via email)
-  Frontend Signature listing limit
-  *Petition Photo(s) Configuration:*
-- Petition Photo Limit
-- Petition Photo Max File Size(MB)
-- Petition Photo Extensions
-- Petition Photo Thumbnail max width
-- Petition Photo Thumbnail max height
-- Petition Photo(s) max width
-- Petition Photo(s) max height
-  *Petition Banner Configuration:*
-- Petition Banner Max File Size(MB)
-- Petition Banner Extensions
-- Petition Banner max width
-- Petition Banner max height
-  **Global Email Template**
-  Header Image
-  Footer Text
-  Base colors and other email template colors
-  **Admin Email Alert**
-  New Sign Admin Email Alert
-  Email enable/disable
-  Email Subject, Template Heading, Template, Template Syntax for dynamic parsing
-  **User Email Alert**
-  New Sign User Email Alert
-  Sign Approve Email Alert
-  Email enable/disable
-  Email Subject, Template Heading, Template, Template Syntax for dynamic parsing
-  **Tools**
-  On Uninstall delete plugin data

**Petition Signature**

-  Backend Signature listing
-  Edit Signature, approve/unapprove signature
-  Delete Signature
-  Search Signature
-  User or guest both can sign
-  Guest signature needs First Name, Last Name and Email
-  Signature submit needs privacy confirmation in frontend

Our Core plugin is free and will always be free. To extends the petition features we have Pro addon called **CBX Petition Pro Addon**
Using the pro addon we have added some cool features like frontend petition submit and user dashboard with some more controls everywhere.


= CBX Petition Pro Addon Features =

**Pro Addon Backend Setting**

-  Who Can Create Petition - Role Selection(s)
-  Who Can Publish Petition - Role Selection(s)
-  Who Can Delete Own petition - Role Selection(s)
-  Maximum Petition Limit
-  Petition Per Page
-  User Front Dashboard Page - Page select dropdown
-  Admin Email Alert for Petition Approval
-- Enable/Disable and Email Template
-  User Email Alert for Petition Approval
-- Enable/Disable and Email Template

**Pro Addon Frontend and Other Features**

-  Frontend Dashboard shorcode [cbxpetition_dashboard]
-  Frontend Petition listing
-  Frontend Petition Create with same backend features(Title, Description, Photos, Banner, Video, Letter, Recipients)
-  Frontend Petition Delete
-  Frontend Petition Edit
-  Frontend Per Petition Signature Listing
-  Role based control and access
-  Template/Theme override features like core for pro addon

Get the [pro addon](https://codeboxr.com/product/cbx-petition-for-wordpress/)

== Installation ==

1. [WordPress has clear documentation about how to install a plugin].(https://codex.wordpress.org/Managing_Plugins)
2. After install activate the plugin "CBX Petition" through the 'Plugins' menu in WordPress
3. You'll now see a menu called "CBX Petition" in left menu, start from there, check the setting and documentation
4. Use shortcode or widget or other custom features as you need.
5. Try our pro and free addons for more extra features



== Screenshots ==


== Changelog ==
= 2.0.0 =
* [new] Totally refreshed dashboard
* [new] New email notification system
* [fixed] Classic widget title fix
* [new] Elementor widgets added(compatible with all shortcodes)
* [new] WPBakery widgets added(compatible with all shortcodes)
* [new] All styles are generated using scss and minified to minimize load time and performance.
* [fixed] Backend signature listing works for search page pagination, pagination fixed
* [new] WordPress core V6.7.2 compatible
* [new] Pro addon V2.0.0 released