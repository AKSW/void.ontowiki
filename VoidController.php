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
    }
}
