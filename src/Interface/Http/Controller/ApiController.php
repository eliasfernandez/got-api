<?php

namespace App\Interface\Http\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;

abstract class ApiController extends AbstractController
{
    protected const PAGE_DEFAULT = 1;
    protected const MAX_RESULTS_PER_PAGE = 20;

    protected function getPage(Request $request): int
    {
        try {
            return $request->query->getInt('page', self::PAGE_DEFAULT);
        } catch (\Exception $_) {
            throw new BadRequestHttpException('Invalid `page` request');
        }
    }

    protected function getLimit(Request $request): int
    {
        try {
            return $request->query->getInt('limit', self::MAX_RESULTS_PER_PAGE);
        } catch (\Exception $_) {
            throw new BadRequestHttpException('Invalid `limit` request');
        }
    }

    protected function getSearchQuery(Request $request): string
    {
        $query = $request->get('q', '*');

        return empty($query) ? '*' : $query;
    }
}
