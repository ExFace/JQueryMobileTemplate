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
	
	function generate_buttons_html(){
		$output = '';
		foreach ($this->get_widget()->get_buttons() as $btn){
			$output .= $this->get_template()->generate_html($btn);
		}
		
		return $output;
	}
	
	function generate_buttons_js($jqm_page_id = null){
		$output = '';
		foreach ($this->get_widget()->get_buttons() as $btn){
			$output .= $this->get_template()->generate_js($btn, $jqm_page_id);
		}
	
		return $output;
	}
}
?>