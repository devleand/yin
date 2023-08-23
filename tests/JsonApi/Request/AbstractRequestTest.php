<?php

declare(strict_types=1);

namespace Devleand\Yin\Tests\JsonApi\Request;

use Laminas\Diactoros\ServerRequest;
use Laminas\Diactoros\Stream;
use Laminas\Diactoros\Uri;
use PHPUnit\Framework\TestCase;
use Psr\Http\Message\ServerRequestInterface;
use Devleand\Yin\JsonApi\Exception\DefaultExceptionFactory;
use Devleand\Yin\JsonApi\Request\AbstractRequest;
use Devleand\Yin\JsonApi\Request\JsonApiRequest;
use Devleand\Yin\JsonApi\Serializer\JsonDeserializer;

class AbstractRequestTest extends TestCase
{
    /**
     * @test
     */
    public function getProtocolVersion(): void
    {
        $protocolVersion = "2";

        $request = $this->createRequest()->withProtocolVersion($protocolVersion);
        $this->assertEquals($protocolVersion, $request->getProtocolVersion());
    }

    /**
     * @test
     */
    public function getHeaders(): void
    {
        $header1Name = "a";
        $header1Value = "b";
        $header2Name = "c";
        $header2Value = "d";
        $headers = [$header1Name => [$header1Value], $header2Name => [$header2Value]];

        $request = $this->createRequestWithHeader($header1Name, $header1Value)->withHeader($header2Name, $header2Value);
        $this->assertEquals($headers, $request->getHeaders());
    }

    /**
     * @test
     */
    public function hasHeaderWhenHeaderNotExists(): void
    {
        $request = $this->createRequestWithHeader("a", "b");

        $this->assertFalse($request->hasHeader("c"));
    }

    /**
     * @test
     */
    public function hasHeaderWhenHeaderExists(): void
    {
        $request = $this->createRequestWithHeader("a", "b");

        $this->assertTrue($request->hasHeader("a"));
    }

    /**
     * @test
     */
    public function getHeaderWhenHeaderExists(): void
    {
        $request = $this->createRequestWithHeader("a", "b");

        $this->assertEquals(["b"], $request->getHeader("a"));
    }

    /**
     * @test
     */
    public function getHeaderLineWhenHeaderNotExists(): void
    {
        $request = $this->createRequestWithHeaders(["a" => ["b", "c", "d"]]);

        $this->assertEquals("", $request->getHeaderLine("b"));
    }

    /**
     * @test
     */
    public function getHeaderLineWhenHeaderExists(): void
    {
        $request = $this->createRequestWithHeaders(["a" => ["b", "c", "d"]]);

        $this->assertEquals("b,c,d", $request->getHeaderLine("a"));
    }

    /**
     * @test
     */
    public function withHeader(): void
    {
        $headers = [];
        $headerName = "a";
        $headerValue = "b";

        $request = $this->createRequestWithHeaders($headers);
        $newRequest = $request->withHeader($headerName, $headerValue);
        $this->assertEquals([], $request->getHeader($headerName));
        $this->assertEquals([$headerValue], $newRequest->getHeader($headerName));
    }

    /**
     * @test
     */
    public function withAddedHeader(): void
    {
        $headerName = "a";
        $headerValue = "b";
        $headers = [$headerName => $headerValue];

        $request = $this->createRequestWithHeaders($headers);
        $newRequest = $request->withAddedHeader($headerName, $headerValue);
        $this->assertEquals([$headerValue], $request->getHeader($headerName));
        $this->assertEquals([$headerValue, $headerValue], $newRequest->getHeader($headerName));
    }

    /**
     * @test
     */
    public function withoutHeader(): void
    {
        $headerName = "a";
        $headerValue = "b";
        $headers = [$headerName => $headerValue];

        $request = $this->createRequestWithHeaders($headers);
        $newRequest = $request->withoutHeader($headerName);

        $this->assertEquals([$headerValue], $request->getHeader($headerName));
        $this->assertEquals([], $newRequest->getHeader($headerName));
    }

