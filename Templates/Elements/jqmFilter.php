<?php
namespace exface\JQueryMobileTemplate\Templates\Elements;

use exface\Core\Templates\AbstractAjaxTemplate\Elements\JqueryFilterTrait;

class jqmFilter extends jqmAbstractElement
{
    use JqueryFilterTrait;

    /**
     * Need to override the generate_js() method of the trait to make sure, the $jqm_page_id is allways passed along
     *
     * {@inheritdoc}
     *
     * @see \exface\Core\Templates\AbstractAjaxTemplate\Elements\AbstractJqueryElement::buildJs()
     */
    function buildJs($jqm_page_id = NULL)
    {
        return $this->getInputElement()->buildJs($jqm_page_id);
    }
    
    /**
     * 
     * {@inheritDoc}
     * @see \exface\Core\Templates\AbstractAjaxTemplate\Elements\AbstractJqueryElement::buildHtml()
     */
    public function buildHtml()
    {
        return $this->getInputElement()->buildHtml();
    }
}
?>