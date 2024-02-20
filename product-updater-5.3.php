<?php
add_action('ens_rename_cameras_activation', 'ens_rename_cameras');
add_action('ens_rename_recorders_activation','ens_rename_recorders');
add_action('ens_rename_accessories_activation', 'ens_rename_accessories');
add_action('ens_add_description_ipc_activation', 'ens_add_description_ipc');
add_action('ens_add_description_coax_activation', 'ens_add_description_coax');
add_action('ens_add_description_nvr_activation', 'ens_add_description_nvr');
add_action('ens_rename_networking_activation', 'ens_rename_networking');
add_action('ens_rename_smartalarm_activation', 'ens_rename_smartalarm');//deprecated, maangment wants fully unique titles
add_action('ens_add_description_dvr_activation', 'ens_add_description_dvr'); //is what calls emails / clears the log
add_action('ens_clean_description_activation', 'ens_clean_description'); //cleans out non accepted charaters, not repeating
add_action('ens_renamer_activation' , 'ens_renamer');


function ens_renamer()
{
	//logger('called', 2);
    ens_gs_clear();
    global $ensincrementerformissingattritubes;
    $ensincrementerformissingattritubes= 2;
	ens_rename_cameras();
    ens_rename_recorders();
    ens_rename_accessories();
    ens_add_description_ipc();
    ens_add_description_coax();
    ens_add_description_nvr();
    ens_add_description_dvr();
    ens_rename_networking();
    //ens_rename_smartalarm();
}

function ens_gs_clear()
{
	// google sheet 
    require_once (__DIR__ . '/google-api-php-client/vendor/autoload.php');

    // configure the Google Client
    $client = new \Google_Client();
    $client->setApplicationName('Google Sheets API');
    $client->setScopes([\Google_Service_Sheets::SPREADSHEETS]);
    $client->setAccessType('offline');

    // credentials.json is the key file we downloaded while setting up our Google Sheets API
    $path = __DIR__ . '/event-attendee-report-723216decb65.json';
    //logger($path, 1);
    $client->setAuthConfig($path);

    // configure the Sheets Service
    $service = new \Google_Service_Sheets($client);
    $spreadsheetId = '1IUiep3itmFFaXi_2RPZwADIgw8xpk4L4N5oJbhwjrKA';

    $range = 'Missing Attributes - US!A2:F500'; // the range to clear
    //logger($range, 1);
    $clear = new \Google_Service_Sheets_ClearValuesRequest();
    $service->spreadsheets_values->clear($spreadsheetId, $range, $clear);
}

function ens_gs_write($updateRow)
{
	// google sheet 
    require_once (__DIR__ . '/google-api-php-client/vendor/autoload.php');
    global $ensincrementerformissingattritubes;

	// configure the Google Client
    $client = new \Google_Client();
    $client->setApplicationName('Google Sheets API');
    $client->setScopes([\Google_Service_Sheets::SPREADSHEETS]);
    $client->setAccessType('offline');

    // credentials.json is the key file we downloaded while setting up our Google Sheets API
    $path = __DIR__ . '/event-attendee-report-723216decb65.json';
    $client->setAuthConfig($path);

    // configure the Sheets Service
    $service = new \Google_Service_Sheets($client);
    $spreadsheetId = '1IUiep3itmFFaXi_2RPZwADIgw8xpk4L4N5oJbhwjrKA';

    //pusing data to google sheet
    $rows = [$updateRow];
    $valueRange = new \Google_Service_Sheets_ValueRange();
    $valueRange->setValues($rows);
    $range = 'Missing Attributes - US!A'. ($ensincrementerformissingattritubes) .'';
    $options = ['valueInputOption' => 'USER_ENTERED'];
    $service->spreadsheets_values->update($spreadsheetId, $range, $valueRange, $options);
    $ensincrementerformissingattritubes++;
}


