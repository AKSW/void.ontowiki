<?php
/**
 * This file is part of the {@link http://ontowiki.net OntoWiki} project.
 *
 * @copyright Copyright (c) 2011, {@link http://aksw.org AKSW}
 * @license http://opensource.org/licenses/gpl-license.php GNU General Public License (GPL)
 */

/**
 * The main VoID controller
 *
 * @category   OntoWiki
 * @package    OntoWiki_extensions_components_void
 * @subpackage component
 */
class VoidController extends OntoWiki_Controller_Component
{
    /** @var Zend_Controller_Response_Abstract */
    protected $response = null;

    /**
     * Constructor
     */
    public function init()
    {
        // this provides many default controller vars and other stuff ...
        parent::init();
        // init controller variables
        $this->store     = $this->_owApp->erfurt->getStore();
        $this->config   = $this->_owApp->config;
        $this->response  = Zend_Controller_Front::getInstance()->getResponse();
        $this->request   = Zend_Controller_Front::getInstance()->getRequest();
    }

    /**
     * create action
     */
    public function createAction()
    {
        // this action needs no view
        $this->_helper->viewRenderer->setNoRender();

        // disable layout
        $this->_helper->layout()->disableLayout();

        // First we need to create an array with the statements to be added.
        // Have a look at here: http://docs.api.talis.com/platform-api/output-types/rdf-json
        $statements = array();

        // We need the graph URI
        $graph = OntoWiki::getInstance()->selectedModel;
        $graphUri = $graph->getModelIri();

        // We need a reference to the store for SPARQL and adding the statements.
        $store = Erfurt_App::getInstance()->getStore();

        // Now we add the statements to the array

        // 1. Add a statement graph a void:Dataset
        $statements[$graphUri] = array();
        $statements[$graphUri][EF_RDF_TYPE] = array();
        $statements[$graphUri][EF_RDF_TYPE][] = array(
            'type'  => 'uri',
            'value' => 'http://rdfs.org/ns/void#Dataset'
        );

        // 2. Add a statement for the dump
        $baseUri = OntoWiki::getInstance()->urlBase;
        $dumpUri = $baseUri . '/model/export/?f=rdfxml&m=' . urlencode($graphUri);
        // TODO

        // 3. Add a statement for the SPARQL endpoint
        $endpointUri = $baseUri . '/service/sparql';
        // TODO

        // 4. void:triples
        $sparql = 'TODO';
        $result = $graph->sparqlQuery($sparql);
        // TODO

        // 5. void:entities
        $sparql = 'TODO';
        $result = $graph->sparqlQuery($sparql);
        // TODO

        // 6. void:classes
        $sparql = 'TODO';
        $result = $graph->sparqlQuery($sparql);
        // TODO

        // 7. void:properties
        $sparql = 'TODO';
        $result = $graph->sparqlQuery($sparql);
        // TODO
        
        // Now we add the statements to the store.
        $store->addMultipleStatements($graphUri, $statements);
    }

    public function suggestAction()
    {
        // this action needs no view
        $this->_helper->viewRenderer->setNoRender();

        // disable layout
        $this->_helper->layout()->disableLayout();

        // We need the graph URI
        $graph = OntoWiki::getInstance()->selectedModel;
        $graphUri = $graph->getModelIri();

        // We need a reference to the store for SPARQL and adding the statements.
        $store = Erfurt_App::getInstance()->getStore();

        // SPARQL queries go here...
        $sparql = '';
        $result = $graph->sparqlQuery($sparql);
        // TODO

        $suggestions = array();
        // TODO

        return json_encode($suggestions);
    }
}
