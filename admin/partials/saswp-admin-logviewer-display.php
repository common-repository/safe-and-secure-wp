<?php
/**
 * The admin-specific functionality of the plugin.
 *
 * @link       https://www.SafeAndSecureWP.com
 * @since      1.0.0
 *
 * @package    SASWP
 * @subpackage SASWP/admin
 * @author     SafeAndSecureWP <info@SafeAndSecureWP.com>
 */

$site_domain = get_option( 'saswp_site_domain' );
$api_url     = 'https://beholder.azure-api.net/authentication-list/v1/authentication-all/' . $site_domain;
$json        = wp_remote_get(
	$api_url,
	array(
		'headers' => array(
			'subscription-key' => get_option( 'saswp_license_key' ),
		),
	)
);
?>

<div class="wrap">
	<h2>Safe and Secure WP</h2>
</div>

<div id="saswp-status" class="notice is-dismissible"></div>

<p>&nbsp;</p>

<table id="innerExample" class="table table-sm table-hover">
	<thead class="thead-dark">
		<tr>
			<th class="center">Timestamp</th>
			<th class="center">Action</th>
			<th class="center">User</th>
			<th class="center">Result</th>
			<th class="center no-sort">Country</th>
			<th class="center">User IP</th>
		</tr>
	</thead>
	<tbody id="result" class="">
	</tbody>
</table>

<script id="myTmpl" type="text/x-jsrender">
	<tr>
		<td class="center">{{:~EpochToDate(timestamp)}}</td>
		<td class="center">{^{:action}}</td>
		<td class="center">{^{:username}}</td>
		<td class="center">{^{:result}}</td>
		<td class="center">{{:~ValidateCountry(country)}}</td>
		<td class="center">{^{:user_ip}}</td>
	</tr>
</script>

<script>
	jQuery(document).ready(function() {
		// https://dev.maxmind.com/geoip/legacy/codes/iso3166/
		var json = <?php echo( wp_kses( $json['body'], null, null ) ); ?>;

		if (json.length == 0) {
			console.log("NO RESULTS FOUND");
		}

		jQuery.views.settings.allowCode(true);
		var tmpl = jQuery.templates("#myTmpl"); // Get compiled template
		var data = json; // Define data

		tmpl.link("#result", data, {

			getSize: function(size) {
				var megabytes = size / (1024 * 1024);
				return megabytes.toFixed(2) + "MB";
			},

			EpochToDate: function(epoch) {
				var itemDate = new Date(epoch * 1000);
				return (itemDate.toLocaleString("en-US", {
					hour12: false
				}));
			},

			ValidateCountry: function(country) {
				if (country == null) {
					return "";
				} else {
					return '<img src="<?php echo plugin_dir_url( __DIR__ ); ?>/flags/4x3/' + country.toLowerCase() + '.svg" alt="Country: ' + country + '" width="20" /> - ' + country;
				}
			}
		}); // Render and data-link template as content of chosen container element

		jQuery.fn.dataTable.moment('M/D/YYYY, HH:mm:ss A');
		jQuery('#innerExample').DataTable({
			"pageLength": 25,
			columnDefs: [{
				targets: 'no-sort',
				orderable: false,
			}],
			"order": [
				[0, "desc"]
			]
		});
	});
</script>
