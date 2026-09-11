<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class UserController extends Controller
{
    public function index(): JsonResponse
    {
        $users = User::query()
            ->visibleTo(request()->user())
            ->with('roles.permissions')
            ->paginate(15);

        return response()->json($users);
    }

    public function me(Request $request): JsonResponse
    {
        return response()->json($request->user()->load('roles.permissions'));
    }
}
