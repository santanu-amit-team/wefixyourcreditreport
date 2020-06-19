<?php

namespace Application;

use Application\Exception;
use Application\Response;
use Symfony\Component\HttpFoundation\Request as SymfonyRequest;
use Symfony\Component\HttpKernel\HttpKernelInterface;
use Symfony\Component\Routing\Exception\InvalidParameterException;
use Symfony\Component\Routing\Exception\MethodNotAllowedException;
use Symfony\Component\Routing\Exception\ResourceNotFoundException;
use Symfony\Component\Routing\Matcher\UrlMatcher;
use Symfony\Component\Routing\RequestContext;

class Framework implements HttpKernelInterface
{

    public function handle(SymfonyRequest $request, $type = HttpKernelInterface::MASTER_REQUEST, $catch = true)
    {
        $context = new RequestContext();
        $context->fromRequest($request);
        $matcher = new UrlMatcher(Router::getRoutes($request->getRealMethod()), $context);
        try {
            Extension::getInstance()->performEventActions('beforeResolveRoute');
            $match = \Bootstrap::isNginxServer()
                ? $request->query->get('route')
                : $request->getPathInfo();
            $response = $this->performAction($matcher->match( $match ));
        } catch (ResourceNotFoundException $ex) {
            Response::send('Not found!', array('statusCode' => 404));
        } catch (MethodNotAllowedException $ex) {
            Response::send('Method not allowed!', array('statusCode' => 405));
        } catch (Exception $ex) {
            Response::send('Not Implemented!', array('statusCode' => 501));
        }
    }

    private function performAction($attributes)
    {
        if (empty($attributes['callback'])) {
            throw new ResourceNotFoundException("No callback method provided.");
        } elseif (is_callable($attributes['callback'])) {
            $parameters = $this->getParameters($attributes['_route'], $attributes);
            Response::send(
                call_user_func_array($attributes['callback'], $parameters)
            );
        } else {
            $parts = explode('@', $attributes['callback']);
            if (count($parts) !== 2) {
                throw new InvalidParameterException("Invalid controller or method.");
            }

            $controllerClass = $parts[0];
            $actionName      = $parts[1];

            $parts = array_filter(explode("\\", $controllerClass));
            if (count($parts) === 1) {
                $controllerClass = "Application\\Controller\\" . $controllerClass;
            }

            Request::attributes()->set('controller', $controllerClass);
            Request::attributes()->set('action', $actionName);

            Extension::getInstance()->performEventActions('beforeControllerAction');
            
            $controller = new $controllerClass();
            $parameters = $this->getParameters($attributes['_route'], $attributes);
            Response::send(
                call_user_func_array(array($controller, $actionName), $parameters)
            );
        }
    }

    private function getParameters($path, $attributes)
    {
        $parameters = $matches = array();
        $match      = preg_match_all('/{([^}\/{]+)}/', $path, $matches);
        if ($match !== 0 && !empty($matches[1]) && is_array($matches[1])) {
            foreach ($matches[1] as $key) {
                $parameters[] = $attributes[$key];
            }
        }
        return $parameters;
    }

}
