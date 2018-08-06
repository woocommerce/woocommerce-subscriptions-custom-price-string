<?php
/*
 * Plugin Name: WooCommerce Subscriptions - Custom Price String
 * Plugin URI: https://github.com/Prospress/woocommerce-subscriptions-custom-pricestring.git
 * Description: A small add-on plugin to customize the product price strings
 * Author: Prospress Inc.
 * Author URI: https://prospress.com/
 * License: GPLv3
 * Version: 1.0.1
 * WC requires at least: 3.0.0
 * WC tested up to: 3.4.0
 *
 * GitHub Plugin URI: Prospress/woocommerce-subscriptions-custom-pricestring
 * GitHub Branch: master
 *
 * Copyright 2018 Prospress, Inc.  (email : freedoms@prospress.com)
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program.  If not, see <http://www.gnu.org/licenses/>.
 *
 * @package		WooCommerce Subscriptions - Custom Price String
 * @author		Prospress Inc.
 * @since		1.0
 */
 
 /*
TODO:
- Should the custom price string for simple/variable products (not subscriptions) be displayed on the cart page too?
- Check the scope of the custom strings (product page, cart, checkout, emails, backend, orders, renewals, etc)
*/

// add_filter("woocommerce_subscription_price_string", "wcs_custom_price_strings_cart", 10, 2);


require_once( 'includes/class-pp-dependencies.php' );

if ( false === PP_Dependencies::is_woocommerce_active( '3.0' ) ) {
	PP_Dependencies::enqueue_admin_notice( 'WooCommerce Subscriptions - Custom price string', 'WooCommerce', '3.0' );
	return;
}

if ( false === PP_Dependencies::is_subscriptions_active( '2.1' ) ) {
	PP_Dependencies::enqueue_admin_notice( 'WooCommerce Subscriptions - Custom price string', 'WooCommerce Subscriptions', '2.1' );
	return;
}

// Uses 'woocommerce_variable_product_before_variations' hook if WC>3.34 to add the "custom From string field" to the Variations tab (https://github.com/woocommerce/woocommerce/pull/19557#pullrequestreview-107731914)
$variations_hook = 'woocommerce_product_options_advanced';
if(true === PP_Dependencies::is_woocommerce_active( '3.3.6' )){
	$variations_hook = 'woocommerce_variable_product_before_variations';
}

/**
 * Adds the 'Custom Price String' field to the product editor page 
 * 
 * @access public
 * @return void
 */
function wcs_cps_admin_field(){
	 global $post;
	 $product = wc_get_product($post->ID);
	 $p_type = $product->get_type();
	 
	 if($p_type=='subscription' || $p_type=='variable-subscription'){
		 $price = WC_Subscriptions_Product::get_price($product);
		 $custom_price_string = WC_Subscriptions_Product::get_price_string($post->ID, array( 'price' => $price ));
		 $original_price_string = WC_Subscriptions_Product::get_price_string($post->ID, array( 'price' => $price, 'custom' => false ));
	 }else{
		  $original_price_string = $product->get_price();
		  $custom_price_string = get_post_meta($post->ID, '_custom_price_string', true);
	 }
	 
	 if($original_price_string == $custom_price_string){ $custom_price_string = ''; }

	 woocommerce_wp_text_input( array(
			'id'          => '_custom_price_string',
			'class'       => 'wc_input_custom_price_string short',
			'label'       => __( 'Custom price string', 'woocommerce-subscriptions' ),
			'placeholder' => strip_tags($original_price_string),
			'description' => __( 'Customize the default price string that is generated for this specific product. Leave it empty to use the default string.', 'woocommerce-subscriptions' ),
			'desc_tip'    => true,
			'type'        => 'text',
			'value'		  => strip_tags( $custom_price_string ),
			'data_type'   => 'any'
		) );
 }
add_action('woocommerce_product_options_pricing', 'wcs_cps_admin_field');


 /**
 * Adds the 'Custom Price String' field to each variation
 * 
 * @access public
 * @param mixed $loop
 * @param mixed $variation_data
 * @param mixed $variation
 * @return void
 */
function wcs_cps_variation_admin_field($loop, $variation_data, $variation){
	 $wc_variation = wc_get_product($variation->ID);
	 $price = $wc_variation->get_price();
	 
	 if($wc_variation->get_type() == "variable-subscription"){
		 $custom_price_string = WC_Subscriptions_Product::get_price_string(($variation->ID), array( 'price' => $price ));
		 $original_price_string = WC_Subscriptions_Product::get_price_string(($variation->ID), array( 'price' => $price, 'custom' => false ));
	 }else{
		 $original_price_string = $wc_variation->get_price();
		 $custom_price_string = get_post_meta($variation->ID, '_custom_price_string', true); 
	 }
	 
	 if($original_price_string == $custom_price_string){ $custom_price_string = ''; }
	 woocommerce_wp_text_input( array(
			'id'          => '_custom_price_string['.$loop.']',
			'class'       => 'wc_input_custom_price_string short',
			'label'       => __( 'Custom price string', 'woocommerce-subscriptions' ),
			'placeholder' => strip_tags($original_price_string),
			'description' => __( 'Customize the default price string that is generated for this specific variation. Leave it empty to use the default string.', 'woocommerce-subscriptions' ),
			'desc_tip'    => true,
			'type'        => 'text',
			'value'		  => strip_tags( $custom_price_string ),
			'data_type'   => 'any'
		) );
 }
 add_action('woocommerce_variable_subscription_pricing', 'wcs_cps_variation_admin_field', 11, 3);


