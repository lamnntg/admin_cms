<?php

namespace App\Http\Controllers\Api;

use App\Services\ArticleServiceInterface;
use App\Http\Requests\CreateHouseArticleRequest;
use App\Http\Requests\CreateServiceArticleRequest;

class ArticleController extends ApiController
{
    protected $articleService;

    /**
     * create a new instance
     *
     * @param ArticleServiceInterface $articleService
     */
    public function __construct(ArticleServiceInterface $articleService)
    {
        $this->articleService = $articleService;
    }

    /**
     * store house article service
     * @param CreateHouseArticleRequest $request
     *
     * @return json
     */
    public function storeHouseArtical(CreateHouseArticleRequest $request)
    {
        list($statusCode, $data) = $this->articleService->storeHouseArtical($request->all());

        return $this->response($data, $statusCode);
    }

    /**
     * store house article service
     * @param CreateServiceArticleRequest $request
     *
     * @return json
     */
    public function storeServiceArtical(CreateServiceArticleRequest $request)
    {
        list($statusCode, $data) = $this->articleService->storeServiceArtical($request->all());

        return $this->response($data, $statusCode);
    }

    /**
     * force delete house article service
     * @param int $id
     *
     * @return json
     */
    public function hardDeleteHA(int $id)
    {
        list($statusCode, $data) = $this->articleService->hardDeleteHA($id);

        return $this->response($data, $statusCode);
    }

    /**
     * soft delete house article service
     * @param int $id
     *
     * @return json
     */
    public function softDeleteHA(int $id)
    {
        list($statusCode, $data) = $this->articleService->softDeleteHA($id);

        return $this->response($data, $statusCode);
    }

    /**
     * force delete service article service
     * @param int $id
     *
     * @return json
     */
    public function hardDeleteSA(int $id)
    {
        list($statusCode, $data) = $this->articleService->hardDeleteSA($id);

        return $this->response($data, $statusCode);
    }

    /**
     * soft delete service article service
     * @param int $id
     *
     * @return json
     */
    public function softDeleteSA(int $id)
    {
        list($statusCode, $data) = $this->articleService->softDeleteSA($id);

        return $this->response($data, $statusCode);
    }
}