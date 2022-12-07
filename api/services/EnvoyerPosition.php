<?php
//connection à la base de données
$dao = new DAO();

//récupération des données 
$pseudo = (empty($dao->request['pseudo'])) ? "" : $dao->request['pseudo'];
$mdpSha1 = (empty($dao->request['mdp'])) ? "" : $dao->request['mdp'];
$idTrace = (empty($dao->request['idTrace']))?"": $dao->request['idTrace'];
$dateHeure = (empty($dao->request['dateHeure']))?"": $dao->request['dateHeure'];
$latitude = (empty($dao->request['latitude']))?"": $dao->request['latitude'];
$longitude = (empty($dao->request['longitude']))?"": $dao->request['longitude'];
$altitude = (empty($dao->request['altitude']))?"": $dao->request['altitude'];
$rythmeCardio = (empty($dao->request['rythmeCardio']))?"": $dao->request['rythmeCardio'];
$lang = (empty($dao->request['lang'])) ? "" : $dao->request['lang'];

if ($lang != "json")
    $lang = "xml";
    
    // initialisation du nombre de réponses
   
    /*if ($dao->getMethodeRequete() != "POST") {
        $msg = "Erreur : méthode HTTP incorrecte.";
        $code_reponse = 406;
    } 
    else {*/
        // Les paramètres doivent être présents
        if ($pseudo == "" || $mdpSha1 == "" || $idTrace = "" || $dateHeure="" || $latitude="" || $longitude="" || $altitude="" || $rythmeCardio="") {
            $msg = "Erreur : données incomplètes.";
            $code_reponse = 400;
        }
        else {
            if ($dao->getNiveauConnexion($pseudo, $mdpSha1) == 0) {
                $msg = "Erreur : authentification incorrecte.";
                $code_reponse = 401;
            } 
             else{
                if ($dao->getUneTrace($idTrace)==false){
                    $msg = "Erreur : le numéro de trace n'existe pas.";
                    $code_reponse = 401;
                }
                else{
                    
                    if($dao->getLesTraces($pseudo->getId())!=$idTrace){ 
                        $msg = "Erreur : le numéro de trace ne correspond pas à cet utilisateur.";
                        $code_reponse = 401;
                    }
                    else
                    {
                        if($dao->TermineeUneTrace($idTrace)==true)
                        {
                            $msg = "Erreur : la trace est déjà terminée";
                            $code_reponse = 401;
                        }
                        else
                        {
                            $unPointDeTrace = new PointDeTrace($idTrace,(PointDeTrace::getId()+1), $latitude, $longitude, $altitude, $dateHeure, $rythmeCardio, PointDeTrace::getDistanceCumule(), PointDeTrace::getUneVitesse());
                         $ok = $dao->creerUnPointDeTrace($unPointDeTrace);
                         //PointDeTrace::getId(); 
                         $msg = "Point crée";

                         
                        }
                   }
                }
            }
            
        }
    
        
        function creerFluxXML($msg, $lesUtilisateurs)
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
            
            // traitement des utilisateurs
            if (sizeof($lesUtilisateurs) > 0) {
                // place l'élément 'donnees' dans l'élément 'data'
                $elt_donnees = $doc->createElement('donnees');
                $elt_data->appendChild($elt_donnees);
                
                // place l'élément 'lesUtilisateurs' dans l'élément 'donnees'
                $elt_lesUtilisateurs = $doc->createElement('lesUtilisateurs');
                $elt_donnees->appendChild($elt_lesUtilisateurs);
                
                foreach ($lesUtilisateurs as $unUtilisateur)
                {
                    // crée un élément vide 'utilisateur'
                    $elt_idPoint= $doc->createElement('utilisateur');
                    // place l'élément 'utilisateur' dans l'élément 'lesUtilisateurs'
                    $elt_lesUtilisateurs->appendChild($elt_idPoint);
                    
                    }
                }
            }
            // Mise en forme finale
            $doc->formatOutput = true;
            
            // renvoie le contenu XML
            return $doc->saveXML();
        }