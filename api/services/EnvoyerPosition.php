<?php
include_once ('../modele/DAO.class.php');
//connection à la base de données
$dao= new DAO();

//récupération des données 
$pseudo = (empty($this->request['pseudo'])) ? "" : $this->request['pseudo'];
$mdpSha1 = (empty($this->request['mdp'])) ? "" : $this->request['mdp'];
$idTrace = (empty($this->request['idTrace']))?"": $this->request['idTrace'];
$dateHeure = (empty($this->request['dateHeure']))?"": $this->request['dateHeure'];
$latitude = (empty($this->request['latitude']))?"": $this->request['latitude'];
$longitude = (empty($this->request['longitude']))?"": $this->request['longitude'];
$altitude = (empty($this->request['altitude']))?"": $this->request['altitude'];
$rythmeCardio = (empty($this->request['rythmeCardio']))?"": $this->request['rythmeCardio'];
$lang = (empty($this->request['lang'])) ? "" : $this->request['lang'];


if ($lang != "json") $lang = "xml";


//echo $unId;
//$unPointDeTrace = new PointDeTrace($idTrace,($dao->getUneTrace($idTrace)->getId()+1), $latitude, $longitude, $altitude, $dateHeure, $rythmeCardio,$dao->getUneTrace($idTrace)->getDureeEnSecondes() ,$$dao->getUneTrace($idTrace)->getDistanceTotale(), $dao->getUneTrace($idTrace)->getVitesseMoyenne());
/*if($unPointDeTrace){
    return "point crée";
}
else{
    return "Erreur";
}
if ($lang != "json")
    $lang = "xml";*/
    
    // initialisation du nombre de réponses
   
    /*if ($this->getMethodeRequete() != "POST") {
        $msg = "Erreur : méthode HTTP incorrecte.";
        $code_reponse = 406;
    } 
    else {*/
$unPointDeTrace=null;

        // Les paramètres doivent être présents
if ($pseudo == "" || $mdpSha1 == "" || $idTrace == "" || $dateHeure=="" || $latitude=="" || $longitude=="" || $altitude=="" || $rythmeCardio=="") 
{           
    $msg = "Erreur : données incomplètes.";
}
 else {
        if ($dao->getNiveauConnexion($pseudo, $mdpSha1) == 0) 
        {
                $msg = "Erreur : authentification incorrecte.";                
        } 
        else{
            $uneTrace = $dao->getUneTrace($idTrace);
            if ($uneTrace==null)
                {
                    $msg = "Erreur : le numéro de trace n'existe pas.";
                }
                else
                {
                    $idPseudo = $dao->getUnUtilisateur($pseudo)->getId();
                    $idUtilisateur= $uneTrace->getIdUtilisateur();
                    
                    if($idPseudo!=$idUtilisateur){ 
                        $msg = "Erreur : le numéro de trace ne correspond pas à cet utilisateur.";
                       
                    }
                    else
                    {
                        if($dao->getUneTrace($idTrace)->getTerminee()==true)
                        {
                            $msg = "Erreur : la trace est déjà terminée";
                           
                        }
                        else
                        {
                        
                         $unPointDeTrace = new PointDeTrace($idTrace, ($dao->getUneTrace($idTrace)->getId()+1), $latitude, $longitude, $altitude, $dateHeure, $rythmeCardio,$dao->getUneTrace($idTrace)->getDureeEnSecondes(),$dao->getUneTrace($idTrace)->getDistanceTotale(), $dao->getUneTrace($idTrace)->getVitesseMoyenne());
                         $ok = $dao->creerUnPointDeTrace($unPointDeTrace);
                         
                         //PointDeTrace::getId(); 
                         if ( !$ok ) {
                             
                             $msg = "Erreur lors de la création";}
                          
                         $msg = "Point crée";

                         
                        }
                   }
                }
            } 
        }
        
        // création du flux en sortie
        /*if ($unPointDeTrace){
            return true;
        }
        return false;*/
        unset($dao);
        
        if ($lang == "xml") {
            $content_type = "application/xml; charset=utf-8";      // indique le format XML pour la réponse
            $donnees = creerFluxXML ($msg, $unPointDeTrace);
        }
        else {
            $content_type = "application/json; charset=utf-8";      // indique le format Json pour la réponse
            $donnees = creerFluxJSON ($msg, $unPointDeTrace);
        }
        
        // envoi de la réponse HTTP
        //$this->envoyerReponse($code_reponse, $content_type, $donnees);
        
        // fin du programme (pour ne pas enchainer sur les 2 fonctions qui suivent)
        exit;
        
        
function creerFluxXML($msg, $unPointDeTrace)
        {
            /* Exemple de code XML
             <?xml version="1.0" encoding="UTF-8"?>
                <data>
                <reponse>............. (message retourné par le service web) ...............</reponse>
                <donnees/>
                </data>
                
                <?xml version="1.0" encoding="UTF-8"?>
                  <data>
                    <reponse>Point créé.</reponse>
                    <donnees>
                    <id>6</id>
                    </donnees>
                  </data>
             */
            
            // crée une instance de DOMdocument (DOM : Document Object Model)
            $doc = new DOMDocument();
            
            // specifie la version et le type d'encodage
            $doc->version = '1.0';
            $doc->encoding = 'UTF-8';
            
            // crée un commentaire et l'encode en UTF-8
            $elt_commentaire = $doc->createComment('Service web GetTousLesUtilisateurs - BTS SIO - Lycée De La Salle - Rennes');
            // place ce commentaire à la racine du document XML
            $doc->appendChild($elt_commentaire);
            
            // crée l'élément 'data' à la racine du document XML
            $elt_data = $doc->createElement('data');
            $doc->appendChild($elt_data);
            
            // place l'élément 'reponse' dans l'élément 'data'
            $elt_reponse = $doc->createElement('reponse', $msg);
            $elt_data->appendChild($elt_reponse);
            
            // place l'élément 'donnees' dans l'élément 'data'
            $elt_donnees = $doc->createElement('donnees');
            $elt_data->appendChild($elt_donnees);
            
            if($unPointDeTrace!=null)
            {// place l'élément 'lesUtilisateurs' dans l'élément 'donnees'
            $elt_id= $doc->createElement('id', $unPointDeTrace->getId());
            $elt_donnees->appendChild($elt_id);
            }
           
            
                
            // Mise en forme finale
            $doc->formatOutput = true;
            
            // renvoie le contenu XML
            echo $doc->saveXML();
            return ;
        }
        
function creerFluxJSON($msg, $unPointDeTrace)
        {
            /*{
                "data": {
                    "reponse": "............. (message retourné par le service web) ...............",
                    "donnees": [ ]
            }
            }
            
}*/
            $elt_data = ["reponse" => $msg];
            
            if ($unPointDeTrace != null){
                $elt_id = ["id" => $unPointDeTrace->getId()];
            // construction de l'élément "data"
                $elt_data = ["reponse" => $msg, "donnees" => $elt_id];
            }
            
            // construction de la racine
            $elt_racine = ["data" => $elt_data];
            
            // retourne le contenu JSON (l'option JSON_PRETTY_PRINT gère les sauts de ligne et l'indentation)
            echo json_encode($elt_racine, JSON_PRETTY_PRINT);
            return ;
}
            
            