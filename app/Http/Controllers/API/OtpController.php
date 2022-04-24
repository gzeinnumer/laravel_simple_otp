<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Http\Resources\OtpResources;
use App\Models\API\EmployeeModel;
use App\Models\API\OtpModel;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use stdClass;

class OtpController extends BaseController
{
    public function getOtp(Request $r)
    {
        try {
            $email = $r->email;

            $input = array(
                'email' => 'required|string|min:1'
            );

            $validator = Validator::make($r->all(), $input);

            if ($validator->fails()) {
                $apiResponse = $this->getApiResponse(0);
                $apiResponse->message = $validator->getMessageBag();
                return $this->responseFailed($apiResponse);
            }

            $checkData = EmployeeModel::select()->where(["email" => $email])->first();

            if ($checkData == null) {
                $apiResponse = $this->getApiResponse(0);
                $apiResponse->message = "account not found";
                return $this->responseFailed($apiResponse);
            }

            DB::beginTransaction();

            $otp = substr(str_shuffle("0123456789"), 0, 4);

            $expiredIn = date('Y-m-d H:i:s');
            $expiredIn = date("Y-m-d H:i:s", strtotime("+10 minutes", strtotime($expiredIn)));

            $otpModel = new OtpModel();
            $otpModel->id_employee = $checkData->id;
            $otpModel->kode = $otp;
            $otpModel->expired_in = $expiredIn;
            $otpModel->flag_active = "1";
            $otpModel->save();

            DB::commit();

            return $this->toObject(new OtpResources($otpModel), 1, 0);
        } catch (\Throwable $th) {
            DB::rollBack();
            return $this->responseError($th);
        }
    }

    public function validateOtp(Request $r)
    {
        try {
            $email = $r->email;
            $kode = $r->kode;

            $input = array(
                'email' => 'required|string|min:1',
                'kode' => 'required|string|min:1',
            );

            $validator = Validator::make($r->all(), $input);

            if ($validator->fails()) {
                $apiResponse = $this->getApiResponse(0);
                $apiResponse->message = $validator->getMessageBag();
                return $this->responseFailed($apiResponse);
            }

            $checkData = EmployeeModel::select()->where(["email" => $email])->first();

            if ($checkData == null) {
                $apiResponse = $this->getApiResponse(0);
                $apiResponse->message = "account not found";
                return $this->responseFailed($apiResponse);
            }

            DB::beginTransaction();

            $checkOtp = OtpModel::where(["id_employee" => $checkData->id])->orderBy('id', 'desc')->first();

            if ($checkOtp == null) {
                $apiResponse = $this->getApiResponse(0);
                return $this->responseFailed($apiResponse);
            }
            if ($checkOtp->kode != $kode) {
                $apiResponse = $this->getApiResponse(0);
                return $this->responseFailed($apiResponse);
            }

            $result = $checkOtp->flag_active;

            $checkOtp->flag_active = 0;
            $checkOtp->save();

            DB::commit();

            if ($result == 1) {
                $apiResponse = $this->getApiResponse(1);
                return $this->responseSuccess($apiResponse);
            } else {
                $apiResponse = $this->getApiResponse(0);
                return $this->responseFailed($apiResponse);
            }
        } catch (\Throwable $th) {
            DB::rollBack();
            return $this->responseError($th);
        }
    }
}
