<?php

namespace App\Http\Controllers;
use App\Helpers\ApiFormatter;
use App\Models\Stuff;
use Illuminate\Http\Request;

class StuffController extends Controller
{
    public function __construct()
{
    $this->middleware('auth:api');
}
    public function index()
    {
        try {
            $data = Stuff::with('stuffStock','inboundStuffs', 'lendings')->get();
            //mendapatkan keseluruhan data dari tabel stuffs

            return ApiFormatter::sendResponse(200, 'success', $data);
        } catch (\Exception $err) {
            return ApiFormatter::sendResponse(400, 'bad request', $err->getMessage());
        }
    }

    public function create()
    {
        //
    }

    public function store (Request $request) {
        try {
            $this->validate($request, [
            'name' => 'required',
            'category' => 'required',
            ]);

            $data = Stuff::create([
            'name' => $request->name,
            'category' => $request->category,
            ]);

            return ApiFormatter::sendResponse(200, 'success', $data);
        } catch (\Exception $err) {
            return ApiFormatter::sendResponse (400, 'bad request', $err->getMessage());
            }

    }
    
    public function show($id) {
        try{
            $data = Stuff::where('id', $id)->with('stuffStock', 'inboundStuffs', 'lendings')->first();

            if (!$data) {
                return ApiFormatter::sendResponse(404, false, 'Data not found!');
            } else {
                return ApiFormatter::sendResponse(200, true, 'Successfully Get A Stuff Data', $data);
            }
        } catch (\Exception $err) {
            return ApiFormatter::sendResponse (400, false, $err->getMessage());
            }
    }

    public function edit(Stuff $stuff)
    {
        //
    }

    public function update(Request $request, $id)
    {
        try {
            $this->validate($request, [
                'name' => 'required',
                'category' => 'required'
            ]);

            $checkProses = Stuff::where('id', $id)->update([
                'name' => $request->name,
                'category' => $request->category
            ]);

            if($checkProses) {
                $data = Stuff::find($id);
                return ApiFormatter::sendResponse(200, 'success', $data);
            } else {
                return ApiFormatter::sendResponse(400, 'bad request', 'Gagal mengubah data! ');
            }
        } catch (\Exception $err) {
            return ApiFormatter::sendResponse (400, 'bad request', $err->getMessage());
            }
    }

    public function destroy($id)
    {
        try {
            $checkProses = Stuff::where('id', $id)->first();

            if (!$checkProses->inboundStuffs || !$checkProses->stuffStock || !$checkProses->lendings) {
                return ApiFormatter::sendResponse(400, 'bad request', 'Data Stuff gagal Dihapus, karena sudah terdapat Data Inbound');
            } else {
                $checkProses->delete();
                return ApiFormatter::sendResponse(200,'success', 'Data Stuff Berhasil Dihapus');
            }
        } catch (\Exception $err) {
            return ApiFormatter::sendResponse (400, 'bad request', $err->getMessage());
        }
    }

    public function trash()
    {
        try {
            $data = Stuff::onlyTrashed()->get();

                return ApiFormatter::sendResponse(200, 'success', $data);
        } catch (\Exception $err) {
            return ApiFormatter::sendResponse (400, 'bad request', $err->getMessage());
        }
    }

    public function restore($id)
    {
        try {
            $checkProses = Stuff::onlyTrashed()->where('id', $id)->restore();

            if($checkProses) {
                $data = Stuff::find($id);
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
            $checkProses = Stuff::onlyTrashed()->where('id', $id)->forceDelete();

                return ApiFormatter::sendResponse(200, 'success', 'Berhasil menghapus data secara permanen!');
        } catch (\Exception $err) {
            return ApiFormatter::sendResponse (400, 'bad request', $err->getMessage());
        }
    }
}
