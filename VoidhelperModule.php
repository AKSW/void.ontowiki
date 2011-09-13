<?php
/**
 * This file is part of the {@link http://ontowiki.net OntoWiki} project.
 *
 * @copyright Copyright (c) 2011, {@link http://aksw.org AKSW}
 * @license http://opensource.org/licenses/gpl-license.php GNU General Public License (GPL)
 */

/**
 * OntoWiki module – VoID Helper
 *
 * presents a void generation button as well as a list of suggested
 * content to allow gradual dataset improvement
 *
 * @category   OntoWiki
 * @package    extensions_modules_void
 */
class VoidhelperModule extends OntoWiki_Module
{
    /*
     * The description of the selected resource
     */
    public $description = null;

    /*
     * The dataset resource
     */
    public $selectedResource = null;

    /*
     * The rendered content of the module
     */
    public $content = null;

    /*
     * indicates that the module should be shown or not
     */
    public $shouldShow = true;

    public function init()
    {
        if (!isset(OntoWiki::getInstance()->selectedResource)) {
            return;
        }

        // get the description
        $this->selectedResource = OntoWiki::getInstance()->selectedResource;
        $this->description = $this->selectedResource->getDescription();
        $this->description = $this->description[(string) $this->selectedResource];
    }

    /**
     * Returns the content
     */
    public function getContents()
    {
        $data = array();
        $this->content = $this->render('Voidhelper', $data);
        return $this->content;
    }

    /*
     * the title of the module window
     */
    public function getTitle()
    {
        return 'VoID Helper';
    }

    /*
     * display the module only if there is content
     */
    public function shouldShow()
    {
        return $this->shouldShow;
    }
}
