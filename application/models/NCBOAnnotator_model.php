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
	private $ANNOTATOR_URL = "http://www.ebi.ac.uk/ols/api/ontologies/";

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
	 * Annotates the free text provided with the ontologies in the parameters
	 *
	 * Iterates over the ontologies to get the prefix that will be used to 
	 * annotate and takes as parameter the text to be annotated. This is just
	 * a partial annotation because it needs to be complemented with the 
	 * synonyms extracted from WordNet.
	 *
	 * @return	array
	 */
    public function annotate()
    {
    	$this->db->select('prefix');
    	$query_ont = $this->db->get('ontology');

    	$ontologies = "";

    	foreach ($query_ont->result() as $ontology) {
    		$ontologies .= $ontology->prefix.",";
    	}

    	// Remove the extra comma
    	$ontologies = substr($ontologies, 0, len($ontologies) - 1);

    	$this->db->select('id,title,description');
   		$query_workflow = $this->db->get('workflow');

    	foreach ($query_workflow->result() as $workflow) {
    		
    		$this->db->select('tag.name AS name');
    		$this->db->where('tag_wf.workflow_id', $workflow->id);
    		$this->db->from('tag');
    		$this->db->join('tag_wf', 'tag_wf.tag_id = tag.id');
    		$query_tags = $this->db->get();
    		$tags = "";

    		foreach ($query_tags->result() as $tag) {
    			$tags = $tag->name." - ";
    		}

    		$title = $workflow->title;
    		$description = $workflow->description;

    		// Merging the metadata
    		$free_text = $title."\n".$description."\n".$tags;


    		$PARAMETERS = 	http_build_query(
	    						array(
	    							'ontologies' => $ontologies,
	    							'text' => $free_text,
	    							'apyKey' => NCBO_ANNOTATOR_API_KEY,
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

			$raw_content = file_get_contents($ANNOTATOR_URL.$PARAMETERS, false, $CONTEXT);
			$annotations = json_decode($raw_content);
			$data = array(),

			foreach ($annotations as $annotation) {

				$this->db->select('id');
				$this->db->where('iri', $annotation->annotatedClass->id);
				$query_term = $this->db->get('term', 1, 0);
				$term = $query_term->row();

				if($term != null) {
					$data[] = array(
						'id_workflow' => $workflow->id,
						'id_term' => $term->id,
						'source' => 'NCBO Annotator',
						'created_at' => date("Y-m-d H:i:s")
					);
				}


			}

			// Insert the semantic annotations
			$this->db->insert_batch('ncbo_annotation', $data);

    	}

    }

}

/* End of file NCBOAnnotator_model.php */
/* Location: ./application/models/NCBOAnnotator_model.php */