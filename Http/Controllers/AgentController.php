<?php

namespace App\Http\Controllers;

use App\Agent;
use App\Band;
use App\Contact;
use App\Task;
use App\Vanue;
use Illuminate\Http\Request;
use App\Http\Requests;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Session;
use Yajra\Datatables\Datatables;
use Illuminate\Support\Facades\DB;
class AgentController extends Controller
{
    public function __construct(Agent $model)
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
        return view('master.agents.index');
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()

    {
        //print_r(Session::get('activeBandId'));die;
        $vanue=Vanue::select()->where('band_id','=',Session::get('activeBandId'))->get();
        //print_r($vanue);die;
        return view('master.agents.create')->with('vanues',$vanue);
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
            'mobile' => 'numeric',
            /*'website' => 'required|url',
            'facebook' => 'required|url',
            'vanue_id' => 'required',
            'twitter' => 'required|url',*/
            /*'street' => 'required',
            'city' => 'required',
            'state' => 'required',*/
            'postcode' => 'numeric',
            /*'country' => 'required',
            'notes' => 'required',*/

        ]);

        if ($validator->fails()) {
            return redirect()
                ->back()
                ->withErrors($validator->errors())
                ->withInput();
            // return $validator->errors()->all();
        }else{
            $input=$request->all();
            $input['band_id']=Session::get('activeBandId');
            $agentId = $this->model->create($input)->id;
            if($agentId){
                if($request->vanue_id!=''){
                    Vanue::where('id', '=', $request->vanue_id)
                        ->update(['agent_id' => $agentId]);
                }
            }
            return redirect()
                ->route('agent.index')->with('success','Successfully Created');
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
        $vanue   = Vanue::where('agent_id','=',$id)->get();

        $agent = $this->model->findOrFail($id);
        $contacts   = Contact::where('band_id','=',$agent->band_id)->where('work','like','a%')->get();

        return view('master.agents.show')->with('vanues', $vanue)->with('agent',$agent)->with('contacts',$contacts);
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        $agent = $this->model->findOrFail($id);

        $vanues=Vanue::select()->where('band_id','=',Session::get('activeBandId'))->get();
        return view('master.agents.edit')->with('agent', $agent)->with('vanues',$vanues);
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
            'mobile' => 'numeric',
            /*'website' => 'required|url',
            'facebook' => 'required|url',
            'vanue_id' => 'required',
            'twitter' => 'required|url',*/
            /*'street' => 'required',
            'city' => 'required',
            'state' => 'required',*/
            'postcode' => 'numeric',
            /*'country' => 'required',
            'notes' => 'required',*/
        ]);

        if ($validator->fails()) {
            return redirect()
                ->back()
                ->withErrors($validator->errors())
                ->withInput();
            // return $validator->errors()->all();
        }else{

            $agent = $this->model->findOrFail($id);

            if($agent->update($request->all())){
                if($request->vanue_id!=''){
                    Vanue::where('id', '=', $request->vanue_id)
                        ->update(['agent_id' => $id]);
                }else{
                    Vanue::where('agent_id', '=', $id)
                        ->update(['agent_id' => 0]);
                }

            }

            return redirect()
                ->route('agent.index')->with('success','Successfully Updated');
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
        $agent = $this->model->findOrFail($id);
        if($agent->delete()){
            try {
                (new Task())->deleteTaskByObjectIdAndObjectNameId(4, $id);
            } catch (\Exception $e) {
            }
        }

        return view('master.agents.index');
    }

    /**
     * Process datatables ajax request.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function anyData()
    {
        $badsId = Band::select(['id'])->where('user_id','=',Auth::user()->id)->get();
        $agent = Agent::select(['id', 'name', 'created_at', 'updated_at','band_id'])->whereIn('band_id', $badsId);

        return Datatables::of($agent)
            ->addColumn('action', function ($agent) {
                $activeBadId = Session::get('activeBandId');
                return '<a '.(($agent->band_id == $activeBadId) ? 'href="'.url("task/create",[4,$agent->id]).'"' : 'onclick=checkActiveBand()').' class="btn btn-danger no-top-margin create-task">T</a><button type="button" id="'.$agent->id.'" onclick="deleteAgent('.$agent->id.')" data-token="'.csrf_token().'" class="btn btn-danger no-top-margin edit-btn action-btn-margin alert_error" id="one" ><i class="mdi mdi-close mdi-18px"></i></button><a href="' . route("agent.show", $agent->id) . '" class="btn btn-danger no-top-margin edit-btn action-btn-margin"><i class="mdi mdi-eye mdi-18px"></i></a><a href="'.route("agent.edit",$agent->id).'" class="btn btn-danger no-top-margin edit-btn"><i class="mdi mdi-pencil mdi-18px"></i></a>';
            })
            ->editColumn('updated_at', function ($agent) {
                return $agent->updated_at->format('d/m/Y');
            })
            ->editColumn('id', 'ID: {{$id}}')
            ->make(true);
    }
}
