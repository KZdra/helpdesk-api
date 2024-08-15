<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class CommentController extends Controller
{

    public function __construct()
    {
        $this->middleware('auth:api', ['except' => ['login', 'register']]);
    }

    // Get all comments for a specific ticket
    public function getComments($ticket_id)
    {
        try {
            $comments = DB::table('comments')
                ->join('users', 'comments.user_id', '=', 'users.id')
                ->where('comments.ticket_id', $ticket_id)
                ->select('comments.*', 'users.name as user_name')
                ->orderBy('comments.created_at','asc')
                ->get();


            foreach ($comments as $comment) {
                if ($comment->attachment) {
                    $comment->attachment_url = url('storage/' . $comment->attachment);
                }
            }

            return response()->json($comments, 200);
        } catch (\Exception $e) {
            return response()->json(['message' => 'Failed to retrieve comments', 'error' => $e->getMessage()], 500);
        }
    }



    // Create a new comment
    public function createComment(Request $request)
    {

        $userId = Auth::id();

        $validator = Validator::make($request->all(), [
            'ticket_id' => 'required|exists:tickets,id_ticket',
            'comment' => 'required|string',
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors(), 422);
        }

        DB::beginTransaction();

        try {
            $filePath = null;
            if ($request->hasFile('attachment')) {
                $originalFileName = $request->file('attachment')->getClientOriginalName();
                $filePath = $request->file('attachment')->storeAs('attachments', $originalFileName, 'public');
            } else {
                $originalFileName = null;
            }

            $commentData = [
                'ticket_id' => $request->ticket_id,
                'user_id' => $userId,
                'comment' => $request->comment,
                'attachment' => $filePath,
                'attachment_name' => $originalFileName,
                'created_at' => now(),
                'updated_at' => now(),
            ];


            $commentId = DB::table('comments')->insertGetId($commentData);

            DB::commit();

            return response()->json(['message' => 'Success'], 201);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['message' => 'Failed to create comment', 'error' => $e->getMessage()], 500);
        }
    }
    public function downloadCommentAttachment($id)
    {
        try {
            $comment = DB::table('comments')
                ->where('id', $id)
                ->first();

            if (!$comment || !$comment->attachment) {
                return $this->errorResponse('Attachment not found', 404);
            }

            return response()->download(public_path("storage/{$comment->attachment}"));
        } catch (\Exception $e) {
            return $this->errorResponse('Failed to download attachment. Please try again.');
        }
    }
}
