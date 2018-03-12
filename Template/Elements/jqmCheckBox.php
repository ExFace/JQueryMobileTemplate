<?php
namespace exface\JQueryMobileTemplate\Template\Elements;

class jqmCheckBox extends jqmAbstractElement
{

    protected function init()
    {
        parent::init();
        $this->setElementType('checkbox');
    }

    function buildHtml()
    {
        $output = '	<div class="exf-grid-item exf-input" title="' . $this->buildHintText() . '">
						<label>' . $this->getWidget()->getCaption() . '</label>
						<input type="checkbox" value="1" 
								form="" 
								id="' . $this->getWidget()->getId() . '_checkbox"
								onchange="$(\'#' . $this->getWidget()->getId() . '\').val(this.checked);"' . '
								' . ($this->getWidget()->getValue() ? 'checked="checked" ' : '') . '
								' . ($this->getWidget()->isDisabled() ? 'disabled="disabled"' : '') . ' />
						<input type="hidden" name="' . $this->getWidget()->getAttributeAlias() . '" id="' . $this->getWidget()->getId() . '" value="' . $this->getWidget()->getValue() . '" />
					</div>';
        return $output;
    }

    function buildJs($jqm_page_id = null)
    {
        return '';
    }

    function buildJsInitOptions()
    {
        $options = 'on: "&#10004;"' . ', off: ""';
        return $options;
    }
}
?>