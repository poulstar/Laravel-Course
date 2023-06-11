<?php

namespace App\Actions;

use App\Models\Comment;
use Illuminate\Support\Facades\Auth;

class CommentAction {
    //Query Part
    public static function getAllComment(){
        $comments = Comment::all();
        return $comments;
    }
    public static function getComment($comment_id){
        $comment = Comment::find($comment_id);
        return $comment;
    }
    public static function getUserComments(){
        $comments = Comment::where('user_id', Auth::id())->get();
        return $comments;
    }
    //Tools Part
    public static function checkState(Comment $comment)
    {
        if($comment->state == 0)
        {
            $comment->state = 1;
            $comment->save();
        }
        return back();
    }
    //Edit Part
    public static function updateComment($request, $comment_id)
    {
        $updateComment = self::getComment($comment_id);
        $updateComment->description = $request->input('description');
        $updateComment->status = $request->input('status');
        $updateComment->save();
        return back();
    }
    public static function addComment($request, $product_id)
    {
        $newComment = new Comment();
        $newComment->user_id = Auth::id();
        $newComment->product_id = $product_id;
        $newComment->description = $request->input('comment');
        $newComment->save();
        return back();
    }
    //necessary function

}
