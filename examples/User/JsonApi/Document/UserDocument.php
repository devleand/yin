<?php
namespace WoohooLabs\Yin\Examples\User\JsonApi\Document;

use WoohooLabs\Yin\Examples\User\JsonApi\Resource\UserResourceTransformer;
use WoohooLabs\Yin\JsonApi\Document\AbstractSingleResourceDocument;
use WoohooLabs\Yin\JsonApi\Schema\Link;
use WoohooLabs\Yin\JsonApi\Schema\Links;

class UserDocument extends AbstractSingleResourceDocument
{
    /**
     * @var array
     */
    protected $domainObject;

    /**
     * @param \WoohooLabs\Yin\Examples\User\JsonApi\Resource\UserResourceTransformer $transformer
     */
    public function __construct(UserResourceTransformer $transformer)
    {
        parent::__construct($transformer);
    }

    /**
     * Provides information about the "jsonApi" member of the current document.
     *
     * The method returns a new JsonApi schema object if this member should be present or null
     * if it should be omitted from the response.
     *
     * @return \WoohooLabs\Yin\JsonApi\Schema\JsonApi|null
     */
    public function getJsonApi()
    {
        return null;
    }

    /**
     * Provides information about the "meta" member of the current document.
     *
     * The method returns an array of non-standard meta information about the document. If
     * this array is empty, the member won't appear in the response.
     *
     * @return array
     */
    public function getMeta()
    {
        return [];
    }

    /**
     *  Provides information about the "links" member of the current document.
     *
     * The method returns a new Links schema object if you want to provide linkage data
     * for the document or null if the section should be omitted from the response.
     *
     * @return \WoohooLabs\Yin\JsonApi\Schema\Links|null
     */
    public function getLinks()
    {
        return Links::createWithoutBaseUri(
            [
                "self" => new Link("/?path=/users/" . $this->getResourceId())
            ]
        );
    }
}
