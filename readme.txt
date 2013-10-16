=== Gallery ===
Contributors: bestwebsoft
Donate link: https://www.2checkout.com/checkout/purchase?sid=1430388&quantity=10&product_id=13
Tags: gallery, image, gallery image, album, foto, fotoalbum, website gallery, multiple pictures, pictures, photo, photoalbum, photogallery
Requires at least: 3.0
Tested up to: 3.6.1
Stable tag: 4.0.2
License: GPLv2 or later
License URI: http://www.gnu.org/licenses/gpl-2.0.html

This plugin allows you to implement a gallery page into your website.

== Description ==

This plugin makes it possible to implement as many galleries as you want into your website. You can add multiple pictures and description for each gallery, show them all at one page, view each one separately. Moreover, you can upload HQ images. Regular updates and simplicity of usage along with efficient functionality make it a perfect choice for your site to have an appealing look. 
There is also a premium version of the plugin with more useful features available.

<a href="http://wordpress.org/extend/plugins/gallery-plugin/faq/" target="_blank">FAQ</a>
<a href="http://support.bestwebsoft.com" target="_blank">Support</a>

<a href="http://bestwebsoft.com/plugin/gallery-pro/?k=57ad5c0c7fe312e2a45ef9a76f47334c" target="_blank">Upgrade to Pro Version</a>

= Features =

* Actions: Create any amount of albums in the gallery.
* Description: Add description to each album.
* Actions: Set a featured image as an album cover.
* Actions: Upoad any number of photos to each album in the gallery.
* Actions: Add Single Gallery to your page or post using a shortcode.
* Actions: Attachment sorting settings in the admin panel.
* Caption: Add a caption and alt tag to each photo in the album.
* Display: Change the size of album cover thumbnails and photos in the album. 
* Display: Choose a number of pictures to display in one row in the gallery album.
* Slideshow: View pictures as a slide show and in a full size.
* Ability: The ability to add comments to a Single Gallery.

= Translation =

* Brazilian Portuguese (pt_BR) (thanks to DJIO, www.djio.com.br)
* Chinese (zh_CN) (thanks to <a href="mailto:mibcxb@gmail.com">Xiaobo Chen</a>)
* Czech (cs_CZ) (thanks to Josef Sukdol)
* Dutch (nl_NL) (thanks to <a href="mailto:ronald@hostingu.nl">HostingU, Ronald Verheul</a>)
* French (fr_FR) (thanks to Didier, <a href="mailto:lcapronnier@yahoo.com">L Capronnier</a>)
* Georgian (ka_GE) (thanks to Vako Patashuri)
* German (de_DE) (thanks to Thomas Bludau)
* Hebrew (he_IL) (thanks to Sagive SEO)
* Hungarian (hu_HU) (thanks to Mészöly Gábor) 
* Italian (it_IT) (thanks to Stefano Ferruggiara)
* Lituanian (lt_LT) (thanks to Naglis Jonaitis)
* Persian (fa_IR) (thanks to Einolah Kiamehr and Meisam)
* Polish (pl_PL) (thanks to Janusz Janczy, Bezcennyczas.pl)
* Russian (ru_RU)
* Serbian (sr_RS) (thanks to <a href="mailto:andrijanan@webhostinggeeks.com">Andrijana Nikolic</a> www.webhostinggeeks.com )
* Spanish (es) (thanks to Victor Garcia)
* Turkish (tr) (thanks to <a href="mailto:ce.demirbilek@gmail.com">Ismail Demirbilek</a>)
* Ukrainian (uk_UA)

If you would like to create your own language pack or update the existing one, you can send <a href="http://codex.wordpress.org/Translating_WordPress" target="_blank">the text of PO and MO files</a> for <a href="http://support.bestwebsoft.com" target="_blank">BestWebSoft</a> and we'll add it to the plugin. You can download the latest version of the program for work with PO and MO files  <a href="http://www.poedit.net/download.php" target="_blank">Poedit</a>.

= Technical support =

Dear users, our plugins are available for free download. If you have any questions or recommendations regarding the functionality of our plugins (existing options, new options, current issues), please feel free to contact us. Please note that we accept requests in English only. All messages in another languages won't be accepted.

