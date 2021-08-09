<?php

declare(strict_types=1);

namespace Xaben\DataFilterApi\Adapter;

use Xaben\DataFilter\Adapter\Adapter;
use Xaben\DataFilter\Adapter\BaseAdapter;
use Xaben\DataFilter\Definition\FilterDefinition;
use Xaben\DataFilter\Filter\CollectionFilter;
use Xaben\DataFilter\Pagination\PaginationConfiguration;

class ApiAdapter extends BaseAdapter implements Adapter
{
    protected function processPagination(
        FilterDefinition $definition,
        array $requestParameters,
        CollectionFilter $collectionFilter
    ): void {
        $paginationConfiguration = $definition->getPaginationConfiguration();
        if (!$paginationConfiguration) {
            return;
        }

        [$offset, $limit] = $paginationConfiguration->getByPage(
            (int) ($requestParameters['page'] ?? 1),
            (int) ($requestParameters['per_page'] ?? PaginationConfiguration::DEFAULT_RESULT_COUNT),
        );

        $collectionFilter->setOffset($offset);
        $collectionFilter->setLimit($limit);
    }

    protected function processSortable(
        FilterDefinition $definition,
        array $requestParameters,
        CollectionFilter $collectionFilter
    ): void {
        $sortConfiguration = $definition->getSortConfiguration();
        $sort = [];
        foreach ($requestParameters['order'] ?? [] as $columnName => $value) {
            $sortDefinition = $sortConfiguration->getSortDefinition($columnName);
            if ($sortDefinition) {
                $sort = array_merge($sort, $sortDefinition->getSortOrder($value));
            }
        }

        if (empty($sort)) {
            foreach ($sortConfiguration->getAllDefinitions() as $sortDefinition) {
                $sort = array_merge($sort, $sortDefinition->getDefaultSortOrder());
            }
        }

        $collectionFilter->setSortOrder($sort);
    }

    protected function processFilters(
        FilterDefinition $definition,
        array $requestParameters,
        CollectionFilter $collectionFilter
    ): void {
        $filterConfiguration = $definition->getFilterConfiguration();
        $criteria = [];
        foreach ($requestParameters['filter'] ?? [] as $columnName => $value) {
            $filter = $filterConfiguration->getFilter($columnName);
            if ($filter) {
                $criteria = array_merge($criteria, $filter->getFilter($value));
            }
        }

        $collectionFilter->setDefaultCriteria($definition->getDefaultFilterConfiguration($requestParameters)->getCriteria());
        $collectionFilter->setUserCriteria($criteria);
        $collectionFilter->setPredefinedCriteria($definition->getPredefinedFilterConfiguration($requestParameters)->getCriteria());
    }
}