function ens_rename_cameras()
{
	//logger('******CAMERA RENAMER******', 1);

	//The query
	$wordpressproducts = new WP_Query( array(
		'post_type'      => array('product'),
		'post_status' => array('publish'),
		'posts_per_page' => -1,
		'tax_query' => array( array(
			'taxonomy'        => 'pa_itemclass1',
			'field'           => 'slug',
			'terms'         => array('Cameras','cameras'),
			'operator' 	=> 'IN',
		) ),

	) );

	//The Loop
	if ( $wordpressproducts->have_posts() )
	{ 
		while ( $wordpressproducts->have_posts() )
		{
			$wordpressproducts->the_post();
			$id = $wordpressproducts->post->ID;
			$wooproduct = wc_get_product( $id );

            //getting SKU and adding to name
			$skustring = get_post_meta( $id, '_sku', true );
			$newtitle = ($skustring . ' || ');

            //getting attributes and adding to name            
			$series = $wooproduct->get_attribute('pa_series');
			$type = $wooproduct->get_attribute('pa_itemclass2');
			$resolution = $wooproduct->get_attribute('pa_camera-resolution');
			$housing = $wooproduct->get_attribute('pa_camera-style');
			$lens = $wooproduct->get_attribute('pa_camera-lens');
			


			//IGNORING UNIVIEW
			if($series == 'Uniview'){continue;}

			//cleaning
			//these ones will be dirty because not sure what were sticking to
			if($type == 'IPCAM'){$type = 'IPC';}
			if($type == 'COAXCAM'){$type = 'COAX';}
			if($series == 'ENS Emerald'){$series = 'Emerald';}
			if($series == 'H Series'){$series = 'ENS-H';}
			if($housing == 'Special'){$housing = 'Special Housing';}
			if($lens == '30X &amp; Up PTZ'){$lens = '30X+ PTZ';}


			//final output 
			$newtitle = ($newtitle . $series . ', ' . $type. ', ' . $resolution . ', ' . $housing . ', ' . $lens);


			//output, comment out undesired
			update_title($newtitle, $id);

			//logging info
			$newtitle = $id . ': ' . $newtitle;
			//logger($newtitle, 1);
		}
		wp_reset_postdata();
	}
}

function ens_rename_recorders()
{
	//logger('******RECORDER RENAMER******', 1);

	//The query
	$wordpressproducts = new WP_Query( array(
		'post_type'      => array('product'),
		'post_status' => 'publish',
		'posts_per_page' => -1,
		'tax_query' => array( array(
			'taxonomy'        => 'pa_itemclass1',
			'field'           => 'slug',
			'terms'         => array('Recorders'),
			'operator' => 'IN',
		) ),

	) );

	//The Loop
	if ( $wordpressproducts->have_posts() )
	{ 
		while ( $wordpressproducts->have_posts() )
		{
			$wordpressproducts->the_post();
			$id = $wordpressproducts->post->ID;
			$wooproduct = wc_get_product( $id );

            //getting SKU and adding to name
			$skustring = get_post_meta( $id, '_sku', true );
			$newtitle = ($skustring . ' || ');

            //getting attributes and adding to name            
			$series = $wooproduct->get_attribute('pa_series');
			$type = $wooproduct->get_attribute('pa_itemclass2');
			$resolution = $wooproduct->get_attribute('pa_recorder-max-resolution');
			$channels = $wooproduct->get_attribute('pa_recorder-channels');
			$sata = $wooproduct->get_attribute('pa_sata-ports');
			

			//IGNORING UNIVIEW
			if($series == 'Uniview'){continue;}

			//some cleaning
			if($sata == '16 SATA PORTS &amp; Up'){$sata = '16X+ SATA PORTS';}
			if($series == 'ENS Emerald'){$series = 'Emerald';}
			if($series == 'H Series'){$series = 'ENS-H';}
			if($resolution == '12MP &amp; Up'){$resolution = '12MP+';}
			if($channels == '256CH &amp; Up'){$channels = '256CH+';}

			//final output
			$newtitle =($newtitle . $series . ', ' . $type . ', ' . $resolution . ', ' . $channels . ', ' . $sata);


			//output, comment out undesired
			update_title($newtitle, $id);


			//logging info
			$newtitle = $id . ': ' . $newtitle;
			//logger($newtitle, 1);
		}
		wp_reset_postdata();
	}
}

