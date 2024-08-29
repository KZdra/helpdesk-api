<?php

namespace App\Http\Controllers;

use App\Traits\ApiResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;


class FaqController extends Controller
{
    use ApiResponse;

    public function getFaqs(){
        try {
            $faqs = DB::table('faqs')->get();
            return $this->successResponse($faqs);
        } catch (\Exception $e) {
            return $this->errorResponse('Failed to retrieve faq. Please try again.', 500);
        }
    }

    public function postFaqs(Request $request){
        $validator = Validator::make($request->all(), [
            'question' => 'required|string|max:255',
            'answer' => 'required|string',
        ]);

        if ($validator->fails()) {
            return $this->errorResponse('Validation errors', 400, $validator->errors());
        }

        DB::beginTransaction();

        try {
            DB::table('faqs')->insert([
                'question' => $request->question,
                'answer' => $request->answer,
                'created_at' => now(),
            ]);

            DB::commit();

            return $this->successResponse(null, 'Faq created successfully', 201);
        } catch (\Exception $e) {
            DB::rollBack();

            return $this->errorResponse('Faq creation failed. Please try again.', 500);
        }
    }

    public function getFaq($id)
    {
        try {
            $faq = DB::table('faqs')->where('id', $id)->first();
            if (!$faq) {
                return $this->errorResponse('faq not found', 404);
            }
            return $this->successResponse($faq);
        } catch (\Exception $e) {
            return $this->errorResponse('Failed to retrieve faq. Please try again.', 500);
        }
    }

    public function updateFaq(Request $request, $id)
    {
        $validator = Validator::make($request->all(), [
            'question' => 'required|string|max:255',
            'answer' => 'required|string',
        ]);


        if ($validator->fails()) {
            return $this->errorResponse('Validation errors', 400, $validator->errors());
        }

        DB::beginTransaction();

        try {
            $faq = DB::table('faqs')->where('id', $id)->first();

            if (!$faq) {
                return $this->errorResponse('faq not found', 404);
            }

            DB::table('faqs')
                ->where('id', $id)
                ->update([
                    'question' => $request->question,
                    'answer' => $request->answer,
                    'updated_at' => now(),
                ]);

            DB::commit();

            return $this->successResponse(null, 'Faq updated successfully');
        } catch (\Exception $e) {
            DB::rollBack();

            return $this->errorResponse('Faq update failed. Please try again.', 500);
        }
    }

    // Delete a category
    public function deleteFaq($id)
    {
        DB::beginTransaction();

        try {
            $faq = DB::table('faqs')->where('id', $id)->first();

            if (!$faq) {
                return $this->errorResponse('faq not found', 404);
            }

            DB::table('faqs')->where('id', $id)->delete();

            DB::commit();

            return $this->successResponse(null, 'faq deleted successfully');
        } catch (\Exception $e) {
            DB::rollBack();

            return $this->errorResponse('faq deletion failed. Please try again.', 500);
        }
    }
}
