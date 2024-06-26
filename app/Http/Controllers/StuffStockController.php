<?php

namespace App\Http\Controllers;
use App\Helpers\ApiFormatter;
use App\Models\StuffStock;
use Illuminate\Http\Request;

class StuffStockController extends Controller
{
    public function __construct()
{
    $this->middleware('auth:api');
}
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        try {
            $data = StuffStock::all()->toArray();

            return ApiFormatter::sendResponse(200, 'success', $data);
        } catch (\Exception $err) {
            return ApiFormatter::sendResponse(400, 'bad request', $err->getMessage());
        }
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
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
        try {
            $this->validate($request, [
            'stuff_id' => 'required',
            'total_available' => 'required',
            'total_defec' => 'required',
            ]);

            $data = StuffStock::create([
            'stuff_id' => $request->stuff_id,
            'total_available' => $request->total_available,
            'total_defec' => $request->total_defec,
            ]);

            return ApiFormatter::sendResponse(200, 'success', $data);
        } catch (\Exception $err) {
            return ApiFormatter::sendResponse (400, 'bad request', $err->getMessage());
            }
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\StuffStock  $stuffStock
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        try{
            $data = StuffStock::where('id', $id)->first();

            if (is_null($data)) {
                return ApiFormatter::sendResponse(400, 'bad request', 'Data not found!');
            } else {
                return ApiFormatter::sendResponse(200, 'success', $data);
            }
        } catch (\Exception $err) {
            return ApiFormatter::sendResponse (400, 'bad request', $err->getMessage());
            }
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\StuffStock  $stuffStock
     * @return \Illuminate\Http\Response
     */
    public function edit(StuffStock $stuffStock)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\StuffStock  $stuffStock
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        try {
            $this->validate($request, [
                'stuff_id' => 'required',
                'total_available' => 'required',
                'total_defec' => 'required',
            ]);

            $checkProses = StuffStock::where('id', $id)->update([
                'stuff_id' => $request->stuff_id,
                'total_available' => $request->total_available,
                'total_defec' => $request->total_defec,
            ]);

            if($checkProses) {
                $data = StuffStock::find($id);
                return ApiFormatter::sendResponse(200, 'success', $data);
            } else {
                return ApiFormatter::sendResponse(400, 'bad request', 'Gagal mengubah data! ');
            }
        } catch (\Exception $err) {
            return ApiFormatter::sendResponse (400, 'bad request', $err->getMessage());
            }
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\StuffStock  $stuffStock
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        try {
            $checkProses = StuffStock::where('id', $id)->delete();

                return ApiFormatter::sendResponse(200, 'success', 'Data berhasil dihapus!');
        } catch (\Exception $err) {
            return ApiFormatter::sendResponse (400, 'bad request', $err->getMessage());
        }
    }

    public function trash()
    {
        try {
            $data = StuffStock::onlyTrashed()->get();

                return ApiFormatter::sendResponse(200, 'success', $data);
        } catch (\Exception $err) {
            return ApiFormatter::sendResponse (400, 'bad request', $err->getMessage());
        }
    }

    public function restore($id)
    {
        try {
            $checkProses = StuffStock::onlyTrashed()->where('id', $id)->restore();

            if($checkProses) {
                $data = StuffStock::find($id);
                return ApiFormatter::sendResponse(200, 'success', $data);
            } else {
                return ApiFormatter::sendResponse(400, 'bad request', 'Gagal mengembalikan data! ');
            }
        } catch (\Exception $err) {
            return ApiFormatter::sendResponse (400, 'bad request', $err->getMessage());
            }
    }

    public function deletePermanent($id)
    {
        try {
            $checkProses = StuffStock::onlyTrashed()->where('id', $id)->forceDelete();

                return ApiFormatter::sendResponse(200, 'success', 'Berhasil menghapus data secara permanen!');
        } catch (\Exception $err) {
            return ApiFormatter::sendResponse (400, 'bad request', $err->getMessage());
        }
    }

    public function substock(Request $request, $id){
        try {
            $getStuffStock = StuffStock::find($id);

            if (!$getStuffStock) {
                return ApiFormatter::sendResponse(404, false, 'Data Stuff Stock Not Found');
            } else {
                $this->validate($request, [
                    'total_available' => 'required',
                    'total_defec' => 'required',
                ]);

                $isStockAvailable = $getStuffStock['total_available'] - $request->total_available;
                $isStockDefec = $getStuffStock['total_defec'] - $request->total_defec;

                if ($isStockAvailable < 0 || $isStockDefec < 0) {
                    return ApiFormatter::sendResponse(200, true, 'A Substraction Stock Cant Less Than a Stock Stored');
                } else {
                    $subStock = $getStuffStock->update()([
                        'total_available' => $isStockAvailable,
                        'total_defec' => $isStockDefec,
                    ]);

                    if ($subStock){
                        $getStockSub = StuffStock::where('id', $id)->with('stuff')->first();

                        return ApiFormatter::sendResponse (200, true, 'Successfully Sub A Stock Of Stuff Stock Data',
                        $getStockSub);
                    }
                }
            }
        } catch (\Exception $err) {
            return ApiFormatter::sendResponse (400, 'bad request', $err->getMessage());
            }
    }
}
