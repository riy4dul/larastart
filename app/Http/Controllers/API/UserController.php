<?php

namespace App\Http\Controllers\API;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\User;
use Illuminate\Support\Facades\Hash;
class UserController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth:api');
    }
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
       // $this->authorize('isAdmin');
        if(\Gate::allows('isAdmin') || \Gate::allow('isAuthor')){
            return User::latest()->paginate(10); 
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
        $this->validate($request,[
            'name'=> 'required|string|max:119',
            'email'=> 'required|email|unique:users',
            'password' => 'required',
            'type'=> 'required',
            'photo'=> '',
            'bio'=> 'required'
        ]); 

        $user = new User();
        $user->name = $request->name;
        $user->email = $request->email;
        $user->password = Hash::make($request->password);
        $user->type = $request->type;
        $user->photo = $request->photo;
        $user->bio = $request->bio;
        $user->save();

        return $user;

    }


    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function profile()
    {
        return Auth('api')->user();
    }

    public function updateProfile(Request $request)
    {
       
        $user = auth('api')->user();
         $this->validate($request,[
            'name' => 'required|string|max:191',
            'email' => 'required|string|email|max:191|unique:users,email,'.$user->id,
            'password' => 'sometimes|required|min:6'
        ]);

        $currentPhoto=$user->photo;
       if($request->photo != $currentPhoto){
        $name = time().'.' . explode('/', explode(':', substr($request->photo, 0, strpos($request->photo, ';')))[1])[1];
            \Image::make($request->photo)->save(public_path('img/profile/').$name);
            $request->merge(['photo' => $name]);
             $userPhoto = public_path('img/profile/').$currentPhoto;
            if(file_exists($userPhoto)){
                @unlink($userPhoto);
            }
           }

        if(!empty($request->password)){
            $request->merge(['password' => Hash::make($request['password'])]);
        }

            $user->update($request->all());
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        $user = User::findOrFail($id);
        $this->validate($request,[
            'name'=> 'required|string|max:119',
            'email'=> 'required|email|unique:users,email,' .$user->id,
            'password'=> 'sometimes|min:6',
            'type'=> 'required',
            'photo'=> '',
            'bio'=> 'required'
        ]); 
        $user->update($request->all());
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {   
        $this->authorize('isAdmin');
        $user = User::findOrFail($id);
        $user->delete();
    }
}