    /**
     * @test
     */
    public function getBody(): void
    {
        $body = new Stream("php://input");

        $serverRequest = $this->createMock(ServerRequestInterface::class);
        $serverRequest->expects($this->once())
            ->method("getBody")
            ->will($this->returnValue($body));

        $request = $this->createRequest($serverRequest);

        $this->assertEquals($body, $request->getBody());
    }

    /**
     * @test
     */
    public function withBody(): void
    {
        $body = new Stream("php://input");

        $request = $this->createRequest();
        $request = $request->withBody($body);

        $this->assertEquals($body, $request->getBody());
    }

    /**
     * @test
     */
    public function getRequestTarget(): void
    {
        $serverRequest = $this->createMock(ServerRequestInterface::class);
        $serverRequest->expects($this->once())
            ->method("getRequestTarget")
            ->will($this->returnValue("/abc"));

        $request = $this->createRequest($serverRequest);

        $this->assertEquals("/abc", $request->getRequestTarget());
    }

    /**
     * @test
     */
    public function withRequestTarget(): void
    {
        $request = $this->createRequest();

        $request = $request->withRequestTarget("/abc");

        $this->assertEquals("/abc", $request->getRequestTarget());
    }

    /**
     * @test
     */
    public function getMethod(): void
    {
        $method = "PUT";

        $request = $this->createRequest();
        $newRequest = $request->withMethod($method);
        $this->assertEquals("GET", $request->getMethod());
        $this->assertEquals($method, $newRequest->getMethod());
    }

    /**
     * @test
     */
    public function getUri(): void
    {
        $uri = new Uri();

        $serverRequest = $this->createMock(ServerRequestInterface::class);
        $serverRequest->expects($this->once())
            ->method("getUri")
            ->will($this->returnValue($uri));

        $request = $this->createRequest($serverRequest);

        $this->assertEquals($uri, $request->getUri());
    }

    /**
     * @test
     */
    public function withUri(): void
    {
        $request = $this->createRequest();

        $request = $request->withUri(new Uri("https://example.com"));

        $this->assertEquals("https://example.com", $request->getUri()->__toString());
    }

    /**
     * @test
     */
    public function getServerParams(): void
    {
        $serverRequest = $this->createMock(ServerRequestInterface::class);
        $serverRequest->expects($this->once())
            ->method("getServerParams")
            ->will($this->returnValue(["abc" => "def"]));

        $request = $this->createRequest($serverRequest);

        $this->assertEquals(["abc" => "def"], $request->getServerParams());
    }

    /**
     * @test
     */
    public function getCookieParams(): void
    {
        $serverRequest = $this->createMock(ServerRequestInterface::class);
        $serverRequest->expects($this->once())
            ->method("getCookieParams")
            ->will($this->returnValue(["abc" => "def"]));

        $request = $this->createRequest($serverRequest);

        $this->assertEquals(["abc" => "def"], $request->getCookieParams());
    }

    /**
     * @test
     */
    public function withCookieParams(): void
    {
        $request = $this->createRequest();

        $request = $request->withCookieParams(["abc" => "def"]);

        $this->assertEquals(["abc" => "def"], $request->getCookieParams());
    }

    /**
     * @test
     */
    public function getUploadedFiles(): void
    {
        $serverRequest = $this->createMock(ServerRequestInterface::class);
        $serverRequest->expects($this->once())
            ->method("getUploadedFiles")
            ->will($this->returnValue(["abc"]));

        $request = $this->createRequest($serverRequest);

        $this->assertEquals(["abc"], $request->getUploadedFiles());
    }

    /**
     * @test
     */
    public function getQueryParams(): void
    {
        $queryParamName = "abc";
        $queryParamValue = "cde";
        $queryParams = [$queryParamName => $queryParamValue];

        $request = $this->createRequest();
        $newRequest = $request->withQueryParams($queryParams);
        $this->assertEquals([], $request->getQueryParams());
        $this->assertEquals($queryParams, $newRequest->getQueryParams());
    }

