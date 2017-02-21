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
class Group_model extends CI_Model {

	/**
	 * Workflow Groups URL from API
	 *
	 * @var	string
	 */
	private $GROUPS_URL = "http://www.myexperiment.org/groups.xml";

	/**
	 * Workflow Group URL from API
	 *
	 * @var	string
	 */
	private $GROUP_URL = "http://www.myexperiment.org/group.xml";

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
	 * Insert Workflow Groups in Database
	 *
	 * @param	int	$groups_per_page	Number of groups per page in the API
	 * @return	array
	 */
    public function insert_groups_ids($groups_per_page = 50)
    {
    	$flag = true;
    	$page = 1;
    	$groups = array();

    	while($flag)
    	{
    		// Construct dinamically a URL until reach all the workflows
    		$PARAMS = "sort=id&num=".$groups_per_page."&page=".$page;
    		$url = $this->GROUPS_URL."?".$PARAMS;

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
				// of groups' ids
				foreach ($xml->children() as $group) 
				{
					$groups[] = array(
										'id' => $group['id'],
										'title' => $group,
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

    	$this->db->insert_batch('group', $groups);

    	return array('status' => 'OK');
    }

    // --------------------------------------------------------------------

    /**
	 * Update Users Metadata
	 *
	 * We need to collect more detailed metadata about groups. For example,
	 * we need to collect metadata such as title, description, etc.
	 *
	 * @return	array
	 */
    public function update_group_metadata()
    {
    	$this->db->select('id');
       	$query = $this->db->get('group');

    	foreach ($query->result() as $group) 
    	{
    		$id_group = $group->id;

    		// Construct dinamically a URL for each workflow
    		$PARAMS = "id=".$id_group."&elements=title,description,members,shared-items";
    		$url = $this->GROUP_URL."?".$PARAMS;

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
				$group/*[]*/ = array(
										//'id' => $id_group,
										'title' => $xml->title,
										'description' => $xml->description,
										'date_last_update' => date("Y-m-d H:i:s")
									);

				$this->db->where('id', $id_group);
				$this->db->update('group', $group);

				foreach ($xml->members->children() as $member) 
				{
					$arr_member = array(
										'id_group' => $id_group,
										'id_member' => $member['id'],
										'date' => date("Y-m-d H:i:s")
									);

					$this->db->insert('member', $arr_member);
				}

				foreach ($xml->{'shared-items'}->children() as $item) 
				{
					if($item->getName() == 'workflow'){
						$arr_workflow = array(
											'id_group' => $id_group,
											'id_workflow' => $item['id'],
											'date' => date("Y-m-d H:i:s")
										);

						$this->db->insert('shared_with_group', $arr_workflow);
					}
				}
			}
		}

		return array('status' => 'OK');
    }

}

/* End of file Group_model.php */
/* Location: ./application/models/Group_model.php */