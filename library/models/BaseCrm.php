<?php

namespace Application\Model;

use Application\Config;
use Application\CrmPayload;
use Application\Extension;
use Exception;
use Symfony\Component\PropertyAccess\PropertyAccess;

abstract class BaseCrm
{
    protected $endpoint, $username, $password, $params, $response;

    protected $extension, $accessor;

    public function __construct($crmId)
    {
        $this->accessor = PropertyAccess::createPropertyAccessor();
        $crmDetails     = Config::crms(sprintf('%d', (int) $crmId));
        if (!is_array($crmDetails)) {
            throw new Exception(
                sprintf('CRM not found with id %d', (int) $crmId), 1001
            );
        }
        $this->endpoint  = $crmDetails['endpoint'];
        $this->username  = $crmDetails['username'];
        $this->password  = $crmDetails['password'];
        $this->account   = !empty($crmDetails['account']) ? $crmDetails['account'] : '';
        $this->extension = Extension::getInstance();

    }

    public function __call($methodName, $arguments)
    {

        if (!method_exists($this, $methodName)) {
            trigger_error(
                sprintf(
                    'Call to undefined method %s::%s', get_class($this), $methodName
                ), E_USER_ERROR
            );
        }

        $this->beforeAnyAction();

        if (CrmPayload::get('meta.terminateCrmRequest') === true) {
            return;
        }

        $this->beforeAnyCrmClassMethodCall();

        call_user_func_array(array($this, $methodName), $arguments);

        if (CrmPayload::get('meta.terminateCrmRequest') === true) {
            return;
        }

        $this->afterAnyAction();

    }

    private function beforeAnyAction()
    {

        if (CrmPayload::get('meta.bypassCrmHooks') === true) {
            return;
        }

        $this->extension->performEventActions('beforeAnyCrmRequest');

        if (CrmPayload::get('meta.bypassCrmHooks') === true) {
            return;
        }

        if (CrmPayload::get('meta.isPrepaidFlow') === true) {
            $this->extension->performEventActions('beforePrepaidOrderCrmRequest');
        }

        if (CrmPayload::get('meta.bypassCrmHooks') === true) {
            return;
        }

        if (CrmPayload::get('meta.isSplitOrder') === true) {
            $this->extension->performEventActions('beforeSplitOrderCrmRequest');
        }
    }

    private function afterAnyAction()
    {
        if (CrmPayload::get('meta.bypassCrmHooks') === true) {
            return;
        }

        if (CrmPayload::get('meta.isReprocessedOrder') === true) {
            return;
        }
        
        $this->extension->performEventActions('afterAnyCrmRequest');

        if (CrmPayload::get('meta.bypassCrmHooks') === true) {
            return;
        }

        if (CrmPayload::get('meta.isPrepaidFlow') === true) {
            $this->extension->performEventActions('afterPrepaidOrderCrmRequest');
        }

        if (CrmPayload::get('meta.bypassCrmHooks') === true) {
            return;
        }

        if (CrmPayload::get('meta.isSplitOrder') === true) {
            $this->extension->performEventActions('afterSplitOrderCrmRequest');
        }
    }

    abstract protected function beforeAnyCrmClassMethodCall();

    abstract protected function prospect();

    abstract protected function newOrderWithProspect();

    abstract protected function newOrder();

    abstract protected function newOrderCardOnFile();

}
