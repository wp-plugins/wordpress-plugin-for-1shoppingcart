<?php
/**
* Plugin Name: ES 1ShoppingCart
* Plugin URI: http://www.equalserving.com/products-page/wordpress-plugin/free-wordpress-plugin-for-1shoppingcart/
* Description: Using shortcodes, you can easily display product details from your 1ShoppingCart.com product catalog on pages or posts within your WordPress site. All that needs to be entered on the page or post is the title and the shortcut code [es1sc_prodlist]. The shortcode [es1sc_prodlist] without any additional arguments will display your entire active product catalog. You can limit the list to specific products by adding the argument prd_ids to the shortcode such as - [es1sc_prodlist prd_ids="8644152,8644145,8580674,8569588,8569508,8361626"].
* Version: 0.1
* Author: EqualServing.com
* Author URI: http://www.equalserving.com/
* Donate link: https://www.paypal.com/cgi-bin/webscr?cmd=_donations&business=H8KWRPTET2SK2&lc=US&item_name=Free%20Wordpress%20Plugin%20for%201ShoppingCart&currency_code=USD&bn=PP%2dDonationsBF%3abtn_donateCC_LG%2egif%3aNonHosted
* License: GPLv2
*
* Free ES 1ShoppingCart is a plugin developed to simplify the process of displaying 1ShoppingCart product catalogs and
* product details on your Wordpress pages and posts.
*
*/

define( 'ES1SCVERSION', '0.1' );

require(plugin_dir_path( __FILE__ ) .'include/OneShopAPI.php');
$merchantId = get_option('es1sc_merchant_id');
$merchantKey = get_option('es1sc_merchant_key');
$apiUri = get_option('es1sc_api_uri');

$shop = new OneShopAPI($merchantId, $merchantKey, $apiUri);

// Set minimum execution time to 5 minutes - won't affect safe mode
$safe_mode = array('On', 'ON', 'on', 1);
if ( !in_array(ini_get('safe_mode'), $safe_mode) && ini_get('max_execution_time') < 300 ) {
	@ini_set('max_execution_time', 300);
}

if ($_POST['option_page'] == "es1sc-settings") {
	es1sc_admin();
}

/**
 * Settings link in the plugins page menu
 * @param array $links
 * @param string $file
 * @return array
 */
function es1sc_set_plugin_meta($links, $file) {
	$plugin = plugin_basename(__FILE__);
	// create link
	if ($file == $plugin) {
		return array_merge(
			$links,
			array( sprintf( '<a href="admin.php?page='.$file.'">%s</a>', __('Settings') ),
			'<a href="https://www.paypal.com/cgi-bin/webscr?cmd=_donations&business=H8KWRPTET2SK2&lc=US&item_name=Free%20Wordpress%20Plugin%20for%201ShoppingCart&currency_code=USD&bn=PP%2dDonationsBF%3abtn_donateCC_LG%2egif%3aNonHosted" target="_blank">Donate</a>')
		);
	}
	return $links;
}
add_filter( 'plugin_row_meta', 'es1sc_set_plugin_meta', 10, 2 );

function es1sc_admin_menu() {
	add_options_page('1ShoppingCart Options', '1SC Settings', 'administrator', __FILE__, 'es1sc_plugin_options');
}
add_action('admin_menu', 'es1sc_admin_menu');

