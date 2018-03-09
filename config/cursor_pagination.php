<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Default results per page
    |--------------------------------------------------------------------------
    |
    | If you don't specify a results per page, this value will be used.
    | Remember that if you use an array instead of an integer, the first value
    | of the array will be results per page when it's fetching previous, and
    | the second one is for next and normal fetches.
    */
    'per_page'         => 15,
    /*
    |--------------------------------------------------------------------------
    | Identifier name
    |--------------------------------------------------------------------------
    |
    | The default identifier is 'id', or the model's primaryKey, but here
    | it's the visible name.
    | They will show up in the URL, and in the meta props.
    |
    | Cursor is more like it, but use whatever.
    */
    'identifier_name'  => 'cursor',

    /*
    |--------------------------------------------------------------------------
    | Navigation Names
    |--------------------------------------------------------------------------
    |
    | Customize the navigation names by changing the first element for the previous
    | identifier and the second element for the next one.
    |
    | Examples: ['before', 'after'] , ['min', 'max']
    */
    'navigation_names' => ['previous', 'next'],

    /*
    |--------------------------------------------------------------------------
    | Transform Name
    |--------------------------------------------------------------------------
    |
    | Specify a global function to be called to format the original snake_case.
    | Can use Laravel's Helper functions.
    |
    | Examples: 'camel_case', 'kebab_case', 'snake_case'.
    */
    'transform_name'   => 'snake_case',
];
