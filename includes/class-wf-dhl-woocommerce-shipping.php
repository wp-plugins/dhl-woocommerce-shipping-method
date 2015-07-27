<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class wf_dhl_woocommerce_shipping_method extends WC_Shipping_Method {
	private $found_rates;
	private $services;

	public function __construct() {
		$this->id                               = WF_DHL_ID;
		$this->method_title                     = __( 'DHL Basic', 'wf-shipping-dhl' );
		$this->method_description               = __( 'Obtain real time shipping rates via DHL Shipping API. This is basic version, Upgrade to Premium version for Print shipping labels, Automatic tracking, Box packing & Services management.', 'wf-shipping-dhl' );
		$this->services                         = include( 'data-wf-service-codes.php' );
		$this->init();
	}

	private function init() {
		// Load the settings.
		$this->init_form_fields();
		$this->init_settings();

		// Define user set variables
		$this->title           = $this->get_option( 'title', $this->method_title );
		$this->origin          = apply_filters( 'woocommerce_dhl_origin_postal_code', str_replace( ' ', '', strtoupper( $this->get_option( 'origin' ) ) ) );
		$this->origin_country  = apply_filters( 'woocommerce_dhl_origin_country_code', WC()->countries->get_base_country() );
		$this->account_number  = $this->get_option( 'account_number' );
		$this->site_id         = $this->get_option( 'site_id' );
		$this->site_password        = $this->get_option( 'site_password' );
		$this->freight_shipper_city  = $this->get_option( 'freight_shipper_city' );
		
		$_stagingUrl = 'https://xmlpitest-ea.dhl.com/XMLShippingServlet';
		$_productionUrl = 'https://xmlpi-ea.dhl.com/XMLShippingServlet';
	
		$this->production      = ( $bool = $this->settings[ 'production' ] ) && $bool == 'yes' ? true : false;
		$this->service_url     = ($this->production == true) ? $_productionUrl  : $_stagingUrl ;
		
		
		$this->debug           = ( $bool = $this->get_option( 'debug' ) ) && $bool == 'yes' ? true : false;
		$this->insure_contents = ( $bool = $this->get_option( 'insure_contents' ) ) && $bool == 'yes' ? true : false;
		$this->request_type    = $this->get_option( 'request_type', 'LIST' );
		$this->packing_method  = $this->get_option( 'packing_method', 'per_item' );
		$this->custom_services = $this->get_option( 'services', array( ));
		$this->offer_rates     = $this->get_option( 'offer_rates', 'all' );
		
		add_action( 'woocommerce_update_options_shipping_' . $this->id, array( $this, 'process_admin_options' ) );
	}

	public function debug( $message, $type = 'notice' ) {
		if ( $this->debug ) {
			wc_add_notice( $message, $type );
		}
	}

	private function environment_check() {
			
		if ( ! $this->origin && $this->enabled == 'yes' ) {
			echo '<div class="error">
				<p>' . __( 'DHL is enabled, but the origin postcode has not been set.', 'wf-shipping-dhl' ) . '</p>
			</div>';
		}
	}

	public function admin_options() {
		// Check users environment supports this method
		$this->environment_check();?>
		<div class="wf-banner updated below-h2">
			<img class="scale-with-grid" src="http://www.wooforce.com/wp-content/uploads/2015/07/WooForce-Logo-Admin-Banner-Basic.png" alt="Wordpress / WooCommerce USPS, Canada Post Shipping | WooForce">
  			<p class="main"><strong>DHL Premium version streamlines your complete shipping process and saves time</strong></p>
			<p>&nbsp;-&nbsp;Print shipping label with postage.<br>
			&nbsp;-&nbsp;Auto Shipment Tracking: It happens automatically while generating the label.<br>
			&nbsp;-&nbsp;Box packing with DHL boxes and custom boxes.<br>
			&nbsp;-&nbsp;Enable/disable, edit the names of, and add handling costs to shipping services.<br>
			&nbsp;-&nbsp;Excellent Support for setting it up!</p>
			<p><a href="http://www.wooforce.com/product/dhl-woocommerce-shipping-with-print-label/" target="_blank" class="button button-primary">Upgrade to Premium Version</a> <a href="http://dhl.wooforce.com/wp-admin/admin.php?page=wc-settings&amp;tab=shipping&amp;section=wf_dhl_woocommerce_shipping_method" target="_blank" class="button">Live Demo</a></p>
		</div>
		<style>
		.wf-banner img {
			float: right;
			margin-left: 1em;
			padding: 15px 0
		}
		</style>
		<?php	
		// Show settings
		parent::admin_options();
	}

	public function init_form_fields() {
		$this->form_fields  = include( 'data-wf-settings.php' );
	}

	public function generate_services_html() {
		return '';
	}

	public function validate_services_field( $key ) {
		$services         = array();
		$posted_services  = $this->services;

		foreach ( $posted_services as $code => $name ) {
			$services[ $code ] = array(
				'name'               => woocommerce_clean( $name ),
				'order'              => '',
				'enabled'            => true,
				'adjustment'         => '',
				'adjustment_percent' => ''
			);
		}

		return $services;
	}

	public function get_dhl_packages( $package ) {
		switch ( $this->packing_method ) {
			case 'per_item' :
			default :
				return $this->per_item_shipping( $package );
			break;
		}
	}

	private function per_item_shipping( $package ) {
		$to_ship  = array();
		$group_id = 1;

		// Get weight of order
		foreach ( $package['contents'] as $item_id => $values ) {

			if ( ! $values['data']->needs_shipping() ) {
				$this->debug( sprintf( __( 'Product # is virtual. Skipping.', 'wf-shipping-dhl' ), $item_id ), 'error' );
				continue;
			}

			if ( ! $values['data']->get_weight() ) {
				$this->debug( sprintf( __( 'Product # is missing weight. Aborting.', 'wf-shipping-dhl' ), $item_id ), 'error' );
				return;
			}

			$group = array();

			$group = array(
				'GroupNumber'       => $group_id,
				'GroupPackageCount' => 1,
				'Weight' => array(
					'Value' => max( '0.5', round( woocommerce_get_weight( $values['data']->get_weight(), 'lbs' ), 2 ) ),
					'Units' => 'LBS'
				),
				'packed_products' => array( $values['data'] )
			);

			if ( $values['data']->length && $values['data']->height && $values['data']->width ) {

				$dimensions = array( $values['data']->length, $values['data']->width, $values['data']->height );

				sort( $dimensions );

				$group['Dimensions'] = array(
					'Length' => max( 1, round( woocommerce_get_dimension( $dimensions[2], 'in' ), 0 ) ),
					'Width'  => max( 1, round( woocommerce_get_dimension( $dimensions[1], 'in' ), 0 ) ),
					'Height' => max( 1, round( woocommerce_get_dimension( $dimensions[0], 'in' ), 0 ) ),
					'Units'  => 'IN'
				);
			}

			$group['InsuredValue'] = array(
				'Amount'   => round( $values['data']->get_price() ),
				'Currency' => get_woocommerce_currency()
			);
			
			for ( $i = 0; $i < $values['quantity']; $i++)
				$to_ship[] = $group;
			
			$group_id++;
		}

		return $to_ship;
	}

	private function get_dhl_requests( $dhl_packages, $package) {

		$mailing_date = date('Y-m-d');		
		$mailing_datetime = date('Y-m-d') . 'T'. date('H:i:s');
		$destination_postcode = str_replace( ' ', '', strtoupper( $package['destination']['postcode'] ));
		$pieces = $this->wf_get_package_piece($dhl_packages);
		$fetch_accountrates = $this->request_type == "ACCOUNT" ? "<PaymentAccountNumber>".$this->account_number."</PaymentAccountNumber>" : "";
		
		$total_value = $this->wf_get_package_total_value($dhl_packages);
		$currency = get_woocommerce_currency();
			
		$insurance_details =  $this->insure_contents ? "<InsuredValue>{$total_value}</InsuredValue><InsuredCurrency >{$currency}</InsuredCurrency>" : "";
		
		$is_dutiable = $package['destination']['country'] == WC()->countries->get_base_country() ? "N" : "Y"; 
		
		$destination_city = strtoupper( $package['destination']['city'] );
		
		$origin_postcode_city =  $this->origin_country == 'MU' ? "<City>{$this->freight_shipper_city}</City>" : "<Postalcode>{$this->origin}</Postalcode>";
		$destination_postcode_city =  $package['destination']['country'] == 'MU' ? "<City>{$destination_city}</City>" : "<Postalcode>{$destination_postcode}</Postalcode>";
			
$xmlRequest = <<<XML
<?xml version="1.0" encoding="UTF-8"?>
<p:DCTRequest xmlns:p="http://www.dhl.com" xmlns:p1="http://www.dhl.com/datatypes" xmlns:p2="http://www.dhl.com/DCTRequestdatatypes" xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance" xsi:schemaLocation="http://www.dhl.com DCT-req.xsd ">
  <GetQuote>
    <Request>
		<ServiceHeader>
			<MessageTime>{$mailing_datetime}</MessageTime>
			<MessageReference>1234567890123456789012345678901</MessageReference>
			<SiteID>{$this->site_id}</SiteID>
			<Password>{$this->site_password}</Password>
		</ServiceHeader>
    </Request>
    <From>
	  <CountryCode>{$this->origin_country}</CountryCode>
	  {$origin_postcode_city}
    </From>
    <BkgDetails>
      <PaymentCountryCode>{$this->origin_country}</PaymentCountryCode>
      <Date>{$mailing_date}</Date>
      <ReadyTime>PT10H21M</ReadyTime>
      <DimensionUnit>IN</DimensionUnit>
      <WeightUnit>LB</WeightUnit>
      <Pieces>
		{$pieces}
	  </Pieces>
	  {$fetch_accountrates}
	  <IsDutiable>{$is_dutiable}</IsDutiable>
      <NetworkTypeCode>AL</NetworkTypeCode>
	  {$insurance_details}
	  </BkgDetails>
    <To>
      <CountryCode>{$package['destination']['country']}</CountryCode>
	  {$destination_postcode_city}
    </To>
   <Dutiable>
	<DeclaredCurrency>{$currency}</DeclaredCurrency>
      <DeclaredValue>{$total_value}</DeclaredValue>
    </Dutiable>
  </GetQuote>
</p:DCTRequest>
XML;

		return $xmlRequest;
	}
	
	private function wf_get_package_piece($dhl_packages){
		$pieces = "";
		if ( $dhl_packages ) {
			foreach ( $dhl_packages as $key => $parcel ) {
				$index = $key + 1;
				$pieces .= '<Piece><PieceID>' . $index . '</PieceID>';
				$pieces .= '<PackageTypeCode>BOX</PackageTypeCode>';
				$pieces .= '<Height>' . $parcel['Dimensions']['Height'] . '</Height>';
				$pieces .= '<Depth>' . $parcel['Dimensions']['Length'] . '</Depth>';
				$pieces .= '<Width>' . $parcel['Dimensions']['Width'] . '</Width>';
				$pieces .= '<Weight>' . $parcel['Weight']['Value'] . '</Weight></Piece>';
			}			
		}
		return $pieces;
		
	}
	
	private function wf_get_package_total_value($dhl_packages){
		$total_value = 0;
		if ( $dhl_packages ) {
			foreach ( $dhl_packages as $key => $parcel ) {
				$total_value   += $parcel['InsuredValue']['Amount'] * $parcel['GroupPackageCount'];				
			}			
		}
		return $total_value;
	}
	public function calculate_shipping( $package ) {
		// Clear rates
		$this->found_rates = array();

		// Debugging
		$this->debug( __( 'dhl debug mode is on - to hide these messages, turn debug mode off in the settings.', 'wf-shipping-dhl' ) );

		
		// Get requests
		$dhl_packages   = $this->get_dhl_packages( $package );
		$dhl_requests   = $this->get_dhl_requests( $dhl_packages, $package );

		if ( $dhl_requests ) {
			$this->run_package_request( $dhl_requests );
		}

		
		
		// Ensure rates were found for all packages
		$packages_to_quote_count = sizeof( $dhl_requests );

		if ( $this->found_rates ) {
			foreach ( $this->found_rates as $key => $value ) {
				if ( $value['packages'] < $packages_to_quote_count ) {
					unset( $this->found_rates[ $key ] );
				}
			}
		}

		$this->add_found_rates();
	}

	public function run_package_request( $requests ) {
		try {
			$this->process_result( $this->get_result( $requests ) , $requests);			
		} catch ( Exception $e ) {
			$this->debug( print_r( $e, true ), 'error' );
			return false;
		}
	}

	private function get_result( $request ) {
		$this->debug( 'DHL REQUEST: <a href="#" class="debug_reveal">Reveal</a><pre class="debug_info" style="background:#EEE;border:1px solid #DDD;padding:5px;">' . print_r( $request, true ) . '</pre>' );
		
		
		$result = wp_remote_post( $this->service_url,
						array(
							'method'           => 'POST',
							'timeout'          => 70,
							'sslverify'        => 0,
							//'headers'          => $this->wf_get_request_header('application/vnd.cpc.shipment-v7+xml','application/vnd.cpc.shipment-v7+xml'),
							'body'             => $request
						)
					);
	
	
	
		$this->debug( 'DHL RESPONSE: <a href="#" class="debug_reveal">Reveal</a><pre class="debug_info" style="background:#EEE;border:1px solid #DDD;padding:5px;">' . print_r( $result, true ) . '</pre>' );

		
		wc_enqueue_js( "
			jQuery('a.debug_reveal').on('click', function(){
				jQuery(this).closest('div').find('.debug_info').slideDown();
				jQuery(this).remove();
				return false;
			});
			jQuery('pre.debug_info').hide();
		" );

		if ( ! empty( $result['body'] ) ) {
			$result = $result['body'];
		} else {
			$result = '';
		}  

		libxml_use_internal_errors(true);
		$xml = simplexml_load_string( $result);
		$shipmentErrorMessage  = "";
		if ($xml) {
			return $xml; 
		} else {
			return null;			
		}		
	}

	private function wf_get_cost_based_on_currency($qtdsinadcur, $default_charge){
		if(!empty($qtdsinadcur)){
			foreach($qtdsinadcur as $multiple_currencies){
				if((string)$multiple_currencies->CurrencyCode == get_woocommerce_currency() && !empty($multiple_currencies->TotalAmount))
					return $multiple_currencies->TotalAmount;
			}				
		}
		return $default_charge;
	}
	
	private function process_result( $result = '' ) {
		if ( $result && ! empty ( $result->GetQuoteResponse->BkgDetails->QtdShp) ) {
			foreach ( $result->GetQuoteResponse->BkgDetails->QtdShp as $quote ) {				
				if((string)$quote->CurrencyCode == get_woocommerce_currency()){
					$rate_cost = floatval( (string)$quote->ShippingCharge );
				}else{
					$rate_cost = floatval( (string)$this->wf_get_cost_based_on_currency($quote->QtdSInAdCur, $quote->ShippingCharge));	
				}
				
				$rate_code = strval( (string)$quote->GlobalProductCode );
				$rate_id   = $this->id . ':' . $rate_code;
				$rate_name = strval( (string)$quote->LocalProductName );
				$this->prepare_rate( $rate_code, $rate_id, $rate_name, $rate_cost );
			}
		}
	}
	
	private function prepare_rate( $rate_code, $rate_id, $rate_name, $rate_cost ) {

		// Name adjustment
		if ( ! empty( $this->custom_services[ $rate_code ]['name'] ) ) {
			$rate_name = $this->custom_services[ $rate_code ]['name'];
		}

		// Cost adjustment %
		if ( ! empty( $this->custom_services[ $rate_code ]['adjustment_percent'] ) ) {
			$rate_cost = $rate_cost + ( $rate_cost * ( floatval( $this->custom_services[ $rate_code ]['adjustment_percent'] ) / 100 ) );
		}
		// Cost adjustment
		if ( ! empty( $this->custom_services[ $rate_code ]['adjustment'] ) ) {
			$rate_cost = $rate_cost + floatval( $this->custom_services[ $rate_code ]['adjustment'] );
		}

		// Enabled check
		if ( isset( $this->custom_services[ $rate_code ] ) && empty( $this->custom_services[ $rate_code ]['enabled'] ) ) {
			return;
		}

		// Merging
		if ( isset( $this->found_rates[ $rate_id ] ) ) {
			$rate_cost = $rate_cost + $this->found_rates[ $rate_id ]['cost'];
			$packages  = 1 + $this->found_rates[ $rate_id ]['packages'];
		} else {
			$packages  = 1;
		}

		// Sort
		if ( isset( $this->custom_services[ $rate_code ]['order'] ) ) {
			$sort = $this->custom_services[ $rate_code ]['order'];
		} else {
			$sort = 999;
		}

		$this->found_rates[ $rate_id ] = array(
			'id'       => $rate_id,
			'label'    => $rate_name,
			'cost'     => $rate_cost,
			'sort'     => $sort,
			'packages' => $packages
		);
	}

	public function add_found_rates() {
		if ( $this->found_rates ) {

			if ( $this->offer_rates == 'all' ) {

				uasort( $this->found_rates, array( $this, 'sort_rates' ) );

				foreach ( $this->found_rates as $key => $rate ) {
					$this->add_rate( $rate );
				}
			} else {
				$cheapest_rate = '';

				foreach ( $this->found_rates as $key => $rate ) {
					if ( ! $cheapest_rate || $cheapest_rate['cost'] > $rate['cost'] ) {
						$cheapest_rate = $rate;
					}
				}

				$cheapest_rate['label'] = $this->title;

				$this->add_rate( $cheapest_rate );
			}
		}
	}

	public function sort_rates( $a, $b ) {
		if ( $a['sort'] == $b['sort'] ) return 0;
		return ( $a['sort'] < $b['sort'] ) ? -1 : 1;
	}
}
