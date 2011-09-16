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
 * @link       https://github.com/AKSW/void.ontowiki
 * @category   OntoWiki
 * @package    OntoWiki_extensions_components_void
 * @subpackage component
 */
class VoidController extends OntoWiki_Controller_Component
{
    /*
     * the suggested properties: an array of categories, which are arrays of 
     * suggestion objects (stdClass)
     */
    private $suggestions = array();

    /*
     * Schema URIs should be created as class constants
     */
    const VOID                  = 'http://rdfs.org/ns/void#';
    const VOID_Dataset          = 'http://rdfs.org/ns/void#Dataset';
    const VOID_triples          = 'http://rdfs.org/ns/void#triples';
    const VOID_classes          = 'http://rdfs.org/ns/void#classes';
    const VOID_entities         = 'http://rdfs.org/ns/void#entities';
    const VOID_distinctObjects  = 'http://rdfs.org/ns/void#distinctObjects';
    const VOID_distinctSubjects = 'http://rdfs.org/ns/void#distinctSubjects';
    const VOID_properties       = 'http://rdfs.org/ns/void#properties';
    const VOID_dataDump         = 'http://rdfs.org/ns/void#dataDump';
    const VOID_sparqlEndpoint   = 'http://rdfs.org/ns/void#sparqlEndpoint';

    /**
     * Constructor
     */
    public function init()
    {
        // We need the graph URI
        $this->graph    = OntoWiki::getInstance()->selectedModel;
        if (!$this->graph) {
            // no session or no model selected
            exit;
        }

        // this provides many default controller vars and other stuff ...
        parent::init();
        // init controller variables
        $this->erfurt   = $this->_owApp->erfurt;
        $this->store    = $this->erfurt->getStore();
        $this->config   = $this->_owApp->config;
        $this->response = Zend_Controller_Front::getInstance()->getResponse();
        $this->request  = Zend_Controller_Front::getInstance()->getRequest();

        // no action needs a view and a layout
        $this->_helper->viewRenderer->setNoRender();
        $this->_helper->layout()->disableLayout();

        $this->graphUri = $this->graph->getModelIri();

        // In addition to the store, we fetch the resource description, which
        // is a more time efficient way to check of direct properties
        $this->graphResource = new OntoWiki_Resource($this->graphUri, $this->graph);
        $this->memModel = new Erfurt_Rdf_MemoryModel($this->graphResource->getDescription());

        $this->titleHelper = new OntoWiki_Model_TitleHelper($this->graph);
    }

    /**
     * clean action
     */
    public function cleanAction()
    {
        // start a versioning transaction
        // currently no registry for types :-(
        $versioning = $this->erfurt->getVersioning();
        $actionSpec                = array();
        $actionSpec['type']        = 81001;
        $actionSpec['modeluri']    = (string) $this->graphUri;
        $actionSpec['resourceuri'] = (string) $this->graphUri;
        $versioning->startAction($actionSpec);
        $this->removeDescription();
        $versioning->endAction();

        // send the response
        $this->response->setHeader('Content-Type', 'application/json');
        $this->response->setBody(json_encode('success'));
        $this->response->sendResponse();
        exit;
    }

    /*
     * removes the automatically created void properties from the resource
     */
    private function removeDescription()
    {
        // to keep source code lines short
        $graphUri = $this->graphUri;
        $graph    = $this->graph;

        // Now we remove all the auto-generated void statements
        $graph->deleteStatement($graphUri, EF_RDF_TYPE, array('value' => self::VOID_Dataset, 'type' => 'uri'));
        $graph->deleteMatchingStatements($graphUri, self::VOID_dataDump, null);
        $graph->deleteMatchingStatements($graphUri, self::VOID_sparqlEndpoint, null);
        $graph->deleteMatchingStatements($graphUri, self::VOID_triples, null);
        $graph->deleteMatchingStatements($graphUri, self::VOID_entities, null);
        $graph->deleteMatchingStatements($graphUri, self::VOID_classes, null);
        $graph->deleteMatchingStatements($graphUri, self::VOID_properties, null);
        $graph->deleteMatchingStatements($graphUri, self::VOID_distinctSubjects, null);
        $graph->deleteMatchingStatements($graphUri, self::VOID_distinctObjects, null);
    }

