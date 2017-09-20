<?php
namespace exface\JQueryMobileTemplate\Template\Elements;

use exface\Core\Widgets\Tabs;

/**
 * 
 * @method Tabs getWidget()
 * 
 * @author Andrej Kabachnik
 *
 */
class jqmTabs extends jqmContainer
{
    public function generateHtml(){        
        return $this->buildHtmlTabHeaders() . $this->buildHtmlTabBodies();
    }
    
    public function buildHtmlTabBodies()
    {
        $bodies = '';
        foreach ($this->getWidget()->getChildren() as $tab) {
            $bodies .= $this->getTemplate()->getElement($tab)->buildHtmlBody();
        }
        return <<<HTML

        <div id="{$this->getId()}_tabs">
            {$bodies}
        </div>
        
HTML;
    }
    
    public function buildHtmlTabHeaders()
    {
        $headers = '';
        foreach ($this->getWidget()->getChildren() as $tab) {
            $headers .= $this->getTemplate()->getElement($tab)->buildHtmlHeader();
        }
        return <<<HTML
        
        <ul id="{$this->getId()}" data-role="nd2tabs" data-swipe="true" class="nd2Tabs">
            {$headers}
        </ul>
        
HTML;
    }
            
    
    
    function generateJs($jqm_page_id = null)
    {
        if (is_null($jqm_page_id)){
            $jqm_page_id = $this->getJqmPageId();
        }
        
        $output = <<<JS
$(document).on('pagebeforeshow', '#{$jqm_page_id}', function(event, ui) {
    $('#{$this->getId()} li').on('click',function(event){
		$('#{$this->getId()} li').each(function(){
			$( '#{$this->getId()}_tabs *[data-tab="' + $(this).data('tab') + '"]' ).hide();
			$(this).removeClass('nd2Tabs-active');
		});
		$( '#{$this->getId()}_tabs *[data-tab="' + $(this).data('tab') + '"]' ).show();
		$(this).addClass('nd2Tabs-active');
		event.preventDefault();
		return false;
	});
	
	var activeTab = $('#{$this->getId()} *[data-tab-active="true"]');
	if (activeTab){
		activeTab.trigger('click');	
	} else {
		
	}
});
JS;
        return $output . parent::generateJs($jqm_page_id);
    }
}
?>