<?php
/**
* Plugin Name: Product Manipulator
* Description: Renames product pages to SKU || [Attributes]. HOOKS: ens_rename_cameras_activation, ens_rename_recorders_activation, ens_rename_accessories_activation, ens_add_description_ipc_activation, ens_add_description_coax_activation, ens_add_description_nvr_activation, ens_add_description_dvr_activation
* Version: 3.4
* Author: RVH
*/

add_action('ens_rename_cameras_activation', 'ens_rename_cameras');
add_action('ens_rename_recorders_activation','ens_rename_recorders');
add_action('ens_rename_accessories_activation', 'ens_rename_accessories');
add_action('ens_add_description_ipc_activation', 'ens_add_description_ipc');
add_action('ens_add_description_coax_activation', 'ens_add_description_coax');
add_action('ens_add_description_nvr_activation', 'ens_add_description_nvr');
add_action('ens_add_description_dvr_activation', 'ens_add_description_dvr');

//register_activation_hook(__FILE__, 'ens_add_description_ipc');
//register_activation_hook(__FILE__, 'ens_add_description_nvr');
//register_activation_hook(__FILE__, 'ens_rename_accessories');

//script global vars
$enspoststatus = 'publish'; //swap between 'publish' and 'draft'

function ens_rename_cameras()
{
	logger('******CAMERA RENAMER******');

	//The query
	$wordpressproducts = new WP_Query( array(
		'post_type'      => array('product'),
		'post_status'    => $enspoststatus,
		'posts_per_page' => -1,
		'tax_query' => array( array(
			'taxonomy'        => 'pa_itemclass1',
			'field'           => 'slug',
			'terms'         => array('cameras'),
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
			if($series == 'Uniview'){break;}

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
			logger($newtitle);
        }
		wp_reset_postdata();
        emailalert();
	}
}

function ens_rename_recorders()
{
	logger('******RECORDER RENAMER ACTIVATED******');

	//The query
	$wordpressproducts = new WP_Query( array(
		'post_type'      => array('product'),
		'post_status'    => $enspoststatus,
		'posts_per_page' => -1,
		'tax_query' => array( array(
			'taxonomy'        => 'pa_itemclass1',
			'field'           => 'slug',
			'terms'         => array('recorders'),
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
			if($series == 'Uniview'){break;}

			//some cleaning
			if($sata == '16 SATA PORTS &amp; Up'){$sata = '16 SATA PORTS+';}
			if($series == 'ENS Emerald'){$series = 'Emerald';}
			if($series == 'H Series'){$series = 'ENS-H';}
			if($resolution == '12MP &amp; Up'){$resolution = '12MP+';}
			if($channels == '256CH &amp; Up'){$channels = '256CH+';}

			//final output
			$newtitle =($newtitle . $series . ', ' . $type . ', ' . $resolution . ', ' . $channels . ', ' . $sata);


			//output, comment out undesired
			update_title($newtitle, $id);
			logger($newtitle);
        }
		wp_reset_postdata();
        emailalert();
	}
}

function ens_rename_accessories()
{
	logger('******ACCESSORY RENAMER ACTIVATED******');

	//The query
	$wordpressproducts = new WP_Query( array(
		'post_type'      => array('product'),
		'post_status'    => $enspoststatus,
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
			if($class2 == 'ToolsTest'){$class2 = 'Testing Tool';}
			if($class2 == 'VideoBalun'){$class2 = 'Video Balun';}
			if($class2 == 'lockbox'){$class2 = 'Lockbox';}
			if($class2 == 'PowerSup'){$class2 = 'Power Supply';}

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
			logger($newtitle);
		}
		wp_reset_postdata();
		emailalert();
	}

}

function ens_add_description_ipc()
{
	logger('******IPC DESCRIPTION******');
	$wordpressproducts = new WP_Query(array(
		'post_type'		=> array('product'),
		'post_status'	=> $enspoststatus,
		'posts_per_page'=> -1,
		'tax_query'		=> array(array(
				'taxonomy'	=> 'pa_itemclass2',
				'field'		=> 'slug',
				'terms'		=> array('IPCAM'),
				'operator' 	=> 'IN',
		)),
	));
	if($wordpressproducts->have_posts())
	{
		while($wordpressproducts->have_posts())
		{
			$wordpressproducts->the_post();
			$id = $wordpressproducts->post->ID;
			$wooproduct = wc_get_product($id);


			$housing = $wooproduct->get_attribute('pa_camera-style');
			$resolution = $wooproduct->get_attribute('pa_camera-resolution');
			$lens = $wooproduct->get_attribute('pa_camera-lens');
			$nv = $wooproduct->get_attribute('pa_camera-night-vision');
			$wdr = $wooproduct->get_attribute('pa_camera-wdr');
			$water = $wooproduct->get_attribute('pa_waterproof');
			$ndaa = $wooproduct->get_attribute('pa_ndaa');
			$vca = $wooproduct->get_attribute('pa_vca-features');
			$hardware = $wooproduct->get_attribute('pa_hardware-features');

			//string cleaning
			if($ndaa == 'NDAA'){$ndaa = 'Yes';}
			if($resolution == '12MP &amp; Up'){$resolution = '12MP+';}
			if($lens == '30X &amp; Up PTZ'){$lens = '30X+ PTZ';}

			$newdesc = (
				'- Housing: ' . $housing . "\n" .
				'- Resolution: ' . $resolution . "\n" .
				'- Lens: ' . $lens . "\n" .
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
			logger($newdesc);
		}
		wp_reset_postdata();
		emailalert();
	}
}

function ens_add_description_coax()
{
	logger('******COAX DESCRIPTION******');
	$wordpressproducts = new WP_Query(array(
		'post_type'		=> array('product'),
		'post_status'	=> $enspoststatus,
		'posts_per_page'=> -1,
		'tax_query'		=> array(array(
				'taxonomy'	=> 'pa_itemclass2',
				'field'		=> 'slug',
				'terms'		=> array('COAXCAM'),
				'operator' 	=> 'IN',
		)),
	));
	if($wordpressproducts->have_posts())
	{
		while($wordpressproducts->have_posts())
		{
			$wordpressproducts->the_post();
			$id = $wordpressproducts->post->ID;
			$wooproduct = wc_get_product($id);


			$housing = $wooproduct->get_attribute('pa_camera-style');
			$resolution = $wooproduct->get_attribute('pa_camera-resolution');
			$signal = $wooproduct->get_attribute('pa_signal');
			$lens = $wooproduct->get_attribute('pa_camera-lens');
			$nv = $wooproduct->get_attribute('pa_camera-night-vision');
			$wdr = $wooproduct->get_attribute('pa_camera-wdr');
			$water = $wooproduct->get_attribute('pa_waterproof');
			$ndaa = $wooproduct->get_attribute('pa_ndaa');
			$hardware = $wooproduct->get_attribute('pa_hardware-features');


			//string cleaning
			if($ndaa == 'NDAA'){$ndaa = 'Yes';}
			if($resolution == '12MP &amp; Up'){$resolution = '12MP+';}
            if($lens == '30X &amp; Up PTZ'){$lens = '30X+ PTZ';}


			$newdesc = (
				'- Housing: ' . $housing . "\n" .
				'- Resolution: ' . $resolution . "\n" .
				'- Signal: ' . $signal . "\n" .
				'- Lens: ' . $lens . "\n" .
				'- Night Vision: ' . $nv . "\n" .
				'- WDR: ' . $wdr . "\n" .
				'- Water Resistance Rating : ' . $water . "\n" .
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
			logger($newdesc);
		}
		wp_reset_postdata();
		emailalert();
	}
}


function ens_add_description_nvr()
{
	logger('******NVR DESCRIPTION******');

	//query
	$wordpressproducts = new WP_Query( array(
		'post_type'		=> array('product'),
		'post_status'	=> $enspoststatus,
		'posts_per_page'=> -1,
		'tax_query'		=> array(array(
				'taxonomy'	=> 'pa_itemclass2',
				'field'		=> 'slug',
				'terms'		=> array('NVR'),
				'operator' 	=> 'IN',
		)),
	));

	//loop
	if($wordpressproducts->have_posts())
	{
		while($wordpressproducts->have_posts())
		{
			$wordpressproducts->the_post();
			$id = $wordpressproducts->post->ID;
			$wooproduct = wc_get_product($id);


			$resolution = $wooproduct->get_attribute('pa_recorder-max-resolution');
			$channels = $wooproduct->get_attribute('pa_recorder-channels');
			$poe = $wooproduct->get_attribute('pa_recorder-poe');
			$lan = $wooproduct->get_attribute('pa_switch-uplink-ports');
			$sata = $wooproduct->get_attribute('pa_sata-ports');
			$output = $wooproduct->get_attribute('pa_recorder-video-output');
			$ndaa = $wooproduct->get_attribute('pa_ndaa');
			$smart = $wooproduct->get_attribute('pa_vca-features');
			$hardware = $wooproduct->get_attribute('pa_recorder-hardware-features');


			//string cleaning
			if($ndaa == 'NDAA'){$ndaa = 'Yes';}
			if($resolution == '12MP &amp; Up'){$resolution = '12MP+';}
			if($channels == '256CH &amp; Up'){$channels = '256CH+';}


			$newdesc = (
			'- Max Recording Resolution: ' . $resolution . "\n" .
			'- Channels: ' . $channels . "\n" .
			'- Built-in POE : ' . $poe . "\n" .
			'- LAN Ports: ' . $lan . "\n" .
			'- SATA: ' . $sata . "\n" .
			'- Video Outputs: ' . $output . "\n" .
			'- NDAA Compliant: ' . $ndaa . "\n" .
			'- VCA Features: ' . $smart . "\n" .
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
			logger($newdesc);
		}
		wp_reset_postdata();
		emailalert();
	}
}

function ens_add_description_dvr()
{
	logger('******DVR DESCRIPTION******');

	//query
	$wordpressproducts = new WP_Query( array(
		'post_type'		=> array('product'),
		'post_status'	=> $enspoststatus,
		'posts_per_page'=> -1,
		'tax_query'		=> array(array(
				'taxonomy'	=> 'pa_itemclass2',
				'field'		=> 'slug',
				'terms'		=> array('DVR'),
				'operator' 	=> 'IN',
		)),
	));

	//loop
	if($wordpressproducts->have_posts())
	{
		while($wordpressproducts->have_posts())
		{
			$wordpressproducts->the_post();
			$id = $wordpressproducts->post->ID;
			$wooproduct = wc_get_product($id);


			$resolution = $wooproduct->get_attribute('pa_recorder-max-resolution');
			$channels = $wooproduct->get_attribute('pa_recorder-channels');
			$lan = $wooproduct->get_attribute('pa_switch-uplink-ports');
			$sata = $wooproduct->get_attribute('pa_sata-ports');
			$output = $wooproduct->get_attribute('pa_recorder-video-output');
			$ndaa = $wooproduct->get_attribute('pa_ndaa');
			$hardware = $wooproduct->get_attribute('pa_recorder-hardware-features');

			//string cleaning
			if($ndaa == 'NDAA'){$ndaa = 'Yes';}
			if($resolution == '12MP &amp; Up'){$resolution = '12MP+';}
			if($channels == '256CH &amp; Up'){$channels = '256CH+';}


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
			logger($newdesc);
		}
		wp_reset_postdata();
		emailalert();
	}
}

//updates table with new title
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
//emails log
function emailalert()
{
	$recipients = array('redacted@email.com');
    wp_mail($recipients, 'RENAMER LOG: ' . date("d-m-Y"), ' ', ' ', array((__DIR__ . '/prerrorlog.txt')));

    clearlog();
}

//debug logger
function logger($content)
{
	$el = fopen(__DIR__ . '/prerrorlog.txt', 'a');
	fwrite($el, $content . "\n");
	fclose($el);
}

function clearlog()
{
	$el = fopen(__DIR__ . '/prerrorlog.txt', 'w');
    fclose($el);
}
?>