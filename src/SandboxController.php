<?php

namespace Jecovier\SandboxSchema;

use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;
use App\Http\Controllers\Controller;

class SandboxController extends Controller
{
    private $schema;

    public function __construct(Request $request)
    {
        if (!$request->schema) return;

        $this->schema = $this->getSchema($request);
        if ($this->schema->relation_table && !$this->isValidRelation($this->schema)) {
            throw new \Exception('no record ' . $this->schema->relation_id . ' in ' . $this->schema->relation_table, 404);
        }

        if ($request->method() !== 'POST' && $request->method() !== 'PUT')
            return;

        $table_fields = $this->getTableFields($this->schema->table, $request->except('schema'));
        $this->addNewFields(
            $this->schema->table,
            $table_fields,
            $request->except('schema')
        );
    }

    private function getSchema($request)
    {
        $id = null;
        $params = explode('/', $request->schema);
        $relation_id = null;
        $relation_table = null;
        $relation_field = null;

        if (count($params) % 2 == 0) {
            $id = array_pop($params);
            $table = array_pop($params);
        } else {
            $table = array_pop($params);
        }

        if (count($params)) {
            $relation_id = array_pop($params);
            $relation_table = array_pop($params);
            $relation_field = Str::singular($relation_table) . '_id';
            $request->merge([
                $relation_field => $relation_id
            ]);
        }

        return (object) [
            'id' => $id,
            'table' => $table,
            'relation_id' => $relation_id,
            'relation_field' => $relation_field,
            'relation_table' => $relation_table
        ];
    }

    private function isValidRelation(object $schema)
    {
        if (!$schema->relation_id && !$schema->relation_table)
            return true;
        try {
            $result = DB::table($schema->relation_table)->where('id', $schema->relation_id)->first();
            return ($result) ? true : false;
        } catch (\Exception $e) {
            return false;
        }
    }

    private function addNewFields(String $schema, array $table_fields, array $request_fields)
    {
        $input_fields = array_keys($request_fields);
        $current_fields = array_map(function ($field) {
            return $field->Field;
        }, $table_fields);
        $new_fields = array_diff($input_fields, $current_fields);

        if (!count($new_fields))
            return;

        $this->updateTable($schema, $new_fields, $request_fields);
    }

    private function getTableFields(String $schema, array $fields)
    {
        try {
            $fields = DB::select('DESC ' . $schema);
        } catch (\Exception $e) {
            $this->createTable($schema, $fields);
            $fields = DB::select('DESC ' . $schema);
        }
        return $fields;
    }

    private function createTable(String $schema, array $fields)
    {
        Schema::create($schema, function (Blueprint $table) use ($fields) {
            $table->bigIncrements('id');
            foreach ($fields as $field => $value) {
                $type = $this->getTypeField($value, $field);
                $table->$type($field);
            }
            $table->timestamps();

            if ($this->schema->relation_table) {
                $table->foreign($this->schema->relation_field)->references('id')->on($this->schema->relation_table);
            }
        });
    }

    private function updateTable(String $schema, array $new_fields, array $request_fields)
    {
        $fields = array_filter($request_fields, function ($field) use ($new_fields) {
            return in_array($field, $new_fields);
        }, ARRAY_FILTER_USE_KEY);

        Schema::table($schema, function (Blueprint $table) use ($fields) {
            foreach ($fields as $field => $value) {
                $type = $this->getTypeField($value, $field);
                $table->$type($field);
            }
        });
    }

    private function getTypeField(string $value, string $field)
    {
        if (is_numeric($value) && substr($field, -3) === '_id')
            return 'unsignedbiginteger';
        if (is_numeric($value))
            return 'unsignedinteger';
        return gettype($value);
    }

    /**
     *  CRUD OPERATIONS
     * ------------------------------------
     * Index, Store, Show, update, delete
     */

    public function index(String $schema)
    {
        if ($this->schema->id) {
            $response = $this->show($schema);
            return $this->responseCors($response);
        }

        if (!$this->schema->relation_table) {
            $response = DB::table($this->schema->table)->paginate(15);
            return $this->responseCors($response);
        }

        $response = DB::table($this->schema->table)->where($this->schema->relation_table . '_id', $this->schema->relation_id)->paginate(15);
        return $this->responseCors($response);
    }

    public function store(String $schema, Request $request)
    {
        $data = $request->all();
        $data['created_at'] = Carbon::now()->toDateTimeString();
        $response = DB::table($this->schema->table)->insertGetId($data);
        return $this->responseCors($response);
    }

    public function show(String $schema)
    {
        $record = (array) DB::table($this->schema->table)->where('id', $this->schema->id)->first();

        if (empty($record))
            throw new \Exception("Record not found", 404);
        return $this->responseCors($record);
    }

    public function update(String $schema, Request $request)
    {
        if (!$this->schema->id) return;
        $data = $request->all();
        $data['updated_at'] = Carbon::now()->toDateTimeString();
        $response = DB::table($this->schema->table)->where('id', $this->schema->id)->update($data);

        if (!$response)
            throw new \Exception("Record can't be updated", 500);

        return $this->responseCors($response);
    }

    public function destroy(String $schema)
    {
        if (!$this->schema->id) return;
        $response = DB::table($this->schema->table)->where('id', $this->schema->id)->delete();

        if (!$response)
            throw new \Exception("Record can't be deleted", 500);

        return $this->responseCors($response);
    }

    private function responseCors($data)
    {
        if (!config('sandboxschema.cors_enabled')) {
            return response($data);
        }

        response($data)
            ->header("Access-Control-Allow-Origin", config('sandboxschema.allow_origins'))
            ->header("Access-Control-Allow-Methods", "GET, POST, PUT, DELETE")
            ->header("Access-Control-Allow-Headers", "X-Requested-With, Content-Type, X-Token-Auth, Authorization");
    }
}
