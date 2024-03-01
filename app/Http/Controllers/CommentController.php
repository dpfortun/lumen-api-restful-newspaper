<?php

namespace App\Http\Controllers;

use App\Models\Content;
use App\Models\Category;
use App\Models\Comment;
use Illuminate\Support\Str;
use App\Traits\ApiResponser;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Auth;

class CommentController extends Controller
{
    use ApiResponser;

    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        //
    }

    public function index() {
        $comments = Comment::select('id', 'text', 'sent_date', 'published_date', 'status', 'content_id', 'user_id')->orderBy('created_at', 'DESC')->get();
        return $this->validResponse($comments);
    }

    public function read($id) {
        $comment = Comment::findOrFail($id);
        return $this->validResponse($comment);

    }

    public function create(Request $request) {
        $rules = [
            'text' => 'required|max:180',
            'sent_date' => 'required|integer|min:20240101',
            'published_date' => 'required|integer|min:20240101',
            'status' => 'required|in:NOT_PUBLISHED,PUBLISHED',
            'content_id' => 'required|integer|exists:contents,id',
            'user_id' => 'required|integer|exists:users,id'
        ];
        $this->validate($request, $rules);

        $data = $request->all();
$data['created_by'] = Auth::user()->email;
        $comment = Comment::create($data);



        return $this->successResponse($comment, Response::HTTP_CREATED);
    }

    public function update($id, Request $request) {
        $rules = [
            'text' => 'required|max:180',
            'sent_date' => 'required|integer|min:20240101',
            'published_date' => 'required|integer|min:20240101',
            'status' => 'required|in:NOT_PUBLISHED,PUBLISHED',
        ];
        $this->validate($request, $rules);
        $data = $request->all();
        $data['updated_by'] = Auth::user()->email;
        $comment = Comment::findOrFail($id);
        $comment->fill($data);
        $comment->save();
        return $this->successResponse($comment, Response::HTTP_OK);
    }

    public function patch($id, Request $request) {
        $rules = [
            'status' => 'required|in:NOT_PUBLISHED,PUBLISHED',
        ];
        $this->validate($request, $rules);

        $comment = comment::findOrFail($id);

        $data = $request->all();
        $comment->fill($data);
        $comment->save();
        return $this->successResponse($comment, Response::HTTP_OK);
    }

    public function delete($id) {
        $comment = Comment::findOrFail($id);
        $comment->delete();
        return $this->successResponse($comment, Response::HTTP_OK);
    }
}
