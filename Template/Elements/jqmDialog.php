<?php
namespace exface\JQueryMobileTemplate\Template\Elements;
class jqmDialog extends jqmPanel {
	
	function generate_html(){
		/* @var $widget \exface\Core\Widgets\Dialog */
		$widget = $this->get_widget();
		if ($widget->get_lazy_loading()){
			return '';
		} else {
			return $this->generate_jqm_page();
		}
	}
	
	function generate_js($jqm_page_id = null){
		return '';
	}
	
	public function generate_jqm_page(){
		/* @var $widget \exface\Core\Widgets\Dialog */
		$widget = $this->get_widget();
		
		$output = <<<HTML
<div data-role="page" id="{$this->get_id()}" data-overlay-theme="b" data-dialog="true" data-close-btn="right">
	<div data-role="header" class="ui-alt-icon">
		<h1>{$widget->get_caption()}</h1>
	</div>

	<div data-role="content">
		{$this->build_html_for_widgets()}
		<div class="dialogButtons ui-alt-icon">
			{$this->build_html_buttons()}
		</div>
	</div>
	
	<script type="text/javascript">
		{$this->build_js_for_widgets($this->get_id())}
		{$this->build_js_buttons($this->get_id())}
	</script>
				
</div>
HTML;
		return $output;
	}
}
?>