<?php


namespace App\Http\Controllers\RECORDS;
use App\Http\Controllers\Controller;

use App\Models\ANIMALES\ANIMales;
use App\Models\CATALOGOS\CATEventos;
use App\Models\PUBLICACIONES\PUBlicaciones;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use DataTables;
use Hashids\Hashids;
use App\Models\TOKENS\TOKen;
class RecordsController extends Controller
{
    /**
     * @autor Alex vergara
     * @function Retorna la datatable de eventos
     * @param request
     * @return mixed
     */
    protected function tableeventos(Request $request)
    {
        $alleventos = CATEventos::getEventos();
        $hash = new Hashids('', 10);
        if ($request->ajax()) {
            return Datatables::of($alleventos)
                ->addIndexColumn()
                ->addColumn('eventonombre', function ($data) {
                    $desc = $data->getEveNombre();
                    $btn = '<div data-toggle="tooltip" data-placement="left" title="' . $desc . '">' . $desc . '</div>';
                    return $btn;
                })->addColumn('eventodescrip', function ($data) {
                    $desc = $data->getEveDescripcion();
                    $btn = '<div data-toggle="tooltip" data-placement="left" title="' . $desc . '">' . $desc . '</div>';
                    return $btn;
                })
                ->addColumn('eventohoraini', function ($data) {
                    $desc = date('h:i A', strtotime($data->getEveHorarioIni()));
                    $btn = '<div data-toggle="tooltip" data-placement="left" title="' . $desc . '">' . $desc . '</div>';
                    return $btn;
                })
                ->addColumn('eventohorafin', function ($data) {
                    $desc = date('h:i A', strtotime($data->getEveHorarioFin()));
                    $btn = '<div data-toggle="tooltip" data-placement="left" title="' . $desc . '">' . $desc . '</div>';
                    return $btn;
                })
                ->addColumn('eventofechaini', function ($data) {
                    $desc = $data->getEveFechaIni();
                    $btn = '<div data-toggle="tooltip" data-placement="left" title="' . $desc . '">' . $desc . '</div>';
                    return $btn;
                })
                ->addColumn('eventofechafin', function ($data) {
                    $desc = $data->getEveFechaFin();
                    $btn = '<div data-toggle="tooltip" data-placement="left" title="' . $desc . '">' . $desc . '</div>';
                    return $btn;
                })
                ->addColumn('action', function ($data) use ($hash) {
                    // onclick="editEvento(\'' . $eventid . '\')"
                    $eventid = $hash->encode($data->eve_eve);
                    return '<div>
                            <button href="javascript:void(0)" onclick="deleteEvento(\'' . $eventid . '\')"
                            class="btn btn-outline-danger btn-sm"
                            data-toggle="tooltip"
                            data-placement="bottom"
                            title="Eliminar evento">
                            <i class="fa fa-trash"></i>
                            </button>

                <button href="javascript:void(0)"
                             onclick="infoEvento(\'' . $eventid . '\')"
                            class="btn btn-outline-success btn-sm"
                            data-toggle="modal"
                            data-placement="bottom"
                            title="Editar evento">
                            <i class="far fa-edit"></i>
                            </button>
                        <button href="javascript:void(0)" onclick="notiEvento(\'' . $eventid . '\')"
                            class="btn btn-outline-warning btn-sm"
                            data-toggle="tooltip"
                            data-placement="bottom"
                            title="Mandar notificaciones">
                            <i class="far fa-bell"></i>
                            </button>
                        <button href="javascript:void(0)" onclick="createPdfEvent(\'' . $eventid . '\')"
                            class="btn btn-outline-primary btn-sm"
                            data-toggle="tooltip"
                            data-placement="bottom"
                            title="Crear pdf">
                            <i  class="fa fa-file-pdf" aria-hidden="true"></i>
                            </button>
                        </div>';

                })
                ->rawColumns(['action', 'eventonombre', 'eventodescrip', 'eventohoraini', 'eventohorafin', 'eventofechaini', 'eventofechafin', 'eventoimage'])
                ->make(true);
        }
        return view('dashboard', compact('alleventos'));

    }
    /**
     * @autor Alex vergara
     * @function Permiter editar un evento
     * @param request
     * @return mixed
     */
    protected function editeventos(Request $request)
    {
        $rules = [
            'name' => ['min:1', 'max:80'],
            'descrip' => ['min:1', 'max:255'],
            'timeini' => ['required'],
            'timefin' => ['required'],
            'dateini' => ['required'],
            'datefin' => ['required']
        ];
        $messages = [
            'name.min' => 'El campo de nombre debe tener al menos un caracter.',
            'name.max' => 'El campo de nombre no debe exceder de los 80 caracteres.',
            'descrip.min' => 'El campo de la descripci??n debe tener al menos un caracter.',
            'descrip.max' => 'El campo de la descripci??n no debe exceder de los 255 caracteres.',
            'timeini.required' => 'La hora de inicio es requerida.',
            'timefin.required' => 'La hora de fin es requerida.',
            'dateini.required' => 'La fecha de inicio es requerida.',
            'datefin.required' => 'La fecha de fin es requerida.'
        ];
        $validator = Validator::make($request->all(), $rules, $messages);

        if ($validator->fails()) {
            $messages = $validator->messages();

            return response()->json(
                [
                    'success' => false,
                    'message' =>$messages->first(),
                ]
            );
        }

        if($request->file('eventeimage')){
            $rules2 = [
                'eventeimage' => ['mimes:jpeg,png,jpg,svg|max:2048'],
            ];
            $messages2 = [
                'eventeimage.max' => 'El archivo no debe ser superior a 2 megas'
            ];
            $validator2 = Validator::make($request->all(), $rules2, $messages2);
            if ($validator2->fails()) {
                $messagesF = $validator2->messages();
                return response()->json(
                    [
                        'success' => false,
                        'message' =>$messagesF->first(),
                    ]
                );
            }

            $file = $request->file('eventeimage');
            $imageName = Str::random(10) . '.' . $file->getClientOriginalExtension();
            $file->move(public_path('images'), $imageName);
            $url = asset('images/' . $imageName);
        }else{
            $url = null;
        }


        try {
            $data = $request->all();
            $hash = new Hashids('', 10);



            $ideve = $hash->decode($data['id']);
            $UpdateEve = CATEventos::where('eve_eve', $ideve)->first();




            $UpdateEve->setEveNombre($data['name']);
            $UpdateEve->setEveDescripcion($data['descrip']);
            $UpdateEve->setEveHorarioIni($data['timeini']);
            $UpdateEve->setEveHorarioFin($data['timefin']);
            $UpdateEve->setEveFechaIni($data['dateini']);
            $UpdateEve->setEveFechaFin($data['datefin']);
            $UpdateEve->setEveImage($url);
            $UpdateEve->save();
            return response()->json([
                'success' => true,
                'message' => "Se ha actualizado correctamente."
            ]);
            #DB::commit();

        } catch (\Exception $exception) {
            #DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => $exception->getMessage()
            ]);
        }
    }
    /**
     * @autor Alex vergara
     * @function Hace la solicitud para ver informaci??n de un evento dentrop del modal
     * @param request
     * @return mixed
     */
    protected function infoediteve(Request $request)
    {
        $data = $request->all();

        try {
            $hash = new Hashids('', 10);
            $id = $hash->decode($data['code']);

            $InfoEventos = CATEventos::find($id[0]);
            return response()->json([
                "nombre" => $InfoEventos->getEveNombre(),
                "descripcion" => $InfoEventos->getEveDescripcion(),
                "horaini" => $InfoEventos->getEveHorarioIni(),
                "horafin" => $InfoEventos->getEveHorarioFin(),
                "fechaini" => $InfoEventos->getEveFechaIni(),
                "fechafin" => $InfoEventos->getEveFechaFin(),
                "image" => $InfoEventos->getEveImage(),
            ]);
        } catch (\Exception $exception) {
            return response()->json([
                'success' => false,
                'message' => $exception->getMessage()
            ]);
        }

    }

    /**
     * @autor Alex vergara
     * @function Retorna la la info de los animales para el select2
     * @param request
     * @return mixed
     */
    protected function getAnimals()
    {
        $Animales = ANIMales::all();
        $hash = new Hashids('', 20);
        $response = array();
        foreach ($Animales as $An) {
            $response[] = array(
                "id" => $hash->encode($An->getId()),
                "text" => $An->getNombre()
            );
        }
        echo json_encode($response);
        exit;
    }

    /**
     * @autor Alex vergara
     * @function Retorna la datatable de las publicaciones
     * @param request
     * @return mixed
     */
    protected function tablepublicaciones(Request $request)
    {

        $allPublicaciones = PUBlicaciones::with('animales')->get();

        $hash = new Hashids('', 10);

        if ($request->ajax()) {
            return Datatables::of($allPublicaciones)
                ->addIndexColumn()
                ->addColumn('animalpub', function ($data) {
                    $desc = $data->animales->getNombre();
                    $btn = '<div data-toggle="tooltip" data-placement="left" title="' . $desc . '">' . $desc . '</div>';
                    return $btn;
                })
                ->addColumn('titulopub', function ($data) {
                    $desc = $data->getTitle();
                    $btn = '<div data-toggle="tooltip" data-placement="left" title="' . $desc . '">' . $desc . '</div>';
                    return $btn;
                })
                ->addColumn('contenidopub', function ($data) {
                    $desc = $data->getDescrip();
                    $btn = '<div data-toggle="tooltip" data-placement="left" title="' . $desc . '">' . $desc . '</div>';
                    return $btn;
                })
                ->addColumn('createdpub', function ($data) {
                    $desc =  $data->getCreatedAt();
                    #$desc =  date('h:i A', $data->getCreatedAt());

                    $btn = '<div data-toggle="tooltip" data-placement="left" title="' . $desc . '">' . $desc . '</div>';
                    return $btn;
                })
                ->addColumn('action', function ($data) use ($hash) {
                    $pubtid = $hash->encode($data->pub_id);
                    return '<div>
                            <button href="javascript:void(0)" onclick="deletePub(\'' . $pubtid . '\')"
                            class="btn btn-outline-danger btn-sm"
                            data-toggle="tooltip"
                            data-placement="bottom"
                            title="Eliminar publicaci??n">
                            <i class="fa fa-trash"></i>
                            </button>
                <button href="javascript:void(0)" onclick="InfoPubli(\'' . $pubtid . '\')"
                            class="btn btn-outline-success btn-sm"
                            data-toggle="tooltip"
                            data-placement="bottom"
                            title="Editar publicaci??n">
                            <i class="far fa-edit"></i>
                            </button>
                        <button href="javascript:void(0)" onclick="notiPost(\'' . $pubtid . '\')"
                            class="btn btn-outline-warning btn-sm"
                            data-toggle="tooltip"
                            data-placement="bottom"
                            title="Mandar notificaciones">
                            <i class="far fa-bell"></i>
                            </button>
                <button href="javascript:void(0)" onclick="createPdfPub(\'' . $pubtid . '\')"
                             class="btn btn-outline-primary btn-sm"
                            data-toggle="tooltip"
                            data-placement="bottom"
                            title="Crear pdf">
                           <i  class="fa fa-file-pdf" aria-hidden="true"></i>
                            </button>
                        </div>';

                })
                ->rawColumns(['animalpub', 'titulopub', 'contenidopub', 'createdpub', 'action'])
                ->make(true);
        }

    }
    /**
     * @autor Alex vergara
     * @function Retorna la datatable de los animales
     * @param request
     * @return mixed
     */
    protected function tableAnimales(Request $request)
    {

        $allAnimales = ANIMales::all();

        $hash = new Hashids('', 10);

        if ($request->ajax()) {
            return Datatables::of($allAnimales)
                ->addIndexColumn()
                ->addColumn('animalname', function ($data) {
                    $desc = $data->getNombre();
                    $btn = '<div data-toggle="tooltip" data-placement="left" title="' . $desc . '">' . $desc . '</div>';
                    return $btn;
                })
                ->addColumn('especieAnimal', function ($data) {
                    $desc = $data->getEspecie();
                    $btn = '<div data-toggle="tooltip" data-placement="left" title="' . $desc . '">' . $desc . '</div>';
                    return $btn;
                })
                ->addColumn('dateAnimal', function ($data) {
                    $desc = $data->created_at;
                    $btn = '<div data-toggle="tooltip" data-placement="left" title="' . $desc . '">' . $desc . '</div>';
                    return $btn;
                })
                ->addColumn('action', function ($data) use ($hash) {
                    $Aniid = $hash->encode($data->getId());
                    return '<div>
                            <button href="javascript:void(0)" onclick="deleteAnimals(\'' . $Aniid . '\')"
                            class="btn btn-outline-danger btn-sm"
                            data-toggle="tooltip"
                            data-placement="bottom"
                            title="Eliminar Animal">
                            <i class="fa fa-trash"></i>
                            </button>
                <button href="javascript:void(0)" onclick="infoAnimals(\'' . $Aniid . '\')"
                            class="btn btn-outline-success btn-sm"
                            data-toggle="tooltip"
                            data-placement="bottom"
                            title="Editar publicaci??n">
                            <i class="far fa-edit"></i>
                            </button>
                 <button href="javascript:void(0)" onclick="createPdfAni(\'' . $Aniid . '\')"
                             class="btn btn-outline-primary btn-sm"
                            data-toggle="tooltip"
                            data-placement="bottom"
                            title="Crear pdf">
                           <i  class="fa fa-file-pdf" aria-hidden="true"></i>
                            </button>

                </div>';
                })
                ->rawColumns(['animalname', 'dateAnimal', 'especieAnimal', 'action'])
                ->make(true);
        }

    }

    /**
     * @autor Alex vergara
     * @function Retorna informaci??n para el preview que aparecera en el modal que se abrira
     * @param request
     * @return mixed
     */
    protected function infoPublications(Request $request)
    {

        $data = $request->all();
        try {
            $hash = new Hashids('', 10);
            $id = $hash->decode($data['code']);

            $InfoPublicaciones = PUBlicaciones::find($id[0]);
            return response()->json([
                "title" => $InfoPublicaciones->getTitle(),
                "descripcion" => $InfoPublicaciones->getDescrip(),
                "contenido" => $InfoPublicaciones->getDescrip(),
                "image" => $InfoPublicaciones->getImage(),
                "animal" => $InfoPublicaciones->getAnimal(),
            ]);


        } catch (\Exception $exception) {
            return response()->json([
                "error" => $exception->getMessage(),
            ]);
        }


    }

    protected function deleteEvento(Request $request)
    {
        $data = $request->all();
        $hash = new Hashids('', 10);
        $id = $hash->decode($data['code']);

        try {
            $evento = CATEventos::where('eve_eve', '=', $id[0])->first();
            $evento->delete();

            return response()->json(
                [
                    'success' => true,
                    'message' => 'Evento eliminado correctamente.'
                ]
            );
        } catch (\Exception $exception) {
            return response()->json(
                [
                    'success' => true,
                    'message' => 'Error, intentar m??s tarde.'
                ]
            );
        }

    }

    protected function editpubs(Request $request)
    {
        $rules = [
            'titlepubli' => ['required','min:1', 'max:80'],
            'decrippubli' => ['required','min:1', 'max:500'],
        ];
        $messages = [
            'titlepubli.required' => 'El t??tulo de la publicaci??n es requerido.',
            'titlepubli.min' => 'El titulo debe tener al menos un caracter.',
            'titlepubli.max' => 'El titulo no debe exceder de los 80 caracteres.',
            'decrippubli.required' => 'El contenido de la publicaci??n es requerido.',
            'condecrippublitenido.min' => 'El contenido debe tener al menos un caracter.',
            'decrippubli.max' => 'El titcontenidoulo no debe exceder de los 500 caracteres.'
        ];
        $validator = Validator::make($request->all(), $rules, $messages);
        if ($validator->fails()) {
            $messages = $validator->messages();
            return response()->json([
                'success' => false,
                'message' => $messages->first(),
            ]);
        }



        if($request->file('updaimagefile')){
            $rules2 = [
                'updaimagefile' => ['mimes:jpeg,png,jpg,svg|max:2048'],
            ];
            $messages2 = [
                'updaimagefile.max' => 'El archivo no debe ser superior a 2 megas'
            ];
            $validator2 = Validator::make($request->all(), $rules2, $messages2);
            if ($validator2->fails()) {
                $messagesF = $validator2->messages();
                return response()->json(
                    [
                        'success' => false,
                        'message' =>$messagesF->first(),
                    ]
                );
            }

            $file = $request->file('updaimagefile');
            $imageName = Str::random(10) . '.' . $file->getClientOriginalExtension();
            $file->move(public_path('images'), $imageName);
            $url = asset('images/' . $imageName);
        }else{
            $url = null;
        }

        try {
            $data = $request->all();

            $hash = new Hashids('', 10);
            $idpub = $hash->decode($data['id']);

            $UpdatPub = PUBlicaciones::where('pub_id', $idpub[0])->first();


            $UpdatPub->setTitle($data['titlepubli']);
            $UpdatPub->setDescrip($data['decrippubli']);
            $UpdatPub->setImage($url);
            $UpdatPub->save();

            return response()->json([
                'success' => true,
                'message' => "Se ha actulizado correctamente."
            ]);
        } catch (\Exception $exception) {
            return response()->json([
                'success' => false,
                'message' => $exception->getMessage()
            ]);
        }

    }

    protected function deletePub(Request $request)
    {
        $data = $request->all();
        $hash = new Hashids('', 10);
        $id = $hash->decode($data['code']);

        try {
            $evento = PUBlicaciones::where('pub_id', '=', $id[0])->first();
            $evento->delete();

            return response()->json(
                [
                    'success' => true,
                    'message' => 'Publicaci??n eliminada correctamente.'
                ]
            );
        } catch (\Exception $exception) {
            return response()->json(
                [
                    'success' => true,
                    'message' => 'Error, intentar m??s tarde.'
                ]
            );
        }
    }

    protected function infoAnimals(Request $request)
    {
        $data = $request->all();
        try {
            $hash = new Hashids('', 10);
            $id = $hash->decode($data['code']);
            $InfoAnimales = ANIMales::find($id[0]);

            return response()->json([
                "nombre" => $InfoAnimales->getNombre(),
                "especie" => $InfoAnimales->getEspecie(),
                "image" => $InfoAnimales->getImage(),
            ]);
        } catch (\Exception $exception) {
            return response()->json([
                "error" => $exception,
            ]);
        }

    }

    protected function editanimals(Request $request)
    {
        $rules = [
        'animalname' => ['required','min:1', 'max:50','regex:/^([A-Z??????????]{1}[a-z????????????]+[\s]*)+$/'],
        'especieanimal' => ['required','min:1', 'max:50'],
        ];
        $messages = [
            'animalname.min' => 'El nombre del animal debe tener al menos un caracter.',
            'animalname.max' => 'El nombre del animal no debe exceder de los 50 caracteres.',
            'animalname.regex' => 'El nombre del animal contiene caracteres no validos.',
            'especieanimal.min' => 'La especie del animal debe tener al menos un caracter.',
            'especieanimal.max' => 'La especie del animal no debe exceder de los 500 caracteres.'
        ];
        $validator = Validator::make($request->all(), $rules, $messages);
        if ($validator->fails()) {
            $messages = $validator->messages();
            return response()->json([
                'success' => false,
                'message' => $messages->first(),
            ]);
        }
        if($request->file('updafileAnimal')){
            $rules2 = [
                'updafileAnimal' => ['mimes:jpeg,png,jpg,svg|max:2048'],
            ];
            $messages2 = [
                'updafileAnimal.max' => 'El archivo no debe ser superior a 2 megas'
            ];
            $validator2 = Validator::make($request->all(), $rules2, $messages2);

            if ($validator2->fails()) {
                $messagesF = $validator2->messages();
                return response()->json(
                    [
                        'success' => false,
                        'message' =>$messagesF->first(),
                    ]
                );
            }

            $file = $request->file('updafileAnimal');
            $imageName = Str::random(10) . '.' . $file->getClientOriginalExtension();
            $file->move(public_path('images'), $imageName);
            $url = asset('images/' . $imageName);
        }else{
            $url = null;
        }
        try {
            $data = $request->all();
            $hash = new Hashids('', 10);
            $idAnimal = $hash->decode($data['id']);
            $UpdateA = ANIMales::where('an_id', $idAnimal)->first();
            $UpdateA->setNombre($data['animalname']);
            $UpdateA->setEspecie($data['especieanimal']);
            $UpdateA->setImage($url);
            $UpdateA->save();

            return response()->json([
                'success' => true,
                'message' => "Se ha actualizado correctamente."
            ]);
        } catch (\Exception $exception) {
            return response()->json([
                'success' => false,
                'message' => $exception->getMessage()
            ]);
        }
    }

    protected function deleteAnimals(Request $request)
    {
        $data = $request->all();
        $hash = new Hashids('', 10);
        $id = $hash->decode($data['code']);

        try {
            $Pubs = PUBlicaciones::where('pub_animal', '=', $id[0])->get();
            if (count($Pubs) > 0) {
                foreach ($Pubs as $post) {
                    $post->delete();
                }

            }
            $Animales = ANIMales::where('an_id', '=', $id[0])->first();
            $Animales->delete();
            return response()->json(
                [
                    'success' => true,
                    'message' => 'Animal eliminado correctamente.'
                ]
            );
        } catch (\Exception $exception) {
            return response()->json(
                [
                    'success' => true,
                    'message' => 'Error, intentar m??s tarde.'
                ]
            );
        }

    }

    protected function sentNofitication(Request $request)
    {
        $hash = new Hashids('', 10);
        $data = $request->all();
        $ideve = $hash->decode($data['code']);

        $Eventos = CATEventos::where('eve_eve', $ideve)->first();

        try {
            foreach (TOKEn::all() as $tokens) {



            //$token = "fQZNTjKkTmqjn7lDPdsrHL:APA91bEwGEVj8iwZ1Kv-ytbdaMdY5RCdvvOgpAa026GCjgl7B3q1aR21uzMbJvg9IJEddh1NkqptUEQhxPgzn25TN8Zk-E6Co7WhCBOWrcHqbZiu-sYs3N15rlYp5CxqBuR3YSFkZJev";
            $token = $tokens->token;
            $from = "AAAADULzWxo:APA91bFVKcx4b_AgNvEv8dLNMCxPUl5rssoIYAaHHrmaUew66Q8o1s3yY1MVHK4ZaW2z8vdjUYoPY1cJtR26Gqz7-BSv1Mi8Q-WVUbdPF6KDv7Z9kEAoDTpH6NY_oJDS9p_glGh5SWwg";
            $msg = array(
                'body' => $Eventos->getEveDescripcion(),
                'title' =>$Eventos->getEveNombre(),
                'receiver' => 'erw',
                'image' => $Eventos->getEveImage(),/*Default Icon*/
                'sound' => 'mySound'/*Default sound*/
            );

            $fields = array(
                'to' => $token,
                'notification' => $msg
            );

            $headers = array(
                'Authorization: key=' . $from,
                'Content-Type: application/json'
            );
            //#Send Reponse To FireBase Server
            $ch = curl_init();
            curl_setopt($ch, CURLOPT_URL, 'https://fcm.googleapis.com/fcm/send');
            curl_setopt($ch, CURLOPT_POST, true);
            curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
            curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($fields));
            curl_exec($ch);
            curl_close($ch);
            }
            return response()->json([
                'success' => true,
                'message' => "Se ha notificado correctamente."
            ]);
        }catch (\Exception $e)
        {
            return response()->json([
                'success' => true,
                'message' => $e->getMessage()
            ]);
        }

}

    protected function sentNofiticationPost(Request $request)
    {
        $hash = new Hashids('', 10);
        $data = $request->all();
        $ideve = $hash->decode($data['code']);

        $Eventos = PUBlicaciones::where('pub_id', $ideve)->first();


        try {
            foreach (TOKEn::all() as $tokens) {


                $token = $tokens->token;
                $from = "AAAADULzWxo:APA91bFVKcx4b_AgNvEv8dLNMCxPUl5rssoIYAaHHrmaUew66Q8o1s3yY1MVHK4ZaW2z8vdjUYoPY1cJtR26Gqz7-BSv1Mi8Q-WVUbdPF6KDv7Z9kEAoDTpH6NY_oJDS9p_glGh5SWwg";
                $msg = array(
                    'body' => $Eventos->getDescrip(),
                    'title' => $Eventos->getTitle(),
                    'receiver' => 'erw',
                    'image' => $Eventos->getImage(),/*Default Icon*/
                    'sound' => 'mySound'/*Default sound*/
                );

                $fields = array(
                    'to' => $token,
                    'notification' => $msg
                );

                $headers = array(
                    'Authorization: key=' . $from,
                    'Content-Type: application/json'
                );

                $ch = curl_init();
                curl_setopt($ch, CURLOPT_URL, 'https://fcm.googleapis.com/fcm/send');
                curl_setopt($ch, CURLOPT_POST, true);
                curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
                curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
                curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
                curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($fields));
                curl_exec($ch);
                curl_close($ch);
            }
            return response()->json([
                'success' => true,
                'message' => "Se ha notificado correctamente."
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => true,
                'message' => $e->getMessage()
            ]);
        }
    }


    }

