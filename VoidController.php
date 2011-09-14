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
    /*
     * Schema URIs should be created as class constants
     */
    const DCTERM_creator = 'http://purl.org/dc/terms/creator';
    const DCTERM_source  = 'http://purl.org/dc/terms/source';
    const DCTERM_license = 'http://purl.org/dc/terms/license';

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
        $this->store    = $this->_owApp->erfurt->getStore();
        $this->config   = $this->_owApp->config;
        $this->response = Zend_Controller_Front::getInstance()->getResponse();
        $this->request  = Zend_Controller_Front::getInstance()->getRequest();
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
        $sparql = <<<'EOT'

EOT;
        $result = $graph->sparqlQuery($sparql);
        // TODO

        // 5. void:entities
        $sparql = <<<'EOT'

EOT;
        $result = $graph->sparqlQuery($sparql);
        // TODO

        // 6. void:classes
        $sparql = <<<'EOT'

EOT;
        $result = $graph->sparqlQuery($sparql);
        // TODO

        // 7. void:properties
        $sparql = <<<'EOT'

EOT;
        $result = $graph->sparqlQuery($sparql);
        // TODO

        // Now we add the statements to the store.
        $store->addMultipleStatements($graphUri, $statements);
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
        // this action needs no view
        $this->_helper->viewRenderer->setNoRender();

        // disable layout
        $this->_helper->layout()->disableLayout();

        // We need the graph URI
        $graph    = OntoWiki::getInstance()->selectedModel;
        $graphUri = $graph->getModelIri();

        // We need a reference to the store for SPARQL and adding the statements.
        $store = Erfurt_App::getInstance()->getStore();

        // In addition to the store, we fetch the resource description, which
        // is a more time efficient way to check of direct properties
        $graphResource = new OntoWiki_Resource($graphUri, $graph);
        $memModel = new Erfurt_Rdf_MemoryModel($graphResource->getDescription());

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
