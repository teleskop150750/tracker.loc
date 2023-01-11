<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Services\UrlGenerator;
use App\Support\Arr;
use DateInterval;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\NonUniqueResultException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Http;
use Illuminate\Validation\ValidationException;
use Modules\Auth\User\Domain\Entity\User;
use Modules\Auth\User\Domain\Entity\ValueObject\UserUuid;
use Modules\Auth\User\Domain\Repository\UserRepositoryInterface;
use Modules\Tracker\Folder\Domain\Entity\Folder\Folder;
use Modules\Tracker\Folder\Domain\Entity\Folder\ValueObject\FolderPublished;
use Modules\Tracker\Folder\Domain\Entity\Folder\ValueObject\FolderType;
use Modules\Tracker\Folder\Domain\Entity\Folder\ValueObject\FolderUuid;
use Modules\Tracker\Folder\Domain\Repository\FolderRepositoryInterface;
use Modules\Tracker\Folder\Domain\Services\FolderFormatter;
use Modules\Tracker\Task\Domain\Entity\Task\ValueObject\TaskPublished;
use Modules\Tracker\Task\Domain\Entity\Task\ValueObject\TaskStatus;
use Modules\Tracker\Task\Domain\Entity\Task\ValueObject\TaskUuid;
use Modules\Tracker\Task\Domain\Entity\TaskRelationship\TaskRelationship;
use Modules\Tracker\Task\Domain\Entity\TaskRelationship\ValueObject\TaskRelationshipType;
use Modules\Tracker\Task\Domain\Entity\TaskRelationship\ValueObject\TaskRelationshipUuid;
use Modules\Tracker\Task\Domain\Repository\FileRepositoryInterface;
use Modules\Tracker\Task\Domain\Repository\TaskRelationshipRepositoryInterface;
use Modules\Tracker\Task\Domain\Repository\TaskRepositoryInterface;
use Modules\Tracker\Task\Domain\Services\UpdateTaskStatusService;

class ExampleController extends Controller
{
    /**
     * Create a new controller instance.
     */
    public function __construct()
    {
    }

    public function test(Request $request)
    {
        // $id = '982c8a7c-fda3-4fff-afc7-25c229f39394';
        // $response = Http::withToken(env('FILE_STORAGE_TOKEN'))->get(env('FILE_STORAGE_API') . '/test');

        // return response($response->body(), $response->status(), $response->headers());

        // $response = Http::withToken(env('FILE_STORAGE_TOKEN'))->get(env('FILE_STORAGE_API') . '/api/v1/files/' . $id . '/download', $request->all());

        // return response($response->body(), $response->status(), $response->headers());

        $this->validate($request, [
            'file' => ['required', 'file'],
        ]);

        $file = $request->file('file');
        $fileName = $file->getClientOriginalName();
        $response = Http::withToken(env('FILE_STORAGE_TOKEN'))
            ->attach('attachment', file_get_contents($file->path()), $fileName)
            ->post(env('FILE_STORAGE_API') . '/api/v1/files', $request->all());

        return response($response->body(), $response->status(), $response->headers());
    }

    public function chunk(Request $request)
    {
        $response = Http::withToken(env('FILE_STORAGE_TOKEN'))
            ->get(env('FILE_STORAGE_API') . '/api/v1/check-chunk', $request->all());

        return response($response->body(), $response->status(), $response->headers());
    }

    public function delete(Request $request, string $fileId)
    {
        $response = Http::withToken(env('FILE_STORAGE_TOKEN'))
            ->delete(env('FILE_STORAGE_API') . '/api/v1/files/' . $fileId , $request->all());

        return response($response->body(), $response->status(), $response->headers());
    }

    public function download(Request $request, string $fileId)
    {
        $response = Http::withToken(env('FILE_STORAGE_TOKEN'))
            ->get(env('FILE_STORAGE_API') . '/api/v1/files/' . $fileId , $request->all());

        return response($response->body(), $response->status(), $response->headers());
    }
}