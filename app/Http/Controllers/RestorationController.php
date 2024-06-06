<?php

namespace App\Http\Controllers;
use App\Helpers\ApiFormatter;
use App\Models\Restoration;
use App\Models\Lending;
use App\Models\StuffStock;
use Illuminate\Http\Request;
use LDAP\Result;

class RestorationController extends Controller
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
            $data = Restoration::all()->toArray();

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
                'user_id' => 'required',
                'lending_id' => 'required',
                'total_good_stuff' => 'required',
                'total_defec_stuff' => 'required',
                'date_time' => 'required',
            ]);

            $getLending = Lending::where('id', $request->lending_id)->first(); // get data peminjaman yang sesuai dnegan pengembaliannya

            $totalStuff = $request->total_good_stuff + $request->total_defec_stuff; // variabel penampug jumalah barang yang akan dikembalikan

            if ($getLending['total_stuff'] != $totalStuff) { // pengecekan apakah jumlah barang yang dipinjam jumlahnya sama atau tidak 
                return ApiFormatter::sendResponse(400, false, 'The amount of items returned does not match the amount borrowed');
            } else {
                $getStuffStock = stuffStock::where('stuff_id', $getLending['stuff_id'])->first(); // get data stuff yang barangnya sedang dipinjam

                $createRestoration = Restoration::create([
                    'user_id' => $request->user_id,
                    'lending_id' => $request->lending_id,
                    'total_good_stuff' => $request->total_good_stuff,
                    'total_defec_stuff' => $request->total_defec_stuff,
                    'date_time' => $request->date_time,
                ]);

                $updateStock = $getStuffStock->update([
                    'total_available' => $getStuffStock['total_available'] + $request->total_good_stuff, 
                    'total_defec' => $getStuffStock['total_defec'] + $request->total_defec_stuff,
                ]); // update jumlah barang yang tersedia yang ditambahkan dengan jumlah barang bagus yang dikembalikan dan update jumlah barang yang rusak ditambah dengan jumlah barang rusak yang dikembalikan 

                if ($createRestoration && $updateStock) {
                    return ApiFormatter::sendResponse(200, 'Successfully Create A Restoration Data', $createRestoration);
                }
            } 
        } catch (\Exception $e) {
            return ApiFormatter::sendResponse(400, false, $e->getMessage());
        }
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\Restoration  $restoration
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        try{
            $data = Restoration::where('id', $id)->first();

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
     * @param  \App\Models\Restoration  $restoration
     * @return \Illuminate\Http\Response
     */
    public function edit(Restoration $restoration)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\Restoration  $restoration
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        try {
            $this->validate($request, [
                'user_id' => 'required',
                'lending_id' => 'required',
                'date_time' => 'required',
                'total_good_stuff' => 'required',
                'total_defec_stuff' => 'required',
            ]);

            $checkProses = Restoration::where('id', $id)->update([
                'user_id' => $request->user_id,
                'lending_id' => $request->lending_id,
                'date_time' => $request->date_time,
                'total_good_stuff' => $request->total_good_stuff,
                'total_defec_stuff' => $request->total_defec_stuff,
            ]);

            if($checkProses) {
                $data = Restoration::find($id);
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
     * @param  \App\Models\Restoration  $restoration
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        try {
            $checkProses = Restoration::where('id', $id)->delete();

                return ApiFormatter::sendResponse(200, 'success', 'Data berhasil dihapus!');
        } catch (\Exception $err) {
            return ApiFormatter::sendResponse (400, 'bad request', $err->getMessage());
        }
    }

    public function trash()
    {
        try {
            $data = Restoration::onlyTrashed()->get();

                return ApiFormatter::sendResponse(200, 'success', $data);
        } catch (\Exception $err) {
            return ApiFormatter::sendResponse (400, 'bad request', $err->getMessage());
        }
    }

    public function restore($id)
    {
        try {
            $checkProses = Restoration::onlyTrashed()->where('id', $id)->restore();

            if($checkProses) {
                $data = Restoration::find($id);
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
            $checkProses = Restoration::onlyTrashed()->where('id', $id)->forceDelete();

                return ApiFormatter::sendResponse(200, 'success', 'Berhasil menghapus data secara permanen!');
        } catch (\Exception $err) {
            return ApiFormatter::sendResponse (400, 'bad request', $err->getMessage());
        }
    }
}
