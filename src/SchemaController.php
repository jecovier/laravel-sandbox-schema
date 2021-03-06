<?php

namespace Jecovier\SandboxSchema;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class SchemaController extends Controller
{
    public function index()
    {
        $tables = DB::select('SHOW TABLES');
        $list = array_map(function ($table) {
            return $table->Tables_in_promofun;
        }, $tables);
        return response('show tables', $list);
    }

    public function show(string $table)
    {
        $fields = DB::select('DESC ' . $table);
        return response($table, $fields);
    }

    public function store(Request $request)
    {
        Schema::create($request->table, function (Blueprint $table) use ($request) {
            $table->bigIncrements('id');
            foreach ($request->fields as $field => $type) {
                $table->$type($field);
            }
            $table->timestamps();
        });

        return response('table created');
    }

    public function update(string $table, Request $request)
    {
        if ($request->table) {
            Schema::rename($table, $request->table);
            $table = $request->table;
        }

        if (!$request->fields)
            return response('table updated');

        Schema::table($table, function (Blueprint $table) use ($request) {
            foreach ($request->fields as $field => $type) {
                $table->$type($field);
            }
        });

        return response('table updated');
    }

    public function destroy(string $table)
    {
        Schema::dropIfExists($table);
        return response('table deleted');
    }
}
