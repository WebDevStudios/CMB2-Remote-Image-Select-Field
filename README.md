# CMB2 Remote Image Select #
**Version:** 0.1.0   
**Contributors:** [George Mamadashvili](https://profiles.wordpress.org/Mamaduka/), [Jay Wood](https://profiles.wordpress.org/phyrax/)   
**Author:** [WebDevStudios](http://webdevstudios.com)   
**Author URI:** http://webdevstudios.com/   
**Requires at least:** 3.6.0   
**Tested up to:**      4.2.2   
**Stable tag:**        0.1.0   
**License:**           GPLv2   
**License URI:**       http://www.gnu.org/licenses/gpl-2.0.html   

## Description ##

Allows users to enter a URL in a text field and select a single image for use in post meta.  Similar to Facebook's featured image selector.

**Note**: This field may not support repetition, repeatable fields/groups is untested

### Usage ###
```php
$cmb->add_field( array(
 	'name'    => __( 'Select an Image', 'textdomain' ),
 	'id'      => 'images',
 	'type'    => 'remote_image_select',
 ) );
 ```

## Installation ##

### Manual Installation ###

1. Upload the entire `/cmb2-remote-img-sel` directory to the `/wp-content/plugins/` directory.
2. Activate CMB2 Remote Image Select through the 'Plugins' menu in WordPress.

## Frequently Asked Questions ##

* None as of yet

## Changelog ##

### 0.1.0 ###
* First release
