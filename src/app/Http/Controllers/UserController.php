<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreUserRequest;
use App\Http\Requests\UpdateUserRequest;
use App\Http\Resources\UserResource;
use App\Models\User;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\ResourceCollection;
use Illuminate\Http\Response;
use Illuminate\Support\Arr;

class UserController extends Controller
{
    use AuthorizesRequests;

    /**
     * Display a listing of the resource.
     */
    public function index(Request $request): ResourceCollection
    {
        $this->authorize('list', User::class);

        return UserResource::collection(User::query()
            ->when($request->filled('search'), function ($query) use ($request) {
                $searchTerms = explode(' ', $request->search);
                return $query->where(function ($query) use ($searchTerms) {
                    foreach ($searchTerms as $term) {
                        $query->where(function ($query) use ($term) {
                            $query->where('first_name', 'like', "%{$term}%")
                                ->orWhere('last_name', 'like', "%{$term}%");
                        });
                    }
                });
            })
            ->when($request->query('teams'), function (Builder $query, $teams) {
                $query->whereHas('team', fn (Builder $query) => $query->whereIn('id', Arr::wrap($teams)));
            })
            ->withCount(['documents'])
            ->with('team')
            ->orderBy('first_name', 'asc')
            ->orderBy('last_name', 'asc')
            ->paginate(50)
        );
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreUserRequest $request): UserResource
    {
        $user = User::create($request->safe());

        return new UserResource($user);
    }

    /**
     * Display the specified resource.
     */
    public function show(Request $request, User $user): UserResource
    {
        $this->authorize('show', $user);
        $user->load(['team']);

        return new UserResource($user);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateUserRequest $request, User $user): UserResource
    {
        $user->update($request->safe());
        $user->load(['team']);

        return new UserResource($user);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(User $user): Response
    {
        $this->authorize('delete', $user);
        $user->delete();

        return response()->noContent();
    }
}
