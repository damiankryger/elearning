<?php

namespace App\Shared\Infrastructure\ArgumentResolver;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Controller\ValueResolverInterface;
use Symfony\Component\HttpKernel\ControllerMetadata\ArgumentMetadata;

class RequestResolver implements ValueResolverInterface
{
    public function resolve(Request $request, ArgumentMetadata $argument): iterable
    {
        $argumentType = $argument->getType();
        
        // Check if the argument is one of our request classes
        if (!$argumentType || !class_exists($argumentType)) {
            return [];
        }
        
        // Check if it's one of our request classes
        if (!str_contains($argumentType, 'Request')) {
            return [];
        }
        
        // Get the JSON content
        $data = [];
        if ($request->getContentTypeFormat() === 'json') {
            $data = json_decode($request->getContent(), true) ?? [];
        }
        
        // Create the request object
        if (class_exists($argumentType)) {
            yield new $argumentType($data);
        }
    }
}