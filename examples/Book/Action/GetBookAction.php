<?php
namespace WoohooLabs\Yin\Examples\Book\Action;

use WoohooLabs\Yin\Examples\Book\JsonApi\Document\BookDocument;
use WoohooLabs\Yin\Examples\Book\JsonApi\Resource\AuthorResourceTransformer;
use WoohooLabs\Yin\Examples\Book\JsonApi\Resource\BookResourceTransformer;
use WoohooLabs\Yin\Examples\Book\JsonApi\Resource\PublisherResourceTransformer;
use WoohooLabs\Yin\Examples\Book\Repository\BookRepository;
use WoohooLabs\Yin\JsonApi\JsonApi;

class GetBookAction
{
    /**
     * @param \WoohooLabs\Yin\JsonApi\JsonApi $jsonApi
     * @return \Psr\Http\Message\ResponseInterface
     */
    public function __invoke(JsonApi $jsonApi)
    {
        // Checking if the "id" query parameter is set
        $id = $jsonApi->getRequest()->getQueryParam("id");
        if ($id === null) {
            die("You must define the 'id' query parameter with a value of '1'!");
        }

        // Fetching the book with an ID of $id
        $book = BookRepository::getBook($id);

        // Instantiating the book document
        $document = new BookDocument(
            new BookResourceTransformer(new AuthorResourceTransformer(), new PublisherResourceTransformer())
        );

        // Responding with "200 Ok" status code along with the book document
        return $jsonApi->fetchResponse()->ok($document, $book);
    }
}
