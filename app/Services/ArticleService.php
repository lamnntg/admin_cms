<?php

namespace App\Services;

use Kreait\Laravel\Firebase\Facades\Firebase;
use App\Http\Resources\Article\HouseArticleResource;
use App\Http\Resources\Article\ServiceArticleResource;
use App\Http\Resources\Article\MarketArticleResource;
use App\Models\FirebaseUser;
use App\Models\HouseArticle;
use App\Models\MarketArticle;
use Illuminate\Http\Response;
use App\Models\ServiceArticle;

class ArticleService implements ArticleServiceInterface
{
    /**
     * Build Filter and query article function
     *
     * @param array $filter
     * @param array $paginate
     * @return array
     */
    public function getHouseArticles(array $filter, array $paginate, int $userId = null)
    {
        $query = HouseArticle::query();

        if ($userId) {
            if (!is_role_admin()) {
                $query = $query->where('user_id', $userId);
            }
            $query = $query->where('type', $filter['type']);
        } else {
            $query = $query->where('type', $filter['type'])->where('status', HouseArticle::STATUS_ACCEPTED);
        }

        if ($filter['sort_fields']) {
            $query = $query->orderBy($filter['sort_fields'], $filter['sort_order']);
        }

        $data = $query->orderBy('updated_at', 'DESC')
            ->paginate($paginate['per_page'], ['*'], 'page', $paginate['page']);

        return [Response::HTTP_OK, $data->toArray()];
    }

    /**
     * houseArticleDetail function
     *
     * @param integer $id
     * @return array
     */
    public function houseArticleDetail(int $id)
    {
        $houseArticle = HouseArticle::with('firebaseUser')->findOrFail($id);
        $data = (new HouseArticleResource($houseArticle))->toArray();

        return [Response::HTTP_OK, $data];
    }

    /**
     * Build Filter and query article function
     *
     * @param array $filter
     * @param array $paginate
     * @return array
     */
    public function getServiceArticles(array $filter, array $paginate, int $userId = null)
    {
        $query = ServiceArticle::query();

        if ($userId) {
            if (!is_role_admin()) {
                $query = $query->where('user_id', $userId);
            }
        } else {
            $query = $query->where('status', ServiceArticle::STATUS_ACCEPTED);
        }

        $data = $query->orderBy('updated_at', 'DESC')
            ->paginate($paginate['per_page'], ['*'], 'page', $paginate['page']);

        return [Response::HTTP_OK, $data->toArray()];
    }

    /**
     * serviceArticleDetail function
     *
     * @param integer $id
     * @return array
     */
    public function serviceArticleDetail(int $id)
    {
        $serviceArticle = ServiceArticle::with('firebaseUser')->findOrFail($id);
        $data = (new ServiceArticleResource($serviceArticle))->toArray();

        return [Response::HTTP_OK, $data];
    }

    /**
     * Build Filter and query article function
     *
     * @param array $filter
     * @param array $paginate
     * @return array
     */
    public function getMarketArticles(array $filter, array $paginate, $userId = null)
    {
        $query = MarketArticle::query();
        if ($userId) {
            if (!is_role_admin()) {
                $query = $query->where('user_id', $userId);
            }
        } else {
            $query = $query->where('status', ServiceArticle::STATUS_ACCEPTED);
        }

        $data = $query->orderBy('updated_at', 'DESC')
            ->paginate($paginate['per_page'], ['*'], 'page', $paginate['page']);

        return [Response::HTTP_OK, $data->toArray()];
    }

    /**
     * marketArticleDetail function
     *
     * @param integer $id
     * @return array
     */
    public function marketArticleDetail(int $id)
    {
        $marketArticle = MarketArticle::with('firebaseUser')->findOrFail($id);
        $data = (new MarketArticleResource($marketArticle))->toArray();

        return [Response::HTTP_OK, $data];
    }

    /**
     * storeHouseArticle function
     *
     * @param array $data
     * @return array
     */
    public function storeHouseArticle(array $data)
    {
        $user = request()->user();

        $nextAutoIncrement = HouseArticle::next();
        $slug = \Str::slug($data['title']) . '-' . $nextAutoIncrement;
        $haData = HouseArticle::where('slug', $slug)->withTrashed()->exists();

        if ($haData) {
            return [Response::HTTP_BAD_REQUEST, ['message' => 'This title exists.']];
        }

        $dataSave = [
            'user_id' => $user->id ?? 0,
            'title' => $data['title'],
            'content' => empty($data['content']) ? '' : $data['content'],
            'slug' => $slug,
            'images' => [],
            'type' => $data['type'],
            'price' => $data['price'],
            'status' => HouseArticle::STATUS_WAITING_ACCEPT,
            'area' => empty($data['area']) ? 0 : $data['area'],
            'bedrooms' => empty($data['bedrooms']) ? 0 : $data['bedrooms'],
            'wcs' => empty($data['wcs']) ? 0 : $data['wcs'],
            'livingrooms' => null,
            'address' => null,
            'direction_house' => empty($data['direction_house']) ? '' : $data['direction_house'],
            'house_number' => empty($data['house_number']) ? 0 : $data['house_number'],
            'kind' => HouseArticle::NEWS_NORMAL,
            'hashtags' => empty($data['hashtags']) ? [] : $data['hashtags']
        ];

        if (!empty($data['images'])) {
            foreach ($data['images'] as $image) {
                $dataSave = uploadImage($image, '/img/house_articles', $dataSave);
            }
        }

        try {
            $houseArticle = HouseArticle::create($dataSave);
        } catch (\Exception $e) {
            return [Response::HTTP_INTERNAL_SERVER_ERROR, $e];
        }

        return [Response::HTTP_OK, []];
    }

