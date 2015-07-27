<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}


/**
 * Array of settings
 */
return array(
	'enabled'          => array(
		'title'           => __( 'Enable DHL', 'wf-shipping-dhl' ),
		'type'            => 'checkbox',
		'label'           => __( 'Enable this shipping method', 'wf-shipping-dhl' ),
		'default'         => 'no'
	),
	'title'            => array(
		'title'           => __( 'Method Title', 'wf-shipping-dhl' ),
		'type'            => 'text',
		'description'     => __( 'This controls the title which the user sees during checkout.', 'wf-shipping-dhl' ),
		'default'         => __( 'DHL Basic Version', 'wf-shipping-dhl' ),
		'desc_tip'        => true
	),
	'account_number'           => array(
		'title'           => __( 'Account Number', 'wf-shipping-dhl' ),
		'type'            => 'text',
		'description'     => __( 'contact DHL.', 'wf-shipping-dhl' ),
		'default'         => '130000279'
    ),
    'site_id'           => array(
		'title'           => __( 'Site ID', 'wf-shipping-dhl' ),
		'type'            => 'text',
		'description'     => __( 'contact DHL.', 'wf-shipping-dhl' ),
		'default'         => 'CIMGBTest',
		'custom_attributes' => array(
			'autocomplete' => 'off'
		)
    ),
    'site_password'           => array(
		'title'           => __( 'Site Password', 'wf-shipping-dhl' ),
		'type'            => 'password',
		'description'     => __( 'contact DHL.', 'wf-shipping-dhl' ),
		'default'         => 'DLUntOcJma',
		'custom_attributes' => array(
			'autocomplete' => 'off'
		)
    ),
    'production'      => array(
		'title'           => __( 'Production Key', 'wf-shipping-dhl' ),
		'label'           => __( 'This is a production key', 'wf-shipping-dhl' ),
		'type'            => 'checkbox',
		'default'         => 'no',
		'desc_tip'    => true,
		'description'     => __( 'If this is a production API key and not a developer key, check this box.', 'wf-shipping-dhl' )
	),
	'region_code'   => array(
		'title'           => __( 'Region Code', 'wf-shipping-dhl' ),
		'type'            => 'select',
		'default'         => 'AM',
		'options'         => array(
			'AP'       => __( 'AP-EM Region: Supports countries in Asia, Africa, Australia and Pacific', 'wf-shipping-dhl' ),
			'EU'       => __( 'EU Region: Supports countries in Europe', 'wf-shipping-dhl' ),
			'AM'       => __( 'AM Region: Supports USA and other countries in North and South Americas', 'wf-shipping-dhl' )
		),
		'description'     => __( 'Choose appropriate Region Code based on the country of origin', 'wf-shipping-dhl' ),
	),
    'packing_method'   => array(
		'title'           => __( 'Parcel Packing Method', 'wf-shipping-dhl' ),
		'type'            => 'select',
		'default'         => 'per_item',
		'class'           => 'packing_method',
		'options'         => array(
			'per_item'       => __( 'Default: Pack items individually', 'wf-shipping-dhl' )
		),
		'description'     => __( 'Determine how items are packed before being sent to DHL. Upgrade to Premium version for Box Packing feature.', 'wf-shipping-dhl' ),
	),
	'insure_contents'      => array(
		'title'       => __( 'Insurance', 'wf-shipping-dhl' ),
		'label'       => __( 'Enable Insurance', 'wf-shipping-dhl' ),
		'type'        => 'checkbox',
		'default'     => 'yes',
		'desc_tip'    => true,
		'description' => __( 'Sends the package value to DHL for insurance.', 'wf-shipping-dhl' ),
	),
	'request_type'     => array(
		'title'           => __( 'Request Type', 'wf-shipping-dhl' ),
		'type'            => 'select',
		'default'         => 'LIST',
		'class'           => '',
		'desc_tip'        => true,
		'options'         => array(
			'LIST'        => __( 'List rates', 'wf-shipping-dhl' ),
			'ACCOUNT'     => __( 'Account rates', 'wf-shipping-dhl' ),
		),
		'description'     => __( 'Choose whether to return List or Account (discounted) rates from the API.', 'wf-shipping-dhl' )
	),
	
	'offer_rates'   => array(
		'title'           => __( 'Offer Rates', 'wf-shipping-dhl' ),
		'type'            => 'select',
		'description'     => '',
		'default'         => 'all',
		'options'         => array(
		    'all'         => __( 'Offer the customer all returned rates', 'wf-shipping-dhl' ),
		    'cheapest'    => __( 'Offer the customer the cheapest rate only, anonymously', 'wf-shipping-dhl' ),
		),
    ),
	'services'  => array(
		'type'            => 'services'
	),
	'origin'           => array(
		'title'           => __( 'Origin Postcode', 'wf-shipping-dhl' ),
		'type'            => 'text',
		'description'     => __( 'Enter postcode for the <strong>Shipper</strong>.', 'wf-shipping-dhl' ),
		'default'         => '10027'
    ),
	'shipper_person_name'           => array(
			'title'           => __( 'Shipper Person Name', 'wf-shipping-dhl' ),
			'type'            => 'text',
			'default'         => 'Mr Shipper',
			'description'     => 'Required for label Printing'			
	),	
	'shipper_company_name'           => array(
			'title'           => __( 'Shipper Company Name', 'wf-shipping-dhl' ),
			'type'            => 'text',
			'default'         => 'Company Name'	,
			'description'     => 'Required for label Printing'
	),	
	'shipper_phone_number'           => array(
			'title'           => __( 'Shipper Phone Number', 'wf-shipping-dhl' ),
			'type'            => 'text',
			'default'         => '1 234 1234567'	,
			'description'     => 'Required for label Printing'
    ),
	'shipper_email'           => array(
			'title'           => __( 'Shipper Email', 'wf-shipping-dhl' ),
			'type'            => 'text',
			'default'         => 'test@test.com'	,
			'description'     => 'Required for label Printing'
    ),
    'freight_shipper_street'           => array(
		'title'           => __( 'Shipper Street Address', 'wf-shipping-dhl' ),
		'type'            => 'text',
		'default'         => '1 New Orchard Road',
		'description'     => 'Required for label Printing.'
    ),
    'shipper_street_2'           => array(
		'title'           => __( 'Shipper Street Address 2', 'wf-shipping-dhl' ),
		'type'            => 'text',
		'default'         => 'Armonk',
		'description'     => 'Required for label Printing.'
    ),
    'freight_shipper_city'           => array(
		'title'           => __( 'Shipper City', 'wf-shipping-dhl' ),
		'type'            => 'text',
		'default'         => 'New York',
		'description'     => 'Required for label Printing.'
    ),
    'freight_shipper_state'           => array(
		'title'           => __( 'Shipper State Code', 'wf-shipping-dhl' ),
		'type'            => 'text',
		'default'         => 'NY',
		'description'     => 'Required for label Printing.'
    ),
	'output_format'   => array(
		'title'           => __( 'Label print size', 'wf-shipping-dhl' ),
		'type'            => 'select',
		'description'     => '8x4 indicates A4 size and 6x4 indicates thermal size.Upgrade to Premium version for Print Label feature.',
		'default'         => '6X4_A4_PDF',
		'options'         => array(
			'8X4_A4_PDF' 						      	=> __( '8X4_A4_PDF', 						'woocommerce-shipping-dhl'),
			'8X4_thermal' 						      	=> __( '8X4_thermal', 						'woocommerce-shipping-dhl'),
			'8X4_A4_TC_PDF' 						    => __( '8X4_A4_TC_PDF', 						'woocommerce-shipping-dhl'),
			'8X4_CI_PDF' 						      	=> __( '8X4_CI_PDF', 						'woocommerce-shipping-dhl'),
			'8X4_CI_thermal' 						    => __( '8X4_CI_thermal', 						'woocommerce-shipping-dhl'),
			'6X4_A4_PDF' 					      		=> __( '6X4_A4_PDF', 					'woocommerce-shipping-dhl'),
			)				
		),		
	'image_type'   => array(
		'title'           => __( 'Image Type', 'wf-shipping-dhl' ),
		'type'            => 'select',
		'description'     => 'Please use printer driver / browser plugin to identify ZPL2/EPL2 format and print directly to the printer.Upgrade to Premium version for Print Label feature.',
		'default'         => 'PDF',
		'options'         => array(
			'PDF' 						      	=> __( 'PDF', 						'woocommerce-shipping-dhl'),
			'ZPL2' 						      	=> __( 'ZPL2', 						'woocommerce-shipping-dhl'),
			'EPL2' 						      	=> __( 'EPL2', 						'woocommerce-shipping-dhl'),
			
			)				
		)	,
	'add_trackingpin_shipmentid' => array(
			'title'           => __( 'Tracking PIN', 'wf-shipping-canada-post' ),
			'label'           => __( 'Add Tracking PIN to customer order notes', 'wf-shipping-canada-post' ),
			'type'            => 'checkbox',
			'default'         => 'no',
			'description'     => 'Upgrade to Premium version for Tracking feature.'
		),	
	
	'debug'      => array(
		'title'           => __( 'Debug Mode', 'wf-shipping-dhl' ),
		'label'           => __( 'Enable debug mode', 'wf-shipping-dhl' ),
		'type'            => 'checkbox',
		'default'         => 'no',
		'desc_tip'    => true,
		'description'     => __( 'Enable debug mode to show debugging information on the cart/checkout.', 'wf-shipping-dhl' )
	)
);