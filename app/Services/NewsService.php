<?php

namespace App\Services;

use App\Models\News;
use Illuminate\Support\Str;
use Illuminate\Http\Response;

class NewsService implements NewsServiceInterface
{
    public function store(array $data)
    {
        $nextAutoIncrement = News::next();
        $slug = \Str::slug($data['title']) . '-' . $nextAutoIncrement;
        $newsData = News::where('slug', $slug)->withTrashed()->exists();

        $dataSave = [
            'user_id' => empty($data['user_id']) ? 0 : $data['user_id'],
            'title' => $data['title'],
            'content' => empty($data['content']) ? '' : $data['content'],
            'slug' => $slug,
            'images' => [],
            'status' => News::STATUS_ACCEPTED,
            'hashtags' => empty($data['hashtags']) ? [] : $data['hashtags']
        ];

        if (!empty($data['images'])) {
            foreach ($data['images'] as $dataImage) {
                $dataSave = uploadImage($dataImage, '/img/news', $dataSave);
            }
        }

        try {
            if (!$newsData) {
                News::create($dataSave);
            } else {
                return [Response::HTTP_BAD_REQUEST, ['message' => 'This title exists.']];
            }
        } catch (\Exception $e) {
            return [Response::HTTP_INTERNAL_SERVER_ERROR, $e];
        }

        return [Response::HTTP_OK, []];
    }

    public function hardDelete(int $id)
    {
        try {
            $news = News::withTrashed()->find($id);

            if ($news) {
                $news->forceDelete();
                return [Response::HTTP_OK, ['message' => 'This record has force deleted.']];
            } else {
                return [Response::HTTP_BAD_REQUEST, ['message' => 'This record not found.']];
            }
        } catch (\Exception $e) {
            return [Response::HTTP_INTERNAL_SERVER_ERROR, $e];
        }
    }

    public function softDelete(int $id)
    {
        try {
            $news = News::find($id);

            if ($news) {
                $news->delete();
                return [Response::HTTP_OK, ['message' => 'This record has soft deleted.']];
            } else {
                return [Response::HTTP_BAD_REQUEST, ['message' => 'This record not found.']];
            }
        } catch (\Exception $e) {
            return [Response::HTTP_INTERNAL_SERVER_ERROR, $e];
        }
    }

    /**
     * Build query news function
     *
     * @param array $paginate
     * @return array
     */
    public function getNews(array $paginate)
    {
        $query = News::query();

        $data = $query->orderBy('updated_at', 'DESC')
            ->paginate($paginate['per_page'], ['*'] , 'page', $paginate['page']);

        return [Response::HTTP_OK, $data->toArray()];
    }

    /**
     * update news function
     *
     * @param array $data
     * @return array
     */
    public function updateNews(array $data)
    {
        $user = request()->user();

        $news= News::findOrFail($data['id']);

        $dataSave = $data;
        $dataSave['user_id'] = $user->id ?? 0;
        $dataSave['images'] = [];

        if (!empty($data['images'])) {
            foreach ($data['remove_images'] ?? [] as $image) {
                deleteImageLocalStorage($image);
            }

            foreach ($data['images'] as $image) {
                $dataSave = uploadImage($image, '/img/news', $dataSave);
            }

            $dataSave['images'] = array_values(array_diff($dataSave['images'], $data['remove_images'] ?? []));
        }

        try {
            $news->update($dataSave);
        } catch (\Exception $e) {
            return [Response::HTTP_INTERNAL_SERVER_ERROR, $e];
        }

        return [Response::HTTP_OK, []];
    }

    public function getNewsDetail(int $id)
    {
        $news = News::findOrFail($id);

        return [Response::HTTP_OK, $news];
    }
}