    /**
     * storeServiceArticle function
     *
     * @param array $data
     * @return void
     */
    public function storeServiceArticle(array $data)
    {
        $user = request()->user();
        $nextAutoIncrement = ServiceArticle::next();
        $slug = \Str::slug($data['title']) . '-' . $nextAutoIncrement;
        $saData = ServiceArticle::where('slug', $slug)->withTrashed()->exists();

        $dataSave = [
            'user_id' => $user->id ?? 0,
            'title' => $data['title'],
            'content' => empty($data['content']) ? '' : $data['content'],
            'slug' => $slug,
            'images' => [],
            'price' => $data['price'],
            'status' => ServiceArticle::STATUS_WAITING_ACCEPT,
            'hashtags' => empty($data['hashtags']) ? [] : $data['hashtags']
        ];

        if (!empty($data['images'])) {
            foreach ($data['images'] as $image) {
                $dataSave = uploadImage($image, '/img/service_articles', $dataSave);
            }
        }

        if ($saData) {
            return [Response::HTTP_BAD_REQUEST, ['message' => 'This title exists.']];
        }

        try {
            ServiceArticle::create($dataSave);
        } catch (\Exception $e) {
            return [Response::HTTP_INTERNAL_SERVER_ERROR, $e];
        }

        return [Response::HTTP_OK, []];
    }

    /**
     * storeMarketArticle function
     *
     * @param array $data
     * @return void
     */
    public function storeMarketArticle(array $data)
    {
        $user = request()->user();
        $nextAutoIncrement = MarketArticle::next();
        $slug = \Str::slug($data['title']) . '-' . $nextAutoIncrement;
        $saData = MarketArticle::where('slug', $slug)->withTrashed()->exists();

        $dataSave = [
            'user_id' => $user->id ?? 0,
            'title' => $data['title'],
            'content' => empty($data['content']) ? '' : $data['content'],
            'slug' => $slug,
            'images' => [],
            'price' => $data['price'],
            'status' => MarketArticle::STATUS_WAITING_ACCEPT,
            'hashtags' => empty($data['hashtags']) ? [] : $data['hashtags']
        ];

        if (!empty($data['images'])) {
            foreach ($data['images'] as $image) {
                $dataSave = uploadImage($image, '/img/market_articles', $dataSave);
            }
        }

        try {
            if (!$saData) {
                MarketArticle::create($dataSave);
            } else {
                return [Response::HTTP_BAD_REQUEST, ['message' => 'This title exists.']];
            }
        } catch (\Exception $e) {
            return [Response::HTTP_INTERNAL_SERVER_ERROR, $e];
        }

        return [Response::HTTP_OK, []];
    }

    /**
     * updateHouseArticle function
     *
     * @param array $data
     * @return array
     */
    public function updateHouseArticle(array $data)
    {
        $user = request()->user();
        $houseArticle = HouseArticle::findOrFail($data['id']);
        $dataSave = $data;
        $dataSave['user_id'] = $user->id ?? 0;
        $dataSave['images'] = $houseArticle->images;

        if (!empty($data['images'])) {
            foreach ($data['remove_images'] ?? [] as $image) {
                deleteImageLocalStorage($image);
            }

            foreach ($data['images'] as $image) {
                $dataSave = uploadImage($image, '/img/house_articles', $dataSave);
            }

            $dataSave['images'] = array_values(array_diff($dataSave['images'], $data['remove_images'] ?? []));
        }

        try {
            $dataSave['status'] = HouseArticle::STATUS_WAITING_ACCEPT;
            $houseArticle->update($dataSave);
        } catch (\Exception $e) {
            return [Response::HTTP_INTERNAL_SERVER_ERROR, $e];
        }

        return [Response::HTTP_OK, []];
    }