If you notice any bugs in the plugins, you can notify us about it and we'll investigate and fix the issue then. Your request should contain URL of the website, issues description and WordPress admin panel credentials.
Moreover we can customize the plugin according to your requirements. It's a paid service (as a rule it costs $40, but the price can vary depending on the amount of the necessary changes and their complexity). Please note that we could also include this or that feature (developed for you) in the next release and share with the other users then. 
We can fix some things for free for the users who provide translation of our plugin into their native language (this should be a new translation of a certain plugin, you can check available translations on the official plugin page).

== Installation ==

1. Upload the `Gallery` folder to the directory `/wp-content/plugins/`.
2. Activate the plugin using the 'Plugins' menu in WordPress.
3. Please check if you have the template file `gallery-template.php` as well as the template `gallery-single-template.php` in the templates directory. If you can't find these files, then just copy them from the directory  `/wp-content/plugins/gallery/template/` to your templates directory.

== Frequently Asked Questions ==

= I cannot view my Gallery page =

1. First of all, you should create your first Gallery page and select 'Gallery' in the list of available templates (it will be used for displaying the Gallery).
2. If you cannot find 'Gallery' in the list of available templates, then just copy it from the directory `/wp-content/plugins/gallery-plugin/template/` to your templates directory.

= How to use the plugin? =

1. Click 'Add New' in the 'Galleries' menu and fill out your page.
2. Upload pictures via the uploader at the bottom of the page. 
3. Save the page.

= How to add an image? =

- Choose the necessary gallery in the list on the Galleries page in the admin section (or create a new gallery by clicking 'Add New' in the 'Galleries' menu). 
- Use the option 'Upload a file' in the uploader, choose the necessary pictures and click 'Open'
- The files uploading process will start.
- Once all pictures are uploaded, please save the page.
- If you see the message 'Please enable JavaScript to use the file uploader', you should enable JavaScript in your browser.

= How to add many images? =

Multiple files upload is supported by all modern browsers except Internet Explorer. 

= I'm getting the following error: "Fatal error: Call to undefined function get_post_thumbnail_id()". What should I do? =

This error means that your theme doesn't support thumbnail option, in order to add this option please find the file 'functions.php' in your theme and add the following strings to this file:

`add_action( 'after_setup_theme', 'theme_setup' );

function theme_setup() {
    add_theme_support( 'post-thumbnails' );
}`

After that your theme will support thumbnail option and the error will disappear.

= How to change image order on the single gallery page? =

