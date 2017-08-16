<?php
namespace exface\JQueryMobileTemplate\Template\Elements;

use exface\Core\Templates\AbstractAjaxTemplate\Elements\JqueryContainerTrait;

class jqmContainer extends jqmAbstractElement
{
    
    use JqueryContainerTrait;

    public function generateJs($jqm_page_id = null)
    {
        return $this->buildJsForChildren($jqm_page_id);
    }

    public function buildJsForChildren($jqm_page_id = null)
    {
        foreach ($this->getWidget()->getChildren() as $subw) {
            $output .= $this->getTemplate()->generateJs($subw, $jqm_page_id) . "\n";
        }
        ;
        return $output;
    }

    public function buildJsForWidgets($jqm_page_id = null)
    {
        foreach ($this->getWidget()->getWidgets() as $subw) {
            $output .= $this->getTemplate()->generateJs($subw, $jqm_page_id) . "\n";
        }
        ;
        return $output;
    }
}
?>