<?php
/*
Plugin Name: WP-Yadis
Plugin URI: http://wordpress.org/extend/plugins/yadis/
Description: Use your wordpress blog URL as an OpenID by delegating to a third party provider.
Version: trunk
Author: Will Norris
Author URI: http://willnorris.com/
*/

/* register with Wordpress */
if (isset($wp_version)) {
    add_action('init', array('Yadis', 'handleXrdsRequest'));
    add_action('wp_head', array('Yadis', 'insert_meta_tags'), 5);
    add_action('admin_menu', array('Yadis', 'menu'));

	add_action('parse_query', array('Yadis', 'xrds_xml'));
	add_filter('rewrite_rules_array', array('Yadis', 'rewrite_rules'));
	add_filter('query_vars', array('Yadis', 'query_vars'));
	add_action('admin_head-options_page_global-yadis-options', array('Yadis', 'admin_head'));
}

class Yadis {

	/**
	 * Insert meta and link tags into template head.
	 */
	function insert_meta_tags() {
		global $wp_rewrite;

		if (is_home()) {
			echo '
					<meta http-equiv="X-XRDS-Location" content="'.get_option('home').($wp_rewrite->using_mod_rewrite_permalinks() ? '' : '/index.php').'/xrds" />';

			$xrdsProviders = get_option('xrds_services');
			if ($provider = current($xrdsProviders)) {
				echo '
					<link rel="openid.server" href="'.$provider['server'].'" />
					<link rel="openid.delegate me" href="'.$provider['delegate'].'" />';
			}
		}
	}


	/**
	 * Register Admin Menu.
	 */
    function menu() {
    	add_options_page('Yadis Options', 'Yadis', 9, 'global-yadis-options', array('Yadis', 'manage'));
    }

	function admin_head() {
		wp_print_scripts(array('jquery', 'interface'));
		$plugin_base = get_option('siteurl').'/wp-content/plugins/yadis';
		?>
<link rel="stylesheet" type="text/css" href="<?php echo $plugin_base?>/yadis.css" />
<script type="text/javascript" src="<?php echo $plugin_base?>/yadis.js"></script>
		<?php
	}


	/**
	 * Build new provider from form input.
	 */
	function new_provider() {
		$provider = null;

		if (@$_REQUEST['xrds-id']) {
			if ($_REQUEST['xrds-id'] == 'other') {
				if (@$_REQUEST['xrds-server'] and @$_REQUEST['xrds-delegate']) {
					$provider = array(
						'server' => $_REQUEST['xrds-server'],
						'delegate' => $_REQUEST['xrds-delegate'],
						'simplereg' => false,
					);
				}
			} 
			else if (@$_REQUEST['xrds-username']) {
				$provider = Yadis::build_provider_definition($_REQUEST['xrds-id'], $_REQUEST['xrds-username']);
			}
		}

		return $provider;
	}


	/**
	 * Build the specified predefined provider definition.
	 */
	function build_provider_definition($providerID, $username) {
		$provider = Array();

		$xrdsProviders = Yadis::predefined();
		if (array_key_exists($providerID, $xrdsProviders)) {
			$provider['server'] = preg_replace('/%/', $username, $xrdsProviders[$providerID][1]);
			$provider['delegate'] = preg_replace('/%/', $username, $xrdsProviders[$providerID][2]);
			$provider['simplereg'] = $xrdsProviders[$providerID][3];
			
			return $provider;
		}
	}


	/**
	 * Get pre-defined identity providers
	 */
    function predefined() {
		$providers = array(
			'aol' => array('AOL', 'http://api.screenname.aol.com/auth/openidServer','http://openid.aol.com/%',true),
			'claimid' => array('ClaimID', 'http://openid.claimid.com/server','http://openid.claimid.com/%',true),
			'livejournal' => array('LiveJournal', 'http://www.livejournal.com/openid/server.bml','http://%.livejournal.com/',true),
			'myopenid' => array('MyOpenID', 'http://www.myopenid.com/server','http://%.myopenid.com/',true),
			'yahoo' => array('Yahoo! (IDProxy)', 'http://idproxy.net/openid/server/','http://%.idproxy.net/',true),
			'wordpress' => array('Wordpress.com', 'http://%.wordpress.com/?openidserver=1','http://%.wordpress.com/',true),
		);

		return $providers;
    }


	/**
	 * URL rewriting stuff, to serve xrds.xml
	 */
	function rewrite_rules($rules) {
		$xrds_rules = array(
			'xrds$' => 'index.php?xrds=xrds',
			'xrds.xml$' => 'index.php?xrds=xrds',
			'index.php/xrds$' => 'index.php?xrds=xrds',
		);
		return $rules + $xrds_rules;
	}


	/**
	 * Add 'xrds' as a valid query variables.
	 **/
	function query_vars($vars) {
		$vars[] = 'xrds';

		return $vars;
	}


