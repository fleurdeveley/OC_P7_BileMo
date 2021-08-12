<?php

namespace App\Listener;

use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Event\ExceptionEvent;

class ExceptionListener
{
    public function onKernelException(ExceptionEvent $event)
    {
        // objet exception de l'évenement reçu
        $exception = $event->getThrowable();

        if(method_exists($exception, 'getStatusCode')) {
            $status = $exception->getStatusCode(); 
        } else {
            $status = $exception->getCode();
        }
        if($status === 0) {
            $status = 500;
        }

        $message = $exception->getMessage();

        // instancier la réponse pour afficher les détails de l'exception
        $response = new JsonResponse(['error' => $message], $status);

        // type spécial d'exception qui contient le code d'état et les détails de l'en-tête

        // envoie l'objet de réponse modifié à l'événement
        $event->setResponse($response);
    }
}
