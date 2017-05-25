<?php
namespace exface\JQueryMobileTemplate\Template;

use exface\AbstractAjaxTemplate\Template\AbstractAjaxTemplate;
use exface\Core\Interfaces\Actions\ActionInterface;
use exface\Core\Widgets\AbstractWidget;

class JQueryMobileTemplate extends AbstractAjaxTemplate
{

    protected $request_columns = array();

    public function init()
    {
        parent::init();
        $this->setClassPrefix('jqm');
        $this->setClassNamespace(__NAMESPACE__);
    }

    /**
     * To generate the JavaScript, jQueryMobile needs to know the page id in addition to the regular parameters for this method
     *
     * @see AbstractAjaxTemplate::generateJs()
     */
    function generateJs(\exface\Core\Widgets\AbstractWidget $widget, $jqm_page_id = null)
    {
        $instance = $this->getElement($widget);
        return $instance->generateJs($jqm_page_id);
    }

    public function processRequest($page_id = NULL, $widget_id = NULL, $action_alias = NULL, $disable_error_handling = NULL)
    {
        $this->request_columns = $this->getWorkbench()->getRequestParams()['columns'];
        $this->getWorkbench()->removeRequestParam('columns');
        $this->getWorkbench()->removeRequestParam('search');
        $this->getWorkbench()->removeRequestParam('draw');
        $this->getWorkbench()->removeRequestParam('_');
        return parent::processRequest($page_id, $widget_id, $action_alias, $disable_error_handling);
    }

    public function getRequestPagingOffset()
    {
        if (! $this->request_paging_offset) {
            $this->request_paging_offset = $this->getWorkbench()->getRequestParams()['start'];
            $this->getWorkbench()->removeRequestParam('start');
        }
        return $this->request_paging_offset;
    }

    public function getRequestPagingRows()
    {
        if (! $this->request_paging_rows) {
            $this->request_paging_rows = $this->getWorkbench()->getRequestParams()['length'];
            $this->getWorkbench()->removeRequestParam('length');
        }
        return $this->request_paging_rows;
    }

    public function getRequestSortingDirection()
    {
        if (! $this->request_sorting_direction) {
            $this->getRequestSortingSortBy();
        }
        return $this->request_sorting_direction;
    }

    public function getRequestSortingSortBy()
    {
        if (! $this->request_sorting_sort_by) {
            $sorters = ! is_null($this->getWorkbench()->getRequestParams()['order']) ? $this->getWorkbench()->getRequestParams()['order'] : array();
            $this->getWorkbench()->removeRequestParam('order');
            
            foreach ($sorters as $sorter) {
                if ($sort_attr = $this->request_columns[$sorter['column']]['data']) {
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
     *
     * @see \exface\AbstractAjaxTemplate\Template\AbstractAjaxTemplate::setResponseFromAction()
     */
    public function setResponseFromAction(ActionInterface $action)
    {
        if ($action->implementsInterface('iShowDialog')) {
            // Perform the action and draw the result
            $action->getResult();
            return parent::setResponse($this->getElement($action->getDialogWidget())->generateJqmPage());
        } else {
            return parent::setResponseFromAction($action);
        }
    }
}
?>