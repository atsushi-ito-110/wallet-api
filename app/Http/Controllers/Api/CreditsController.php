<?php

namespace App\Http\Controllers\Api;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

use App\Models\Credit;
use App\Models\CreditDetail;
use App\Models\Shop;
use App\Http\Controllers\Controller;

use League\Csv\Reader;
use League\Csv\CharsetConverter;
// use League\Csv\Statement;

class CreditsController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $credits = Credit::orderBy('billing_month', 'desc')->get();
        return response()->json([
            'message' => 'ok',
            'credits' => $credits
        ], 200, [], JSON_UNESCAPED_UNICODE);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $last_month = Credit::orderBy('billing_month', 'desc')->first();
        $billing_month = null;
        if (is_null($last_month)) {
            $billing_month = date('Y-m-01');
        } else {
            $billing_month = $last_month->billing_month;
        }
        $billing_month = date('Y-m-01', strtotime(date($billing_month).'+1 month'));
        if (date('Y-m-d') < $billing_month) {
            return response()->json([
                'message' => '翌月以降のデータは作成できません。'
            ], 500, [], JSON_UNESCAPED_UNICODE);
        }
        $credit = Credit::create([
            'billing_month' => $billing_month
        ]);
        return response()->json([
            'message' => 'ok',
            'credit' => $credit
        ], 200, [], JSON_UNESCAPED_UNICODE);
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($year_month)
    {
        Log::info($year_month);
        $billing_month = date('Y-m-d', strtotime("{$year_month}-01"));
        Log::info($billing_month);
        $credit = Credit::where('billing_month', $billing_month)
            ->with(['credit_details' => function ($query) {
                $query->orderBy('billing_date', 'desc');
            }])
            ->first();
        // \Log::info($contents->toSql());
        // Log::info($credit);
        // $credit->with('credit_details')->get();
        // Log::info($credit);
        return response()->json([
            'message' => 'ok',
            'credit' => $credit
        ], 200, [], JSON_UNESCAPED_UNICODE);
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
        Log::Info($request);
        try {

            $billing_month = date('Y-m-d', strtotime("{$request->billing_month}01"));

            $credit = Credit::firstOrCreate([
                'billing_month' => $billing_month
            ], [
                'billing_month' => $billing_month
            ]);

            $csv = Reader::createFromPath($request->file, 'r');
            $csv->setHeaderOffset(0); //set the CSV header offset
            $encoder = (new CharsetConverter())->inputEncoding('SJIS-win');
            $records = $encoder->convert($csv);

            // refactor: ごちゃってる
            foreach ($records as $record) {
                $type_column = '種別（ショッピング、キャッシング、その他）';
                $except_types = ['お支払合計額', 'キャッシング'];
                if (in_array($record[$type_column], $except_types, true)) {
                    continue;
                }
                $place_column = 'ご利用場所';
                $except_places = ['－'];
                if (in_array($record[$place_column], $except_places, true)) {
                    continue;
                }

                $record['ご利用場所'] = preg_replace('/[　]+$/u', '', $record['ご利用場所']);
                // Log::info($record);
                $shop_id = Shop::where('name', $record['ご利用場所'])->first()->id ?? null;
                if (mb_strpos($record['ご利用場所'], 'ソフトバンクＭ') === 0) {
                    $shop_id = Shop::where('name', 'ソフトバンクＭ')->first()->id ?? null;
                }
                $date_ja_strings = ['年', '月', '日'];
                $billing_date = date('Y-m-d', strtotime(str_replace($date_ja_strings, '', $record['ご利用年月日'])));
                $credit_detail_params = [
                    'credit_id' => $credit->id,
                    'shop_id' => $shop_id,
                    'name' => $record['ご利用場所'],
                    'billing_date' => $billing_date,
                    'amount' => intval($record['ご利用金額']),
                ];
                CreditDetail::updateOrCreate(
                    [
                        'credit_id' => $credit->id,
                        // 'shop_id' => $shop_id,
                        'name' => $record['ご利用場所'],
                        'billing_date' => $billing_date,
                        'amount' => intval($record['ご利用金額']),
                    ],
                    [
                        'credit_id' => $credit->id,
                        'shop_id' => $shop_id,
                        'name' => $record['ご利用場所'],
                        'billing_date' => $billing_date,
                        'amount' => intval($record['ご利用金額']),
                    ],
                );
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
