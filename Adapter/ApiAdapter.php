<?php

declare(strict_types=1);

namespace Xaben\DataFilterApi\Adapter;

use Xaben\DataFilter\Adapter\AdapterInterface;
use Xaben\DataFilter\Adapter\BaseAdapter;
use Xaben\DataFilter\Definition\FilterDefinitionInterface;
use Xaben\DataFilter\Filter\CollectionFilter;
use Xaben\DataFilter\Pagination\PaginationConfiguration;
use Symfony\Component\HttpFoundation\Request;

class ApiAdapter extends BaseAdapter implements AdapterInterface
{
    protected function processPagination(
        FilterDefinitionInterface $definition,
        Request $request,
        CollectionFilter $collectionFilter
    ): void {
        $paginationConfiguration = $definition->getPaginationConfiguration();
        if (!$paginationConfiguration) {
            return;
        }

        [$offset, $limit] = $paginationConfiguration->getByPage(
            (int) $request->query->get('page', '1'),
            (int) $request->query->get('per_page', (string) PaginationConfiguration::DEFAULT_RESULT_COUNT)
        );

        $collectionFilter->setOffset($offset);
        $collectionFilter->setLimit($limit);
    }

    protected function processSortable(
        FilterDefinitionInterface $definition,
        Request $request,
        CollectionFilter $collectionFilter
    ): void {
        $sortConfiguration = $definition->getSortConfiguration();
        $sort = [];
        foreach ($request->query->all()['order'] ?? [] as $columnName => $value) {
            $sortDefinition = $sortConfiguration->getSortDefinitionByName($columnName);
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
        FilterDefinitionInterface $definition,
        Request $request,
        CollectionFilter $collectionFilter
    ): void {
        $filterConfiguration = $definition->getFilterConfiguration();
        $criteria = [];
        foreach ($request->query->all()['filter'] ?? [] as $columnName => $value) {
            $filter = $filterConfiguration->getFilterByName($columnName);
            if ($filter) {
                $criteria = array_merge($criteria, $filter->getFilter($value));
            }
        }

        $predefinedFilters = $definition->getPredefinedFilterConfiguration($request)->getAllFilters();
        $collectionFilter->setCriteria(
            array_merge(
                $definition->getDefaultFilterConfiguration($request)->getAllFilters(),
                $criteria,
                $predefinedFilters
            )
        );

        $collectionFilter->setPredefinedCriteria($predefinedFilters);
    }
}
