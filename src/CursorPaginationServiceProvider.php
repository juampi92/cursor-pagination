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
                __DIR__ . '/../config/cursor_pagination.php' => config_path('cursor_pagination.php'),
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
        $this->mergeConfigFrom(__DIR__ . '/../config/cursor_pagination.php', 'cursor_pagination');
    }

    /**
     * Create Macros for the Builders.
     */
    public function registerMacro()
    {
        /**
         * @param null $perPage default=null
         * @param array $columns default=['*']
         * @param array $options
         *
         * @return CursorPaginator
         */
        $macro = function ($perPage = null, $columns = ['*'], array $options = []) {
            $query_orders = isset($this->query) ? collect($this->query->orders) : collect($this->orders);
            $identifier_sort = null;

            // Build the default identifier by considering column sorting and primaryKeys
            if (!isset($options['identifier'])) {

                // Check if has explicit orderBy clause
                if ($query_orders->isNotEmpty()) {
                    // Make the identifier the name of the first sorted column
                    $identifier_sort = $query_orders->first();
                    $options['identifier'] = $identifier_sort['column'];
                } else {
                    // If has no orderBy clause, use the primaryKeyName (if it's a Model), or the default 'id'
                    $options['identifier'] = isset($this->model) ? $this->model->getKeyName() : 'id';
                }
            } else {
                $identifier_sort = $query_orders->firstWhere('column', $options['identifier']);
            }

            if (!isset($options['request'])) {
                $options['request'] = request();
            }

            // The identifier_alias is attribute on the model used.
            // This should be the name of the Query Select column.
            if (!isset($options['identifier_alias'])) {
                // If no identifier_alias is defined, we define it by parsing
                // the identifier and striping any table names, leaving only
                // the column name. `following.created_at` will be `created_at`.
                $identifierName = last(explode('.', $options['identifier']));
                $options['identifier_alias'] = $identifierName;
            }

            // If there's no date_identifier option, and the query is
            // built in a Model, we can check if the model has a datetime
            // or date cast on the `identifier_alias`, and guess if it's
            // a datetime identifier or not.
            if (!isset($options['date_identifier']) && isset($this->model)) {
                $options['date_identifier'] = $this->model->hasCast($options['identifier_alias'], ['datetime', 'date']);
            }

            // Resolve the cursor by using the request query params
            $cursor = CursorPaginator::resolveCurrentCursor($options['request']);

            if (isset($options['date_identifier']) && $options['date_identifier']) {
                // Also check that the database does not contain unix dates.
                $not_using_unix = !(isset($options['date_unix']) && $options['date_unix']);
                $cursor->setDateIdentifier($not_using_unix);
            }

            // If there's a sorting by the identifier, check if it's desc so the cursor is inverted
            $identifier_sort_inverted = $identifier_sort ? $identifier_sort['direction'] === 'desc' : false;

            if ($cursor->isPrev()) {
                $this->where($options['identifier'], $identifier_sort_inverted ? '>' : '<', $cursor->getPrevQuery());
            }
            if ($cursor->isNext()) {
                $this->where($options['identifier'], $identifier_sort_inverted ? '<' : '>', $cursor->getNextQuery());
            }

            // Use configs perPage if it's not defined
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
