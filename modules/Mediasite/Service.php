<?php 

/**
 * 
 */
class MediasiteBackup_Mediasite_Service
{
	/**
	 * @var Bss_Core_Application
	 */
	private $app;

	/**
	 * @var string
	 */
	private $baseUrl;

	/**
	 * @var string
	 */
	private $apiKey;

	/**
	 * @var string
	 */
	private $username;

	/**
	 * @var string
	 */
	private $password;


	function __construct(Bss_Core_Application $application)
	{
		$this->app = $application;
		$this->initializeProperties(
			$this->app->configuration->getProperty('mediasite.service')
		);
	}


	public function get($url, $params = [])
	{
		$client = $this->getClient();

		return $this->validateResult($client->get($url, $params));
	}


	public function post($url, $data, $params = [])
	{
		$client = $this->getClient()->withPostBody($data, 'json');

		return $this->validateResult($client->post($url, $params));
	}


	public function put($url, $data, $params = [])
	{
		$client = $this->getClient()->withPostBody($data, 'json');

		return $this->validateResult($client->put($url, $params));
	}


	public function patch($url, $data, $params = [])
	{
		$client = $this->getClient()->withPostBody($data, 'json');

		return $this->validateResult($client->patch($url, $params));
	}


	protected function delete($url, $params = [])
	{
		$client = $this->getClient();

		return $this->validateResult($client->delete($url, $params));
	}


	protected function getClient($url = null)
	{
		$url = $url ?: $this->baseUrl;

		return new Bss_Http_Client($url, [
			'headers' => ['Content-Type' => 'application/json', 'sfapikey' => $this->apiKey],
			'auth' => [$this->username, $this->password]
		]);
	}


	protected function validateResult($response)
	{
		$result = false;

		$body = $response->getBody();
		$json = null;
		
		if (!empty($body))
		{
		    $json = @json_decode($body, true);
		}
		elseif ($response->getStatusCode() != '204')
		{
			$this->app->log(
				'error', 
				'Mediasite API request failed with no response. '
			);

			return $result;
		}

		if ($json)
		{
			if (!isset($json['odata.error']))
			{
				$result = $json;
			}
			else
			{
				$this->app->log(
					'error', 
					'Mediasite API request failed with response: ' . print_r($json, true)
				);
			}
		}
		else
		{
			$this->app->log(
				'error', 
				'Mediasite API request failed with non json response.'
			);
		}

		return $result;
	}


	private function initializeProperties ($attributes)
	{
		$this->baseUrl = $attributes['url'];
		$this->apiKey = $attributes['credentials']['key'];
		$this->username = $attributes['credentials']['username'];
		$this->password = $attributes['credentials']['password'];
	}
}