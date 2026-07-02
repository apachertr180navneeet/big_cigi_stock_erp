<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;

use Illuminate\Http\Request;
use App\Models\Notification;
use App\Models\NotificationUser;
use Carbon\Carbon;
use Exception,Auth;

class NotificationController extends Controller
{
    public function index(){
        try{
            $user = Auth::user();
            $user_notifications = NotificationUser::where('user_id',$user->id)->pluck('notification_id')->toArray();
            $notifications = Notification::select('*','id as uuid')->whereIn('id',$user_notifications)->orderBy('created_at', 'desc')->paginate(10);
            return view('admin.notifications.index',compact('notifications'));
        }catch(Exception $e){
            return back()->withError($e->getMessage());
        }
    }

    public function clear(){
        try{
            $user = Auth::user();
            $date = Carbon::now();
            $user_notifications = NotificationUser::where('user_id',$user->id)->where('read_at',null)->update(['read_at'=>$date]);
            return back()->withSuccess('All notifications clear successful');
        }catch(Exception $e){
            return back()->withError($e->getMessage());
        }
    }


    public function destroy($id)
    {
        try{
            $user = Auth::user();
            NotificationUser::where('user_id',$user->id)->where('notification_id',$id)->delete();
            return response()->json([
                'status' => true,
                'message' => 'Notification deleted successfully',
            ]);
        }
        catch(Exception $e){
            return response()->json([
                'status' => false,
                'message' => $e->getMessage(),
            ]);
        }
    }
}
