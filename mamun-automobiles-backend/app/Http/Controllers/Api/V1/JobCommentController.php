<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\JobComment;
use Illuminate\Http\Request;

class JobCommentController extends Controller
{
    public function store(Request $request)
    {
        $request->validate([
            'job_card_id' => 'required|integer',
            'comment' => 'required|string',
            'is_internal' => 'boolean'
        ]);

        $comment = JobComment::create([
            'job_card_id' => $request->job_card_id,
            'user_id' => auth()->id() ?? 1,
            'comment' => $request->comment,
            'is_internal' => $request->is_internal ?? false,
        ]);

        return response()->json($comment, 201);
    }
}
