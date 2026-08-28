<?php

namespace App\Http\Controllers;

use App\Models\Task;
use Illuminate\Http\Request;
use OpenApi\Attributes as OA;

#[OA\Tag(name: 'Tasks')]
class TaskController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    #[OA\Get(
        path: '/api/tasks',
        summary: 'List the authenticated user\'s tasks',
        tags: ['Tasks'],
        security: [['sanctum' => []]],
        responses: [
            new OA\Response(
                response: 200,
                description: 'List of tasks',
                content: new OA\JsonContent(type: 'array', items: new OA\Items(ref: '#/components/schemas/Task'))
            ),
            new OA\Response(response: 401, description: 'Unauthenticated'),
        ]
    )]
    public function index(Request $request)
    {
        return $request->user()->tasks()->latest()->get();
    }

    /**
     * Store a newly created resource in storage.
     */
    #[OA\Post(
        path: '/api/tasks',
        summary: 'Create a task',
        tags: ['Tasks'],
        security: [['sanctum' => []]],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                required: ['title'],
                properties: [
                    new OA\Property(property: 'title', type: 'string', example: 'Buy groceries'),
                    new OA\Property(property: 'description', type: 'string', nullable: true, example: 'Milk, eggs, bread'),
                ]
            )
        ),
        responses: [
            new OA\Response(response: 201, description: 'Task created', content: new OA\JsonContent(ref: '#/components/schemas/Task')),
            new OA\Response(response: 401, description: 'Unauthenticated'),
            new OA\Response(response: 422, description: 'Validation error'),
        ]
    )]
    public function store(Request $request)
    {
        $validated = $request->validate([
            'title' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
        ]);

        return $request->user()->tasks()->create($validated);
    }

    /**
     * Display the specified resource.
     */
    #[OA\Get(
        path: '/api/tasks/{task}',
        summary: 'Get a single task',
        tags: ['Tasks'],
        security: [['sanctum' => []]],
        parameters: [
            new OA\Parameter(name: 'task', in: 'path', required: true, schema: new OA\Schema(type: 'integer')),
        ],
        responses: [
            new OA\Response(response: 200, description: 'Task', content: new OA\JsonContent(ref: '#/components/schemas/Task')),
            new OA\Response(response: 401, description: 'Unauthenticated'),
            new OA\Response(response: 403, description: 'Forbidden'),
            new OA\Response(response: 404, description: 'Not found'),
        ]
    )]
    public function show(Request $request, Task $task)
    {
        abort_unless($task->user_id === $request->user()->id, 403);

        return $task;
    }

    /**
     * Update the specified resource in storage.
     */
    #[OA\Put(
        path: '/api/tasks/{task}',
        summary: 'Update a task',
        tags: ['Tasks'],
        security: [['sanctum' => []]],
        parameters: [
            new OA\Parameter(name: 'task', in: 'path', required: true, schema: new OA\Schema(type: 'integer')),
        ],
        requestBody: new OA\RequestBody(
            content: new OA\JsonContent(
                properties: [
                    new OA\Property(property: 'title', type: 'string', example: 'Buy groceries'),
                    new OA\Property(property: 'description', type: 'string', nullable: true, example: 'Milk, eggs, bread'),
                    new OA\Property(property: 'completed', type: 'boolean', example: true),
                ]
            )
        ),
        responses: [
            new OA\Response(response: 200, description: 'Task updated', content: new OA\JsonContent(ref: '#/components/schemas/Task')),
            new OA\Response(response: 401, description: 'Unauthenticated'),
            new OA\Response(response: 403, description: 'Forbidden'),
            new OA\Response(response: 422, description: 'Validation error'),
        ]
    )]
    public function update(Request $request, Task $task)
    {
        abort_unless($task->user_id === $request->user()->id, 403);

        $validated = $request->validate([
            'title' => ['sometimes', 'required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'completed' => ['sometimes', 'boolean'],
        ]);

        $task->update($validated);

        return $task;
    }

    /**
     * Remove the specified resource from storage.
     */
    #[OA\Delete(
        path: '/api/tasks/{task}',
        summary: 'Delete a task',
        tags: ['Tasks'],
        security: [['sanctum' => []]],
        parameters: [
            new OA\Parameter(name: 'task', in: 'path', required: true, schema: new OA\Schema(type: 'integer')),
        ],
        responses: [
            new OA\Response(response: 204, description: 'Task deleted'),
            new OA\Response(response: 401, description: 'Unauthenticated'),
            new OA\Response(response: 403, description: 'Forbidden'),
        ]
    )]
    public function destroy(Request $request, Task $task)
    {
        abort_unless($task->user_id === $request->user()->id, 403);

        $task->delete();

        return response()->noContent();
    }
}
