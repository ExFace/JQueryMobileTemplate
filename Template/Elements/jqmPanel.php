<?php
namespace exface\JQueryMobileTemplate\Template\Elements;
class jqmPanel extends jqmContainer {
	
	function generate_html(){
		$output = '
				<div class="panel" 
					title="' . $this->get_widget()->get_caption() . '">' . "\n";
		$output .= $this->build_html_for_children();			
		$output .= '</div>';
		return $output;
	}
	
	function build_html_buttons(){
		$output = '';
		foreach ($this->get_widget()->get_buttons() as $btn){
			$output .= $this->get_template()->generate_html($btn);
		}
		
		return $output;
	}
	
	function build_js_buttons($jqm_page_id = null){
		$output = '';
		foreach ($this->get_widget()->get_buttons() as $btn){
			$output .= $this->get_template()->generate_js($btn, $jqm_page_id);
		}
	
		return $output;
	}
}
?>