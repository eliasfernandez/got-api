<?php

namespace App\Interface\Http\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;

abstract class ApiController extends AbstractController
{
    protected const PAGE_DEFAULT = 1;
    protected const MAX_RESULTS_PER_PAGE = 20;
    protected const MAX_RESULTS_PER_PAGE_LIMIT = 100;

    protected function getPage(Request $request): int
    {
        try {
            $page = $request->query->getInt('page', self::PAGE_DEFAULT);
            if ($page > 0) {
                 return $page;
            }
        } catch (\Exception $_) {
        }

        throw new BadRequestHttpException('Invalid `page` request');
    }

    protected function getLimit(Request $request): int
    {
        try {
            $limit = $request->query->getInt('limit', self::MAX_RESULTS_PER_PAGE);
            if ($limit > 0 && $limit <= self::MAX_RESULTS_PER_PAGE_LIMIT) {
                 return $limit;
            }
        } catch (\Exception $_) {
        }

        throw new BadRequestHttpException('Invalid `limit` request');
    }

    protected function getSearchQuery(Request $request): string
    {
        $query = $request->query->getString('q', '*');

        return empty($query) ? '*' : $query;
    }
}