function ens_rename_accessories()
{
	//logger('******ACCESSORY RENAMER******', 1);

	//The query
	$wordpressproducts = new WP_Query( array(
		'post_type'      => array('product'),
		'post_status'    => 'publish',
		'posts_per_page' => -1,
		'tax_query' => array( array(
			'taxonomy'        => 'pa_itemclass1',
			'field'           => 'slug',
			'terms'         => array('accessory'),
			'operator' => 'IN',
		) ),

	) );

	if($wordpressproducts->have_posts())
	{
		while($wordpressproducts->have_posts())
		{
			$wordpressproducts->the_post();
			$id = $wordpressproducts->post->ID;
			$wooproduct = wc_get_product($id);

			//getting sku and making new name
			$skustring = get_post_meta($id, '_sku', true);
			$newtitle = ($skustring . ' || ');

			//getting attributes
			$class1 = $wooproduct->get_attribute('pa_itemclass1');
			$class2 = $wooproduct->get_attribute('pa_itemclass2');
			$capacity = $wooproduct->get_attribute('pa_storage-capacity');
			$lan = $wooproduct->get_attribute('pa_switch-uplink-ports');
			$poe = $wooproduct->get_attribute('pa_switch-poe-ports');

			//string / term cleaning
			if($class2 == 'others'){$class2 = 'Other';}//might switch this to speciality
			if($class2 == 'bodytemp'){$class2 = 'Bodytemp';}
			if($class2 == 'brackets'){$class2 = 'Bracket';}
			if($class2 == 'DisplayAcc'){$class2 = 'Display';}
			if($class2 == 'ToolsTest'){$class2 = 'Tools/Tester';}
			if($class2 == 'VideoBalun'){$class2 = 'Video Balun';}
			if($class2 == 'lockbox'){$class2 = 'Lockbox';}
			if($class2 == 'PowerSup'){$class2 = 'Power Supply';}
			if($class2 == 'wiretie'){$class2 = 'Wire Tie';}
			if($class2 == 'whtbrdrlt'){$class2 = 'Smart Whiteboard';}
			if($class2 == 'cablemgmt'){$class2 = 'Cable Management';}
			if($class2 == 'poeacc'){$class2 = 'PoE Accessory';}
			if($class2 == 'servercab'){$class2 = 'Server Cabinet';}
			if($class2 == 'mobile'){$class2 = 'Mobile Solution';}
			if($class2 == 'conduit'){$class2 = 'Conduit';}
			if($class2 == 'keyboard'){$class2 = 'Keyboard';}
			if($class2 == 'accessacc'){$class2 = 'Access Accessory';}
			if($class2 == 'camhousing'){$class2 = 'Camera Housing';}
			if($class2 == 'sdcard'){$class2 = 'SD Card';}
			if($class2 == 'videoext'){$class2 = 'Video Extender';}
			if($class2 == 'lens'){$class2 = 'Lens';}

			//special for HDD
			if($class2 == 'HDD')
			{
				$newtitle = ($newtitle . $class2 . ', ' . $capacity);
			}
			else if($class2 == 'Switch')
			{
				$newtitle = ($newtitle . $class2 . ', ' . $lan . ' Uplink Ports, ' . $poe);
			}
			else
			{
				$newtitle = ($newtitle . $class1 . ', ' . $class2);
			}

			update_title($newtitle, $id);



			//logging info
			$newtitle = $id . ': ' . $newtitle;
			//logger($newtitle, 1);
		}
		wp_reset_postdata();
	}

}

function ens_rename_networking()
{
	//logger('******NETWORKING RENAMER******', 1);

	//The query
	$wordpressproducts = new WP_Query( array(
		'post_type'      => array('product'),
		'post_status'    => 'publish',
		'posts_per_page' => -1,
		'tax_query' => array( array(
			'taxonomy'        => 'pa_itemclass1',
			'field'           => 'slug',
			'terms'         => array('Networking'),
			'operator' => 'IN',
		) ),

	) );

	if($wordpressproducts->have_posts())
	{
		while($wordpressproducts->have_posts())
		{
			$wordpressproducts->the_post();
			$id = $wordpressproducts->post->ID;
			$wooproduct = wc_get_product($id);

			//getting sku and making new name
			$skustring = get_post_meta($id, '_sku', true);
			$newtitle = ($skustring . ' || ');

			//getting attributes
			$series = $wooproduct->get_attribute('pa_series');
			$class1 = $wooproduct->get_attribute('pa_itemclass1');
			$class2 = $wooproduct->get_attribute('pa_itemclass2');


			//string / term cleaning
			if($class2 == 'mesh'){$class2 = 'Mesh Router';}
			if($class2 == 'router'){$class2 = 'Router';}
			if($class2 == 'accpoint'){$class2 = 'Access Point';} 
			if($class2 == 'poeacc'){$class2 = 'PoE Accessory';}
			if($class2 == 'WIFI'){$class2 = 'Wireless Bridge';}
			if($class2 == 'others'){$class2 = 'Accessory';}

			//final output
			$newtitle =($newtitle . $series . ', ' . $class1 . ', ' . $class2);

			update_title($newtitle, $id);

			//logging info
			$newtitle = $id . ': ' . $newtitle;
			//logger($newtitle, 1);
		}
		wp_reset_postdata();
	}

}

