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
 * WorkflowHunt Semantic Annotation Model
 *
 * @category	Models
 * @author		Juan Sebastián Beleño Díaz
 * @link		xxx
 */
class Semantic_annotation_model extends CI_Model {

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
	 * Annotate Semantically the Workflow Metadata
	 *
	 * For each ontology term and synonym, I find all the workflow metadata
	 * that contains them, and write a semantic annotation.
	 *
	 * @return	array
	 */
    public function annotate_workflows()
    {
    	// ***** Ontology terms ****
    	$this->db->select('id, label');
    	$query_terms = $this->db->get('term');

    	$semantic_annotations = array();

    	// For each ontology term, I perform semantic annotation on the 
    	// workflow metadata
    	foreach ($query_terms->result() as $term) 
    	{
    		$this->db->select('id');
    		$this->db->like('title', $term->label);
    		$this->db->or_like('description', $term->label);
    		$query_workflow = $this->db->get('workflow');

    		// It annotates the workflow title and description
    		foreach ($query_workflow->result() as $workflow) 
    		{
    			$semantic_annotations[] = array(
    				'id_term' => $term->id,
    				'id_workflow' => $workflow->id,
    				'hits' => 1,
    				'date' => date("Y-m-d H:i:s")
    			);
    		}

    		$this->db->select('id');
    		$this->db->like('name', $term->label);
    		$query_tags = $this->db->get('tag');

    		// It annotates the workflow tags
    		foreach ($query_tags->result() as $tag) 
    		{
    			$this->db->select('workflow_id');
    			$this->db->where('tag_id', $tag->id);
    			$tag_wf = $this->db->get('tag_wf', 1, 0)->row();

    			$semantic_annotations[] = array(
    				'id_term' => $term->id,
    				'id_workflow' => $tag_wf->workflow_id,
    				'hits' => 1,
    				'date' => date("Y-m-d H:i:s")
    			);
    		}
    	}

    	// **** Synonyms of the ontology terms ****

    	$this->db->select('id_term, name');
    	$query_synonym = $this->db->get('synonym');

    	// For each synonym, I perform semantic annotation on the 
    	// workflow metadata
    	foreach ($query_synonym->result() as $synonym) 
    	{
    		$this->db->select('id');
    		$this->db->like('title', $synonym->name);
    		$this->db->or_like('description', $synonym->name);
    		$query_workflow = $this->db->get('workflow');

    		// It annotates the workflow title and description
    		foreach ($query_workflow->result() as $workflow) 
    		{
    			$semantic_annotations[] = array(
    				'id_term' => $synonym->id_term,
    				'id_workflow' => $workflow->id,
    				'hits' => 1,
    				'date' => date("Y-m-d H:i:s")
    			);
    		}

    		$this->db->select('id');
    		$this->db->like('name', $synonym->name);
    		$query_tags = $this->db->get('tag');

    		// It annotates the workflow tags
    		foreach ($query_tags->result() as $tag) 
    		{
    			$this->db->select('workflow_id');
    			$this->db->where('tag_id', $tag->id);
    			$tag_wf = $this->db->get('tag_wf', 1, 0)->row();

    			$semantic_annotations[] = array(
    				'id_term' => $synonym->id_term,
    				'id_workflow' => $tag_wf->workflow_id,
    				'hits' => 1,
    				'date' => date("Y-m-d H:i:s")
    			);
    		}
    	}

    	$this->db->insert_batch('s_annotation', $semantic_annotations);

    	return array('status' => 'OK');
    }

    // --------------------------------------------------------------------

    /**
	 * Select the Scientific Domain for each workflow
	 *
	 * For each workflow, I classify them using the terms in each ontology.
	 * If the workflow metadata has an ontology term, then it belongs to the
	 * scientific domain that belongs to that ontology. If the workflow 
	 * metadata belongs to more than one scientific domain, then it is 
	 * classified with the scientific domain with majority of ontology 
	 * terms
	 *
	 * @return	array
	 */
    public function select_domain()
    {

    }
}

/* End of file Semantic_annotation_model.php */
/* Location: ./application/models/Semantic_annotation_model.php */