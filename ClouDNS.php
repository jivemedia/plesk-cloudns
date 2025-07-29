<?php
/**
 * This file is part of plesk-cloudns.
 *
 * To work, this file needs to be placed at `/usr/local/psa/admin/plib/registry/EventListener/` and credentials entered.
 *
 * @package   plesk-cloudns-event
 * @license   http://www.gnu.org/licenses/lgpl.txt GNU LESSER GENERAL PUBLIC LICENSE v3
 * @copyright 2018–2025 Nick Andriopoulos
 * @copyright 2025 Greg Sevastos
 * @author    Nick Andriopoulos <nand@lambda-twelve.com>
 * @author    Greg Sevastos <info@jivemedia.com.au>
 */

class ClouDnsSlaveManager implements EventListener {
	// Get your API user settings: https://www.cloudns.net/api-settings/
	private $authid = ''; // Add your API `auth-id`.
	private $authkey = ''; // Add your API `auth-key`.
	private $masterip = ''; // (Optional) Add your server primary IP here.
	private $baseurl = 'https://api.cloudns.net/';
	private $debug = false;

	/**
	 * Mandatory from Plesk 18.0.69+
	 * Declares which events this listener handles.
	 *
	 * @return array
	 */
	public function filterActions() {
		return array(
			'domain_create',
			'domain_delete',
			'domain_alias_create',
			'domain_alias_delete',
			'site_create',
			'site_delete',
		);
	}

	/**
	 * Handles the event fired by Plesk.
	 */
	public function handleEvent( $objectType, $objectId, $action, $oldValues, $newValues ) {
		if ( $this->debug ) {
			error_log( 'ClouDnsSlaveManager handleEvent triggered for: ' . $objectType . ' - ' . $action );
		}

		if ( empty( $this->authid ) || empty( $this->authkey ) ) {
			error_log( 'ClouDNS credentials empty, doing nothing.' );

			return;
		}

		switch ( $objectType ) {
			case 'domain_alias':
				if ( $action === 'domain_alias_create' ) {
					$this->addSlave( $newValues['Domain Alias Name'] );
				} elseif ( $action === 'domain_alias_delete' ) {
					$this->delSlave( $oldValues['Domain Alias Name'] );
				}
				break;

			case 'domain':
				if ( $action === 'domain_create' ) {
					$this->addSlave( $newValues['Domain Name'] );
				} elseif ( $action === 'domain_delete' ) {
					$this->delSlave( $oldValues['Domain Name'] );
				}
				break;

			case 'site':
				if ( $action === 'site_create' ) {
					$this->addSlave( $newValues['Domain Name'] );
				} elseif ( $action === 'site_delete' ) {
					$this->delSlave( $oldValues['Domain Name'] );
				}
				break;
		}
	}

	private function addSlave( $zone ) {
		if ( $this->debug ) {
			error_log( 'Adding zone to ClouDNS slave: ' . $zone );
		}

		$params = array(
			'domain-name' => $zone,
			'zone-type'   => 'slave',
			'master-ip'   => $this->masterip,
		);

		$response = $this->apiCall( 'dns/register.json', $params );
		if ( $this->debug ) {
			error_log( print_r( $response, true ) );
		}

		if ( isset( $response['status'] ) && $response['status'] === 'Failed' ) {
			error_log( 'Failed to create zone "' . $zone . '": ' . $response['statusDescription'] );
		}
	}

	private function apiCall( $url, $data ) {
		$url    = $this->baseurl . $url;
		$params = array_merge(
			array(
				'auth-id'       => $this->authid,
				'auth-password' => $this->authkey,
			),
			$data
		);

		$curl = curl_init();
		curl_setopt( $curl, CURLOPT_RETURNTRANSFER, true );
		curl_setopt( $curl, CURLOPT_URL, $url );
		curl_setopt( $curl, CURLOPT_POST, true );
		curl_setopt( $curl, CURLOPT_POSTFIELDS, http_build_query( $params ) );

		$content = curl_exec( $curl );
		curl_close( $curl );

		return json_decode( $content, true );
	}

	private function delSlave( $zone ) {
		if ( $this->debug ) {
			error_log( 'Deleting zone from ClouDNS slave: ' . $zone );
		}

		$params   = array( 'domain-name' => $zone );
		$response = $this->apiCall( 'dns/delete.json', $params );
		if ( $this->debug ) {
			error_log( print_r( $response, true ) );
		}

		if ( isset( $response['status'] ) && $response['status'] === 'Failed' ) {
			error_log( 'Failed to delete zone "' . $zone . '": ' . $response['statusDescription'] );
		}
	}
}

// Required by Plesk to return an instance.
return new ClouDnsSlaveManager();
