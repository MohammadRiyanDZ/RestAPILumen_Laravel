<?php

namespace App\Http\Controllers;
use App\Helpers\ApiFormatter;
use App\Models\InboundStuff;
use App\Models\Stuff;
use App\Models\StuffStock;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Files;
use Illuminate\Support\Facades\App;

class InboundStuffController extends Controller
{
    public function __construct()
{
    $this->middleware('auth:api');
}
    public function index()
    {
        try {
            $data = InboundStuff::with('stuff', 'stuff.stuffStock')->get();

            return ApiFormatter::sendResponse(200, true,'Successfully get all inbound stuff data', $data);
        } catch (\Exception $err) {
            return ApiFormatter::sendResponse(400, false, $err->getMessage());
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
            'total' => 'required',
            'date' => 'required',
            'proff_file' => 'required|mimes:jpeg,png,jpg,pdf|max:2048',
            ]);

            $checkStuff = Stuff::where('id', $request->stuff_id)->first();

            if (!$checkStuff){
                return ApiFormatter::sendResponse(400, false, 'Data Stuff does not exists');
            } else {
                if ($request->hasFile('proff_file')) {
                    $proff = $request->file('proff_file');
                    $destinationPath = 'proff/';
                    $proffName = date('YmdHis') . "." . $proff->getClientOriginalExtension();
                    $proff->move($destinationPath, $proffName); // file yg sudah di get diatas dipindahkan je folder public/proff
                    //dengan bana sesuai yg di variable proffname
                }
    
                $data = InboundStuff::create([
                'stuff_id' => $request->stuff_id,
                'total' => $request->total,
                'date' => $request->date,
                'proff_file' => $proffName,
                ]);
    
                if($data) {
                    $getStuff = Stuff::where('id', $request->stuff_id)->first();
                    $getStuffStock = StuffStock::where('stuff_id', $request->stuff_id)->first();
    
                    if(!$getStuffStock) {
                        $updateStock = StuffStock::create([ 
                            'stuff_id' => $request->stuff_id,
                            'total_available' => $request->total,
                            'total_defec' => 0,
                        ]);
                    } else {
                        $updateStock = $getStuffStock->update([
                            'stuff_id' => $request->stuff_id,
                            'total_available' => $getStuffStock['total_available'] + $request->total,
                            'total_defec' => $getStuffStock['total_defec'],
                        ]);
                    }
                    if ($updateStock) {
                        $getStock = StuffStock::where('stuff_id', $request->stuff_id)->first();
                        $stuff = [
                            'stuff' => $getStuff,
                            'inboundStuff' => $data,
                            'stuffStock' => $getStock,
                        ];
    
                        return ApiFormatter::sendResponse (200, 'Successfully Created An Inbound Stuff Data', $stuff);
                    } else {
                        return ApiFormatter::sendResponse (400, false, 'Failed To Update A Stuff Stock Data'); 
                    }
                } else {
                    return ApiFormatter::sendResponse (400, false, 'Failed To Create Inbound Stuff Data');
                }
                return ApiFormatter::sendResponse(200, 'success', $data);
            }
        } catch (\Exception $err) {
            return ApiFormatter::sendResponse (400, 'bad request', $err->getMessage());
            }
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\InboundStuff  $InboundStuff
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        try{
            $data = InboundStuff::with('stuff', 'stuff.stuffStock')->find($id);

            if (is_null($data)) {
                return ApiFormatter::sendResponse(400, 'Data Inbound Stuff not found!');
            } else {
                return ApiFormatter::sendResponse(200, 'Successfully get a Inbound Stuff Data', $data);
            }
        } catch (\Exception $err) {
            return ApiFormatter::sendResponse (400, 'bad request', $err->getMessage());
            }
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\InboundStuff  $InboundStuff
     * @return \Illuminate\Http\Response
     */
    public function edit(InboundStuff $InboundStuff)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\InboundStuff  $InboundStuff
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        try {
            $this->validate($request, [
                'stuff_id' => 'required',
                'total' => 'required',
                'date' => 'required',
                'proff_file' => 'required',
            ]);

            $checkProses = InboundStuff::where('id', $id)->update([
                'stuff_id' => $request->stuff_id,
                'total' => $request->total,
                'date' => $request->date,
                'proff_file' => $request->proff_file,
            ]);

            if($checkProses) {
                $data = InboundStuff::find($id);
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
     * @param  \App\Models\InboundStuff  $InboundStuff
     * @return \Illuminate\Http\Response
     */
    public function destroy(inboundStuff $inboundStuff, $id)
    {
        try {
            $checkProses = InboundStuff::where('id', $id)->first();

            if ($checkProses) {
                $dataStock = StuffStock::where('stuff_id', $checkProses->stuff_id)->first();

                if ($dataStock->total_available < $checkProses->total) {
                    return Apiformatter::sendResponse(400, 'Bad Request', 'TOtal available kurang dari total data yang dipinjam');
                } else {
                    $stuffId = $checkProses->stuff_id;
                $totalInbound = $checkProses->total;
                $checkProses->delete();
                    

                if ($dataStock) {
                    $total_available = (int)$dataStock->total_available - (int)$totalInbound;
                    $minusTotalStock = $dataStock->update(['total_available' => $total_available]);
    
                    if ($minusTotalStock) {
                        $updateStufAndInbound = Stuff::where('id', $stuffId)->with('inboundStuffs', 'stuffStock')->first();
                        return ApiFormatter::sendResponse(200, 'success', $updateStufAndInbound);
                    }
                } else {
                    // Tangani jika data stok tidak ditemukan
                    return ApiFormatter::sendResponse(404, 'not found', 'Data stok stuff tidak ditemukan');
                }
                }
            } else {
                // Tangani jika data InboundStuff tidak ditemukan
                return ApiFormatter::sendResponse(404, 'not found', 'Data InboundStuff tidak ditemukan');
            }
        } catch (\Exception $err) {
            // Tangani kesalahan
            return ApiFormatter::sendResponse(400, 'bad request', $err->getMessage());
        }
    }

    public function trash()
    {
        try {
            $data = InboundStuff::onlyTrashed()->get();

                return ApiFormatter::sendResponse(200, 'success', $data);
        } catch (\Exception $err) {
            return ApiFormatter::sendResponse (400, 'bad request', $err->getMessage());
        }
    }

    public function restore($id)
    {
        try {
            $checkProses = InboundStuff::onlyTrashed()->where('id', $id)->restore();

            if($checkProses) {
                $data = InboundStuff::find($id);
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
            $checkProses = InboundStuff::onlyTrashed()->where('id', $id)->forceDelete();

                return ApiFormatter::sendResponse(200, 'success', 'Berhasil menghapus data secara permanen!');
        } catch (\Exception $err) {
            return ApiFormatter::sendResponse (400, 'bad request', $err->getMessage());
        }
    }

    public function addStock(Request $request, $id){
        try {
            $getStuffStock = StuffStock::find($id);

            if (!$getStuffStock) {
                return ApiFormatter::sendResponse(404, false, 'Data Stuff Stock Not Found');
            } else {
                $this->validate($request, [
                    'total_available' => 'required',
                    'total_defec' => 'required',
                ]);

                $addStock = $getStuffStock->update([
                    'total_available' => $getStuffStock['total_available'] + $request->total_available,
                    'total_defec' => $getStuffStock['total_defec'] + $request->total_defec,
                ]);

                if ($addStock) {
                    $getStockAdded = StuffStock::where('id', $id)->with('stuff')->first();

                    return ApiFormatter::sendResponse(200, true, 'Successfully Add A Stock Of Stuff Stock Data', $getStockAdded);
                }
            }
        } catch (\Exception $err) {
            return ApiFormatter::sendResponse (400, 'bad request', $err->getMessage());
            }
    }

    // private function deleteAssociatedFile(InboundStuff $inboundStuff){
    //     $publicPath = $_SERVER['DOCUMENT_ROOT'] . '/public/proff';

    //     $filePath = public_path('proff/'.$inboundStuff->proff_file);

    //     if (file_exists($filePath)) {
    //         unlink(base_path($filePath));
    //     }
    // }
}
