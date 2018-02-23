<?php

namespace Juampi92\CursorPagination;

use Illuminate\Database\Eloquent\Builder as EloquentBuilder;
use Illuminate\Database\Query\Builder as QueryBuilder;
use Illuminate\Support\ServiceProvider;

class CursorPaginationServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap the application events.
     *
     * @return void
     */
    public function boot()
    {
        if ($this->app->runningInConsole()) {
            $this->publishes([
                __DIR__.'/../config/cursor_pagination.php' => config_path('cursor_pagination.php'),
            ], 'config');
        }

        $this->registerMacro();
    }

    /**
     * Register the service provider.
     *
     * @return void
     */
    public function register()
    {
        $this->mergeConfigFrom(__DIR__.'/../config/cursor_pagination.php', 'cursor_pagination');
    }

    /**
     * Create Macros for the Builders.
     */
    public function registerMacro()
    {
        $macro = function ($perPage = null, $columns = ['*'], array $options = []) {

            // Use model's key name by default if EloquentBuilder
            if (!isset($options['identifier'])) {
                $options['identifier'] = isset($this->model) ? $this->model->getKeyName() : 'id';
            }

            if (!isset($options['request'])) {
                $options['request'] = request();
            }

            // Resolve the cursor by using the request query params
            $cursor = CursorPaginator::resolveCurrentCursor($options['request']);

            $query_orders = isset($this->query) ? $this->query->orders : $this->orders;

            $identifier_sort_inverted = collect($query_orders)->firstWhere('column', $options['identifier']);
            $identifier_sort_inverted = $identifier_sort_inverted ? $identifier_sort_inverted['direction'] === 'desc' : false;

            if ($cursor->isPrev()) {
                $this->where($options['identifier'], $identifier_sort_inverted ? '>' : '<', $cursor->getPrevCursor());
            }
            if ($cursor->isNext()) {
                $this->where($options['identifier'], $identifier_sort_inverted ? '<' : '>', $cursor->getNextCursor());
            }

            if (is_null($perPage)) {
                $perPage = config('cursor_pagination.per_page', 10);
            }

            // Dynamic perPage.
            // If it's an array, use first value to refer as prev, and second to refer as next
            if (is_array($perPage)) {
                $isOnlyPrev = $cursor->isPrev() && !$cursor->isNext();
                $perPage = $perPage[$isOnlyPrev ? 0 : 1];
            }

            // Limit results
            $this->take($perPage + 1);

            return new CursorPaginator($this->get($columns), $perPage, $options);
        };

        // Register macros
        QueryBuilder::macro('cursorPaginate', $macro);
        EloquentBuilder::macro('cursorPaginate', $macro);
    }
}
