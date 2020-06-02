<?php

namespace App;

use BaoPham\DynamoDb\DynamoDbModel;
use Illuminate\Database\Eloquent\Model;

class Search extends DynamoDbModel
{
    use UsesUuid;

    protected $table = 'search_history';
    public $timestamps = false;
    protected $fillable = ['user_id', 'query', 'search_date'];
    protected $dates = ['search_date'];

    protected $dynamoDbIndexKeys = [
        'search_index' => [
            'hash' => 'id',
            'range' => 'search_date'
        ],
    ];
}