    /**
     * updateServiceArticle function
     *
     * @param array $data
     * @return array
     */
    public function updateServiceArticle(array $data)
    {
        $user = request()->user();

        $serviceArticle= ServiceArticle::findOrFail($data['id']);

        $dataSave = $data;
        $dataSave['user_id'] = $user->id ?? 0;
        $dataSave['images'] = [];

        if (!empty($data['images'])) {
            foreach ($data['remove_images'] ?? [] as $image) {
                deleteImageLocalStorage($image);
            }

            foreach ($data['images'] as $image) {
                $dataSave = uploadImage($image, '/img/service_articles', $dataSave);
            }

            $dataSave['images'] = array_values(array_diff($dataSave['images'], $data['remove_images'] ?? []));
        }

        try {
            $dataSave['status'] = ServiceArticle::STATUS_WAITING_ACCEPT;
            $serviceArticle->update($dataSave);
        } catch (\Exception $e) {
            return [Response::HTTP_INTERNAL_SERVER_ERROR, $e];
        }

        return [Response::HTTP_OK, []];
    }

    /**
     * updateMarketArticle function
     *
     * @param array $data
     * @return array
     */
    public function updateMarketArticle(array $data)
    {
        $user = request()->user();

        $marketArticle= MarketArticle::findOrFail($data['id']);

        $dataSave = $data;
        $dataSave['user_id'] = $user->id ?? 0;
        $dataSave['images'] = [];

        if (!empty($data['images'])) {
            foreach ($data['remove_images'] ?? [] as $image) {
                deleteImageLocalStorage($image);
            }

            foreach ($data['images'] as $image) {
                $dataSave = uploadImage($image, '/img/market_articles', $dataSave);
            }

            $dataSave['images'] = array_values(array_diff($dataSave['images'], $data['remove_images'] ?? []));
        }

        try {
            $dataSave['status'] = MarketArticle::STATUS_WAITING_ACCEPT;
            $marketArticle->update($dataSave);
        } catch (\Exception $e) {
            return [Response::HTTP_INTERNAL_SERVER_ERROR, $e];
        }

        return [Response::HTTP_OK, []];
    }

    public function hardDeleteHA(int $id)
    {
        try {
            $houseArticle = HouseArticle::withTrashed()->find($id);

            if ($houseArticle) {
                $houseArticle->forceDelete();
                return [Response::HTTP_OK, ['message' => 'This record has force deleted.']];
            } else {
                return [Response::HTTP_BAD_REQUEST, ['message' => 'This record not found.']];
            }
        } catch (\Exception $e) {
            return [Response::HTTP_INTERNAL_SERVER_ERROR, $e];
        }
    }

    public function softDeleteHA(int $id)
    {
        try {
            $houseArticle = HouseArticle::find($id);

            if ($houseArticle) {
                $houseArticle->delete();
                return [Response::HTTP_OK, ['message' => 'This record has soft deleted.']];
            } else {
                return [Response::HTTP_BAD_REQUEST, ['message' => 'This record not found.']];
            }
        } catch (\Exception $e) {
            return [Response::HTTP_INTERNAL_SERVER_ERROR, $e];
        }
    }

    public function hardDeleteSA(int $id)
    {
        try {
            $serviceArticle = ServiceArticle::withTrashed()->find($id);

            if ($serviceArticle) {
                $serviceArticle->forceDelete();
                return [Response::HTTP_OK, ['message' => 'This record has force deleted.']];
            } else {
                return [Response::HTTP_BAD_REQUEST, ['message' => 'This record not found.']];
            }
        } catch (\Exception $e) {
            return [Response::HTTP_INTERNAL_SERVER_ERROR, $e];
        }
    }

    public function softDeleteSA(int $id)
    {
        try {
            $serviceArticle = ServiceArticle::find($id);

            if ($serviceArticle) {
                $serviceArticle->delete();
                return [Response::HTTP_OK, ['message' => 'This record has soft deleted.']];
            } else {
                return [Response::HTTP_BAD_REQUEST, ['message' => 'This record not found.']];
            }
        } catch (\Exception $e) {
            return [Response::HTTP_INTERNAL_SERVER_ERROR, $e];
        }
    }

    public function softDeleteMA(int $id)
    {
        try {
            $marketArticle = MarketArticle::find($id);

            if ($marketArticle) {
                $marketArticle->delete();
                return [Response::HTTP_OK, ['message' => 'This record has soft deleted.']];
            } else {
                return [Response::HTTP_BAD_REQUEST, ['message' => 'This record not found.']];
            }
        } catch (\Exception $e) {
            return [Response::HTTP_INTERNAL_SERVER_ERROR, $e];
        }
    }

    private function verifyUserRequestFromAdmin() {
        $accessToken = request()->bearerToken();
        if (!$accessToken) {
            return null;
        }

        $result = Firebase::auth()->verifyIdToken($accessToken)->claims()->all();
        $firebaseUser = FirebaseUser::where('uid', $result['user_id'])->first();

        return $firebaseUser;
    }
}
