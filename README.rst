WorkflowHunt
========================

WorkflowHunt is a search engine for scientific workflow repositories.

Theoretical Foundation
========================

Semantic Annotations
____________________

Code::
    def semantic_annotation()
        // Semantic Annotations: <s, m, o>
        // s: subject
        // m: predicate
        // o: object
        foreach ontology in ontologies
            foreach term in ontology->terms
                foreach workflow in workflows                   
                    if( term->label  ⊆ workflow->metadata )
                        save( term->url, contained_in, workflow->url      

                    foreach synonym in term->synonyms
                        if( synonym->label  ⊆ workflow->metadata )
                            save( term->url, contained_in, workflow->url )


Semantic Search
_______________

Code::
    def semantic_search( query )
        terms_detected = ∅
        results = ∅

        // First Step: Detecting ontology terms in the query
        foreach ontology in ontologies
            foreach term in ontology->terms
                if( term->label ⊆ query )
                    terms_detected.add( term->url )

                foreach synonym in term->synonyms
                    if( synonym->label  ⊆ workflow->metadata )
                        terms_detected.add( term->url )

        // Second Step: Searching workflows that have semantic annotations with 
        // ontology terms in terms_detected
        foreach sa in semantic_annotations
            if( sa->s  ⊆ terms_detected )
                workflow = search_workflow( sa->o )
                results.add( workflow )

        return results

