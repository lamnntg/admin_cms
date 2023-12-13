<?php

namespace App\Services;

use App\Models\News;
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
            'images' => !empty($data['images']) ? [$data['images']] : [],
            'status' => News::STATUS_ACCEPTED
        ];

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
}