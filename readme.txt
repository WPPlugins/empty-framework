=== empty framework ===
Contributors: emptyframework
Tags: responsive, page builder, starting point, breakpoint, viewport, responsive page builder, drag & drop, drag & drop editor, template builder, developer, visual editor, wysiwyg, extendable, grid, grid-based, translation-ready 
Requires at least: 4.4 
Tested up to: 4.7.3 
Stable tag: 2.5.8 
License: GPLv2 or later
License URI: http://www.gnu.org/licenses/old-licenses/gpl-2.0


A truly responsive page builder and CSS generator for WordPress developers. 

== Description ==
A truly responsive page builder and CSS generator for WordPress developers.

empty offers a page builder to create layouts for different breakpoints and devices. It therefore uses individual responsive settings and generates corresponding clean CSS files which will be included in the frontend of your Wordpress theme.

Install empty and transform your WordPress installation into a fully responsive page builder and developer environment.
Start by adding breakpoints for the devices you want to target (eg. smartphones, tablets, desktop computers, …) which will be available in the page builder interface. You can customize any page layout for each breakpoint right in your WordPress backend. Create elements with different widths and positions for different viewports by simple resizing and dragging them in place.
empty then takes care of delivering those responsive layouts to the frontend. It writes clean HTML code with corresponding CSS classes and includes dynamically created CSS files with all @media tags in place.

Don't worry about setting up responsive content elements blind and without visual feedback in code anymore — do it visually right in your WordPress editor.

= Keyfeatures =
* grid-based responsive page builder
* multiple layouts for multiple devices
* lightweight
* easily extendable
* deep Wordpress integration (wysiwyg editor, media uploader)
* detailed documentation

= Links =
* To learn all about adding breakpoints to your environment have a look at this tutorial:
http://www.empty-framework.com/tutorial/edit-breakpoints/
* Edit grid and typographic values:
http://www.empty-framework.com/tutorial/edit-grid/
http://www.empty-framework.com/tutorial/edit-typography/
* Learn using the page builder:
http://www.empty-framework.com/tutorial/the-page-builder/
* Any question about any empty function? See the documentation:
http://www.empty-framework.com/docs/

= Pro =
Go Pro and enjoy the benefits of extending all parts of empty such as the breakpoints (and therefore the ability to create layouts for more than three devices), CSS definitions and the basic page builder elements by your own. Additionally you will receive updates and new features earlier than the free basic version.

To purchase a license have a look at your options here:
http://www.empty-framework.com/pro/

= Website =
empty's project website including the documentation as well as news and support forums
http://www.empty-framework.com

= Support =
* Documentation: http://www.empty-framework.com/docs/
* Support Forums: http://www.empty-framework.com/forums/ 

== Installation ==
1. Download empty from the WordPress Plugin Directory or if you purchased a license for empty, from your account page. Afterwards you upload the unzipped empty folder to your plugins folder. 

1. Log into your WordPress installation and activate the plugin empty framework through the WordPress Plugins panel.

1. If you have purchased an empty license switch to Empty > Updates to enter your license and activate updates.

1. Open up Empty → Settings and activate the page builder for each post type you want to use it for. The page builder will replace the WordPress WYSIWYG editor in the edit screen of the selected post types.

1. Switch to Design → Breakpoints and enter your desired settings. To fully understand all the options have a look at this tutorial: http://www.empty-framework.com/tutorial/edit-breakpoints/

1. Switch to Design → Grid / Typography and setup your desired grid system and typographic styles. To fully understand all the options have a look at these tutorials: http://www.empty-framework.com/tutorial/edit-grid/ 
http://www.empty-framework.com/tutorial/edit-typography/

1. After you’ve setup everything according to your needs you are finished with the configuration of empty and can now start using the page builder by creating a new page or any new post type you activated the page builder for.

To learn more about the page builder itself watch this tutorial: http://www.empty-framework.com/tutorial/the-page-builder/ 

== Frequently Asked Questions ==
You didn't find what you were looking for? 
Try searching the support forums or create a topic there.
http://www.empty-framework.com/forums/ 

== Changelog ==
= 2.5.8 =
* New: Refined layout function 
* Bugfix: After deletion of one element the others are no longer editable 
* Little things: Language files updated; New description on settings page 
 
= 2.5.7 =
* New: Set default values after clean installation 
* New: Doubleclick into a settings field to copy the values from a lower breakpoint 
* Little Things: Update process changes; Unified file update messages; Code changes to prevent PHP notices 
 
= 2.5.6 =
* Hotfix: CSS files were not included 
* Bugfix: Unable to duplicate elements 
* Bugfix: Responsive classes for WYSIWYG editor were not set correctly 
 
= 2.5.5 =
* New: Notice of some values dependence on line-height value 
* New: Clean filenames of uploads to prevent umlauts, etc. 
* New: Unified version numbers 
* Bugfix: Responsive classes for WYSIWYG editor were not set correctly 
* Bugfix: Element filters were not displayed correctly 
* Little things: Language files updated 
 
= 2.5.4 =
* Little things: Output unification of JS variables 
 
= 2.5.3 =
* New: New functions to generate settings more efficient 
* Bugfix: Insert elements in right position 
* Bugfix: Select right elements in Media Uploader 
 
= 2.5.2 =
* New: Hooks & filters 
* New: hidden-classes 
* Bugfix: Right font sizes in WYSIWYG editor 
 
= 2.5.1 =
* New: Breadcrumbs 
* Bugfix: Include parser on frontend 
* Little things: Keep context menus alive 
 
= 2.5 =
* New: Better editor elements [pro] 
* New: Full width rows 
* New: Experimental rows without gutter 
* New: Automatically generate CSS files after update 
* New: Language files for version 2 
* Update: Updated sortable function in editor 
* Little things: Chrome & Firefox bugfixes, Update of helper functions, Notices, Reset elements 
 
= 2.4.1 =
* New: More stable editor 
 
= 2.4 =
* I N I T I A L Release after overhaul 
 
= 2.0 =
* Complete change and revamp of the internal PHP Core 
 
= 1.1.1 =
* New: Context menu for selected elements 
* New: Full width rows 
* Bugfix: media was removed on resize 
* Bugfix: Allow percentage values 
* Little things: new icon, new banners, new update infos [premium] 
 
= 1.1 =
* I N I T I A L Release 
 
= 1.0.5 =
* New: Backup changes while connection problems 
* New: Sample Theme included 
* New: Update of language files 
* Changes: Filesize reduction 
* Changes: Code formatted to match Wordpress standard 
 
= 1.0.4 =
* Readme Update 
* PHP: update of helper functions 
* Admin: error messages are now wordpress notices 
 
= 1.0.3 =
* BUGFIX: body tag is not extendable 
* BUGFIX: textareas unwanted spaces 
* BUGFIX: allow quotes in selector [premium] 
* BUGFIX: Selector of additional tags wasn’t saved [premium] 
* PHP: function use_etemplate [premium] 
* PHP: update of helper functions 
* Tags: setting for using tag in editor [premium] 
* Shortcode [php] [premium] 
* Language file update 
 
= 1.0.2 =
* minor design changes 
 
= 1.0.1 =
* BUGFIX: Tags: allow specific characters in selector name [premium] 
* BUGFIX: display self hosted videos 
* Updates: update system [premium] 
* Tags: editable 
* Tags: seperate margin & padding 
* Tags: minimizable & sortable 
 