function es1sc_plugin_options() {
	if (!current_user_can('manage_options'))  {
		wp_die( __('You do not have sufficient permissions to access this page.') );
	}
	if ( $_REQUEST['saved'] ) {
		echo '<div id="message" class="updated fade"><p><strong>ES1SC settings saved.</strong></p></div>';
	}
	if ( $_REQUEST['reset'] ) {
		echo '<div id="message" class="updated fade"><p><strong>ES1SC settings reset.</strong></p></div>';
	}

	echo '<div class="wrap">';
	echo '<h2>Your 1ShoppingCart.com API Information</h2>';

	echo '<form name="updatesettings" id="updatesettings" method="post" action="'. $_SERVER['REQUEST_URI']. '">';
    settings_fields( 'es1sc-settings' );
	echo '	<table class="form-table">';

	$pluginoptions = array (
		array("name" => __('Merchant ID','thematic'),
			"desc" => __('Your 1ShoppingCart.com Merchant ID','thematic'),
			"id" => "es1sc_merchant_id",
			"std" => "999999",
			"type" => "text"
		),
		array("name" => __('Merchant Key','thematic'),
			"desc" => __('Your 1ShoppingCart.com Merchant Key','thematic'),
			"id" => "es1sc_merchant_key",
			"std" => "999999",
			"type" => "text"
		),
		array("name" => __('API URI','thematic'),
			"desc" => __('Your 1ShoppingCart.com API URI','thematic'),
			"id" => "es1sc_api_uri",
			"std" => "999999",
			"type" => "text"
		),
		array("name" => __('No Image URL','thematic'),
			"desc" => __('The location of the no product image available','thematic'),
			"id" => "es1sc_no_image",
			"std" => "999999",
			"type" => "text"
		),
		array("name" => __('Add to Cart Image URL','thematic'),
			"desc" => __('The location of the add to cart image','thematic'),
			"id" => "es1sc_cart_image",
			"std" => "999999",
			"type" => "text"
		),
		array("name" => __('Buy Now URL','thematic'),
			"desc" => __('Your 1ShoppingCart.com URL <br /> usually something like '.get_option('siteurl').'/?cmd.php','thematic'),
			"id" => "es1sc_buynow_url",
			"std" => "999999",
			"type" => "text",
		),
		array("name" => __('Product List Item Format','thematic'),
			"desc" => __('Enter the formatting you would like to use for your product listing.','thematic'),
			"id" => "es1sc_product_list_item_format",
			"std" => "999999",
			"type" => "textarea",
			"options" => array("cols" => 60, "rows" => 4),
		),
	);

	foreach ($pluginoptions as $value) {
		// Output the appropriate form element
		switch ( $value['type'] ) {
			case 'text':
			?>
			<tr valign="top">
				<th scope="row"><?php echo $value['name']; ?>:</th>
				<td>
					<input name="<?php echo $value['id']; ?>" id="<?php echo $value['id']; ?>" type="text"
						value="<?php echo stripslashes(get_option( $value['id'],$value['std'] )); ?>"/>
					<?php echo $value['desc']; ?>
				</td>
			</tr>
			<?php
			break;
			case 'select':
			?>
			<tr valign="top">
				<th scope="row"><?php echo $value['name']; ?></th>
				<td>
					<select name="<?php echo $value['id']; ?>" id="<?php echo $value['id']; ?>">
						<option value="">--</option>
						<?php foreach ($value['options'] as $key=>$option) {
							if ($key == get_option($value['id'], $value['std']) ) {
								$selected = "selected=\"selected\"";
							} else {
								$selected = "";
							}
							?>
							<option value="<?php echo $key ?>" <?php echo $selected ?>> <?php echo $option; ?></option>
						<?php } ?>
					</select>
					<?php echo $value['desc']; ?>
				</td>
			</tr>
			<?php
			break;
			case 'textarea':
				$ta_options = $value['options'];
				?>
				<tr valign="top">
					<th scope="row"><?php echo $value['name']; ?>:</th>
					<td>
						<textarea name="<?php echo $value['id']; ?>" id="<?php echo $value['id']; ?>"
							cols="<?php echo $ta_options['cols']; ?>"
							rows="<?php echo $ta_options['rows']; ?>"><?php
							echo stripslashes(get_option($value['id'], $value['std'])); ?>
						</textarea>
						<br /><?php echo $value['desc']; ?>
					</td>
				</tr>
			<?php
			break;
			case "radio":
			?>
				<tr valign="top">
					<th scope="row"><?php echo $value['name']; ?>:</th>
					<td>
						<?php foreach ($value['options'] as $key=>$option) {
							if ($key == get_option($value['id'], $value['std']) ) {
								$checked = "checked=\"checked\"";
							} else {
								$checked = "";
							}
							?>
							<input type="radio"
								name="<?php echo $value['id']; ?>"
								value="<?php echo $key; ?>"
								<?php echo $checked; ?>
								/><?php echo $option; ?>
								<br />
						<?php } ?>
						<?php echo $value['desc']; ?>
					</td>
				</tr>
			<?php
			break;
			case "checkbox":
			?>
				<tr valign="top">
					<th scope="row"><?php echo $value['name']; ?></th>
					<td>
						<?php
						if(get_option($value['id'])){
							$checked = "checked=\"checked\"";
						} else {
							$checked = "";
						}
						?>
						<input type="checkbox"
							name="<?php echo $value['id']; ?>"
							id="<?php echo $value['id']; ?>"
							value="true"
							<?php echo $checked; ?>
							/>
							<?php echo $value['desc']; ?>
					</td>
				</tr>
			<?php
			break;
			default:
			break;
		}
	}
	echo '	</table>';
	echo '	<p class="submit">';
	echo '	<input type="hidden" name="es1sc_admin" value="update_settings" />';
	echo '	<input type="submit" class="button-primary" value="Save Changes" />';
	echo '	</p>';
	echo '	</form>';
	echo '</div>';
}

