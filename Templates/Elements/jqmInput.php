<?php
namespace exface\JQueryMobileFacade\Facades\Elements;

use exface\Core\Interfaces\Actions\ActionInterface;

class jqmInput extends jqmAbstractElement
{

    protected function init()
    {
        parent::init();
        $this->setElementType('text');
    }

    public function buildHtml()
    {
        $output = '	<div class="exf-grid-item exf-input" title="' . $this->buildHintText() . '">
						<label for="' . $this->getId() . '">' . $this->getWidget()->getCaption() . '</label>
						<input data-clear-btn="true"
								type="' . $this->getElementType() . '"
								name="' . $this->getWidget()->getAttributeAlias() . '" 
								value="' . $this->escapeString($this->getWidget()->getValue()) . '" 
								id="' . $this->getId() . '"  
								' . ($this->getWidget()->isRequired() ? 'required="true" ' : '') . '
								' . ($this->getWidget()->isDisabled() ? 'disabled="disabled" ' : '') . '/>
					</div>';
        return $output;
    }

    public function buildJs($jqm_page_id = null)
    {
        return '';
    }

    /**
     *
     * {@inheritdoc}
     *
     * @see \exface\Core\Facades\AbstractAjaxFacade\Elements\AbstractJqueryElement::buildJsDataGetter($action, $custom_body_js)
     */
    public function buildJsDataGetter(ActionInterface $action = null)
    {
        if ($this->getWidget()->isDisplayOnly()) {
            return '{}';
        } else {
            return parent::buildJsDataGetter($action);
        }
    }
}
?>