<?php
## app/Console/Commands/DynamoDB.php
namespace App\Console\Commands;

use Illuminate\Support\Facades\App;
use Illuminate\Console\Command;
use Aws\DynamoDb\Exception\DynamoDbException;

use Symfony\Component\Process\Process;
use Symfony\Component\Process\Exception\ProcessFailedException;

class DynamoDB extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'dynamodb:exec {action} {--table=} {--id=} {--user=} {--tag=true}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'DynamoDB Wrapper';

    private $client;
    private $config = [];

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
        if(env('DYNAMODB_LOCAL')) {
            $this->config['endpoint'] = env('DYNAMODB_LOCAL_ENDPOINT');
        }
        $this->client = \BaoPham\DynamoDb\Facades\DynamoDb::client();
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        $action = $this->argument('action');
        $tables = $this->option('table');
        if(!empty($tables)) {
            $names = explode(',', $tables);
            $arr = [];
            foreach($names as $name) {
                $modelClass = 'App\\' . $name;
                $model = new $modelClass();
                $arr[$name] = $model->getTable();
            }
            $tables = $arr;
        }
        $ids = $this->option('id');
        if(!empty($ids)) {
            $ids = explode(',', $ids);
        }
        $users = $this->option('user');
        if(!empty($ids)) {
            $users = explode(',', $users);
        }

        $tag = $this->option('tag');
        if($tag == 'false') $tag = false;
        else $tag = true;
        echo "Tagging: " . ($tag ? 'Enabled' : 'Disabled') . "\n";

        switch($action) {
            case 'list-tables': {
                $this->info('Listing Tables');
                $tables = $this->client->listTables();
                foreach($tables['TableNames'] as $name=>$table) {
                    $this->line($table);
                }
            } break;
            case 'scan': {
                foreach($tables as $table) {
                    echo "Scanning: " . $table . "\n";;
                    $items = $this->client->getIterator('Scan', [
                        'TableName' => $table,
                    ]);
                    foreach($items as $item) {
                        $this->line(json_encode($item));
                    }
                }
            } break;
            case 'count': {
                if(empty($tables)) $tables = $this->getAllTables();
                foreach($tables as $table) {
                    echo $table;
                    $output = $this->client->describeTable([
                        'TableName' => $table,
                    ]);
                    echo " Items: " . $output['Table']['ItemCount'] . "\n";
                }
            } break;
            case 'describe-table': {
                foreach($tables as $table) {
                    echo $table . ": \n";
                    $output = $this->client->describeTable([
                        'TableName'=>$table
                    ]);
                }
            } break;
            case 'delete': {
                if(count($tables) !== 1) { $this->error('You must provide a single table to delete item(s) from.'); return; }
                if(count($users) > 1 && count($users) != count($ids)) { $this->error('If passing User IDs, the number of Users must match the number of IDs'); return ; }
                $table = $tables[0];
                if($this->confirm('Are you sure you want to delete "' . implode(',', $ids) . '" from "' . $table . '"')) {
                    foreach($ids as $index=>$id) {
                        try {
                            $key = [
                                'id'   => ['S' => $id],
                            ];
                            if(count($users) == 1) {
                                $key['user_id'] = ['S' => $users[0]];
                            }
                            if(count($users) == count($ids)) {
                                $key['user_id'] = ['S' => $users[$index]];
                            }
                            $response = $this->client->deleteItem([
                                'TableName' => $table,
                                'Key' => $key,
                            ]);
                            $meta = $response->get('@metadata');
                            if($meta['statusCode'] == 200) {
                                $this->line('Successfully deleted ' . $id . ' from ' . $table);
                            } else {
                                $this->error('Failed to delete ' . $id . ' from ' . $table);
                            }
                        } catch(DynamoDbException $e) {
                            $this->error('Unable to delete ' . $id . ' from ' . $table);
                            $this->line($e->getMessage());
                        }
                    }
                }
            } break;
            case 'get': {
                if(count($tables) !== 1) { $this->error('You must provide a single table to delete item(s) from.'); return; }
                if(count($users) > 1 && count($users) != count($ids)) { $this->error('If passing User IDs, the number of Users must match the number of IDs'); return ; }
                $table = $tables[0];
                foreach($ids as $index=>$id) {
                    try {
                        $key = [
                            'id'   => ['S' => $id],
                        ];
                        if(count($users) == 1) {
                            $key['user_id'] = ['S' => $users[0]];
                        }
                        if(count($users) == count($ids)) {
                            $key['user_id'] = ['S' => $users[$index]];
                        }
                        $response = $this->client->getItem([
                            'ConsistentRead' => true,
                            'TableName' => $table,
                            'Key' => $key,
                        ]);
                        if(empty($response['Item'])) $this->error('Item does not exists, ' . $id . ' in ' . $table);
                        else var_dump($response['Item']);
                    } catch(DynamoDbException $e) {
                        $this->error('Unable to delete ' . $id . ' from ' . $table);
                        $this->line($e->getMessage());
                    }
                }
            } break;
            case 'delete-table': {
                foreach($tables as $table) {
                    if($this->confirm('Are you sure you want to delete "' . $table . '"')) {
                        try {
                            $this->client->deleteTable([
                                'TableName' => $table
                            ]);
                            $this->line($table . ' deleted.');
                        } catch(DynamoDbException $e) {
                            $this->error('Unable to delete ' . $table);
                            $this->line($e->getMessage());
                        }
                    }
                }
            } break;
            case 'create-table': {
                foreach($tables as $basic=>$table) {
                    $path = join(DIRECTORY_SEPARATOR, [base_path(), 'database', 'schemas', $basic . '.json']);
                    $this->line('Loading: ' . $path);
                    $input = json_decode(file_get_contents($path), true);
                    $env = config('app.env');
                    if($env !== 'local') {
                        $input['TableName'] = $table;
                    }
                    $error = json_last_error();
                    if($error) {
                        $this->line('ERROR: ' . $error);
                    }
                    $table = $this->client->createTable($input);
                    if($tag) {
                        $description = $table->get('TableDescription');
                        echo "ARN: "; var_dump($description['TableArn']);
                        if(!empty($description['TableArn'])) {
                            $tags = config('aws.tags', null);
                            if($tags !== null) {
                                echo "Waiting for 5s before calling TagResource ... ";
                                sleep(5); // wait, because it takes a moment for the table to become available to tag
                                echo "Done.\n";
                                $errors = 0;
                                while($errors < 3) {
                                    try {
                                        $tagged = $this->client->tagResource([
                                            'ResourceArn' => $description['TableArn'],
                                            "Tags" => $tags
                                        ]);
                                        break;
                                    } catch(Exception $e) {
                                        $this->line($e->getMessage());
                                        $errors++;
                                    }
                                }
                                echo "Tagged: "; var_dump($tagged);
                            }
                        }
                    }
                    $this->line('Created ' . $table);
                }
            } break;
            default: {
                $this->error('Unsupported DynamoDB command');
            }
        }
    }

    private function getAllTables() {
        $output = [];
        $tables = $this->client->listTables();
        foreach($tables['TableNames'] as $name=>$table) {
            $output[] = $table;
        }
        return $output;
    }
}
