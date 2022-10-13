<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Post;
use App\Category;
use App\Tag;
use Illuminate\Http\Request;
use illuminate\Support\Str;

class PostController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $posts = Post::all();
        return view('admin.posts.index', compact('posts'));
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        $categories = Category::all();
        $tags = Tag::all();
        return view('admin.posts.create',compact('categories','tags'));
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $request->validate([
            'title'=>'required|max:255',
            'content'=>'required|max:65535',
            'category_id'=>'nullable|exists:categories,id',
            'tags'=> 'exists:tags,id'
        ]);

        $data = $request->all();

        $post = new Post();
        $post->fill($data);

        $slug = Str::slug($post->title, '-');

        $checkPost = Post::where('slug', $slug)->first();

        $counter=1;
        while($checkPost){
            $slug = Str::slug($post->title . '-'. $counter, '-');

            $counter++;
            $checkPost = Post::where('slug', $slug)->first();
        }
        $post->slug = $slug;
        $post->save();

        $post->tags()->sync($data['tags']);

        return redirect()->route('admin.posts.index')->with('status','post creato!');
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show(Post $post)
    {
        return view('admin.posts.show', compact('post'));
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit(Post $post)
    {
        $categories = Category::all();
        $tags = Tag::all();
        return view('admin.posts.edit', compact('post','categories','tags'));
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request,Post $post)
    {
        $request->validate([
            'title'=>'required|max:255',
            'content'=>'required|max:65535',
            'tags'=> 'exists:tags,id'
        ]

        );
        $data = $request->all();



        if ($post->title !== $data['title']){
            $slug = Str::slug($data['title'], '-');

        $checkPost = Post::where('slug', $slug)->first();

        $counter=1;
        while($checkPost){
            $slug = Str::slug($data['title']. '-'. $counter, '-');
            $counter++;
            $checkPost = Post::where('slug', $slug)->first();
        }
        $data['slug']= $slug;
        }
        $post->update($data);

        if (array_key_exists('tags',$data)){
            $post->tags()->sync($data['tags']);
        }else{
            $post->tags()->sync([]);
        }

        return redirect()->route('admin.posts.index')->with('status','post aggiornato con successo');

    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy(Post $post)
    {
        $post->tags()->sync([]);//cancello le relazioni della tabella ponte altrimenti error//
        
        $post->delete();
        return redirect()->route('admin.posts.index')->with('status','cancellazione riuscita!');
    }
}
