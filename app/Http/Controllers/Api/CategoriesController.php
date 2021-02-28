<?php

namespace App\Http\Controllers\Api;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

use App\Models\Category;
use App\Http\Controllers\Controller;

use League\Csv\Reader;
use League\Csv\Statement;

class CategoriesController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        //
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        //
    }

    /**
     *
     *
     * @return \Illuminate\Http\Response
     */
    public function import(Request $request)
    {
        Log::info('import');
        try {
            $csv = Reader::createFromPath($request->file, 'r');
            $csv->setHeaderOffset(0); //set the CSV header offset

            $stmt = Statement::create();

            $records = $stmt->process($csv);
            foreach ($records as $record) {
                $category = Category::find($record['id']);
                // memo: find or createの方がいいかも
                if (is_null($category)) {
                    Category::create($record);
                } else {
                    $category->fill($record)->save();
                }

            }
            return response()->json([
                'message' => '成功',
            ], 200, [], JSON_UNESCAPED_UNICODE);
        } catch (Exception $e) {
            Log::info('error');
            Log::error($e);
            return response()->json([
                'message' => 'エラーが発生しました',
            ], 500, [], JSON_UNESCAPED_UNICODE);
        }
    }

}
