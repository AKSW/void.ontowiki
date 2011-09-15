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
     * Schema URIs should be created as class constants
     */
    const DCTERM_creator        = 'http://purl.org/dc/terms/creator';
    const DCTERM_source         = 'http://purl.org/dc/terms/source';
    const DCTERM_license        = 'http://purl.org/dc/terms/license';
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

    /*
     * an array of terms which are tested and HIGHLY suggested
     * TODO: define more suggestions here
     */
    private $suggestedHigh = array(
        self::DCTERM_creator,
        self::DCTERM_license
    );

    /*
     * an array of terms which are tested and suggested as NICE-TO-HAVE
     */
    private $suggestedMedium = array(
        self::DCTERM_source
    );


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
        $this->erfurt   = $this->_owApp->erfurt;
        $this->store    = $this->erfurt->getStore();
        $this->config   = $this->_owApp->config;
        $this->response = Zend_Controller_Front::getInstance()->getResponse();
        $this->request  = Zend_Controller_Front::getInstance()->getRequest();

        // no action needs a view and a layout
        $this->_helper->viewRenderer->setNoRender();
        $this->_helper->layout()->disableLayout();

        // We need the graph URI
        $this->graph    = OntoWiki::getInstance()->selectedModel;
        $this->graphUri = $this->graph->getModelIri();

        // In addition to the store, we fetch the resource description, which
        // is a more time efficient way to check of direct properties
        $this->graphResource = new OntoWiki_Resource($this->graphUri, $this->graph);
        $this->memModel = new Erfurt_Rdf_MemoryModel($this->graphResource->getDescription());
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
        $this->graph->deleteMatchingStatements($graphUri, self::VOID_dataDump, null);

        // 3. Add a statement for the SPARQL endpoint
        $endpointUrl = $urlBase . '/service/sparql';
        $newGraph->addRelation($graphUri, self::VOID_sparqlEndpoint, $endpointUrl);
        $this->graph->deleteMatchingStatements($graphUri, self::VOID_sparqlEndpoint, null);

        // 4. void:triples
        $count = $this->store->countWhereMatches($this->graphUri, 'WHERE{?s ?p ?o}', '*', false, false);
        $newGraph->addAttribute($graphUri, self::VOID_triples, $count);
        $this->graph->deleteMatchingStatements($graphUri, self::VOID_triples, null);

        // 5. void:entities
        $count = $this->store->countWhereMatches($this->graphUri, 'WHERE{?s a ?o}', '?s', true, false);
        $newGraph->addAttribute($graphUri, self::VOID_entities, $count);
        $this->graph->deleteMatchingStatements($graphUri, self::VOID_entities, null);

        // 6. void:classes
        $count = $this->store->countWhereMatches($this->graphUri, 'WHERE{?s a ?o}', '?o', true, false);
        $newGraph->addAttribute($graphUri, self::VOID_classes, $count);
        $this->graph->deleteMatchingStatements($graphUri, self::VOID_classes, null);

        // 7. void:properties
        $count = $this->store->countWhereMatches($this->graphUri, 'WHERE{?s ?p ?o}', '?p', true, false);
        $newGraph->addAttribute($graphUri, self::VOID_properties, $count);
        $this->graph->deleteMatchingStatements($graphUri, self::VOID_properties, null);

        // 8. void:distinctSubjects
        $count = $this->store->countWhereMatches($this->graphUri, 'WHERE{?s ?p ?o}', '?s', true, false);
        $newGraph->addAttribute($graphUri, self::VOID_distinctSubjects, $count);
        $this->graph->deleteMatchingStatements($graphUri, self::VOID_distinctSubjects, null);

        // 9. void:distinctObjects
        $count = $this->store->countWhereMatches($this->graphUri, 'WHERE{?s ?p ?o}', '?o', true, false);
        $newGraph->addAttribute($graphUri, self::VOID_distinctObjects, $count);
        $this->graph->deleteMatchingStatements($graphUri, self::VOID_distinctObjects, null);

        // Now we add the statements to the store.
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
     * suggest action: scans for highly suggested as well as nice to have
     * attributes which can not be created automatically and suggests the
     * missing attributes
     *
     * @todo add more attributes to scan for (at the beginning of this file)
     */
    public function suggestAction()
    {
        // we now prepare everything at init
        $memModel = $this->memModel;
        $graphUri = $this->graphUri;
        $graph    = $this->graph;

        // an array of HIGHLY suggested properties which do NOT exist yet
        $notFoundHigh = array();
        // test for the existence of all HIGHLY suggested property
        foreach ($this->suggestedHigh as $propertyUri) {
            if (!$memModel->hasSP($graphUri, $propertyUri)) {
                $notFoundHigh[] = $propertyUri;
            }
        }

        // an array of MEDIUM suggested properties which do NOT exist yet
        $notFoundMedium = array();
        foreach ($this->suggestedMedium as $propertyUri) {
            if (!$memModel->hasSP($graphUri, $propertyUri)) {
                $notFoundMedium[] = $propertyUri;
            }
        }

        // enrich the the array with property titles: fill the titlehelper
        $titleHelper = new OntoWiki_Model_TitleHelper($graph);
        $titleHelper->addResources(array_merge($notFoundHigh, $notFoundMedium));

        // enrich the the array with property titles: fetch the titles
        $url = new OntoWiki_Url(
            array('controller' => 'resource', 'action' => 'properties'),
            array('r')
        );
        foreach ($notFoundHigh as $propertyUri) {
            $url->setParam('r', $propertyUri);
            $finalHigh[$propertyUri]          = array();
            $finalHigh[$propertyUri]['url']   = (string) $url;
            $finalHigh[$propertyUri]['uri']   = $propertyUri;
            $finalHigh[$propertyUri]['label'] = $titleHelper->getTitle($propertyUri);
        }
        foreach ($notFoundMedium as $propertyUri) {
            $url->setParam('r', $propertyUri);
            $finalMedium[$propertyUri]          = array();
            $finalMedium[$propertyUri]['url']   = (string) $url;
            $finalMedium[$propertyUri]['uri']   = $propertyUri;
            $finalMedium[$propertyUri]['label'] = $titleHelper->getTitle($propertyUri);
        }
        // build the final output structure
        $suggestions = array();
        $suggestions[0]['class']      = 'message error';
        $suggestions[0]['label']      = 'Highly Recommended Properties';
        $suggestions[0]['properties'] = $finalHigh;
        $suggestions[1]['class']      = 'message warning';
        $suggestions[1]['label']      = 'Suggested Additional Properties';
        $suggestions[1]['properties'] = $finalMedium;

        // send the response
        $this->response->setHeader('Content-Type', 'application/json');
        $this->response->setBody(json_encode($suggestions));
        $this->response->sendResponse();
        exit;
    }
}
