<?php
namespace exface\JQueryMobileFacade\Facades\Elements;

use exface\Core\Facades\AbstractAjaxFacade\Elements\AbstractJqueryElement;
use exface\JQueryMobileFacade\Facades\JQueryMobileFacade;

abstract class jqmAbstractElement extends AbstractJqueryElement
{

    private $jqm_page_id = null;

    public function buildJsInitOptions()
    {
        return '';
    }

    public function buildJsInlineEditorInit()
    {
        return '';
    }

    public function escapeString($string)
    {
        return htmlentities($string, ENT_QUOTES);
    }

    /**
     *
     * {@inheritdoc}
     *
     * @see \exface\Core\Facades\AbstractAjaxFacade\Elements\AbstractJqueryElement::getFacade()
     * @return JQueryMobileFacade
     */
    public function getFacade()
    {
        return parent::getFacade();
    }

    /**
     * Returns the id of the jQuery Mobile page holding the current widget.
     * This is very usefull if a complex widget (like a dataGrid) creates multiple pages for
     * its subwidgets. Each widget needs to know, which page it is on, to be able to bind its
     * events once this specific page ist created.
     *
     * NOTE: If the page id is not set explicitly, it is generated from the resource id
     * TODO The generation of a page id from a resource id should be some kind of parameter, so it can be controlled by the user, who also controlls the CMS-template
     *
     * @return string
     */
    public function getJqmPageId()
    {
        if ($this->jqm_page_id) {
            return $this->jqm_page_id;
        } else {
            return 'jqm' . $this->getWorkbench()->getCMS()->getPageIdInCms($this->getWidget()->getPage());
        }
    }

    public function setJqmPageId($value)
    {
        $this->jqm_page_id = $value;
    }

    public function buildJsBusyIconShow()
    {
        return "$.mobile.loading('show');";
    }

    public function buildJsBusyIconHide()
    {
        return "$.mobile.loading('hide');";
    }
    
    /**
     * Element ids for jQuery mobile must contain the page id as the default AJAX loading method
     * keeps pages in the DOM as long as it likes. This means, that all kinds of cheks for existing
     * or initialized elements (like wether a DataTable is initialized for a given element id)
     * may return TRUE if the previously shown page had an element with the same id. This happens
     * quite often since element ids are derived from widget types: thus, a all typical pages with a
     * DataTable will have the same id for the data table.
     *
     * {@inheritDoc}
     * @see \exface\Core\Facades\AbstractAjaxFacade\Elements\AbstractJqueryElement::getId()
     */
    public function getId()
    {
        return '_' . $this->cleanId($this->getJqmPageId()) . '_' . parent::getId();
    }
}
?>