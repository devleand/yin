<?php

declare(strict_types=1);

namespace Devleand\Yin\Examples\User\JsonApi\Document;

use Devleand\Yin\Examples\User\JsonApi\Resource\UserResource;
use Devleand\Yin\JsonApi\Schema\Document\AbstractSingleResourceDocument;
use Devleand\Yin\JsonApi\Schema\JsonApiObject;
use Devleand\Yin\JsonApi\Schema\Link\DocumentLinks;
use Devleand\Yin\JsonApi\Schema\Link\Link;

class UserDocument extends AbstractSingleResourceDocument
{
    public function __construct(UserResource $resource)
    {
        parent::__construct($resource);
    }

    /**
     * Provides information about the "jsonapi" member of the current document.
     *
     * The method returns a new JsonApiObject object if this member should be present or null
     * if it should be omitted from the response.
     */
    public function getJsonApi(): ?JsonApiObject
    {
        return new JsonApiObject("1.1");
    }

    /**
     * Provides information about the "meta" member of the current document.
     *
     * The method returns an array of non-standard meta information about the document. If
     * this array is empty, the member won't appear in the response.
     */
    public function getMeta(): array
    {
        return [];
    }

    /**
     *  Provides information about the "links" member of the current document.
     *
     * The method returns a new DocumentLinks object if you want to provide linkage data
     * for the document or null if the section should be omitted from the response.
     */
    public function getLinks(): ?DocumentLinks
    {
        return DocumentLinks::createWithoutBaseUri(
            [
                "self" => new Link("/users/" . $this->getResourceId()),
            ]
        );
    }
}