1. Please open the menu "Galleries" and choose random gallery from the list. It will take you to the gallery editing page. 
Please use the drag and drop function to change the order of the images and do not forget to save the post.
Please do not forget to select `Sort images by` -> `sort images` in the plugin settings (http://your_domain/wp-admin/admin.php?page=gallery-plugin.php) 

2. Please go to the "Galleries" menu and select random gallery in the list. It will take you to the gallery editing page. 
There will be one or several media upload icons between the title and the content blocks. Please choose any icon. 
After that you'll see a popup window containing three or four tabs. 
Go to the Gallery tab and you will see attachments related to this gallery. 
You can change their order using the drag and drop option. 
Just set an order and click the 'Save' button.

= I am using WP with rtl language and I have a problem with the lightbox displaying on iPad/iPhone. =

1. In the file header.php you should substitute
<html <?php language_attributes(); ?>>
with
<html>
2. Remove the "direction:rtl" from the css of the body, and move it to the main wrapper. For example, in your theme CSS file (usually it's rtl.css) remove the following lines:
body {
    direction:rtl;
}
and add (for the themes Twenty Eleven or Twenty Ten):
.hfeed {
    direction:rtl;
}

== Screenshots ==

1. Gallery Admin page.
2. Gallery albums page in the front-end.
3. Gallery Options page in the admin panel.
4. Single Gallery page.
5. PrettyPhoto pop-up window containing the album images.

== Changelog ==

= V4.0.2 - 11.10.2013 =
* NEW : Added Alt tag field for each image in the gallery.

= V4.0.1 - 02.10.2013 =
* Update : The Brazilian Portuguese language file is updated.

= V4.0.0 - 24.09.2013 =
* Update : The Ukrainian language file is updated. 

= V3.9.9 - 13.09.2013 =
* Update : The French language file is updated. 
* Update : We updated all functionality for wordpress 3.6.1.

= V3.9.8 - 04.09.2013 =
* Update : Function for displaying BWS plugins section placed in a separate file and has own language files.

= V3.9.7 - 26.08.2013 =
* Update : The French language file is updated. 
* Bugfix : We added replacing spaces in the file name when uploading.

= V3.9.6 - 13.08.2013 =
* Update : The Serbian language file is updated.

= V3.9.5 - 07.08.2013 =
* Update : We updated all functionality for wordpress 3.6.
* Bugfix : We fixed the bug of displaying images on the gallery edit page.
* Update : The Turkish language file is updated.

= V3.9.4 - 24.07.2013 =
* NEW : Added an ability to display comments on the gallery template pages.
* Update : The Brazilian Portuguese language file is updated.
* NEW : The Turkish language file is added to the plugin.

= V3.9.3 - 18.07.2013 =
* NEW : Added an ability to view and send system information by mail.

= V3.9.2 - 11.07.2013 =
* Update : The Chinese language file is updated.
* Update : We updated all functionality for wordpress 3.5.2.

= V3.9.1 - 02.07.2013 =
* Update : The French language file is updated. 

= V3.9 - 28.05.2013 =
* Update : BWS plugins section is updated. 
* Update : The French language file is updated. 
* Bugfix : We changed using the abspath to plugin_dir_path(). 

= V3.8.9 - 16.05.2013 =
* Bugfix : We fixed the bug of SQL queries.

= V3.8.8 - 22.04.2013 =
* NEW : Added html blocks.
* Update : The French language file is updated.

= V3.8.7 - 10.04.2013 =
* Update : The English language file is updated in the plugin.
* Bugfix : We fixed the bug of deleting images.

= V3.8.6 - 26.02.2013 =
* NEW : The Chinese language file is added to the plugin.

= V3.8.5 - 14.02.2013 =
* Update : We updated th fancybox displaying for iPhone and iPad.
* Update : We updated all functionality for wordpress 3.5.1.

= V3.8.4 - 25.01.2013 =
* Update : The French language file is updated.

= V3.8.3 - 04.01.2013 =
* Bugfix : We fixed the bug of image order on the Signle Gallery page.

= V3.8.2 - 03.01.2013 =
* Bugfix : The bug with drag'n drop and left admin's panel menu animations when hovered was fixed.

= V3.8.1 - 21.12.2012 =
* Update : We deleted all p,a,c,k,e,r code.

= V3.8 - 20.12.2012 =
* NEW : Serbian and Persian language files is added to the plugin.
* NEW : Added setting for Border for image on gallery page - display, width, color.
* NEW : Added setting for URL for Return link - Gallery Template page or Custom page.
* Update : We updated all functionality for wordpress 3.5.

= V3.7 - 23.10.2012 =
* NEW : Added link url field - clicking on image open the link in new window.

= V3.6 - 03.10.2012 =
* NEW : Added function to display 'Download High resolution image' link in lightbox on gallery page
* NEW : Added setting for 'Download High resolution image' link

= V3.5 - 27.07.2012 =
* NEW : Lituanian language file is added to the plugin.
* NEW : Added drag and drop function to change the order of the output of images
* NEW : Added a shortcode for displaying short gallery type (like [print_gllr id=211 display=short])

= V3.4 - 24.07.2012 =
* Bugfix : Cross Site Request Forgery bug was fixed. 

= V3.3 - 12.07.2012 =
* NEW : Brazilian Portuguese and Hebrew language files are added to the plugin.
* Update : We updated Italian language file.
* Update : We updated all functionality for wordpress 3.4.1.

= V3.2 - 27.06.2012 =
* Update : We updated all functionality for wordpress 3.4.

= V3.1.2 - 15.06.2012 =
* Bugfix : The bug with gallery uploader (undefined x undefined) was fixed.

= V3.1.1 - 13.06.2012 =
* Bugfix : The bug with gallery uploader was fixed.

= V3.1 - 11.06.2012 =
* New : Metabox with shortcode has been added on Edit Gallery Page to add it on your page or post.
* Bugfix : The bug with gallery shortcode was fixed.

= V3.06 - 01.06.2012 =
* Bugfix : The bug with gallery appears above text content was fixed.

= V3.05 - 25.05.2012 =
* NEW : Added shortcode for display Single Gallery on your page or post.
* NEW : Added attachment order.
* NEW : Added 'Return to all albums' link for Single Gallery page.
* NEW : Spanish language file are added to the plugin.

= V3.04 - 27.04.2012 =
* NEW : Added slideshow for lightbox on single gallery page.

= V3.03 - 19.04.2012 =
* Bugfix : The bug related with the upload of the photos on the multisite network was fixed.

= V3.02 - 12.04.2012 =
* Bugfix : The bug related with the display of the photo on the single page of the gallery was fixed.

= V3.01 - 12.04.2012 =
* NEW : Czech, Hungarian and German language files are added to the plugin.
* NEW : Possibility to set featured image as cover of the album.
* Change: Replace prettyPhoto library to fancybox library.
* Change: Code that is used to display a lightbox for images in `gallery-single-template.php` template file is changed.

= V2.12 - 27.03.2012 =
* NEW : Italian language files are added to the plugin.

= V2.11 - 26.03.2012 =
* Bugfix : The bug related with the indication of the menu item on the single page of the gallery was fixed.

= V2.10 - 20.03.2012 =
* NEW : Polish language files are added to the plugin.

= V2.09 - 12.03.2012 =
* Changed : BWS plugins section. 

= V2.08 - 24.02.2012 =
* Change : Code that is used to connect styles and scripts is added to the plugin for correct SSL verification.
* Bugfix : The bug with style for image block on admin page was fixed.

= V2.07 - 17.02.2012 =
* NEW : Ukrainian language files are added to the plugin.
* Bugfix : Problem with copying files gallery-single-template.php to theme was fixed.

= V2.06 - 14.02.2012 =
* NEW : Dutch language files are added to the plugin.

= V2.05 - 18.01.2012 =
* NEW : A link to the plugin's settings page is added.
* Change : Revised Georgian language files are added to the plugin.

= V2.04 - 13.01.2012 =
* NEW : French language files are added to the plugin.

= V2.03 - 12.01.2012 =
* Bugfix : Position to display images on a Gallery single page was fixed.

= V2.02 - 11.01.2012 =
* NEW : Georgian language files are added to the plugin.

= V2.01 - 03.01.2012 =
* NEW : Adding of the caption to each photo in the album.
* NEW : A possibility to select the dimensions of the thumbnails for the cover of the album and for photos in album is added.
* NEW : A possibility to select a number of the photos for a separate page of the album in the gallery which will be placed in one line is added.
* Change : PrettyPhoto library was updated up to version 3.1.3.
* Bugfix : Button 'Sluiten' is replaced with a 'Close' button.

= V1.02 - 13.10.2011 =
* noConflict for jQuery is added.  

= V1.01 - 23.09.2011 =
*The file uploader is added to the Galleries page in admin section. 

== Upgrade Notice ==

= V4.0.2 =
Added Alt tag field for each image in the gallery.

= V4.0.1 =
The Brazilian Portuguese language file is updated.

= V4.0.0 =
The Ukrainian language file is updated. 

= V3.9.9 =
The French language file is updated.  We updated all functionality for wordpress 3.6.1.

= V3.9.8 =
Function for displaying BWS plugins section placed in a separate file and has own language files.

= V3.9.7 =
The French language file is updated. We added replacing spaces in the file name when uploading.

= V3.9.6 =
The Serbian language file is updated.

= V3.9.5 =
We updated all functionality for wordpress 3.6. We fixed the bug of displaying images on the gallery edit page. The Turkish language file is updated.

= V3.9.4 =
Added an ability to display comments on the gallery template pages. The Brazilian Portuguese language file is updated. The Turkish language file is added to the plugin.

= V3.9.3 =
Added an ability to view and send system information by mail.

= V3.9.2 = 
The Chinese language file is updated. We updated all functionality for wordpress 3.5.2.

= V3.9.1 =
The French language file is updated.

= V3.9 =
BWS plugins section is updated. The French language file is updated. We changed using the abspath to plugin_dir_path().

= V3.8.9 =
We fixed the bug of SQL queries.

= V3.8.8 =
Added html blocks. The French language file is updated.

= V3.8.7 =
The English language file is updated in the plugin. We fixed the bug of deleting images.

= V3.8.6 =
Chinese language file is added to the plugin.

= V3.8.5 =
We updated displaying fancybox for iPhone and iPad. We updated all functionality for wordpress 3.5.1.

= V3.8.4 =
The French language file is updated.

= V3.8.3 =
The bug with the ability to order images in a single gallery page was fixed.

= V3.8.2 =
The bug with drag'n drop and left admin's panel menu animations when hovered was fixed.

= V3.8.1 =
We deleted all p,a,c,k,e,r code.

= V3.8 =
Slovak and Persian language files is added to the plugin. Added setting for Border for image on gallery page - display, width, color. Added setting for URL for Return link - Gallery Template page or Custom page. We updated all functionality for wordpress 3.5.

= V3.7 =
Added link url field - clicking on image open the link in new window.

= V3.6 =
Added function to display 'Download High resolution image' link in lightbox on gallery page. Added setting for 'Download High resolution image' link.

= V3.5 =
Lituanian language file is added to the plugin. Added drag and drop function to change the order of the output of images. Added a shortcode for displaying short gallery type (like [print_gllr id=211 display=short])

= V3.4 =
Cross Site Request Forgery bug was fixed. 

= V3.3 =
Brazilian Portuguese and Hebrew language files are added to the plugin. We updated Italian language file. We updated all functionality for wordpress 3.4.1.

= V3.2 =
We updated all functionality for wordpress 3.4.

= V3.1.2 =
The bug with gallery uploader (undefined x undefined) was fixed.

= V3.1.1 =
The bug with gallery uploader was fixed.

= V3.1 =
Metabox with shortcode has been added on Edit Gallery Page to add it on your page or post. The bug with gallery shortcode was fixed.

= V3.06 =
The bug with gallery appears above text content was fixed.

= V3.05 =
Added shortcode for display Single Gallery on your page or post. Added attachment order. Added 'Return to all albums' link for Single Gallery page. Spanish language file are added to the plugin.

= V3.04 =
Added slideshow for lightbox on single gallery page.

= V3.03 =
The bug related with the upload of the photos on the multisite network was fixed.

= V3.02 =
The bug related with the display of the photo on the single page of the gallery was fixed.

= V3.01 =
Czech, Hungarian and German language files are added to the plugin. Possibility to set featured image as cover of the album is added. Replace prettyPhoto library to fancybox library. Code that is used to display a lightbox for images in `gallery-single-template.php` template file is changed.

= V2.12 =
Italian language files are added to the plugin.

= V2.11 =
The bug related with the indication of the menu item on the single page of the gallery was fixed.

= V2.10 =
Polish language files are added to the plugin.

= V2.09 - 07.03.2012 =
BWS plugins section has been changed. 

= V2.08 =
Code that is used to connect styles and scripts is added to the plugin for correct SSL verification. The bug with a style for an image block on admin page was fixed.

= V2.07 =
Ukrainian language files are added to the plugin. Problem with copying files gallery-single-template.php to the theme was fixed.

= V2.06 =
Dutch language files are added to the plugin.

= V2.05 =
A link to the plugin's settings page is added. Revised Georgian language files are added to the plugin.

= V2.04 =
French language files are added to the plugin.

= V2.03 =
Position to display images on a single page of the Gallery was fixed. Please upgrade the Gallery plugin. Thank you.

= V2.02 =
Georgian language files are added to the plugin.

= V2.01 =
A possibility to add a caption to each photo of the album is added. A possibility to select dimensions of the thumbnails for the cover of the album and for photos in album is added. A possibility to select a number of the photos for a separate page of the album in the gallery which will be placed in one line is added. PrettyPhoto library was updated. Button 'Sluiten' is replaced with a 'Close' button. Please upgrade the Gallery plugin immediately. Thank you.

= V1.02 =
noConflict for jQuery is added.

= V1.01 =
The file uploader is added to the Galleries page in admin section.
