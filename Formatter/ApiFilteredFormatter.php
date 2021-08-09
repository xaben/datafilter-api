<?php

declare(strict_types=1);

namespace Xaben\DataFilterApi\Formatter;

use Xaben\DataFilter\Filter\Result;
use Xaben\DataFilter\Formatter\Formatter;
use Xaben\DataFilter\Transformer\Transformer;

class ApiFilteredFormatter implements Formatter
{
    public function format(Result $result, Transformer $transformer): array
    {
        $pagination = [];

        if ($result->getFilter() &&
            $result->getFilter()->getDefinition() &&
            $result->getFilter()->getDefinition()->getPaginationConfiguration()
        ) {
            $pagination = [
                'pagination' => [
                    'total' => $result->getFilteredResults(),
                    'page' => round($result->getFilter()->getOffset() / $result->getFilter()->getLimit()) + 1,
                    'per_page' => $result->getFilter()->getLimit(),
                ],
            ];
        }

        return array_merge(
            $pagination,
            $transformer->transformCollection($result->getData())
        );
    }
}