function ens_rename_smartalarm()
{
    //The query
    $wordpressproducts = new WP_Query( array(
        'post_type'      => array('product'),
        'post_status'    => 'publish',
        'posts_per_page' => -1,
        'tax_query' => array( array(
            'taxonomy'        => 'pa_itemclass1',
            'field'           => 'slug',
            'terms'         => array('smartalarm'),
            'operator' => 'IN',
        ) ),

    ) );

    if($wordpressproducts->have_posts())
    {
        while($wordpressproducts->have_posts())
        {
            $wordpressproducts->the_post();
            $id = $wordpressproducts->post->ID;
            $wooproduct = wc_get_product($id);

            //getting sku and making new name
            $skustring = get_post_meta($id, '_sku', true);
            $newtitle = ($skustring . ' || ');

            //getting attributes
            $series = $wooproduct->get_attribute('pa_series');
            $class1 = $wooproduct->get_attribute('pa_itemclass1');
            $class2 = $wooproduct->get_attribute('pa_itemclass2');


            //string / term cleaning
            if($class1 == 'smartalarm'){$class1 = 'Smart Alarm';}
            if($class2 == 'waterleak'){$class2 = 'Water Leak Prevention';}
            if($class2 == 'intruprot'){$class2 = 'Intrusion Protection';}
            if($class2 == 'comandprod'){$class2 = 'Comfort & Productivity';}

            //final output
            $newtitle =($newtitle . $series . ', ' . $class1 . ', ' . $class2);

            update_title($newtitle, $id);

            //logging info
            $newtitle = $id . ': ' . $newtitle;
            //logger($newtitle, 1);
        }
        wp_reset_postdata();
    }
}

function ens_add_description_ipc()
{
	//logger('******IPC DESCRIPTION******', 1);
	//logger("******IPC MISSING ATTRIBUTES******", 2);
	$wordpressproducts = new WP_Query(array(
		'post_type'		=> array('product'),
		'post_status' => 'publish',
		'posts_per_page'=> -1,
		'tax_query'		=> array(
			array(
			'taxonomy'	=> 'pa_itemclass2',
			'field'		=> 'slug',
			'terms'		=> array('IPCAM'),
			'operator' 	=> 'IN'),
			array(
				'taxonomy' => 'product_cat',
				'field_id' => 'term_id',
				'terms' => 192,
				'operator' => 'NOT IN'
			)
		),
	));
	if($wordpressproducts->have_posts())
	{
		while($wordpressproducts->have_posts())
		{
			$wordpressproducts->the_post();
			$id = $wordpressproducts->post->ID;
			$wooproduct = wc_get_product($id);


			$series = $wooproduct->get_attribute('pa_series');
			$housing = $wooproduct->get_attribute('pa_camera-style');
			$resolution = $wooproduct->get_attribute('pa_camera-resolution');
			$lens = $wooproduct->get_attribute('pa_camera-lens');
			$nv = $wooproduct->get_attribute('pa_camera-night-vision');
			$wdr = $wooproduct->get_attribute('pa_camera-wdr');
			$water = $wooproduct->get_attribute('pa_waterproof');
			$ndaa = $wooproduct->get_attribute('pa_ndaa');
			$vca = $wooproduct->get_attribute('pa_vca-features');
			$hardware = $wooproduct->get_attribute('pa_hardware-features');

			//IGNORING UNIVIEW
			if($series == 'Uniview'){continue;}

			//string cleaning
			if($ndaa == 'NDAA'){$ndaa = 'Yes';}
			if($resolution == '12MP &amp; Up'){$resolution = '12MP+';}
			if($lens == '30X &amp; Up PTZ'){$lens = '30X Plus PTZ';}
			if($lens == '20X-30X PTZ'){$lens = '20X Plus PTZ';}

			$newdesc = (
				'- Housing: ' . $housing . "\n" .
				'- Resolution: ' . $resolution . "\n" .
				'- Lens Category: ' . $lens . "\n" .
				'- Night Vision: ' . $nv . "\n" .
				'- WDR: ' . $wdr . "\n" .
				'- Water Resistance Rating : ' . $water . "\n" .
				'- NDAA Compliant: ' . $ndaa . "\n" .
				'- VCA Features: ' . $vca . "\n" .
				'- Hardware Features: ' . $hardware
			);

			update_short_description($newdesc, $id);

			//adding details for make debugging easier
			$skustring = get_post_meta($id, '_sku', true);
			$newdesc = (
				"-----------------------------------\n" .
				$skustring . "\n" .
				$newdesc . "\n" .
				"-----------------------------------\n"
			);
			//logger($newdesc, 1);

			//missing attributes
			$missatt = ($skustring . " is missing: ");

			if($housing == ""){$missatt = ($missatt . 'Housing, ');}
			if($resolution == ""){$missatt = ($missatt . 'Resolution, ');}
			if($lens == ""){$missatt = ($missatt . 'Lens, ');}
			if($nv == ""){$missatt = ($missatt . 'Night Vision, ');}
			if($wdr == ""){$missatt = ($missatt . 'WDR, ');}
			if($water == ""){$missatt = ($missatt . 'Water Resistance, ');}
			if($ndaa == ""){$missatt = ($missatt . 'NDAA, ');}
			//if($vca == ""){$missatt = ($missatt . 'VCA Features, ');} LEAVING OUT FOR NOW WHILE FIXING ATTRIBUTES
			//if($hardware == ""){$missatt = ($missatt . 'Hardware Features, ');} LEAVING OUT FOR NOW WHILE FIXING ATTRIBUTES


			//ignoring non missing Sku
			if(substr($missatt, -2) != ': ')
			{
				//logger($missatt, 2);
				$payload[0] = $missatt;
				ens_gs_write($payload);
			}

		}
		wp_reset_postdata();
	}
}

