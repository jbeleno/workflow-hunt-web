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
 * WorkflowHunt NCBO Annotator Model
 *
 * GAMBIARRA ALERT: It seems this annotator is not as perfect as I though.
 * It allows annotation overlapping even when the option "match the 
 * longest only" is activated.
 *
 * @category	Models
 * @author		Juan Sebastián Beleño Díaz
 * @link		xxx
 */
class NCBOAnnotator_model extends CI_Model {

	/**
	 * NCBO Annotator endpoint
	 *
	 * @var	string
	 */
	private $ANNOTATOR_URL = "http://data.bioontology.org/annotator?";

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
	 * Annotates the individual metadata
	 *
	 * Takes a free text from a metadata field that belongs to a workflow
	 * and annotates it using NCBO Annotator.
	 */
    private function annotate( $metadata_id, $metadata_name, $metadata_value, 
    							$ontologies, $id_workflow) {

		$PARAMETERS = 	http_build_query(
    						array(
    							'ontologies' => $ontologies,
    							'text' => $metadata_value,
    							'apikey' => NCBO_ANNOTATOR_API_KEY,
    							'longest_only' => true
    						)
    					);

    	// Request the content in JSON format
		$CONTEXT  = stream_context_create(
						array(
							'http' => array(
										'header' => 'Accept: application/json'
										)
							)
						);

		$raw_content = file_get_contents($this->ANNOTATOR_URL.$PARAMETERS, false, $CONTEXT);
		$annotations = json_decode($raw_content);
		//$data = array();

		foreach ($annotations as $annotation) {

			$this->db->select('id');
			$this->db->where('iri', $annotation->annotatedClass->{'@id'});
			$query_term = $this->db->get('term', 1, 0);
			$term = $query_term->row();

			if($term != null) {
				$data = array(
					'id_workflow' => $id_workflow,
					'id_term' => $term->id,
					'source' => 'NCBO Annotator',
					'from' => $annotation->annotations[0]->from,
					'to' => $annotation->annotations[0]->to,
					'metadata_type' => $metadata_name,
					'id_metadata' => $metadata_id,
					'created_at' => date("Y-m-d H:i:s")
				);

				// Insert the semantic annotations
				$this->db->insert('ncbo_annotation', $data);
			}
		}
    }

    // --------------------------------------------------------------------

    /**
	 * Annotates the workflow metadata
	 *
	 * Iterates over the ontologies to get the prefix that will be used to 
	 * annotate and takes as parameter the text to be annotated. This is just
	 * a partial annotation because it needs to be complemented with the 
	 * synonyms extracted from WordNet.
	 *
	 * @return	array
	 */
    public function annotate_metadata()
    {
    	$this->db->select('prefix');
    	$query_ont = $this->db->get('ontology');

    	$ontologies = "";

    	foreach ($query_ont->result() as $ontology) {
    		$ontologies .= $ontology->prefix.",";
    	}

    	// Remove the extra comma
    	$ontologies = substr($ontologies, 0, strlen($ontologies) - 1);

    	$this->db->select('id,title,description');
   		$query_workflow = $this->db->get('workflow');

    	foreach ($query_workflow->result() as $workflow) {
    		
    		$this->db->select('tag.id AS id, tag.name AS name');
    		$this->db->where('tag_wf.workflow_id', $workflow->id);
    		$this->db->from('tag');
    		$this->db->join('tag_wf', 'tag_wf.tag_id = tag.id');
    		$query_tags = $this->db->get();
    		$tags = array();

    		foreach ($query_tags->result() as $tag) {
    			$tags[] = array($tag->id => $tag->name);
    		}

    		$title = $workflow->title;
    		$description = $workflow->description;

    		$metadata = array(
    						'title' => $title,
    						'description' => $description,
    						'tags' => $tags
    					);

    		foreach ($metadata as $key => $value) {
    			if(is_array($value)) {
    				// This is the case of the tags and the internal foreach just
    				// has one iteration
    				foreach ($value as $val) {
    					foreach ($val as $key2 => $value2) {
    						$this->annotate( $key2, $key, $value2, $ontologies, $workflow->id);
    					}
    				}

    			}else{
    				$this->annotate( null, $key, $value, $ontologies, $workflow->id);
    			}
    		}

    	}

    	return array('status' => 'OK');
    }

    // --------------------------------------------------------------------

    /**
	 * Semantic reinforcement
	 *
	 * In this case, we extends the annotations using synonyms that are not 
	 * present in the default NCBO Annotator. Nevertheless, this can be 
	 * extended to cover generalization and specialization. Semantic 
	 * Reinforcement is done after the semantic annotations were done.
	 *
	 * @return	array
	 */
    public function reinforcement()
    {
    	$this->db->select('id_term,name');
    	$this->db->where('source', 'WordNet');
    	$query_synonyms = $this->db->get('synonym');

    	foreach ($query_synonyms->result() as $synonym) {
    		foreach ($variable as $key => $value) {
    			# code...
    		}
    	}

    	return array('status' => 'OK');
    }

}

/* End of file NCBOAnnotator_model.php */
/* Location: ./application/models/NCBOAnnotator_model.php */