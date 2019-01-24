<?php
namespace exface\JQueryMobileTemplate\Templates;

use exface\Core\Templates\AbstractAjaxTemplate\AbstractAjaxTemplate;
use exface\Core\Interfaces\Actions\ActionInterface;
use exface\Core\Templates\AbstractAjaxTemplate\Middleware\JqueryDataTablesUrlParamsReader;
use exface\Core\Interfaces\DataSheets\DataSheetInterface;
use exface\Core\Interfaces\WidgetInterface;

class JQueryMobileTemplate extends AbstractAjaxTemplate
{

    public function init()
    {
        parent::init();
        $this->setClassPrefix('jqm');
        $this->setClassNamespace(__NAMESPACE__);
    }

    /**
     * To generate the JavaScript, jQueryMobile needs to know the page id in addition to the regular parameters for this method
     *
     * @see AbstractAjaxTemplate::buildJs()
     */
    public function buildJs(\exface\Core\Widgets\AbstractWidget $widget, $jqm_page_id = null)
    {
        $instance = $this->getElement($widget);
        return $instance->buildJs($jqm_page_id);
    }

    /**
     * In jQuery mobile we need to do some custom handling for the output of ShowDialog-actions: it must be wrapped in a
     * JQM page.
     * 
     * FIXME make it work with API v4
     *
     * @see \exface\Core\Templates\AbstractAjaxTemplate\AbstractAjaxTemplate::setResponseFromAction()
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
    
    /**
     * 
     * {@inheritDoc}
     * @see \exface\Core\Interfaces\Templates\HttpTemplateInterface::getUrlRoutePatterns()
     */
    public function getUrlRoutePatterns() : array
    {
        return [
            "/[\?&]tpl=jqm/",
            "/\/api\/jqm[\/?]/"
        ];
    }
    
    /**
     *
     * {@inheritDoc}
     * @see \exface\Core\Templates\AbstractAjaxTemplate\AbstractAjaxTemplate::getMiddleware()
     */
    protected function getMiddleware() : array
    {
        $middleware = parent::getMiddleware();
        $middleware[] = new JqueryDataTablesUrlParamsReader($this, 'getInputData', 'setInputData');
        return $middleware;
    }
    
    public function buildResponseData(DataSheetInterface $data_sheet, WidgetInterface $widget = null)
    {
        $data = array();
        $data['data'] = $data_sheet->getRows();
        $data['recordsFiltered'] = $data_sheet->countRowsInDataSource();
        $data['recordsTotal'] = $data_sheet->countRowsInDataSource();
        $data['footer'] = $data_sheet->getTotalsRows();
        return $data;
    }
}
?>