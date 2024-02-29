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

        $comment = Comment::create($data);



        return $this->successResponse($comment, Response::HTTP_CREATED);
    }

    public function update($id, Request $request) {
        $rules = [
            'pretitle' => 'max:180',
            'title' => 'required|max:180',
            'author' => 'required|max:60',
            'image_url' => 'required|max:255',
            'introduction' => 'required|max:300',
            'body' => 'required',
            'format' => 'required|in:ONLY_TEXT,WITH_IMAGE,WITH_GALLERY,WITH_VIDEO',
            'status' => 'required|in:PUBLISHED,NOT_PUBLISHED,ARCHIVED',
            'edition_date' => 'required|integer|min:20240101',
            'category_id' => 'required|integer|exists:categories,id',
            'tags' => 'required|array'
        ];
        $this->validate($request, $rules);

        $data = $request->all();

        $category = Category::select('title', 'alias')->where('id', $data['category_id'])->first();

        $data['alias'] = Str::slug($data['title']);
        $data['updated_by'] = Auth::user()->email;
        $data['category_title'] = $category->title;
        $data['category_alias'] = $category->alias;

        $content = Content::findOrFail($id);

        // Validamos si el content es único, es decir si no hay otro con el mismo (edition_date, category_alias y alias)
        $exists = Content::where('edition_date', $data['edition_date'])
            ->where('category_alias', $category->alias)
            ->where('alias', $data['alias'])
            ->where('id', '<>', $id)
            ->exists();

        if ($exists) {
            return $this->errorResponse('There is Content with the same data (edition_date, category, title)', Response::HTTP_UNPROCESSABLE_ENTITY);
        }

        $content->fill($data);
        $content->save();

        // Asocia los tags al content
        $tags = $request->input('tags');
        $content->tags()->sync($tags);

        return $this->successResponse($content, Response::HTTP_OK);
    }

    public function patch($id, Request $request) {
        $rules = [
            'pretitle' => 'max:180',
            'title' => 'max:180',
            'author' => 'max:60',
            'image_url' => 'max:255',
            'introduction' => 'max:300',
            'format' => 'in:ONLY_TEXT,WITH_IMAGE,WITH_GALLERY,WITH_VIDEO',
            'status' => 'in:WRITING,PUBLISHED,NOT_PUBLISHED,ARCHIVED',
            'edition_date' => 'integer|min:20240101',
            'category_id' => 'integer|exists:categories,id',
            'tags' => 'array'
        ];
        $this->validate($request, $rules);

        $content = Content::findOrFail($id);

        $data = $request->all();
        if (isset($data['title'])) {
            $data['alias'] = Str::slug($data['title']);
        }
        $data['updated_by'] = Auth::user()->email;
        if (isset($data['category_id'])) {
            $category = Category::select('title', 'alias')->where('id', $data['category_id'])->first();
            $data['category_title'] = $category->title;
            $data['category_alias'] = $category->alias;
        }

        $content->fill($data);
        $content->save();

        // Asocia los tags al content
        $tags = $request->input('tags');
        $content->tags()->sync($tags);

        return $this->successResponse($content, Response::HTTP_OK);
    }

    public function delete($id) {
        $content = Content::findOrFail($id);

        // Desasocia los tags antes de borrar el post
        $content->tags()->detach();

        $content->delete();
        return $this->successResponse($content, Response::HTTP_OK);
    }

    public function tags($id) {
        $content = Content::findOrFail($id);
        $tags = $content->tags()->select('title', 'alias')->get();
        return $this->validResponse($tags);
    }
}
