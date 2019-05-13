<?php
namespace exface\JQueryMobileFacade\Facades\Elements;

class jqmWidgetGroup extends jqmPanel
{

    public function buildHtml()
    {
        $children_html = $this->buildHtmlForChildren();
        
        $output = '
				<fieldset class="exface_inputgroup">
					<legend>' . $this->getWidget()->getCaption() . '</legend>
					' . $children_html . '
				</fieldset>';
        return $output;
    }
}
?>
