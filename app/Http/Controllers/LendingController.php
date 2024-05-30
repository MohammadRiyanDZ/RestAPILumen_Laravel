<?php

namespace App\Http\Controllers;

use App\Helpers\ApiFormatter;
use App\Models\Lending;
use App\Models\StuffStock;
use Illuminate\Http\Request;

class LendingController extends Controller
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
            $getLending = Lending::with('stuff', 'user')->get();

            return ApiFormatter::sendResponse(200, 'Successfully Get All Lending Data', $getLending);
        } catch (\Exception $e) {
            return ApiFormatter::sendResponse(400, false, $e->getMessage());
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
                'date_time' => 'required',
                'name' => 'required',
                'user_id' => 'required',
                'notes' => 'required',
                'total_stuff' => 'required',
            ]);

            $createLending = Lending::create([
                'stuff_id' => $request->stuff_id,
                'date_time' => $request->date_time,
                'name' => $request->name,
                'user_id' => $request->user_id,
                'notes' => $request->notes,
                'total_stuff' => $request->total_stuff,
            ]);

            $getStuffStock = StuffStock::where('stuff_id', $request->stuff_id)->first();
            $updateStock = $getStuffStock->update([
                'total_available' => $getStuffStock['total_available'] - $request->total_stuff,
            ]);

            return ApiFormatter::sendResponse(200, 'Successfully Create A Lending Data', $createLending);
        } catch (\Exception $e) {
            return ApiFormatter::sendResponse(400, false, $e->getMessage());
        }
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        try {
            $getLending = Lending::where('id', $id)->with('stuff', 'user')->first();

            if (!$getLending) {
                return ApiFormatter::sendResponse(404, false, 'Data Lending Not Found');
            } else {
                return ApiFormatter::sendResponse(200, 'Successfully Get A Lending Data', $getLending);
            }
        } catch (\Exception $e) {
            return ApiFormatter::sendResponse(400, false, $e->getMessage());
        }
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
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
        try {

            $getLending = Lending::find($id);

            if ($getLending) {
                $this->validate($request, [
                    'stuff_id' => 'required',
                    'date_time' => 'required',
                    'name' => 'required',
                    'user_id' => 'required',
                    'notes' => 'required',
                    'total_stuff' => 'required',
                ]);


                $getStuffStock = StuffStock::where('stuff_id', $request->stuff_id)->first();//get stock berdasarkan request stuff id
                $getCurrentStock = StuffStock::where('stuff_id', $getLending['stuff_id'])->first();

                if ($request->stuff_id == $getCurrentStock['stuff_id']) {
                    $updateStock = $getCurrentStock->update([
                        'total_available' => $getCurrentStock['total_available'] + $getLending['total_stuff'] - $request->total_stuff,
                    ]);//total available lama akan di jumlahkan dengan total peminjaman barang lama lalu di kurangin dengan total peminjaman yg baru
                } else {
                    $updateStock = $getCurrentStock->update([
                        'total_available' => $getCurrentStock['total_available'] + $getLending['total_stuff'],
                    ]);//total available lama di jumlahkan dengan total pinjaman yg lama
                    $updateStock = $getStuffStock->update([
                        'total_available' => $getStuffStock['total_available'] - $request['total_stuff'],
                    ]);
                }//total available baru di kurangi dengan total pinjaman baru 

                $updateLending = $getLending->update([
                    'stuff_id' => $request->stuff_id,
                    'date_time' => $request->date_time,
                    'name' => $request->name,
                    'user_id' => $request->user_id,
                    'notes' => $request->notes,
                    'total_stuff' => $request->total_stuff,
                ]);

                $getUpdatedLending = Lending::where('id', $id)->with('stuff', 'user')->first();

                return ApiFormatter::sendResponse(200, 'Successfully Update A Lending Data', $getUpdatedLending);
            }
        } catch (\Exception $e) {
            return ApiFormatter::sendResponse(400, false, $e->getMessage());
        }
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        try {

            $getLending = Lending::find($id);

            if (!$getLending) {
                return ApiFormatter::sendResponse(404, false, 'Data Lending Not Found');
            } else {
                if ($getLending -> restoration){
                    return ApiFormatter::sendResponse(404, false, 'Data Lending Sudah Memiliki Restoration');
                } else {
                    $addStock = StuffStock::where('stuff_id', $getLending['stuff_id'])->first();
                    $updateStock = $addStock->update([
                        'total_available' => $addStock['total_available'] + $getLending['total_stuff'],
                    ]);
    
                    $deleteLending = $getLending->delete();
    
                    if ($deleteLending && $updateStock) {
                        return ApiFormatter::sendResponse(200, true, 'Successfully Delete A Lending Data');
                    }
                }
               
            }
        } catch (\Exception $e) {   
            return ApiFormatter::sendResponse(400, false, $e->getMessage());
        }
    }

    public function recycleBin()
    {
        try {

            $lendingDeleted = Lending::onlyTrashed()->get();

            if (!$lendingDeleted) {
                return ApiFormatter::sendResponse(404, false, 'Deleted Data Lending Doesnt Exists');
            } else {
                return ApiFormatter::sendResponse(200, 'Successfully Get Delete All Lending Data', $lendingDeleted);
            }
        } catch (\Exception $e) {
            return ApiFormatter::sendResponse(400, false, $e->getMessage());
        }
    }

    public function restore($id)
    {
        try {

            $getLending = Lending::onlyTrashed()->where('id', $id);

            if (!$getLending) {
                return ApiFormatter::sendResponse(404, false, 'Restored Data Lending Doesnt Exists');
            } else {
                $restoreLending = $getLending->restore();

                if ($restoreLending) {
                    $getRestore = Lending::find($id);
                    $addStock = StuffStock::where('stuff_id', $getRestore['stuff_id'])->first();
                    $updateStock = $addStock->update([
                        'total_available' => $addStock['total_available'] - $getRestore['total_stuff'],
                    ]);

                    return ApiFormatter::sendResponse(200, 'Successfully Restore A Deleted Lending Data', $getRestore);
                }
            }
        } catch (\Exception $e) {
            return ApiFormatter::sendResponse(400, false, $e->getMessage());
        }
    }

    public function forceDestroy($id)
    {
        try {

            $getLending = Lending::onlyTrashed()->where('id', $id);

            if (!$getLending) {
                return ApiFormatter::sendResponse(404, false, 'Data Lending for Permanent Delete Doesnt Exists');
            } else {
                $forceStuff = $getLending->forceDelete();

                if ($forceStuff) {
                    return ApiFormatter::sendResponse(200, 'Successfully Permanent Delete A Lending Data');
                }
            }
        } catch (\Exception $e) {
            return ApiFormatter::sendResponse(400, false, $e->getMessage());
        }
    }
}