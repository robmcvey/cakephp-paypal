<?php
/**
 * Custom class to allow us to comunicate with Payapal using OAuth2
 * 
 * @author Israel Sotomayor
 */
class OAuthAuthentication {

/**
 * Authentication
 *
 * @param HttpSocket $http
 * @param array $authInfo
 * @return void 
 * 
 * @author Israel Sotomayor
 */
	public static function authentication(HttpSocket $http, &$authInfo) {
		$http->request['header']['Content-Type'] = 'application/json';
		$http->request['header']['Authorization'] = $authInfo['token_type'] . ' ' . $authInfo['access_token'];
	}
}