function register_es1scsettings() {
	//register our settings
	register_setting('es1sc-settings', 'es1sc_merchant_id');
	register_setting('es1sc-settings', 'es1sc_merchant_key');
	register_setting('es1sc-settings', 'es1sc_api_uri');
	add_option('es1sc_merchant_id', '');
	add_option('es1sc_merchant_key', '');
	add_option('es1sc_api_uri', '');
	add_option('es1sc_no_image','');
	add_option('es1sc_cart_image','');
	add_option('es1sc_buynow_url',get_option('siteurl').'/cmd.php');
	add_option('es1sc_product_list_item_format','<div class="product"><span class="product_name">#ProductName</span><br />#ProductImage<span class="product_details"><span class="description">#ShortDescription</span><span class="sku">#ProductSku</span><br /><span class="price">#ProductPrice</span><br /><span class="buy-now">#BuyNow</span></span><div class="clear"> </div></div>');
}
add_action('admin_init', 'register_es1scsettings');

function es1sc_admin()
{

	//print_r($_POST);
	switch ($_POST['action']) {

	case ("update"):

		update_option('es1sc_merchant_id', $_POST['es1sc_merchant_id']);
		update_option('es1sc_merchant_key', $_POST['es1sc_merchant_key']);
		update_option('es1sc_api_uri', $_POST['es1sc_api_uri']);
		update_option('es1sc_no_image',$_POST['es1sc_no_image']);
		update_option('es1sc_cart_image',$_POST['es1sc_cart_image']);
		update_option('es1sc_buynow_url',$_POST['es1sc_buynow_url']);
		update_option('es1sc_product_list_item_format',$_POST['es1sc_product_list_item_format']);

		break;

	case ("update_fields"):
		break;
	}
}

function es1sc_product_list($atts) {
	global $shop;

	$display_format = stripslashes(get_option('es1sc_product_list_item_format'));

	$retVal = "";

	extract(shortcode_atts(array("prd_ids" => 0), $atts));
	if (isset($prd_ids) && $prd_ids != "") {
		$prd_ids_temp = explode(",",$prd_ids);
		$prd_ids = (object) $prd_ids_temp;
	} else {
		$limitoffset = 0;
		$limitcount = 30;
		$prd_ids = array();
		while (!is_null($limitoffset)) {
			$shop->_apiParameters = array("LimitCount" => $limitcount, "LimitOffset" => $limitoffset);
			$products_xml = $shop->GetProductsList();
			$products = @simplexml_load_string($products_xml) or die ("no file loaded");
			//print_r($products);
			foreach ($products->Products->Product as $prd_id) {
				$prd_ids[] = $prd_id;
			}
			$limitoffset = $products->NextRecordSet->LimitOffset;
		}
	}
	foreach ($prd_ids as $prd_id) {
		$product_details_xml = $shop->GetProductById($prd_id);
		$product_details = @simplexml_load_string($product_details_xml) or die ("no file loaded");

		if ($product_details->ProductInfo->IsActive == "true") {
			if ($product_details->ProductInfo->ImageUrl == "") {
				$ImageUrlSrc = get_option('es1sc_no_image');
			} else {
				$ImageUrlSrc = "https://www.mcssl.com".$product_details->ProductInfo->ImageUrl;
			}
			$ImageUrl = '<img class="product_image" src="'. $ImageUrlSrc.'" alt="'.$product_details->ProductInfo->ProductName.'" />';

			$ProductPrice = "";
			if ($product_details->ProductInfo->UseSalePrice == "true") {
				$ProductPrice .= '<strike>$'.$product_details->ProductInfo->ProductPrice.'</strike> Only $'.$product_details->ProductInfo->SalePrice;
			} else {
				$ProductPrice .= "$".$product_details->ProductInfo->ProductPrice;
			}
			$BuyNow = '<a href="'.get_option('es1sc_buynow_url').'?pid='.$product_details->ProductInfo->VisibleId.'"><img src="'.get_option('es1sc_cart_image').'" /></a>';

			$aVariables = array('#ProductName', '#ProductImage', '#ShortDescription', '#LongDescription', '#ProductSku', '#ProductPrice','#BuyNow');
			$aReplacements = array($product_details->ProductInfo->ProductName, $ImageUrl, wpautop($product_details->ProductInfo->ShortDescription), wpautop($product_details->ProductInfo->LongDescription), $product_details->ProductInfo->ProductSku, $ProductPrice, $BuyNow);

			$retVal .= str_replace($aVariables, $aReplacements, $display_format);
		}
	}

	return $retVal;
}
add_shortcode('es1sc_prodlist', 'es1sc_product_list');

?>