	/**
	 * Print XRDS document if 'xrds' query variable is present
	 **/
	function xrds_xml($query) {

		if ($query) $xrds = $query->query_vars['xrds'];
		if (!empty($xrds)) {
			$debug = ($xrds == 'debug' || array_key_exists('debug', $_REQUEST)) ? true : false;
			Yadis::print_xrds($debug);
		}
	}


	/**
	 * Print XRDS document.
	 **/
	function print_xrds($debug = false) {
		if ($debug) {
			header('Content-Type: text/plain');
		} else {
			header('Content-Type: application/xrds+xml');
			header('Content-Disposition: attachment;filename=xrds.xml');
		}

		$xrdsProviders = get_option('xrds_services');
		echo '<?xml version="1.0" encoding="UTF-8"?>
<xrds:XRDS xmlns="xri://$xrd*($v*2.0)" xmlns:xrds="xri://$xrds" xmlns:openid="http://openid.net/xmlns/1.0">
	<XRD>';
	foreach((array)$xrdsProviders as $k => $v) {
		echo'
		<Service priority="'.$k.'">
			<Type>http://openid.net/signon/1.0</Type>
			<URI>'.$v['server'].'</URI>
			<openid:Delegate>'.$v['delegate'].'</openid:Delegate>
		</Service>
';
	}
	echo '
	</XRD>
</xrds:XRDS>
';
		exit;
	}


	/**
	 * Handle "Accept: application/xml+xrds" requests.
	 **/
	function handleXrdsRequest() {
		if (strcasecmp($_SERVER['HTTP_ACCEPT'], 'application/xrds+xml') == 0) {
			Yadis::print_xrds();
			exit;
		}
	}


	/** 
	 * Manage admin option page.
	 */
    function manage() {
        $xrdsProviders = get_option('xrds_services');
		$updated = false;

        if (isset($_REQUEST['action'])) {

			switch($_REQUEST['action']) {
				case 'submit':
					$newXrdsProviders = array();
					parse_str($_REQUEST['services_order']);

					// copy user sorting preference
					foreach((array)$yadis_services as $i) {
						$i = ereg_replace('service_', '', $i);
						$newXrdsProviders[] = $xrdsProviders[$i];
					}
					$xrdsProviders = $newXrdsProviders;

					if ($provider = Yadis::new_provider($xrdsProviders)) {
						$xrdsProviders[] = $provider;
					}

					$updated = true;
					break;

				case 'delete':
					if (isset($_REQUEST['id'])) {
						unset($xrdsProviders[$_REQUEST['id']]);
						$updated = true;
					}
					break;
			}
		}

		if ($updated) {
			update_option('xrds_services', $xrdsProviders);

			# Update Permalinks
			global $wp_rewrite;
			$wp_rewrite->flush_rules();
		}
		?>

		<?php if ($updated) { 
			echo '<div id="message" class="updated fade"><p>'.__('Changes have been saved', '').'</p></div>';
		} ?>

		<div class="wrap">
			<form id="yadis_form" method="post">
        	<h2>Yadis Options</h2>

				<p>You may drag existing services to change their order of priority.</p>

				<h3><?php _e('Yadis Services') ?></h3>
				<ul id="yadis_services">
					<?php
						$id=0;
						foreach ((array)$xrdsProviders as $k => $v) {
							if ($v) {
								echo '
                    <li id="service_'.$k.'" class="service">
						<div>
							<a class="delete" href="?page='.$_REQUEST['page'].'&action=delete&id='.$k.'">Delete</a>
							<span class="server"><b>Server:</b> '.$v['server'].'</span>
							<span class="delegate"><b>Delegate:</b> '.$v['delegate'].'</i></span>
						</div>
					</li>
						';
								$id++;
							}
						}
					?>
				</ul>

				<h3>Add New Service</h3>
				<div id="xrds_new_service_form">
					<select name="xrds-id" id="new_provider">
						<option value="">- Add a new OpenID provider -</option>

		<?php
		// input box for new provider
		$predefined = Yadis::predefined();
		foreach ((array)$predefined as $k => $v) {
			echo'
						<option value="'.$k.'">'.$v[0].'</option>';
		}
		?>

						<option value="other">Other...</option>
					</select>
		
					<div id="xrds_predefined_service">
						Username: <input name="xrds-username">
					</div>

					<div id="xrds_custom_service">
						<table>
							<tr>
								<th>OpenID Server</th>
								<td><input name="xrds-server"></td>
							</tr>
							<tr>
								<th>OpenID Delegate</th>
								<td><input name="xrds-delegate"></td>
							</tr>
						</table>
					</div>
				</div>


				<input type="hidden" id="services_order" name="services_order" value="" />
				<input type="hidden" name="page" value="<?php echo $_REQUEST['page']; ?>" />
				<div class="submit"><input type="submit" name="action" value="<?php _e('submit', '') ?>" /></div>
       		</form>
		</div>
	<?php
    }

}
?>
