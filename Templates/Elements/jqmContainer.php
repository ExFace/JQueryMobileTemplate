<?php
namespace exface\JQueryMobileFacade\Facades\Elements;

use exface\Core\Facades\AbstractAjaxFacade\Elements\JqueryContainerTrait;

class jqmContainer extends jqmAbstractElement
{
    use JqueryContainerTrait;

    public function buildJs($jqm_page_id = null)
    {
        return $this->buildJsForChildren($jqm_page_id);
    }

    public function buildJsForChildren($jqm_page_id = null)
    {
        foreach ($this->getWidget()->getChildren() as $subw) {
            $output .= $this->getFacade()->buildJs($subw, $jqm_page_id) . "\n";
        }
        ;
        return $output;
    }

    public function buildJsForWidgets($jqm_page_id = null)
    {
        foreach ($this->getWidget()->getWidgets() as $subw) {
            $output .= $this->getFacade()->buildJs($subw, $jqm_page_id) . "\n";
        }
        return $output;
    }
    
    /**
     * 
     * {@inheritDoc}
     * @see \exface\Core\Facades\AbstractAjaxFacade\Elements\AbstractJqueryElement::buildHtml()
     */
    public function buildHtml()
    {
        return $this->buildHtmlForChildren();
    }
}
?>