function ens_add_description_coax()
{
	//logger('******COAX DESCRIPTION******', 1);
	//logger("******COAX MISSING ATTRIBUTES******", 2);
	$wordpressproducts = new WP_Query(array(
		'post_type'		=> array('product'),
		'post_status' => 'publish',
		'posts_per_page'=> -1,
		'tax_query'		=> array(
			array(
			'taxonomy'	=> 'pa_itemclass2',
			'field'		=> 'slug',
			'terms'		=> array('COAXCAM'),
			'operator' 	=> 'IN'),
			array(
			'taxonomy' => 'product_cat',
			'field_id' => 'term_id',
			'terms' => 192,
			'operator' => 'NOT IN'
			)
		),
	));
	if($wordpressproducts->have_posts())
	{
		while($wordpressproducts->have_posts())
		{
			$wordpressproducts->the_post();
			$id = $wordpressproducts->post->ID;
			$wooproduct = wc_get_product($id);


			$series = $wooproduct->get_attribute('pa_series');
			$housing = $wooproduct->get_attribute('pa_camera-style');
			$resolution = $wooproduct->get_attribute('pa_camera-resolution');
			$signal = $wooproduct->get_attribute('pa_signal');
			$lens = $wooproduct->get_attribute('pa_camera-lens');
			$nv = $wooproduct->get_attribute('pa_camera-night-vision');
			$wdr = $wooproduct->get_attribute('pa_camera-wdr');
			$water = $wooproduct->get_attribute('pa_waterproof');
			$ndaa = $wooproduct->get_attribute('pa_ndaa');
			$hardware = $wooproduct->get_attribute('pa_hardware-features');


			//IGNORING UNIVIEW
			if($series == 'Uniview'){continue;}


			//string cleaning
			if($ndaa == 'NDAA'){$ndaa = 'Yes';}
			if($resolution == '12MP &amp; Up'){$resolution = '12MP+';}
			if($lens == '30X &amp; Up PTZ'){$lens = '30X+ PTZ';}


			$newdesc = (
				'- Housing: ' . $housing . "\n" .
				'- Resolution: ' . $resolution . "\n" .
				'- Signal: ' . $signal . "\n" .
				'- Lens Category: ' . $lens . "\n" .
				'- Night Vision: ' . $nv . "\n" .
				'- WDR: ' . $wdr . "\n" .
				'- Water Resistance Rating : ' . $water . "\n" .
				'- NDAA Compliant: ' . $ndaa . "\n" .
				'- Hardware Features: ' . $hardware
			);

			update_short_description($newdesc, $id);


			//adding details to make debugging easier
			$skustring = get_post_meta($id, '_sku', true);
			$newdesc = (
				"-----------------------------------\n" .
				$skustring . "\n" .
				$newdesc . "\n" .
				"-----------------------------------\n"
			);
			//logger($newdesc, 1);


			//missing attributes
			$missatt = ($skustring . " is missing: ");

			if($housing == ""){$missatt = ($missatt . 'Housing, ');}
			if($resolution == ""){$missatt = ($missatt . 'Resolution, ');}
			if($signal == ""){$missatt = ($missatt . 'Signal, ');}
			if($lens == ""){$missatt = ($missatt . 'Lens, ');}
			if($nv == ""){$missatt = ($missatt . 'Night Vision, ');}
			if($wdr == ""){$missatt = ($missatt . 'WDR, ');}
			if($water == ""){$missatt = ($missatt . 'Water Resistance, ');}
			if($ndaa == ""){$missatt = ($missatt . 'NDAA, ');}
			//if($hardware == ""){$missatt = ($missatt . 'Hardware Features, ');} LEAVING OUT FOR NOW WHILE FIXING ATTRIBUTES

			//ignoring non missing Sku
			if(substr($missatt, -2) != ': ')
			{
				//logger($missatt, 2);
				$payload[0] = $missatt;
				ens_gs_write($payload);
			}
		}
		wp_reset_postdata();
	}
}


