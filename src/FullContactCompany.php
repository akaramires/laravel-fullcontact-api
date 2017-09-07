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

/**
 * This class handles everything related to the Person lookup API.
 *
 * @package  Services\FullContact
 * @author   Keith Casey <contrib@caseysoftware.com>
 * @license  http://www.apache.org/licenses/LICENSE-2.0 Apache
 */
class FullContactCompany extends FullContact
{
    /**
     * Supported lookup methods
     * @var $_supportedMethods
     */
    protected $_supportedMethods = array('domain', 'companyName');
    protected $_supportedResources = array('json' => '/company/lookup.json', 'html' => '/company/lookup.html', 'xml' => '/company/lookup.xml');
    protected $_resourceUri = null;

    public function lookupByDomain($search, $resource = 'json')
    {
        $this->_execute(array('domain' => $search, 'method' => 'domain', 'resource' => $resource));

        return $this->response_obj;
    }

    public function lookupByCompanyName($search, $resource = 'json')
    {
        $this->_execute(array('companyName' => $search, 'method' => 'companyName', 'resource' => $resource));

        return $this->response_obj;
    }

}