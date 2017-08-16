<?php
namespace exface\JQueryMobileTemplate\Template\Elements;

use exface\Core\Templates\AbstractAjaxTemplate\Elements\JqueryFilterTrait;

class jqmFilter extends jqmAbstractElement
{
    
    use JqueryFilterTrait;

    /**
     * Need to override the generate_js() method of the trait to make sure, the $jqm_page_id is allways passed along
     *
     * {@inheritdoc}
     *
     * @see \exface\Core\Templates\AbstractAjaxTemplate\Elements\AbstractJqueryElement::generateJs()
     */
    function generateJs($jqm_page_id = NULL)
    {
        return $this->getInputElement()->generateJs($jqm_page_id);
    }
}
?>