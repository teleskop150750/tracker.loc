<?php

declare(strict_types=1);

namespace Modules\Tracker\Task\Domain\Services;

use App\Support\Arr;
use Illuminate\Http\Client\Pool;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Client\Response;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Http;

class FileStorageServivce
{
    public function index(array $fileIds = []): array
    {
        $query = null;

        if (count($fileIds) > 0) {
            $queryArr = ['filters[id]' => $fileIds];
            $query = '?' . Arr::query($queryArr);
        }

        $response = Http::withToken(env('FILE_STORAGE_TOKEN'))
            ->get(env('FILE_STORAGE_API') . '/api/v1/files' . $query);

        return $response->json('data');
    }

    public function save(UploadedFile $file, array $data): Response
    {
        $fileName = $file->getClientOriginalName();
        return Http::withToken(env('FILE_STORAGE_TOKEN'))
            ->attach('attachment', file_get_contents($file->path()), $fileName)
            ->post(env('FILE_STORAGE_API') . '/api/v1/files', $data);
    }

    // public function chunk(Request $request)
    // {
    //     $response = Http::withToken(env('FILE_STORAGE_TOKEN'))
    //         ->get(env('FILE_STORAGE_API') . '/api/v1/check-chunk', $request->all());

    //     return response($response->body(), $response->status(), $response->headers());
    // }


    /**
     * @param string | string[] $fileId
     * @return JsonResponse
     */
    public function delete(mixed $fileId) : JsonResponse
    {
        $fileIds = Arr::wrap($fileId);

        Http::pool(fn (Pool $pool) => Arr::map($fileIds, static fn($id) => $pool->withToken(env('FILE_STORAGE_TOKEN'))->delete(env('FILE_STORAGE_API') . '/api/v1/files/' . $id)));

        
        return response()->json([
            'status' => 'success',
            'code' => 200,
            'title' => 'Удален',
        ]);
    }


    public function download(string $fileId): Response
    {
        return Http::withToken(env('FILE_STORAGE_TOKEN'))->get(env('FILE_STORAGE_API') . '/api/v1/files/' . $fileId . '/download');
    }
}