    /**
     * create action
     */
    public function createAction()
    {
        // to keep source code lines short
        $graphUri = $this->graphUri;
        // we need to create memory model with the statements to add
        $newGraph = new Erfurt_Rdf_MemoryModel();

        // start a versioning transaction
        // currently no registry for types :-(
        $versioning = $this->erfurt->getVersioning();
        $actionSpec                = array();
        $actionSpec['type']        = 81000;
        $actionSpec['modeluri']    = (string) $graphUri;
        $actionSpec['resourceuri'] = (string) $graphUri;
        $versioning->startAction($actionSpec);

        // Now we add the statements to the memory model

        // 1. Add a statement graph a void:Dataset
        $newGraph->addRelation($graphUri, EF_RDF_TYPE, self::VOID_Dataset);

        // 2. Add a statement for the dump
        $urlBase = $this->_owApp->view->urlBase;
        $dumpUrl = $urlBase . '/model/export/?f=rdfxml&m=' . urlencode($graphUri);
        $newGraph->addRelation($graphUri, self::VOID_dataDump, $dumpUrl);

        // 3. Add a statement for the SPARQL endpoint
        $endpointUrl = $urlBase . '/service/sparql';
        $newGraph->addRelation($graphUri, self::VOID_sparqlEndpoint, $endpointUrl);

        // 4. void:triples
        $count = $this->store->countWhereMatches($this->graphUri, 'WHERE{?s ?p ?o}', '*', false, false);
        $newGraph->addAttribute($graphUri, self::VOID_triples, $count);

        // 5. void:entities
        $count = $this->store->countWhereMatches($this->graphUri, 'WHERE{?s a ?o}', '?s', true, false);
        $newGraph->addAttribute($graphUri, self::VOID_entities, $count);

        // 6. void:classes
        $count = $this->store->countWhereMatches($this->graphUri, 'WHERE{?s a ?o}', '?o', true, false);
        $newGraph->addAttribute($graphUri, self::VOID_classes, $count);

        // 7. void:properties
        $count = $this->store->countWhereMatches($this->graphUri, 'WHERE{?s ?p ?o}', '?p', true, false);
        $newGraph->addAttribute($graphUri, self::VOID_properties, $count);

        // 8. void:distinctSubjects
        $count = $this->store->countWhereMatches($this->graphUri, 'WHERE{?s ?p ?o}', '?s', true, false);
        $newGraph->addAttribute($graphUri, self::VOID_distinctSubjects, $count);

        // 9. void:distinctObjects
        $count = $this->store->countWhereMatches($this->graphUri, 'WHERE{?s ?p ?o}', '?o', true, false);
        $newGraph->addAttribute($graphUri, self::VOID_distinctObjects, $count);

        // first we remove the old description
        $this->removeDescription();
        // Now we add the new statements to the store.
        $statements = $newGraph->getStatements();
        $this->graph->addMultipleStatements($statements);
        $versioning->endAction();

        // send the response
        $this->response->setHeader('Content-Type', 'application/json');
        $this->response->setBody(json_encode('success'));
        $this->response->sendResponse();
        exit;
    }

    /*
     * this method maintains the output structure for the suggestAction
     */
    private function addSuggestion($propertyUri, $categoryName)
    {
        // get the suggestion category = an array of stdClass objects
        if ( (!isset($this->suggestions[$categoryName])) || (!is_array($this->suggestions[$categoryName])) ) {
            $this->suggestions[$categoryName] = array();
        }
        $category = $this->suggestions[$categoryName];

        // prepare a link to the properties view
        $url = new OntoWiki_Url(
            array('controller' => 'resource', 'action' => 'properties'),
            array('r')
        );
        $url->setParam('r', $propertyUri);

        // create the properties object
        $property               = new stdClass();
        $property->uri          = $propertyUri;
        $property->url          = (string) $url;

        // add the property object to the category and the category back to the 
        // suggestions array
        $category[$propertyUri]           = $property;
        $this->suggestions[$categoryName] = $category;

        // pre-fill the titleHelper (used in getSuggestions)
        $this->titleHelper->addResource($propertyUri);
    }

    /*
     * finalizes the suggestAction output and retuns a json_encoded string
     */
    private function getSuggestions()
    {
        // fetch all these titles from the helper
        $suggestions = $this->suggestions;
        foreach ($suggestions as $categoryName => $category) {
            foreach ($category as $propertyUri => $property) {
                $label = $this->titleHelper->getTitle($propertyUri);
                $suggestions[$categoryName][$propertyUri]->label = $label;
            }
        }

        // return structure consists of categories and suggestions
        $return = array();
        $return['categories'] = $this->_privateConfig->categories->toArray();
        $return['suggestions'] = $suggestions;
        return $return;
    }

    /*
     * suggest action: scans for highly suggested as well as nice to have
     * attributes which can not be created automatically and suggests the
     * missing attributes
     *
     * @todo add more attributes to scan for (at the beginning of this file)
     */
    public function suggestAction()
    {
        $memModel    = $this->memModel;
        $graphUri    = $this->graphUri;
        $graph       = $this->graph;
        $suggestions = $this->_privateConfig->suggestions;

        // a suggestion should have a preferred and a category,
        // and optional alternatives
        foreach ($suggestions as $key => $s) {
            // skip incorrect config
            if (!isset($s->preferred)) {
                continue;
            }

            // test for the preferred property first
            if (!$memModel->hasSP($graphUri, $s->preferred)) {
                // preferred property is not available, so try alternatives
                $foundAlternative = false;
                // go through alternatives and check them too
                if ( (isset($s->alternatives)) && (is_array($s->alternatives)) ) {
                    foreach ($s->alternatives as $alternative) {
                        if ($memModel->hasSP($graphUri, $alternative)) {
                            $foundAlternative = true;
                            // skip rest if an alternative was found
                            break;
                        }
                    }
                }
                // check if we found an alternative property
                if ($foundAlternative === false) {
                    // even no alternative was found, so suggest the preferred 
                    // property for this case
                    $this->addSuggestion($s->preferred, $s->category);
                }
                // do nothing if an alternative was found
            }
            // do nothing if the preferred property was found
        }

        // send the response
        $this->response->setHeader('Content-Type', 'application/json');
        $this->response->setBody(json_encode($this->getSuggestions()));
        $this->response->sendResponse();
        exit;
    }
}
