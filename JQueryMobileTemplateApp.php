<?php
namespace exface\JQueryMobileFacade;

use exface\Core\Interfaces\InstallerInterface;
use exface\Core\Facades\AbstractHttpFacade\HttpFacadeInstaller;
use exface\Core\CommonLogic\Model\App;
use exface\Core\Factories\FacadeFactory;

class JQueryMobileFacadeApp extends App
{

    /**
     * {@inheritdoc}
     * 
     * An additional installer is included to condigure the routing for the HTTP templates.
     * 
     * @see App::getInstaller($injected_installer)
     */
    public function getInstaller(InstallerInterface $injected_installer = null)
    {
        $installer = parent::getInstaller($injected_installer);
        $tplInstaller = new HttpFacadeInstaller($this->getSelector());
        $tplInstaller->setFacade(FacadeFactory::createFromString('exface.JQueryMobileFacade.JQueryMobileFacade', $this->getWorkbench()));
        $installer->addInstaller($tplInstaller);
        return $installer;
    }
}
?>