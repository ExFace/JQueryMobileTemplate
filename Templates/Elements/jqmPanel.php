<?php
namespace exface\JQueryMobileFacade\Facades\Elements;

class jqmPanel extends jqmContainer
{

    function buildHtml()
    {
        $output = '
				<div class="panel" 
					title="' . $this->getWidget()->getCaption() . '">' . "\n";
        $output .= $this->buildHtmlForChildren();
        $output .= '</div>';
        return $output;
    }

    function buildHtmlButtons()
    {
        $output = '';
        foreach ($this->getWidget()->getButtons() as $btn) {
            $output .= $this->getFacade()->buildHtml($btn);
        }
        
        return $output;
    }

    function buildJsButtons($jqm_page_id = null)
    {
        $output = '';
        foreach ($this->getWidget()->getButtons() as $btn) {
            $output .= $this->getFacade()->buildJs($btn, $jqm_page_id);
        }
        
        return $output;
    }
}
?>