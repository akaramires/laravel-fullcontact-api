<?php
namespace Akaramires\FullContact;

/**
 * Licensed under the Apache License, Version 2.0 (the "License");
 * you may not use this file except in compliance with the License.
 * You may obtain a copy of the License at
 *
 * http://www.apache.org/licenses/LICENSE-2.0
 *
 * Unless required by applicable law or agreed to in writing, software
 * distributed under the License is distributed on an "AS IS" BASIS,
 * WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
 * See the License for the specific language governing permissions and
 * limitations under the License.
 */

//function Services_FullContact_autoload($className) {
//	$library_name = 'FullContact';
//
//	if (substr($className, 0, strlen($library_name)) != $library_name) {
//		return false;
//	}
//	$file = str_replace('_', '/', $className);
//	$file = str_replace('Services/', '', $file);
//	return include dirname(__FILE__) . "/$file.php";
//}
//
//spl_autoload_register('Services_FullContact_autoload');

/**
 * This class handles the actually HTTP request to the FullContact endpoint.
 *
 * @package  Services\FullContact
 * @author   Keith Casey <contrib@caseysoftware.com>
 * @license  http://www.apache.org/licenses/LICENSE-2.0 Apache
 */
class FullContact
{
	const USER_AGENT = 'caseysoftware/fullcontact-php-0.9.0';

	protected $_baseUri = 'https://api.fullcontact.com/';
	protected $_version = 'v2';

	protected $_apiKey = null;

	public $response_obj  = null;
	public $response_code = null;
	public $response = null;

	/**
     * The base constructor needs the API key available from here:
     * http://fullcontact.com/getkey
     *
     * @param type $api_key
     */
	public function __construct($api_key)
	{
		$this->_apiKey = $api_key;
	}

	/**
     * This is a pretty close copy of my work on the Contactually PHP library
     *   available here: http://github.com/caseysoftware/contactually-php
     *
     * @author  Keith Casey <contrib@caseysoftware.com>
     * @param   array $params
     * @return  object
     * @throws  FullContactExceptionNotImplemented
     */
	protected function _execute($params = array())
	{
		if(!in_array($params['method'], $this->_supportedMethods)){
			throw new FullContactExceptionNotImplemented(__CLASS__ .
			" does not support the [" . $params['method'] . "] method");
		}

        if(!array_key_exists($params['resource'],$this->_supportedResources)){
            throw new FullContactExceptionNotImplemented(__CLASS__ .
                " does not support the [" . $params['resource'] . "] resource");
        }

        $this->_setResource($params['resource']);

		$params['apiKey'] = urlencode($this->_apiKey);

		$fullUrl = $this->_baseUri . $this->_version . $this->_resourceUri .
		'?' . http_build_query($params);

		$cached = $this->_getFromCache($fullUrl);
		if ( $cached !== false )
		{
			$this->response = $cached;
			$this->response_code = 200;
			// if the response is json, we need to decode it
            $this->response_obj = ($this->_isJson($this->response)) ? json_decode($this->response) : $this->response;
		}
		else
		{
			//open connection
			$connection = curl_init($fullUrl);
			curl_setopt($connection, CURLOPT_SSL_VERIFYHOST, 0);
			curl_setopt($connection, CURLOPT_SSL_VERIFYPEER, 0);
			curl_setopt($connection, CURLOPT_RETURNTRANSFER, 1);
			curl_setopt($connection, CURLOPT_USERAGENT, self::USER_AGENT);
			
			//execute request
			$this->response = curl_exec($connection);
			$this->response_code = curl_getinfo($connection, CURLINFO_HTTP_CODE);
			if ( '200' == $this->response_code )
			{
				$this->_saveToCache($fullUrl, $this->response);
			}
            // if the response is json, we need to decode it
            $this->response_obj = ($this->_isJson($this->response)) ? json_decode($this->response) : $this->response;

			curl_close($connection);

			if ('403' == $this->response_code) {
				throw new ServicesFullContactExceptionNoCredit($this->response_obj->message);
			}
		}

		return $this->response_obj;
	}

	protected function _saveToCache($url, $response)
	{
		$cache_path = 'FullContactCache/';
		$cache_file_name = $cache_path.'/'.md5(urldecode($url)).'.'.$this->_getResourceExtension();
		
		return \Storage::put($cache_file_name, $response);
	}

	protected function _getFromCache($url)
	{
		$cache_path = 'FullContactCache/';
		$cache_file_name = $cache_path.'/'.md5(urldecode($url)).'.'.$this->_getResourceExtension();

		if ( \Storage::exists($cache_file_name) )
		{
			$content = \Storage::get($cache_file_name);
			return $content;
		}

		return false;
	}

	protected function _getResourceExtension()
    {
        return explode(".",$this->_resourceUri)[1];
    }

    protected function _isJson($string)
    {
        json_decode($string);
        return (json_last_error() == JSON_ERROR_NONE);
    }

    public function _setResource($resource_type)
    {
        $this->_resourceUri = ($this->_supportedResources[$resource_type]) ? $this->_supportedResources[$resource_type] : $this->_supportedResources['json'];
    }
}