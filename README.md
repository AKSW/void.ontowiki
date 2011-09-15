# VoiD creation / suggestion extension for OntoWiki [edit this file](https://github.com/AKSW/void.ontowiki/edit/master/README.md)

> VoID is an RDF Schema vocabulary for expressing metadata about RDF
> datasets. It is intended as a bridge between the publishers and
> users of RDF data, with applications ranging from data discovery to
> cataloging and archiving of datasets.

Presentation slides: https://docs.google.com/present/edit?id=0AdCkvqPnyU_qZGRjM3d3YzVfMTFobXpqcnRkZA&hl=es

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

__Preferred property:__ `cc:license`
__Alternative properties:__
* `dc:rights`

    PREFIX cc: <http://creativecommons.org/ns#>
    PREFIX dc: <http://purl.org/dc/elements/1.1/>
    
    ASK {
      { ?s cc:license ?licence }
      UNION
      { ?s dc:rights ?licence }
    }

### Creator

__Preferred property:__ `dcterms:creator`
__Alternative properties:__
* `dc:creator`
* `dcterms:contributor`

    PREFIX dc: <http://purl.org/dc/elements/1.1/>
    PREFIX dcterms: <http://purl.org/dc/terms/>
    PREFIX void: <http://rdfs.org/ns/void#>

    ASK {
     ?s a void:Dataset .
     { ?s dc:creator ?creator }
     UNION
     { ?s dcterms:creator ?creator }
     UNION
     { ?s dcterms:contributor ?creator }
    }

### Description

__Preferred property:__ `dcterms:description`

### Highly suggested

#### Title

Preferred property: `dcterms:title`
Alternative properties:
* `dc:title`
* `rdfs:label`

    PREFIX dc: <http://purl.org/dc/elements/1.1/>
    PREFIX dcterms: <http://purl.org/dc/terms/>
    PREFIX void: <http://rdfs.org/ns/void#>
    
    ASK {
      ?s a void:Dataset .
      { ?s dcterms:title ?title }
      UNION
      { ?s dc:title ?title }
    }

### Nice To Have
