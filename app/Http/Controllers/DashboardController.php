<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\DB;

use Illuminate\Http\Request;

class DashboardController extends Controller
{
    //
    public function index()
    {

        $data = ['userInfo' => DB::table('tbl_user')->where('employee_no', session('loggedInUser'))->first()];

        return view('dashboard.index', $data);
    }


    public function customer_list()
    {
        // if(request()->ajax())
        // {
        //     echo 'Ajax';
        // }else{
        //     echo 'false';
        // }

        $dataCustomer = DB::table('tbl_customer')->get();

        return response()->json([
            'status' => 200,
            'data' => $dataCustomer
        ]);
    }

    public function model_list(Request $request)
    {

        $customerId = $request->post('values');

        if ($customerId) {
            $dataModel = DB::table('tbl_model')->where('customer_id', $customerId)->get();

            if ($dataModel) {
                return response()->json([
                    'status' => 200,
                    'data' => $dataModel
                ]);
            }
        }
    }

    public function ng_station(Request $request)
    {

        $customerModel = $request->post('values');

        $dataNGStation = DB::table('tbl_stationRoute')
            ->join('tbl_vlookup', 'tbl_vlookup.id', '=', 'tbl_stationRoute.station_id')
            ->where('tbl_stationRoute.model_id', $customerModel)
            ->get();

        return response()->json([
            'status' => 200,
            'data' => $dataNGStation
        ]);
    }

    public function simpan_repair(Request $request)
    {

        $repairCat              = $request->post('repairCat');
        $serialNum              = $request->post('serialNum');
        $selectCustomer         = $request->post('selectCustomer');
        $selectModelCustomer    = $request->post('selectModelCustomer');
        $rejectCategory         = $request->post('rejectCategory');
        $selectNGStation        = $request->post('selectNGStation');
        $ngSymptom              = $request->post('ngSymptom');


        DB::table('tbl_datarepairanalyst')->get();



        return response()->json([
            'status' => 200,
            'data' => $repairCat
        ]);
    }
}
