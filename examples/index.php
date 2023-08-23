<?php

declare(strict_types=1);

require_once "../vendor/autoload.php";

use Laminas\Diactoros\Response;
use Laminas\Diactoros\ServerRequestFactory;
use Laminas\HttpHandlerRunner\Emitter\SapiEmitter;
use Devleand\Yin\Examples\Book\Action\CreateBookAction;
use Devleand\Yin\Examples\Book\Action\GetAuthorsOfBookAction;
use Devleand\Yin\Examples\Book\Action\GetBookAction;
use Devleand\Yin\Examples\Book\Action\GetBookRelationshipsAction;
use Devleand\Yin\Examples\Book\Action\GetBooksAction;
use Devleand\Yin\Examples\Book\Action\UpdateBookAction;
use Devleand\Yin\Examples\Book\Action\UpdateBookRelationshipAction;
use Devleand\Yin\Examples\User\Action\GetUserAction;
use Devleand\Yin\Examples\User\Action\GetUserRelationshipsAction;
use Devleand\Yin\Examples\User\Action\GetUsersAction;
use Devleand\Yin\Examples\Utils\ExampleExceptionFactory;
use Devleand\Yin\JsonApi\Exception\ExceptionFactoryInterface;
use Devleand\Yin\JsonApi\Exception\JsonApiExceptionInterface;
use Devleand\Yin\JsonApi\JsonApi;
use Devleand\Yin\JsonApi\Request\JsonApiRequest;
use Devleand\Yin\JsonApi\Serializer\JsonDeserializer;

// Defining routes
$routes = [
    "GET /books" => function (JsonApiRequest $request): JsonApiRequest {
        return $request
            ->withAttribute("action", GetBooksAction::class);
    },
    "GET /books/{id}" => function (JsonApiRequest $request, array $matches): JsonApiRequest {
        return $request
            ->withAttribute("action", GetBookAction::class)
            ->withAttribute("id", $matches[1]);
    },
    "GET /books/{id}/relationships/{rel}" => function (JsonApiRequest $request, array $matches): JsonApiRequest {
        return $request
            ->withAttribute("action", GetBookRelationshipsAction::class)
            ->withAttribute("id", $matches[1])
            ->withAttribute("rel", $matches[2]);
    },
    "GET /books/{id}/authors" => function (JsonApiRequest $request, array $matches): JsonApiRequest {
        return $request
            ->withAttribute("action", GetAuthorsOfBookAction::class)
            ->withAttribute("id", $matches[1]);
    },
    "POST /books" => function (JsonApiRequest $request) {
        return $request
            ->withAttribute("action", CreateBookAction::class);
    },
    "PATCH /books/{id}" => function (JsonApiRequest $request, array $matches): JsonApiRequest {
        return $request
            ->withAttribute("action", UpdateBookAction::class)
            ->withAttribute("id", $matches[1]);
    },
    "PATCH /books/{id}/relationships/{rel}" => function (JsonApiRequest $request, array $matches): JsonApiRequest {
        return $request
            ->withAttribute("action", UpdateBookRelationshipAction::class)
            ->withAttribute("id", $matches[1])
            ->withAttribute("rel", $matches[2]);
    },

    "GET /users" => function (JsonApiRequest $request): JsonApiRequest {
        return $request
            ->withAttribute("action", GetUsersAction::class);
    },
    "GET /users/{id}" => function (JsonApiRequest $request, array $matches): JsonApiRequest {
        return $request
            ->withAttribute("action", GetUserAction::class)
            ->withAttribute("id", $matches[1]);
    },
    "GET /users/{id}/relationships/{rel}" => function (JsonApiRequest $request, array $matches): JsonApiRequest {
        return $request
            ->withAttribute("action", GetUserRelationshipsAction::class)
            ->withAttribute("id", $matches[1])
            ->withAttribute("rel", $matches[2]);
    },
];

// Finding the current route
$exceptionFactory = new ExampleExceptionFactory();
$deserializer = new JsonDeserializer();
$request = new JsonApiRequest(ServerRequestFactory::fromGlobals(), $exceptionFactory, $deserializer);
$jsonApi = new JsonApi($request, new Response(), $exceptionFactory);

// Invoking the current action
try {
    $request = findRoute($request, $routes, $exceptionFactory);
    $jsonApi->request = $request;
    $action = $request->getAttribute("action");
    $response = call_user_func(new $action(), $jsonApi);
} catch (JsonApiExceptionInterface $exception) {
    $response = $jsonApi->respond()->genericError($exception->getErrorDocument());
} catch (Throwable $throwable) {
    $response = $jsonApi->respond()->genericError($exceptionFactory->createApplicationErrorException($request)->getErrorDocument());
}

$response = $response->withHeader("Access-Control-Allow-Origin", "*");

// Emitting the response
$emitter = new SapiEmitter();
$emitter->emit($response);

function findRoute(JsonApiRequest $request, array $routes, ExceptionFactoryInterface $exceptionFactory): JsonApiRequest
{
    $path = $request->getUri()->getPath();
    $method = $request->getMethod();
    $requestLine = "$method $path";

    foreach ($routes as $pattern => $route) {
        $matches = [];
        $pattern = str_replace(
            ["{id}", "{rel}"],
            ["([A-Za-z0-9-]+)", "([A-Za-z0-9-]+)"],
            $pattern
        );
        if (preg_match("#^$pattern/{0,1}$#", $requestLine, $matches) === 1) {
            return $route($request, $matches);
        }
    }

    throw $exceptionFactory->createResourceNotFoundException($request);
}
