# WorkflowHunt

WorkflowHunt is a search engine for scientific workflow repositories.

# Theoretical Foundation

This project has two important parts: semantic annotations and semantic search based on those semantic annotations.

## Semantic Annotations

We perform semantic annotations of workflows following the code below. First, we iterate over the ontologies available in the system. Second, we iterate over the terms of each ontology. Third, we iterate over the workflows available in the system. Fourth, we verify if the ontology term is contained in the workflow metadata using exact string comparison if so, then we perform a semantic annotation using that ontology term and the workflow. Fifth, we verify if the synonym of the ontology term is contained in the workflow metadata if so, then we perform a semantic annotation using the ontology term associated with the synonym and the workflow.

Semantic annotations use the format <s, p, o, c>, where s is the subject of annotation (workflow), o is the object of annotation (a term in a domain ontology), p is the predicate (the relationship between s and p), and c is the context (provenance information) [1].

```
def semantic_annotation()
    foreach ontology in ontologies
        foreach term in ontology
            foreach workflow in workflows                   
                if( term  ⊆ workflow->metadata )
                    save( workflow, contains, term, { author, date } )

                foreach synonym in term
                    if( synonym  ⊆ workflow->metadata )
                        save( workflow, contains, term, { author, date } )
```

## Semantic Search

The semantic search algorithm uses a two-step approach:

**Step 1**. First, we iterate over the ontologies available in the system. Second, we iterate over the terms of each ontology. Third, we verify if the ontology term is contained in the user query using exact string comparison if so, then we store the ontology terms detected in the user query.

**Step 2**. First, we iterate over the semantic annotations stored in the system. Second, we select the workflows that have semantic annotations which match with the ontology term detected in Step 1. Finally, we return those workflows (without ranking their relevance).

```
def semantic_search( query )
    terms_detected = ∅
    results = ∅

    // First Step: Detecting ontology terms in the query
    foreach ontology in ontologies
        foreach term in ontology
            if( term ⊆ query )
                terms_detected.add( term )

            foreach synonym in term
                if( synonym  ⊆ workflow->metadata )
                    terms_detected.add( term )

    // Second Step: Searching workflows that have semantic annotations with 
    // ontology terms in terms_detected
    foreach sa in semantic_annotations
        if( sa->o  ⊆ terms_detected )
            workflow = search_workflow( sa->o )
            results.add( workflow )

    return results
```

## References

[1] Oren, E., Möller, K., Scerri, S., Handschuh, S., & Sintek, M. (2006). What are semantic annotations. Technical report. DERI Galway, 9, 62.