function ens_add_description_nvr()
{
	//logger('******NVR DESCRIPTION******', 1);
	//logger("******NVR MISSING ATTRIBUTES******", 2);

	//query
	$wordpressproducts = new WP_Query( array(
		'post_type'		=> array('product'),
		'post_status' => 'publish',
		'posts_per_page'=> -1,
		'tax_query'		=> array(array(
			'taxonomy'	=> 'pa_itemclass2',
			'field'		=> 'slug',
			'terms'		=> array('NVR'),
			'operator' 	=> 'IN'),
		array(
				'taxonomy' => 'product_cat',
				'field_id' => 'term_id',
				'terms' => 192,
				'operator' => 'NOT IN'
			)
	),
	));

	//loop
	if($wordpressproducts->have_posts())
	{
		while($wordpressproducts->have_posts())
		{
			$wordpressproducts->the_post();
			$id = $wordpressproducts->post->ID;
			$wooproduct = wc_get_product($id);


			$series = $wooproduct->get_attribute('pa_series');
			$resolution = $wooproduct->get_attribute('pa_recorder-max-resolution');
			$channels = $wooproduct->get_attribute('pa_recorder-channels');
			$poe = $wooproduct->get_attribute('pa_recorder-poe');
			$lan = $wooproduct->get_attribute('pa_switch-uplink-ports');
			$sata = $wooproduct->get_attribute('pa_sata-ports');
			$output = $wooproduct->get_attribute('pa_recorder-video-output');
			$ndaa = $wooproduct->get_attribute('pa_ndaa');
			$vca = $wooproduct->get_attribute('pa_vca-features');
			$hardware = $wooproduct->get_attribute('pa_recorder-hardware-features');


			//IGNORING UNIVIEW
			if($series == 'Uniview'){continue;}


			//string cleaning
			if($ndaa == 'NDAA'){$ndaa = 'Yes';}
			if($resolution == '12MP &amp; Up'){$resolution = '12MP+';}
			if($channels == '256CH &amp; Up'){$channels = '256CH+';}
			if($sata == '16 SATA PORTS &amp; Up'){$sata = '16X+ SATA PORTS';}


			$newdesc = (
				'- Max Recording Resolution: ' . $resolution . "\n" .
				'- Channels: ' . $channels . "\n" .
				'- Built-in POE : ' . $poe . "\n" .
				'- LAN Ports: ' . $lan . "\n" .
				'- SATA: ' . $sata . "\n" .
				'- Video Outputs: ' . $output . "\n" .
				'- NDAA Compliant: ' . $ndaa . "\n" .
				'- VCA Features: ' . $vca . "\n" .
				'- Hardware Features: ' . $hardware
			);

			update_short_description($newdesc, $id);


			//adding details for make debugging easier
			$skustring = get_post_meta($id, '_sku', true);
			$newdesc = (
				"-----------------------------------\n" .
				$skustring . "\n" .
				$newdesc . "\n" .
				"-----------------------------------\n"
			);
			//logger($newdesc, 1);


			//missing attributes
			$missatt = ($skustring . " is missing: ");

			if($resolution == ""){$missatt = ($missatt . 'Resolution, ');}
			if($channels == ""){$missatt = ($missatt . 'Channels, ');}
			if($poe == ""){$missatt = ($missatt . 'PoE, ');}
			if($lan == ""){$missatt = ($missatt . 'LAN, ');}
			if($sata == ""){$missatt = ($missatt . 'SATA, ');}
			if($output == ""){$missatt = ($missatt . 'Output, ');}
			if($ndaa == ""){$missatt = ($missatt . 'NDAA, ');}
			//if($vca == ""){$missatt = ($missatt . 'VCA Features, ');} LEAVING OUT FOR NOW WHILE FIXING ATTRIBUTES
			//if($hardware == ""){$missatt = ($missatt . 'Hardware Features, ');} LEAVING OUT FOR NOW WHILE FIXING ATTRIBUTES

			//ignoring non missing Sku
			if(substr($missatt, -2) != ': ')
			{
				//logger($missatt, 2);
				$payload[0] = $missatt;
				ens_gs_write($payload);
			}
		}
		wp_reset_postdata();
	}
}