/**
 * Adds the 'Custom From String' field to a variable product
 * 
 * @access public
 * @return void
 */
function wcs_cps_from_field(){
	global $post;
	$product = wc_get_product($post->ID);
	$p_type = $product->get_type();
	
	$remnoved = remove_filter('woocommerce_get_price_html', 'wcs_cps_from_string', 10);
	$original_from_string = $product->get_price_html();

	add_filter('woocommerce_get_price_html', 'wcs_cps_from_string', 10, 2);	
	$custom_from_string = get_post_meta($post->ID, '_custom_from_string', true); 
	
	if($original_from_string == $custom_from_string){ $custom_from_string = ''; }
	echo "<div class='toolbar toolbar-variations-from-string'>";
	woocommerce_wp_text_input( array(
		'id'          => '_custom_from_string',
		'class'       => 'wc_input_custom_from_string short',
		'label'       => __( 'Custom from string', 'woocommerce-subscriptions' ),
		'placeholder' => strip_tags($original_from_string),
		'description' => __( 'Customize the default From string that is generated for this specific product. Leave it empty to use the default string.', 'woocommerce-subscriptions' ),
		'desc_tip'    => true,
		'type'        => 'text',
		'value'		  => strip_tags( $custom_from_string ),
		'data_type'   => 'any'
	) );
	echo "</div>";
}
add_action($variations_hook, 'wcs_cps_from_field'); 


/**
  * Save the custom price string in the current product's '_custom_price_string' meta
  * 
  * @access public
  * @param mixed $post_id
  * @return void
  */
 function save_custom_price_string($post_id){
	 if(isset($_REQUEST['_custom_price_string'])){
		 update_post_meta( $post_id, '_custom_price_string', stripslashes( $_REQUEST['_custom_price_string']  ) );
	 }
	 if(isset($_REQUEST['_custom_from_string'])){
		 update_post_meta( $post_id, '_custom_from_string', stripslashes( $_REQUEST['_custom_from_string']  ) );
	 }
 }
 add_action( 'save_post',  'save_custom_price_string', 11 );


/**
 * Save the custom price string in the current variation's '_custom_price_string' meta
 * 
 * @access public
 * @param mixed $variation_id
 * @param mixed $index
 * @return void
 */
function save_variation_custom_price_string($variation_id, $index){
	if ( isset( $_POST['_custom_price_string'][ $index ] ) ) {
		update_post_meta( $variation_id, '_custom_price_string', stripslashes( $_POST['_custom_price_string'][ $index ]  ) );
	}
 }
add_action( 'woocommerce_save_product_variation', 'save_variation_custom_price_string', 20, 2 ); 


/**
 * Hooks 'woocommerce_subscriptions_product_price_string' filter to return the custom price string if the product has the '_custom_price_string' meta
 * 
 * @access public
 * @param mixed $subscription_string
 * @param mixed $product
 * @param mixed $include
 * @return void
 */
function wcs_custom_price_strings($subscription_string, $product, $include){
	$product_id = $product->get_id();
	if(!isset($include['custom'])){ $include['custom']= true;}
	
	$custom_price_string = get_post_meta($product_id, '_custom_price_string', true);
	if(false != $custom_price_string && '' != $custom_price_string && $include['custom']!=false){
	 $subscription_string = $custom_price_string;
	}
	return $subscription_string;
}
add_filter("woocommerce_subscriptions_product_price_string", "wcs_custom_price_strings", 10, 3);


/**
 * Hooks 'woocommerce_get_price_html' filter to return the custom price string if the product has the '_custom_price_string' meta
 * 
 * @access public
 * @param mixed $price
 * @param mixed $product
 * @return void
 */
function wcs_custom_price_strings_simple_prod($price, $product){
	$product_id = $product->get_id();
	if(!isset($include['custom'])){ $include['custom']= true;}
	
	$custom_price_string = get_post_meta($product_id, '_custom_price_string', true);
	if(false != $custom_price_string && '' != $custom_price_string && $include['custom']!=false){
	 $price = $custom_price_string;
	}
	return $price;
}
add_filter("woocommerce_get_price_html", "wcs_custom_price_strings_simple_prod", 10, 2);


/**
 * Hooks 'woocommerce_get_price_html' filter to return the "custom from string" when the (variable) product has the "_custom_from_string" meta
 * 
 * @access public
 * @param mixed $price
 * @param mixed $product
 * @return void
 */
function wcs_cps_from_string( $price, $product ) {
	$target_product_types = array( 
		'variable',
		'variable-subscription'
	);

	if ( in_array ( $product->get_type(), $target_product_types ) ) {
		$custom_from_string = get_post_meta($product->get_id(), '_custom_from_string', true);
		if($custom_from_string!=''){ $price = $custom_from_string; }
	}
	return $price;
}
add_filter('woocommerce_get_price_html', 'wcs_cps_from_string', 10, 2);


