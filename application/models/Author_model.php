<?php
/**
 * WorkflowHunt
 *
 * A semantic search engine for scientific workflow repositories
 *
 * This content is released under the MIT License (MIT)
 *
 * Copyright (c) 2016 - 2017, Juan Sebastián Beleño Díaz
 *
 * Permission is hereby granted, free of charge, to any person obtaining a copy
 * of this software and associated documentation files (the "Software"), to deal
 * in the Software without restriction, including without limitation the rights
 * to use, copy, modify, merge, publish, distribute, sublicense, and/or sell
 * copies of the Software, and to permit persons to whom the Software is
 * furnished to do so, subject to the following conditions:
 *
 * The above copyright notice and this permission notice shall be included in
 * all copies or substantial portions of the Software.
 *
 * THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
 * IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
 * FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE
 * AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
 * LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
 * OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN
 * THE SOFTWARE.
 *
 * @package	WorkflowHunt
 * @author	Juan Sebastián Beleño Díaz
 * @copyright	Copyright (c) 2016 - 2017, Juan Sebastián Beleño Díaz
 * @license	http://opensource.org/licenses/MIT	MIT License
 * @link	https://github.com/jbeleno
 * @since	Version 1.0.0
 * @filesource
 */
defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * WorkflowHunt Author Model
 *
 * @category	Models
 * @author		Juan Sebastián Beleño Díaz
 * @link		xxx
 */
class Author_model extends CI_Model {

	/**
	 * Workflow Users URL from API
	 *
	 * @var	string
	 */
	private $USERS_URL = "http://www.myexperiment.org/users.xml";

	/**
	 * Workflow User URL from API
	 *
	 * @var	string
	 */
	private $USER_URL = "http://www.myexperiment.org/user.xml";

	// --------------------------------------------------------------------

	/**
	 * Constructor
	 *
	 * @return	void
	 */
	public function __construct()
    {
        // Call the CI_Model constructor
        parent::__construct();
    }

    // --------------------------------------------------------------------

    /**
	 * Insert Workflow Users in Database
	 *
	 * @param	int	$users_per_page	Number of users per page in the API
	 * @return	array
	 */
    public function insert_users_ids($users_per_page = 50)
    {
    	$flag = true;
    	$page = 1;
    	$users = array();

    	while($flag)
    	{
    		// Construct dinamically a URL until reach all the workflows
    		$PARAMS = "sort=id&num=".$users_per_page."&page=".$page;
    		$url = $this->USERS_URL."?".$PARAMS;

    		// Request the content in XML format
    		$context  = stream_context_create(
    						array(
    							'http' => array(
    										'header' => 'Accept: application/xml'
    										)
    							)
    						);

			$xml = file_get_contents($url, false, $context);
			$xml = simplexml_load_string($xml);

			if(!empty($xml))
			{
				// If the content is converted into XML, we'll create the array
				// of users' ids
				foreach ($xml->children() as $user) 
				{
					$users[] = array(
										'id' => $user['id'],
										'name' => $user,
										'date' => date("Y-m-d H:i:s"),
										'date_last_update' => date("Y-m-d H:i:s")
									);
				}
			}
			else
			{
				// If the content isn't converted into XML, we'll exit the loop
				$flag = false;
			}

    		$page++;
    	}

    	$this->db->insert_batch('author', $users);

    	return array('status' => 'OK');
    }

    // --------------------------------------------------------------------

    /**
	 * Update Users Metadata
	 *
	 * We need to collect more detailed metadata about users. For example,
	 * we need to collect metadata such as email, website, photo, etc.
	 *
	 * @return	array
	 */
    public function update_user_metadata()
    {
    	$this->db->select('id');
    	//$this->db->where('id >', 23655);
    	$query = $this->db->get('author');

    	foreach ($query->result() as $user) 
    	{
    		$id_user = $user->id;

    		// Construct dinamically a URL for each workflow
    		$PARAMS = "id=".$id_user."&elements=name,email,avatar,website,country,city";
    		$url = $this->USER_URL."?".$PARAMS;

    		// Request the content in XML format
    		$context  = stream_context_create(
    						array(
    							'http' => array(
    										'header' => 'Accept: application/xml'
    										)
    							)
    						);

			$xml = file_get_contents($url, false, $context);
			$xml = simplexml_load_string($xml);

			if(!empty($xml))
			{
				$user/*[]*/ = array(
										//'id' => $id_user,
										'name' => $xml->name,
										'email' => $xml->email,
										'photo' => $xml->avatar['resource'],
										'website' =>  $xml->website,
										'country' => $xml->country,
										'city' => $xml->city,
										'date_last_update' => date("Y-m-d H:i:s")
									);

				$this->db->where('id', $id_user);
				$this->db->update('author', $user);
			}
		}

		return array('status' => 'OK');
    }


}

/* End of file Author_model.php */
/* Location: ./application/models/Author_model.php */