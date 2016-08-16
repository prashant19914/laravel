<?php

namespace App\Http\Controllers;

use App\Contact;
use Illuminate\Http\Request;

use App\Http\Requests;
use App\Band;
use App\Member;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Session;
use Yajra\Datatables\Datatables;
use Illuminate\Support\Facades\DB;

class BandController extends Controller
{
    public function __construct(Band $model)
    {
        $this->model = $model;
    }
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        return view('master.band.index');
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        return view('master.band.create');
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)

    {
        $validator = validator($request->all(), [
            'name' => 'required|string',
            /*'website' => 'required|url',
            'youtube' => 'required|url',
            'facebook' => 'required|url',
            'instagram' => 'required|url',
            'twitter' => 'required|url',
            'memberName.*' => 'required',*/
        ]);

        if ($validator->fails()) {
            return redirect()
                ->back()
                ->withErrors($validator->errors())
                ->withInput();
            // return $validator->errors()->all();
        }else{
            $input=$request->all();
            $input['user_id']=Auth::user()->id;
            $band = $this->model->create($input)->id;
            /*foreach($request->memberName as $memberName){
                $member = new Member;

                $member->band_id = $band;
                $member->memberName = $memberName;
                $member->save();

            }*/
            foreach($request->memberName as $memberName){
                $trimMember = trim($memberName);
                if($trimMember!=''){
                    $contact = new Contact;

                    $contact->band_id = $band;

                    $parts = explode(" ", $memberName);
                    $fname = array_shift($parts);
                    $lname = implode(" ", $parts);

                    $contact->fname = $fname;
                    $contact->lname = $lname;
                    $contact->isband = 1;
                    $contact->work = 'b,'.$band;
                    $contact->save();
                }

            }
            if (!$request->session()->has('activeBandId')) {
                Session::set('activeBandId', $band);
                Session::set('activeBandName', $input['name']);
            }
            return view('master.band.index')->with('success','Successfully Created');
        }

    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        $band   = $this->model->findOrFail($id);
       return view('master.band.show')->with('band', $band);
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        $band = $this->model->findOrFail($id);
        return view('master.band.edit')->with('band', $band);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {   //echo "<pre>";print_r($request->all());die;
        $validator = validator($request->all(), [
            'name' => 'required|string',
            /*'website' => 'required|url',
            'youtube' => 'required|url',
            'facebook' => 'required|url',
            'instagram' => 'required|url',
            'twitter' => 'required|url',
            'memberName.*' => 'required',*/
        ]);

        if ($validator->fails()) {
            return redirect()
                ->back()
                ->withErrors($validator->errors())
                ->withInput();
            // return $validator->errors()->all();
        }else{

                $band = $this->model->findOrFail($id);

                $band->update($request->all());

                $count = 0;
                if(isset($request->memberName)){
                    $trimArray = array_map("trim", $request->memberName);
                    if(count($trimArray)== count(array_unique($trimArray))) {
                        Contact::where('band_id', $id)->update(['isband' => 0]);
                    }
                }else{
                    Contact::where('band_id', $id)->update(['isband' => 0]);
                }


                if (count($request->memberName) > 0) {
                    $trimArray = array_map("trim", $request->memberName);
                    if(count($trimArray)== count(array_unique($trimArray))) {
                        foreach ($request->memberName as $memberName) {
                        $contact = array();

                        $parts = explode(" ", trim($memberName));
                        $fname = array_shift($parts);
                        $lname = implode(" ", $parts);

                        $contact['fname'] = $fname;
                        $contact['lname'] = $lname;
                        $contact['isband'] = 1;
                        //$contact->save();
                        if (isset($request->memberId[$count]) && $request->memberId[$count] != '') {
                            Contact::where('id', '=', $request->memberId[$count])
                                ->update($contact);
                        } else {
                            $Contact = DB::table('contacts')->where('fname', '=', $fname)->where('lname', '=', $lname)->first();
                            if (count($Contact) > 0) {
                                Contact::where('id', '=', $Contact->id)
                                    ->update(['isband' => 1]);
                            } else {
                                $contactSave = new Contact;

                                $contactSave->fname = $fname;
                                $contactSave->lname = $lname;
                                $contactSave->isband = 1;
                                $contactSave->band_id = $id;
                                $contactSave->work = 'b,'.$id;
                                $contactSave->save();
                            }

                        }

                        $count++;
                    }
                    }else{
                        return redirect()
                            ->back()->with('success', 'Contact is unique');
                    }
                }

                return redirect()
                    ->route('band.index')->with('success', 'Successfully Updated');

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
        $band = $this->model->findOrFail($id);
        $band->delete();
        if(Session::get('activeBandId')==$id){
            Session::forget('activeBandId');
            Session::forget('activeBandName');
        }

        return view('master.band.index');
    }

    /**
     * Process datatables ajax request.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function anyData()
    {

        $bands = Band::select(['id', 'name', 'created_at', 'updated_at'])->where('user_id', '=', Auth::user()->id);

        return Datatables::of($bands)
            ->addColumn('action', function ($bands) {
                if(Session::get('activeBandId')==$bands->id) {
                    return '<button type="button" id="' . $bands->id . '" onclick="deleteBand(' . $bands->id . ')" data-token="' . csrf_token() . '" class="btn btn-danger no-top-margin edit-btn action-btn-margin alert_error" id="one" ><i class="mdi mdi-close mdi-18px"></i></button><a href="' . route("band.show", $bands->id) . '" class="btn btn-danger no-top-margin edit-btn action-btn-margin"><i class="mdi mdi-eye mdi-18px"></i></a><a href="' . route("band.edit", $bands->id) . '" class="btn btn-danger no-top-margin edit-btn action-btn-margin"><i class="mdi mdi-pencil mdi-18px"></i></a><input type="radio" name="radio2"  data-radio-all-off="true" class="switch-radio2 action-btn-margin" value="' . $bands->id . '" data-token="' . csrf_token() . '" checked test="'.Session::get("activeBandId").'" band-name="">';
                }else{
                    return '<button  type="button" id="' . $bands->id . '" onclick="deleteBand(' . $bands->id . ')" data-token="' . csrf_token() . '" class="btn btn-danger no-top-margin edit-btn action-btn-margin alert_error" id="one" ><i class="mdi mdi-close mdi-18px"></i></button><a href="' . route("band.show", $bands->id) . '" class="btn btn-danger no-top-margin edit-btn action-btn-margin"><i class="mdi mdi-eye mdi-18px"></i></a><a href="' . route("band.edit", $bands->id) . '" class="btn btn-danger no-top-margin edit-btn action-btn-margin"><i class="mdi mdi-pencil mdi-18px"></i></a><input type="radio" name="radio2"  data-radio-all-off="false" class="switch-radio2 action-btn-margin" value="' . $bands->id . '" data-token="' . csrf_token() . '" band-name="' . $bands->name . '">';
                }
            })
            ->editColumn('updated_at', function ($bands) {
                return $bands->updated_at->format('d/m/Y');
            })
            ->editColumn('id', 'ID: {{$id}}')
            ->make(true);
    }
    public function activeBand(){
        if($_POST['status']=='true'){
            Session::set('activeBandId', $_POST['bandId']);
            $activeBandName = DB::table('bands')->where('id', '=', $_POST['bandId'])->first();
            Session::set('activeBandName', $activeBandName->name);
            return $activeBandName->name;
        }else{
            Session::forget('activeBandId');
            Session::forget('activeBandName');
            return "";
        }

    }
}
