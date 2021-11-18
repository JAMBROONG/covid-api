<?php

namespace App\Http\Controllers;

use App\Models\Patient;
use App\Models\PatientStatuses;
use App\Models\Status;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Symfony\Component\HttpFoundation\Response;

class PatientController extends Controller
{

    /**
     * Function untuk memformat tampilan data pasien.
     */
    private function formatPatient($patient)
    {
        return [
            "id" => $patient->patient->id,
            "name" => ucwords($patient->patient->name),
            "phone" => ucwords($patient->patient->phone),
            "address" => ucwords($patient->patient->address),
            "status" => ["id" => $patient->status->id,
                         "name" => ucwords($patient->status->name)
                        ],
            "date_in" => $patient->date_in,
            "date_out" => $patient->date_out,
            "created_at" => $patient->created_at,
            "updated_at" => $patient->updated_at
        ];
    }


    /**
     * Function untuk mencari id status.
     * parameter (id status atau nama status)
     */
    private function getStatusId($status)
    {
        
        if (is_numeric($status) == TRUE) {
            $statusId = Status::find($status);
            if ($statusId == NULL) {
                return FALSE;
            }else {
                return $statusId->id;
            }
            
        }else {
            $statuses = Status::where("name", "=", strtolower($status))->first();
            
            if ($statuses == NULL) {
                return FALSE;
            }else {
                $statusId = $statuses["id"];
                return $statusId;
            }
        }
        
    }

    /**
     * Function untuk menampilkan seluruh data pasien.
     */
    function index()
    {
        $patients = PatientStatuses::all();

        if ($patients->isNotEmpty()) {
            $patients = $patients->map(function($patient){
                return $this->formatPatient($patient);
            });     

            $response = [
                "message" => "Get all patients",
                "data" => $patients
            ];

            return response()->json($response, Response::HTTP_OK);
            
        } else {
            $response = [
                "message" => "Data not found!"
            ];
            return response()->json($response, Response::HTTP_NOT_FOUND);
        }
    }

    /**
     * Function untuk menampilkan data pasien berdasarkan parameter (id pasien).
     */
    function show($id)
    {
        if (is_numeric($id) == TRUE) {
            $patients = PatientStatuses::where("patient_id", "=", $id)->get();

            if ($patients->isNotEmpty()) {
                $patients = $patients->map(function($patient){
                    return $this->formatPatient($patient);
                });     

                $response = [
                    "message" => "Get patient's id $id",
                    "data" => $patients
                ];

                return response()->json($response, Response::HTTP_OK);
                
            } else {
                $response = [
                    "message" => "Data not found!"
                ];
                return response()->json($response, Response::HTTP_NOT_FOUND);
            }
        }else {
            $response = [
                "message" => "Id must be numeric!"
            ];
            return response()->json($response, Response::HTTP_UNPROCESSABLE_ENTITY);
        }
        
    }