    /**
     * @test
     */
    public function getQueryParamWhenNotFound(): void
    {
        $queryParams = [];

        $request = $this->createRequestWithQueryParams($queryParams);
        $this->assertEquals("xyz", $request->getQueryParam("a_b", "xyz"));
    }

    /**
     * @test
     */
    public function getQueryParamWhenNotEmpty(): void
    {
        $queryParamName = "abc";
        $queryParamValue = "cde";
        $queryParams = [$queryParamName => $queryParamValue];

        $request = $this->createRequestWithQueryParams($queryParams);
        $this->assertEquals($queryParamValue, $request->getQueryParam($queryParamName));
    }

    /**
     * @test
     */
    public function withQueryParam(): void
    {
        $queryParams = [];
        $addedQueryParamName = "abc";
        $addedQueryParamValue = "def";

        $request = $this->createRequestWithQueryParams($queryParams);
        $newRequest = $request->withQueryParam($addedQueryParamName, $addedQueryParamValue);
        $this->assertNull($request->getQueryParam($addedQueryParamName));
        $this->assertEquals($addedQueryParamValue, $newRequest->getQueryParam($addedQueryParamName));
    }

    /**
     * @test
     */
    public function getParsedBody(): void
    {
        $parsedBody = [
            "data" => [
                "type" => "cat",
                "id" => "tom",
            ],
        ];

        $request = $this->createRequest();
        $newRequest = $request->withParsedBody($parsedBody);
        $this->assertEquals(null, $request->getParsedBody());
        $this->assertEquals($parsedBody, $newRequest->getParsedBody());
    }

    /**
     * @test
     */
    public function getAttributes(): void
    {
        $attribute1Key = "a";
        $attribute1Value = true;
        $attribute2Key = "b";
        $attribute2Value = 123456;
        $attributes = [$attribute1Key => $attribute1Value, $attribute2Key => $attribute2Value];

        $request = $this->createRequest();
        $newRequest = $request
            ->withAttribute($attribute1Key, $attribute1Value)
            ->withAttribute($attribute2Key, $attribute2Value);

        $this->assertEquals([], $request->getAttributes());
        $this->assertEquals($attributes, $newRequest->getAttributes());
        $this->assertEquals($attribute1Value, $newRequest->getAttribute($attribute1Key));
    }

    /**
     * @test
     */
    public function withoutAttributes(): void
    {
        $request = $this->createRequest();
        $newRequest = $request
            ->withAttribute("abc", "cde")
            ->withoutAttribute("abc");

        $this->assertEquals([], $request->getAttributes());
        $this->assertEmpty($newRequest->getAttributes());
    }

    private function createRequest(?ServerRequestInterface $serverRequest = null): JsonApiRequest
    {
        return new JsonApiRequest(
            $serverRequest ?? new ServerRequest(),
            new DefaultExceptionFactory(),
            new JsonDeserializer()
        );
    }

    private function createRequestWithHeaders(array $headers): AbstractRequest
    {
        $psrRequest = new ServerRequest([], [], null, null, "php://temp", $headers);

        return new JsonApiRequest($psrRequest, new DefaultExceptionFactory(), new JsonDeserializer());
    }

    private function createRequestWithHeader(string $headerName, string $headerValue): AbstractRequest
    {
        $psrRequest = new ServerRequest([], [], null, null, "php://temp", [$headerName => $headerValue]);

        return new JsonApiRequest($psrRequest, new DefaultExceptionFactory(), new JsonDeserializer());
    }

    private function createRequestWithQueryParams(array $queryParams): AbstractRequest
    {
        $psrRequest = new ServerRequest();
        $psrRequest = $psrRequest->withQueryParams($queryParams);

        return new JsonApiRequest($psrRequest, new DefaultExceptionFactory(), new JsonDeserializer());
    }
}
