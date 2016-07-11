<?php
namespace exface\JQueryMobileTemplate\Template\Elements;
class jqmContainer extends jqmAbstractElement {
	
	function generate_html(){
		return $this->children_generate_html();
	}
	
	function children_generate_html(){
		foreach ($this->get_widget()->get_children() as $subw){
			$output .= $this->get_template()->generate_html($subw) . "\n";
		};
		return $output;
	}
	
	function generate_js($jqm_page_id = null){
		return $this->children_generate_js($jqm_page_id);
	}
	
	public function children_generate_js($jqm_page_id = null){
		foreach ($this->get_widget()->get_children() as $subw){
			$output .= $this->get_template()->generate_js($subw, $jqm_page_id) . "\n";
		};
		return $output;
	}
	
	public function generate_widgets_html(){
		foreach ($this->get_widget()->get_widgets() as $subw){
			$output .= $this->get_template()->generate_html($subw) . "\n";
		};
		return $output;
	}
	
	public function generate_widgets_js($jqm_page_id = null){
		foreach ($this->get_widget()->get_widgets() as $subw){
			$output .= $this->get_template()->generate_js($subw, $jqm_page_id) . "\n";
		};
		return $output;
	}
}
?>