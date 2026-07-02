<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;

use Illuminate\Http\Request;
use App\Models\User;
use App\Models\Contact;
use Mail,Hash,File,Auth,DB,Helper,Exception,Session,Redirect;
use Carbon\Carbon;
use App\Mail\ContactMail;

class ContactController extends Controller
{
    public function index()
    {
        return view('admin.contacts.index');
    }

    public function getallcontact(Request $request){
        $contacts = Contact::orderBy('id','desc')->get();
        return response()->json(['data' => $contacts]);
    }


    public function contactUsSubmit(Request $request)
    {
        
        $request->validate([
            'name' => 'required', 
            'email' => 'required|email', 
            'message' => 'required'
        ]);
        
        try {
            //======================== Submit Feedback   ===============//
            $contact = new Contact();
            $contact->name = $request->name;
            $contact->email = $request->email;
            $contact->message = $request->message;
            $contact->save();
            $contactId = $contact->id;
            $contact_details = Contact::where('id', $contactId)->first();
            if (!empty($contact_details)) {
                $admin = Helper::admin();
                Mail::to($admin->business_email)->send(new ContactMail($contact_details));
            }
            return redirect()->route('/')->withSuccess('Thank you for getting in touch!');
        } catch (Exception $e) {
            DB::rollback();
            return back()->withInput()
                ->withError($e->getMessage());
        }
    }

    public function destroy($id)
    {

        try{
            Contact::where('id',$id)->delete();
            return response()->json([
                'success' => 'success',
                'message' => 'deleted successfully',
            ]);
        }catch(Exception $e){
            return response()->json([
                'success' => 'error',
                'message' => $e->getMessage(),
            ]);
        }

    }


}
