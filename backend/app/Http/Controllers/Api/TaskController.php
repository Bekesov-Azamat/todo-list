<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Task\IndexTaskRequest;
use App\Http\Requests\Task\StoreTaskRequest;
use App\Http\Requests\Task\UpdateTaskRequest;
use App\Http\Resources\TaskResource;
use App\Models\Task;
use App\Models\User;
use App\Queries\TaskQuery;
use App\Services\TaskService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Gate;

final class TaskController extends Controller
{
    public function __construct(
        private readonly TaskQuery $taskQuery,
        private readonly TaskService $taskService,
    ) {}

    public function index(
        IndexTaskRequest $request,
    ): AnonymousResourceCollection {
        Gate::authorize('viewAny', Task::class);

        $tasks = $this->taskQuery->paginate(
            $this->authenticatedUser($request),
            $request->validated(),
        );

        return TaskResource::collection($tasks);
    }

    public function store(
        StoreTaskRequest $request,
    ): JsonResponse {
        Gate::authorize('create', Task::class);

        $task = $this->taskService->create(
            $this->authenticatedUser($request),
            $request->validated(),
        );

        return (new TaskResource($task))
            ->response()
            ->setStatusCode(Response::HTTP_CREATED);
    }

    public function show(Task $task): TaskResource
    {
        Gate::authorize('view', $task);

        return new TaskResource($task);
    }

    public function update(
        UpdateTaskRequest $request,
        Task $task,
    ): TaskResource {
        Gate::authorize('update', $task);

        $task = $this->taskService->update(
            $task,
            $request->validated(),
        );

        return new TaskResource($task);
    }

    public function destroy(Task $task): Response
    {
        Gate::authorize('delete', $task);

        $this->taskService->delete($task);

        return response()->noContent();
    }

    private function authenticatedUser(Request $request): User
    {
        $user = $request->user();

        if (! $user instanceof User) {
            abort(Response::HTTP_UNAUTHORIZED);
        }

        return $user;
    }
}
