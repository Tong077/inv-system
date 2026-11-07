<?php

namespace App\Http\Controllers\Concerns;

use Illuminate\Contracts\View\View;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;

trait ListsModelRecords
{
    /**
     * @param  class-string<Model>  $model
     * @param  list<string>  $searchable
     * @param  list<string>  $relations
     */
    protected function listing(
        Request $request,
        string $resource,
        string $model,
        array $searchable = [],
        array $relations = [],
        array $emptyState = [],
    ): View {
        $search = $request->string('search')->trim()->limit(100)->toString();
        $query = $model::query();

        if ($relations !== []) {
            $query->with($relations);
        }

        if ($search !== '' && $searchable !== []) {
            $query->where(function (Builder $builder) use ($searchable, $search): void {
                foreach ($searchable as $column) {
                    $builder->orWhere($column, 'like', '%'.$search.'%');
                }
            });
        }

        return view($resource.'.index', [
            'records' => $query->orderByDesc('id')->paginate(15)->withQueryString(),
            'search' => $search,
            'emptyTitle' => $emptyState['title'] ?? 'No records yet',
            'emptyDescription' => $emptyState['description'] ?? 'Records will appear here when they are available.',
        ]);
    }
}
