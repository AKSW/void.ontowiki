# VoiD creation / suggestion extension for OntoWiki [edit this file](https://github.com/AKSW/void.ontowiki/edit/master/README.md)

> VoID is an RDF Schema vocabulary for expressing metadata about RDF
> datasets. It is intended as a bridge between the publishers and
> users of RDF data, with applications ranging from data discovery to
> cataloging and archiving of datasets.

This extension should be able:

  * to generate VoID statements automatically where possible, and
  * to suggest additional content based on a suggestion-list

## Generated Content

### Statistics

#### void:triples

    SELECT (COUNT(*) AS ?no) {
      ?s ?p ?o .
    }
   
#### void:entities

    SELECT (COUNT(DISTINCT ?s) AS ?no) WHERE {
      ?s a [] .
    }
    
#### void:classes

    SELECT (COUNT(DISTINCT ?o) AS ?no) WHERE {
      ?s a ?o .
    }
    
### void:properties

    SELECT (COUNT(DISTINCT ?p) AS ?no) WHERE {
      ?s ?p ?o .
    }
    
### Hosting / Feature description

### Linksets


## Suggested Content

### Licence

    PREFIX cc: <http://creativecommons.org/ns#>
    PREFIX dc: <http://purl.org/dc/elements/1.1/>
    
    ASK {
      { ?s cc:license ?o1 }
      UNION
      { ?s dc:rights ?o2 }
    }

### Creator
    
    PREFIX dc: <http://purl.org/dc/elements/1.1/>
    PREFIX dcterms: <http://purl.org/dc/terms/>
    PREFIX void: <http://rdfs.org/ns/void#>

    ASK {
     ?s a void:Dataset .
     { ?s dc:creator ?creator1 }
     UNION
     { ?s dcterms:creator ?creator2}
    }
    
### Highly suggested

### Nice To Have
