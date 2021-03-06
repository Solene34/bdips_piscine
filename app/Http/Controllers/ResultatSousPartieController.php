<?php

namespace App\Http\Controllers;

use Auth;
use Carbon\Carbon;
use App\Models\ResultatSousPartie;
use App\Models\Question;
use App\Models\Programmer;
use App\Models\Sujet;
use App\Models\Session;
use Illuminate\Http\Request;

class ResultatSousPartieController extends Controller
{
    //Redirige vers le questionnaire du toeic  
    public function index()
    {
        $id_sujet=request('id_sujet');
        $id_session=request('id_session');
	    $temps_debut=Session::hourSession($id_session);
        return view('questionnaire/quiz',['id_sujet' => $id_sujet,'id_session' => $id_session, 'temps_debut' => $temps_debut ]);
    }

    //Calcul du score total et des sous-parties pour le toeic passé par l'utilisateur
    public function result()
    {
        $id=Auth::user()->id;
        if($id != null){
            $id_session = request('id_session'); //à mettre dans le questionnaire
            $id_sujet = request('id_sujet');
            //récupération des rep de chaques sous parties
            for ($i=1;$i<=7;$i++){ 
                $res = 0;

                $subject_answers = Question::getAnswersFromSousPartie($i,$id_sujet); //id_sujet à remplacer
                $indice = Question::getFirstIdFromSousPartie($i);

                ${'user_answers'.$i}=[];

                //récupération des rep de l'utilisateur pour la sous partie courante ($i)
                for ($j=$indice;$j<$indice+count($subject_answers);$j++){
                    array_push(${'user_answers'.$i},request('result'.$j));
                }

                for ($k=0;$k<count(${'user_answers'.$i});$k++){

                    //on compare les réponse de l'utilisateur et du sujet
                    if($subject_answers[$k] == ${'user_answers'.$i}[$k]){
                            $res += 1;
                        }
                }

                $resultat = new ResultatSousPartie;
                $resultat->idSousPartie = $i;
                $resultat->idSession = $id_session;
                $resultat->idUtilisateur =$id;
                $resultat->scoreSousPartie = $res;
                $resultat -> save();
            }
            
            $score_list=ResultatSousPartie::getScoreListening($id_session,$id);
            $score_read=ResultatSousPartie::getScoreReading($id_session,$id);

            $score_final=$score_list+$score_read;

            return view('questionnaire.result_quiz',['score_final' => $score_final,'score_listening' => $score_list,'score_reading'=> $score_read]);
        } else {
            return view('questionnaire.quiz');
        }
    }
    
}
