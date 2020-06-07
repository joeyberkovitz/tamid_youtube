<?php

namespace App;

use BaoPham\DynamoDb\DynamoDbModel;
use Illuminate\Database\Eloquent\Model;

// Search model, extends DynamoDbModel to use DynamoDB
// To use Laravel supported DB, switch to extend Model
class Search extends DynamoDbModel
{
    use UsesUuid;

    // DB table name
    protected $table = 'search_history';
    // Don't put timestamps in table
    public $timestamps = false;
    // Fields in DB
    protected $fillable = ['user_id', 'query', 'search_date'];
    // Fields that should be interpreted as dates
    protected $dates = ['search_date'];

    // For DynamoDB - indicates how the table is indexed
    protected $dynamoDbIndexKeys = [
        'search_index' => [
            'hash' => 'id',
            'range' => 'search_date'
        ],
    ];
}
