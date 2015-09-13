<?php

/**
 * A simple PHP class for interacting with Mint.com
 *
 * This class provides methods for connecting to Mint.com as a specific
 * user and downloading that user's transactions in CSV format.
 *
 * PHP version 5
 *
 * @author     Aaron Forgue <forgue@gmail.com>
 * @link       https://github.com/forgueam/php-mint-api
 */

class PhpMintApi {

	/**
	 * The base Mint.com URL used for all HTTP reuqests
	 */
	private $mintBaseUrl = 'https://wwws.mint.com';

	/**
	 * The URL action used for authenticating a Mint.com user
	 */
	private $mintLoginAction = 'loginUserSubmit.xevent';

	/**
	 * The URL action used for downloading all transactions from 
	 * a user's Mint.com account
	 */
	private $mintTransactionsAction = 'transactionDownload.event?';

	/**
	 * The absolute path to a writeable file in which to store
	 * cURL session cookie data
	 */
	private $cookieFilePath;

	/**
	 * Mint.com user credentials
	 */
	private $mintUserEmail;
	private $mintUserPassword;

	/**
	 * Initialize object with user credentials and session cookie file
	 *
	 * @param sting $email Mint.com user email
	 * @param string $password Mint.com user password
	 * @param string $cookieFilePath Absolute path to writeable file
	 */
	function __construct($params) {
		// Make sure the cookie jar is writeable
		if (!file_exists($params['cookieFilePath']) || !is_writable($params['cookieFilePath'])) {
			throw new Exception('Cookie file does not exist or is not writeable.');
		}

		$this->mintUserEmail = $params['email'];
		$this->mintUserPassword = $params['password'];
		$this->cookieFilePath = $params['cookieFilePath'];
	}

	/**
	 * Log user into Mint.com and store session cookies
	 */
	public function connect() {
		$ch = curl_init();
		curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
		curl_setopt($ch, CURLOPT_URL, $this->mintBaseUrl . '/' . $this->mintLoginAction);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
		curl_setopt($ch, CURLOPT_COOKIEJAR, $this->cookieFilePath);
		curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);
		curl_setopt($ch, CURLOPT_POST, 1);
		curl_setopt($ch, CURLOPT_POSTFIELDS, array(
			'username' => $this->mintUserEmail,
			'password' => $this->mintUserPassword,
			'task' => 'L',
			'nextPage' => ''
		));

		$response = curl_exec($ch);
		$curlError = curl_error($ch);
		curl_close($ch);
		unset($ch);

		if ($response === false) {
			throw new Exception('cURL Error: ' . $curlError);
		}

		// Get token
		$response_sub = substr($response, strpos($response, 'javascript-token') + 17);
		$pat = '/\"([^\"]*?)\"/';
		preg_match($pat, $response_sub, $matches);
		$token = $matches[1];

		if (!$token) {
			throw new Exception('Mint.com login failed.');
		}

	}

	/**
	 * Download a comma-separated value string of all account transactions
	 *
	 * @return string|bool The comma-separated value data returned from the HTTP request
	 */
	public function getMintData($query) {
		$query['rnd'] = $this->getRnd();

		$ch = curl_init();
		curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
		curl_setopt($ch, CURLOPT_URL, $this->mintBaseUrl . '/app/getJsonData.xevent?' . http_build_query($query));
		curl_setopt($ch, CURLOPT_COOKIEFILE, $this->cookieFilePath);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);

		$response = curl_exec($ch);
		$curlError = curl_error($ch);

		curl_close($ch);
		unset($ch);

		if ($response === false) {
			throw new Exception('cURL Error: ' . $curlError);
			return false;
		}

		return json_decode($response);
	}

	private function getRnd(){
		return strtotime("now") . sprintf('%03d',rand(0,999));
	}
}

?>