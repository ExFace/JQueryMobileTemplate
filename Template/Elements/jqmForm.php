<?php
namespace exface\JQueryMobileTemplate\Template\Elements;
class jqmForm extends jqmPanel {
	
	function generate_html(){
		$output = '';
		if ($this->get_widget()->get_caption()){
			$output = '<div class="ftitle">' . $this->get_widget()->get_caption() . '</div>';
		}
		
		$output .= '<form id="' . $this->get_widget()->get_id() . '">';
		$output .= $this->build_html_for_widgets();					
		$output .= '</form>';
		
		return $output;
	}
	
	public function get_method() {
		return $this->method;
	}
	
	public function set_method($value) {
		$this->method = $value;
	}
}
?>