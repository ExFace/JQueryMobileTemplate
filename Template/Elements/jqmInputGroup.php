<?php namespace exface\JQueryMobileTemplate\Template\Elements;

class jqmInputGroup extends jqmPanel {
	
	public function generate_html(){
		$children_html = $this->build_html_for_children();
		
		$output = '
				<fieldset class="exface_inputgroup">
					<legend>'.$this->get_widget()->get_caption().'</legend>
					'.$children_html.'
				</fieldset>';
		return $output;
	}
}
?>
