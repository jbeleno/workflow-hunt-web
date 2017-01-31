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
 * WorkflowHunt Ontology Model
 *
 * GAMBIARRA ALERT: Temporarily, I'm using ontology names manually, so
 * I have a table called 'ontology' with the following fields: id, name, 
 * prefix, and iri. Currently, I'm working with EDAM and CHEMINF.
 *
 * @category	Models
 * @author		Juan Sebastián Beleño Díaz
 * @link		xxx
 */
class Ontology_model extends CI_Model {

	/**
	 * Ontologies URL from OLS (Ontology Lookup Service) API
	 *
	 * @var	string
	 */
	private $ONTOLOGIES_URL = "http://www.ebi.ac.uk/ols/api/ontologies/";

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
	 * Download and Save Ontology Terms in the Database
	 *
	 * Each page that contains ontology terms is scrapped in JSON format and
	 * the ontology terms with synonyms are stored in a relational database
	 * NOTE: Maybe is better to store this data in a JSON database like MongoDB.
	 *		 Nevertheless, this will be done later (probably).
	 *
	 * @return	array
	 */
    public function download_terms()
    {
    	$this->db->select('id, name, prefix');
    	$query_ont = $this->db->get('ontology');

    	foreach ($query_ont->result() as $ontology) 
    	{
    		$page = 0;
    		$size = 20;
    		$total_pages = 1;

    		// Iterate over all the pages that have ontology terms
    		do
    		{
	    		$ONTOLOGY_URL = $this->ONTOLOGIES_URL.$ontology->name.'/terms?page='.$page.'&size='.$size;

	    		// Request the content in JSON format
	    		$context  = stream_context_create(
	    						array(
	    							'http' => array(
	    										'header' => 'Accept: application/json'
	    										)
	    							)
	    						);

				$raw_content = file_get_contents($ONTOLOGY_URL, false, $context);
				$json_content = json_decode($raw_content);

				$total_pages = $json_content->page->totalPages;
				$terms = $json_content->_embedded->terms;

				// Iterate over each ontology term
				for ($i=0; $i < $size; $i++) 
				{ 
					$term = $terms[$i];

					if(!is_null($term))
					{
						$data_term = array(
							'label' => $term->label,
							'id_ontology' => $ontology->id,
							'description' => $term->description[0],
							'iri' => $term->iri,
							'short_form' => $term->short_form,
							'obo_id' => $term->obo_id,
							'date' => date("Y-m-d H:i:s")
						);

						// Storing the term in the database
						$this->db->insert('term', $data_term);

						// Getting the term id in the database to save synonyms
						$term_id = $this->db->insert_id();

						if(!is_null($term->synonyms))
						{
							foreach ($term->synonyms as $synonym) {
								$data_synonym = array(
									'id_term' => $term_id,
									'name' => $synonym,
									'source' => $ontology->prefix,
									'date' => date("Y-m-d H:i:s")
								);

								// Save synonyms
								$this->db->insert('synonym', $data_synonym);
							}
						}
					}	
				}

				$page++;
			} while( $page < $total_pages );

    	}

    	return array( 'status' => 'OK' );
    }

}

/* End of file Ontology_model.php */
/* Location: ./application/models/Ontology_model.php */