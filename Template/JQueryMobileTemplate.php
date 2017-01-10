<?php
namespace exface\JQueryMobileTemplate\Template;
use exface\AbstractAjaxTemplate\Template\AbstractAjaxTemplate;
use exface\Core\Interfaces\Actions\ActionInterface;
use exface\Core\Widgets\AbstractWidget;
class JQueryMobileTemplate extends AbstractAjaxTemplate {
	protected $request_columns = array();

	public function init(){
		$this->set_class_prefix('jqm');
		$this->set_class_namespace(__NAMESPACE__);
	}
	
	/**
	 * To generate the JavaScript, jQueryMobile needs to know the page id in addition to the regular parameters for this method
	 * @see AbstractAjaxTemplate::generate_js()
	 */
	function generate_js(\exface\Core\Widgets\AbstractWidget $widget, $jqm_page_id = null){
		$instance = $this->get_element($widget);
		return $instance->generate_js($jqm_page_id);
	}

	public function process_request($page_id=NULL, $widget_id=NULL, $action_alias=NULL, $disable_error_handling=NULL){
		$this->request_columns = $this->get_workbench()->get_request_params()['columns'];
		$this->get_workbench()->remove_request_param('columns');
		$this->get_workbench()->remove_request_param('search');
		$this->get_workbench()->remove_request_param('draw');
		$this->get_workbench()->remove_request_param('_');
		return parent::process_request($page_id, $widget_id, $action_alias, $disable_error_handling);
	}

	public function get_request_paging_offset(){
		if (!$this->request_paging_offset){
			$this->request_paging_offset = $this->get_workbench()->get_request_params()['start'];
			$this->get_workbench()->remove_request_param('start');
		}
		return $this->request_paging_offset;
	}
	
	public function get_request_paging_rows(){
		if (!$this->request_paging_rows){
			$this->request_paging_rows = $this->get_workbench()->get_request_params()['length'];
			$this->get_workbench()->remove_request_param('length');
		}
		return $this->request_paging_rows;
	}
	
	public function get_request_sorting_direction(){
		if (!$this->request_sorting_direction){
			$this->get_request_sorting_sort_by();
		}
		return $this->request_sorting_direction;
	}
	
	public function get_request_sorting_sort_by(){
		if (!$this->request_sorting_sort_by){
			$sorters = !is_null($this->get_workbench()->get_request_params()['order']) ? $this->get_workbench()->get_request_params()['order'] : array();
			$this->get_workbench()->remove_request_param('order');

			foreach ($sorters as $sorter){
				if ($sort_attr = $this->request_columns[$sorter['column']]['data']){
					$this->request_sorting_sort_by .= ($this->request_sorting_sort_by ? ',' : '') . $sort_attr;
					$this->request_sorting_direction .= ($this->request_sorting_direction ? ',' : '') . $sorter['dir'];
				}
			}
		}
		return $this->request_sorting_sort_by;
	}
	
	/**
	 * In jQuery mobile we need to do some custom handling for the output of ShowDialog-actions: it must be wrapped in a 
	 * JQM page. 
	 * @see \exface\AbstractAjaxTemplate\Template\AbstractAjaxTemplate::set_response_from_action()
	 */
	public function set_response_from_action(ActionInterface $action){
		if ($action->implements_interface('iShowDialog')) {
			// Perform the action and draw the result
			$action->get_result();
			return parent::set_response($this->get_element($action->get_dialog_widget())->generate_jqm_page());
		} else {
			return parent::set_response_from_action($action);
		}
	}
}
?>