function ens_add_description_dvr()
{
	//logger('******DVR DESCRIPTION******', 1);
	//logger("******DVR MISSING ATTRIBUTES******", 2);

	//query
	$wordpressproducts = new WP_Query( array(
		'post_type'		=> array('product'),
		'post_status' => 'publish',
		'posts_per_page'=> -1,
		'tax_query'		=> array(array(
			'taxonomy'	=> 'pa_itemclass2',
			'field'		=> 'slug',
			'terms'		=> array('DVR'),
			'operator' 	=> 'IN'),
					array(
				'taxonomy' => 'product_cat',
				'field_id' => 'term_id',
				'terms' => 192,
				'operator' => 'NOT IN'
			)
	),
	));

	//loop
	if($wordpressproducts->have_posts())
	{
		while($wordpressproducts->have_posts())
		{
			$wordpressproducts->the_post();
			$id = $wordpressproducts->post->ID;
			$wooproduct = wc_get_product($id);


			$series = $wooproduct->get_attribute('pa_series');
			$resolution = $wooproduct->get_attribute('pa_recorder-max-resolution');
			$channels = $wooproduct->get_attribute('pa_recorder-channels');
			$lan = $wooproduct->get_attribute('pa_switch-uplink-ports');
			$sata = $wooproduct->get_attribute('pa_sata-ports');
			$output = $wooproduct->get_attribute('pa_recorder-video-output');
			$ndaa = $wooproduct->get_attribute('pa_ndaa');
			$hardware = $wooproduct->get_attribute('pa_recorder-hardware-features');


			//IGNORING UNIVIEW
			if($series == 'Uniview'){continue;}

			//string cleaning
			if($ndaa == 'NDAA'){$ndaa = 'Yes';}
			if($resolution == '12MP &amp; Up'){$resolution = '12MP+';}
			if($channels == '256CH &amp; Up'){$channels = '256CH+';}
			if($sata == '16 SATA PORTS &amp; Up'){$sata = '16X+ SATA PORTS';}


			$newdesc = (
				'- Max Recording Resolution: ' . $resolution . "\n" .
				'- Channels: ' . $channels . "\n" .
				'- LAN Ports: ' . $lan . "\n" .
				'- SATA: ' . $sata . "\n" .
				'- Video Outputs: ' . $output . "\n" .
				'- NDAA Compliant: ' . $ndaa . "\n" .
				'- Hardware Features: ' . $hardware
			);

			update_short_description($newdesc, $id);


			//adding details for make debugging easier
			$skustring = get_post_meta($id, '_sku', true);
			$newdesc = (
				"-----------------------------------\n" .
				$skustring . "\n" .
				$newdesc . "\n" .
				"-----------------------------------\n"
			);
			//logger($newdesc, 1);


			//missing attributes
			$missatt = ($skustring . " is missing: ");

			if($resolution == ""){$missatt = ($missatt . 'Resolution, ');}
			if($channels == ""){$missatt = ($missatt . 'Channels, ');}
			if($lan == ""){$missatt = ($missatt . 'LAN, ');}
			if($sata == ""){$missatt = ($missatt . 'SATA, ');}
			if($output == ""){$missatt = ($missatt . 'Output, ');}
			if($ndaa == ""){$missatt = ($missatt . 'NDAA, ');}
			//if($vca == ""){$missatt = ($missatt . 'VCA Features, ');} LEAVING OUT FOR NOW WHILE FIXING ATTRIBUTES


			//ignoring non missing Sku
			if(substr($missatt, -2) != ': ')
			{
				//logger($missatt, 2);
				$payload[0] = $missatt;
				ens_gs_write($payload);
			}
		}
		wp_reset_postdata();
		//emailalert(1);
		//emailalert(2);
	}
}

