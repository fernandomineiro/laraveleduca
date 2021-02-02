<?php

namespace App\Http\Controllers\Admin;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Support\Facades\Validator;

use App\Voucher;

class VoucherController extends Controller {
    public function index(Request $Request) {
        $data = $Request->all();

        if ($Request->isMethod('get')){
            if (isset(request()->segments()[1])){
                $data['code'] = base64_decode(request()->segments()[1]);
            }
        }

        $data_view = array();
        $data_view['voucher'] = false;
        $data_view['code'] = (!empty($data['code'])) ? $data['code'] : '';
        if (!empty($data['code'])){
            $data_view['voucher'] = 0;

            $voucher = $this->getVoucher($data['code']);

            if ($voucher){
                $data_view['url'] = $voucher['url'];
                $data_view['voucher'] = 1;
            }
        }

        return view('voucher/valida', $data_view);
    }

    private function getVoucher($code){
        $voucher = Voucher::where('code', $code)->first();

        if (isset($voucher->id)){
            return $voucher->toArray();
        } else {
            return false;
        }
    }
}
