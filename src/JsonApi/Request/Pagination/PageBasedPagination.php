<?php

declare(strict_types=1);

namespace Devleand\Yin\JsonApi\Request\Pagination;

use Devleand\Yin\Utils;

use function http_build_query;

class PageBasedPagination
{
    /**
     * @var int
     */
    protected $page;

    /**
     * @var int
     */
    protected $size;

    public static function fromPaginationQueryParams(
        array $paginationQueryParams,
        int $defaultPage = 0,
        int $defaultSize = 0
    ): PageBasedPagination {
        return new PageBasedPagination(
            Utils::getIntegerFromQueryParam($paginationQueryParams, "number", $defaultPage),
            Utils::getIntegerFromQueryParam($paginationQueryParams, "size", $defaultSize)
        );
    }

    public function __construct(int $page, int $size)
    {
        $this->page = $page;
        $this->size = $size;
    }

    public function getPage(): int
    {
        return $this->page;
    }

    public function getSize(): int
    {
        return $this->size;
    }

    public static function getPaginationQueryString(int $page, int $size): string
    {
        return http_build_query(static::getPaginationQueryParams($page, $size));
    }

    public static function getPaginationQueryParams(int $page, int $size): array
    {
        return [
            "page" => [
                "number" => $page,
                "size" => $size,
            ],
        ];
    }
}
