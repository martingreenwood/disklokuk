<?php
/**
 * @package Make Child
 *
 * Add your custom functions here.
 */

// Options page
require_once( get_stylesheet_directory() . '/incs/options.php' );

// Distributors
//require_once( get_stylesheet_directory() . '/incs/distributors.php' );

add_filter( 'woocommerce_order_number', 'webendev_woocommerce_order_number', 1, 2 );
/**
 * Add Prefix to WooCommerce Order Number
 * 
 */
function webendev_woocommerce_order_number( $oldnumber, $order ) {
	return 'DO' . $order->id;
}


/**
  * ADD FB SCIPT TO HEAD
  */
function disklok_fb_script() {
    ?>
<!-- Facebook Conversion Code for Checkouts - Disklok 1 -->
<script>(function() {
var _fbq = window._fbq || (window._fbq = []);
if (!_fbq.loaded) {
var fbds = document.createElement('script');
fbds.async = true;
fbds.src = '//connect.facebook.net/en_US/fbds.js';
var s = document.getElementsByTagName('script')[0];
s.parentNode.insertBefore(fbds, s);
_fbq.loaded = true;
}
})();
window._fbq = window._fbq || [];
window._fbq.push(['track', '6027925808244', {'value':'0.00','currency':'GBP'}]);
</script>
<noscript><img height="1" width="1" alt="" style="display:none" src="https://www.facebook.com/tr?ev=6027925808244&amp;cd[value]=0.00&amp;cd[currency]=GBP&amp;noscript=1" /></noscript>
<?php
}
add_action('wp_head', 'disklok_fb_script', 50);




// Hook in remove comany field
//add_filter( 'woocommerce_checkout_fields' , 'custom_override_checkout_fields' );

// Our hooked in function - $fields is passed via the filter!
function custom_override_checkout_fields( $fields ) {
	unset($fields['billing']['billing_company']);
	unset($fields['shipping']['shipping_company']); 
	return $fields;
}

