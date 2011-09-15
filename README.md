# VoiD creation / suggestion extension for OntoWiki [edit this file](https://github.com/AKSW/void.ontowiki/edit/master/README.md)

> VoID is an RDF Schema vocabulary for expressing metadata about RDF
> datasets. It is intended as a bridge between the publishers and
> users of RDF data, with applications ranging from data discovery to
> cataloging and archiving of datasets.

Presentation slides: https://docs.google.com/present/edit?id=0AdCkvqPnyU_qZGRjM3d3YzVfMTFobXpqcnRkZA&hl=es

This extension should be able:

  * to generate VoID statements automatically where possible, and
  * to suggest additional content based on a suggestion-list
  
The following prefixes are used in this document:

    @cc:       <http://creativecommons.org/ns#>
    @dc:       <http://purl.org/dc/elements/1.1/>
    @dcterms:  <http://purl.org/dc/terms/>
    @foaf:     <http://xmlns.com/foaf/0.1/>
    @rdfs:     <http://www.w3.org/2000/01/rdf-schema#>
    @void:     <http://rdfs.org/ns/void#>
    @wv:       <http://vocab.org/waiver/terms/>

## Generated Content

### Statistics

#### void:classes

    SELECT (COUNT(DISTINCT ?o) AS ?no) WHERE {
      ?s a ?o .
    }

#### void:distinctObjects

    SELECT (COUNT(DISTINCT ?o) AS ?no) WHERE {
      ?s ?p ?o .
    }
    
#### void:distinctSubjects

    SELECT (COUNT(DISTINCT ?s) AS ?no) WHERE {
      ?s ?p ?o .
    }
    
#### void:entities

    SELECT (COUNT(DISTINCT ?s) AS ?no) WHERE {
      ?s a [] .
    }
    
### void:properties

    SELECT (COUNT(DISTINCT ?p) AS ?no) WHERE {
      ?s ?p ?o .
    }
    
#### void:triples

    SELECT (COUNT(*) AS ?no) {
      ?s ?p ?o .
    }
    
#### void:vocabulary

To be retrieved via OntoWiki.
    
### Hosting / Feature description

#### void:dataDump

To be retrieved via OntoWiki.

#### void:feature

List of available RDF searializations to be retrived via OntoWiki.

#### void:openSearchDescription

To be retrieved via OntoWiki.

#### void:sparqlEndpoint

To be retrieved via OntoWiki.

#### void:subset

List all named graphs in a given OntoWiki dataset.

#### void:uriLookupEndpoint

To be retrieved via OntoWiki.

#### void:uriSpace

To be retrieved via OntoWiki.

### Linksets


## Suggested Content

The suggested content is based on the recommendation from the [VoID specification](http://www.w3.org/TR/void/#dublin-core).

### Example representing the dataset

*  __Preferred property:__ `void:exampleResource`

### Licence

*  __Preferred property:__ `cc:license`
*  __Alternative properties:__
    *  `dc:rights`
    *  `wv:norms`
    *  `wv:waiver`


    PREFIX cc: <http://creativecommons.org/ns#>
    PREFIX dc: <http://purl.org/dc/elements/1.1/>
    
    ASK {
      { ?s cc:license ?licence }
      UNION
      { ?s dc:rights ?licence }
    }

### Creator

*  __Preferred property:__ `dcterms:creator`
*  __Alternative properties:__
    *  `dc:creator`
    *  `dcterms:contributor`

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

*  __Preferred property:__ `dcterms:description`
*  __Alternative properties:__
   *  `rdfs:comment`

### Publisher

*  __Preferred property:__ `dcterms:publisher`

### Source of the dataset

*  __Preferred property:__ `dcterms:source`

### Date

*  __Preferred property:__ `dcterms:date`

### Date of issue

*  __Preferred property:__ `dcterms:issued`

### Date of modification

*  __Preferred property:__ `dcterms:modified`

### Highly suggested

#### Title

*  __Preferred property:__ `dcterms:title`
*  __Alternative properties:__
    *  `dc:title`
    *  `rdfs:label`


    PREFIX dc: <http://purl.org/dc/elements/1.1/>
    PREFIX dcterms: <http://purl.org/dc/terms/>
    PREFIX void: <http://rdfs.org/ns/void#>
    
    ASK {
      ?s a void:Dataset .
      { ?s dcterms:title ?title }
      UNION
      { ?s dc:title ?title }
    }

#### Date created

*  __Preferred property:__  `dcterms:created`

### Nice To Have

#### Homepage of the publisher

*  __Preferred property:__ `foaf:homepage`

#### Root resource

*  __Preferred property:__ `void:rootResource`

#### Subject described by the dataset

*  __Preferred property:__ `dcterms:subject`
*  __Alternative properties:__
    *  `dc:subject`
    *  `foaf:primaryTopic`
    
#### URI pattern of the dataset

*  __Preferred property:__ `void:uriRegexPattern`