//update title
function update_title($title, $id)
{
	$payload = array(
		'ID' => $id,
		'post_title' => $title
	);
	wp_update_post( $payload );
}
//update short description
function update_short_description($desc, $id)
{
	$payload = array(
		'ID' => $id,
		'post_excerpt' => $desc
	);
	wp_update_post($payload);
}
//emails log, $team: 1 = dev, 2 = Product Team
function emailalert($team)
{
	$product = array('robin.h@enssecurity.com', 'alex.w@enssecurity.com');
	$dev = array('robin.h@enssecurity.com');

	//determinig location, leaving with else as this because not sure if were going to add more options
	if($team == 2)
	{
	wp_mail($product, 'MISSING ATTRIBUTES LOG: ' . date("d-m-Y"), ' ', ' ', array((__DIR__ . '/missingattributes.txt')));
	}
	else if($team == 1)
	{
	wp_mail($dev, 'RENAMER LOG: ' . date("d-m-Y"), ' ', ' ', array((__DIR__ . '/prerrorlog.txt')));
	}
	else
	{
	wp_mail($dev, 'RENAMER LOG: ' . date("d-m-Y"), ' ', ' ', array((__DIR__ . '/prerrorlog.txt')));
	}

	clearlog($team);
}

//debug logger - $file: 1 = general log, 2 = missing attributes
function logger($content, $team)
{

	//determinig location, leaving with else because not sure if were going to add more options
	if($team == 2)
	{
		$el = fopen(__DIR__ . '/missingattributes.txt', 'a');
	}
	else if($team == 1)
	{
		$el = fopen(__DIR__ . '/prerrorlog.txt', 'a');
	}
	else
	{
		$el = fopen(__DIR__ . '/prerrorlog.txt', 'a');
	}


	fwrite($el, $content . "\n");
	fclose($el);
}

//clears logger - $file: 1 = general log, 2 = missing attributes
//currently only called by emaillog()
function clearlog($team)
{
	//determinig location, leaving with else as this because not sure if were going to add more options
	if($team == 2)
	{
		$el = fopen(__DIR__ . '/missingattributes.txt', 'w');
	}
	else if($team == 1)
	{
		$el = fopen(__DIR__ . '/prerrorlog.txt', 'w');
	}
	else
	{
		$el = fopen(__DIR__ . '/prerrorlog.txt', 'w');
	}

	fclose($el);

}

function ens_clean_description()
{
	//logger('*********Cleaning Description*********', 1);


	//The query
	$wordpressproducts = new WP_Query( array(
		'post_type'      => 'product',
		'post_status' => 'publish',
		'posts_per_page'=> -1,
    ));
	if($wordpressproducts->have_posts())
	{ 
		while($wordpressproducts->have_posts())
		{
			$wordpressproducts->the_post();
			$id = $wordpressproducts->post->ID;
            logging('called loop', 1);
            logging($id, 1);

			if($id == 42725)
			{
                logging('found prod', 1);
				$desc = $wordpressproducts ->post_content();

				//logger($desc, 1);

				$chars = str_split($desc);
				$newdesc = '';

				foreach ($chars as $char)
				{
					if($char == ''){$char = '-';}
					$newdesc = $newdesc . $char;
				}

				$payload = array(
					'ID' => $id,
					'post_excerpt' => $newdesc
				);
				wp_update_post($payload);
                //logger($newdesc, 1);
			}

		}
		wp_reset_postdata();
	}
}
?>