    /**
     * Function untuk membuat data pasien baru.
     */
    function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            "name" => ["required"],
            "phone" => ["required", "numeric"],
            "address" => ["required"],
            "status" => ["required"],
            "date_in" => ["required"]
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors(), Response::HTTP_UNPROCESSABLE_ENTITY);
        } else {
            $name = $request->name;
            $phone = $request->phone;
            $address = $request->address;
            $status = $this->getStatusId($request->status);
            $date_in = $request->date_in;
            $date_out = $request->date_out;

            if ($status == FALSE) {
                $response = [
                    "message" => "Status id or status name not found!",
                ];
                
    
                return response()->json($response, Response::HTTP_NOT_FOUND);
            }else {
                $patientCreate = Patient::create([
                    "name" => $name,
                    "phone" => $phone,
                    "address" => $address
                ]);
    
                $getPatient = Patient::where("created_at", "=", $patientCreate["created_at"])->first();
                $patientId = $getPatient["id"];   
    
                $patientStatus = PatientStatuses::create([
                    "patient_id" => $patientId,
                    "status_id" => $status,
                    "date_in" => $date_in,
                    "date_out" => $date_out
    
                ]);
    
                $patientStatus = $this->formatPatient($patientStatus);
    
                $response = [
                    "message" => "New patient created successfully!",
                    "data" => $patientStatus
                ];
                
    
                return response()->json($response, Response::HTTP_CREATED);
            }
        }
    
    }

    /**
     * Function untuk mengupdate data pasien berdasarkan parameter (id pasien).
     */
    function update(Request $request, $id)
    {
        $validator = Validator::make($request->all(), [
            "phone" => ["numeric"]
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors(), Response::HTTP_UNPROCESSABLE_ENTITY);
        }else {
            $name = $request->name;
            $phone = $request->phone;
            $address = $request->address;
            $status = $this->getStatusId($request->status);
            $date_in = $request->date_in;
            $date_out = $request->date_out;
            $index = $id - 1;

            $patient = Patient::find($id);
            $getPatientStatus = PatientStatuses::all()->where("patient_id", "=", $id);
            $patientStatus = PatientStatuses::find($getPatientStatus[$index]->id);

            
            if ($patient) {
                $patient->update([
                    "name" => ($name != NULL) ? $name : $patient->name,
                    "phone" => ($phone != NULL) ? $phone : $patient->phone,
                    "address" => ($address != NULL) ? $address : $patient->address
                ]);
            }

            if ($getPatientStatus) {
                $patientStatus->update([
                    "status_id" => ($status != NULL) ? $status : $getPatientStatus[$index]->status_id,
                    "date_in" => ($date_in != NULL) ? $date_in : $getPatientStatus[$index]->date_in,
                    "date_out" => ($date_out != NULL) ? $date_out : $getPatientStatus[$index]->date_out
                ]);
            }
            $patientStatus = $this->formatPatient($patientStatus);
            $response = [
                "message" => "Patient's id $id updated successfully",
                "data" => $patientStatus
            ];

            return response()->json($response, Response::HTTP_OK);
        }
    }


    /**
     * Function untuk menghapus data pasien berdasarkan parameter (id pasien).
     */
    function destroy($id)
    {
        $index = $id - 1;
        $patient = Patient::find($id);

        if ($patient) {
            $patient->delete();

            $getPatientStatus = PatientStatuses::all()->where("patient_id", "=", $id);
            $patientStatus = PatientStatuses::find($getPatientStatus[$index]->id);
            

            if ($getPatientStatus) {
                $patientStatus->delete();
            }

        $response = [
            "message" => "Patient's id $id deleted successfully!"
        ];

        return response()->json($response, Response::HTTP_OK);
        } else {
            $response = [
                "message" => "Data not found!"
            ];
    
            return response()->json($response, Response::HTTP_NOT_FOUND);
        }
    }


    /**
     * Function untuk menampilkan data pasien berdasarkan parameter (status pasien).
     */
    function status($status)
    {
        $statusId = $this->getStatusId($status);
        $patients = PatientStatuses::where("status_id", "=", $statusId)->get();
        if ($patients->isNotEmpty()) {
            $patients = $patients->map(function($patient){
                return $this->formatPatient($patient);
            });     

            $response = [
                "message" => "Get all patients who are $status",
                "data" => $patients
            ];

            return response()->json($response, Response::HTTP_OK);
            
        } else {
            $response = [
                "message" => "Data not found!"
            ];
            return response()->json($response, Response::HTTP_NOT_FOUND);
        }
    }


    /**
     * Function untuk mencari data pasien berdasarkan parameter (nama pasien).
     */
    function search($name)
    {
        $patients = Patient::where("name", "like", "%" . $name . "%")->get();
        
        if ($patients->isNotEmpty()) {
            $patients = $patients->map(function($patient){
                $patient = PatientStatuses::where("patient_id", "=", $patient->id)->get();
                return $patient = $patient->map(function($patientStatus){
                    return $this->formatPatient($patientStatus);
                });
            });
               
            $response = [
                "message" => "Search results '$name'",
                "data" => $patients
            ];

            return response()->json($response, Response::HTTP_OK);
            
        } else {
            $response = [
                "message" => "Data not found!"
            ];
            return response()->json($response, Response::HTTP_NOT_FOUND);
        }

    }

}
