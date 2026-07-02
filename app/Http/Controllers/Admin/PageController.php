<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;

use Illuminate\Http\Request;
use App\Models\Page;
use Mail,Hash,File,Auth,DB,Helper,Exception,Session,Redirect,Validator;

class PageController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        //
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create($key)
    {
        try{
            $page = Page::where('key',$key)->first();
            return view('admin.pages.create',compact('page'));
        }catch(Exception $e){
            return back()->withError($e->getMessage());
        }
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        //
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $key)
    {
        $input = $request->all();

        $validator = Validator::make($input, [
            'value' => 'required|string',
        ],[
            'value.required' => 'Please enter page content',
        ]);
        if($validator->fails()) {
            return response()->json(['error' => true,'message' => $validator->getMessageBag()->first()]);
        }
        try{
            $page = Page::where('key',$key)->first();
            $page->value = $request->value;
            $page->save();
            return response()->json(['success'=>true]);
        }catch(Exception $e){
            return response()->json(['error'=>true,'message' => $e->getMessage()]);
        }
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        //
    }
}
