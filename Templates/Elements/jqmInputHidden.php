<?php
namespace exface\JQueryMobileTemplate\Templates\Elements;

class jqmInputHidden extends jqmInput
{

    protected function init()
    {
        parent::init();
        $this->setElementType('hidden');
    }

    public function buildHtml()
    {
        $output = '<input type="hidden" 
								name="' . $this->getWidget()->getAttributeAlias() . '" 
								value="' . addslashes($this->getWidget()->getValue()) . '" 
								id="' . $this->getId() . '" />';
        return $output;
    }
}