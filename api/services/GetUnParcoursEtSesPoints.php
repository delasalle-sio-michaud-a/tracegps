<?php

// Les paramètres doivent être passés par la méthode GET :
// http://<hébergeur>/tracegps/api/GetTousLesUtilisateurs?pseudo=callisto&mdp=13e3668bbee30b004380052b086457b014504b3e&lang=xml

// connexion du serveur web à la base MySQL
$dao = new DAO();

// Récupération des données transmises
$pseudo = (empty($dao->request['pseudo'])) ? "" : $dao->request['pseudo'];
$mdpSha1 = (empty($dao->request['mdp'])) ? "" : $dao->request['mdp'];
$idTrace = (empty($dao->request['idTrace'])) ? "" : $dao->request['idTrace'];
$lang = (empty($dao->request['lang'])) ? "" : $dao->request['lang'];

// "xml" par défaut si le paramètre lang est absent ou incorrect
if ($lang != "json")
    $lang = "xml";

    
    
    
    
    // initialisation du nombre de réponses
//    $nbReponses = 0;
    $lesTraces = array();
    
    // La méthode HTTP utilisée doit être GET
    if ($dao->getMethodeRequete() != "GET") 
    {
        $msg = "Erreur : méthode HTTP incorrecte.";
        $code_reponse = 406;
    } 
    
    else 
    {
        
            // Les paramètres doivent être présents
            if ($pseudo == "" || $mdpSha1 == "") 
            {
                $msg = "Erreur : données incomplètes.";
                $code_reponse = 400;
                
            } 
            
            else 
            {
                $niveauConnexion = $dao->getNiveauConnexion($pseudo, $mdpSha1);
                
                switch ($niveauConnexion) {
                    case 0:
                        $msg = "Erreur : authentification incorrecte.";
                        $code_reponse = 401;

                    case 1:
                        $msg = "Utilisateur authentifié.";
                        $code_reponse = 200;

                    case 2:
                        $msg = "Administrateur authentifié.";
                        $code_reponse = 200;
                        
                        if($dao->getUneTrace($idTrace) == null)
                        {
                            
                            $msg = "Erreur : Parcours inexistante.";
                            $code_reponse = 404;
                            
                        }
                        
                        
                        else
                        {
                            
                            if($pseudo->getUneTrace($idTrace))
                            {
                                
                                return $dao->getUneTrace($idTrace);
                                
                            }
                            
                            else 
                            {
                                                                
                                $msg = "Erreur : vous n'êtes pas autorisé par le propriétaire du parcours.";
                                $code_reponse = 401;
                                
                            }
                            
                            
                        }
                  }
               }      
            }
//        }
//    }
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    // ferme la connexion à MySQL :
    unset($dao);
    
    // création du flux en sortie
    if ($lang == "xml") {
        $content_type = "application/xml; charset=utf-8"; // indique le format XML pour la réponse
        $donnees = creerFluxXML($msg, $lesTraces);
    } else {
        $content_type = "application/json; charset=utf-8"; // indique le format Json pour la réponse
        $donnees = creerFluxJSON($msg, $lesTraces);
    }
    
    // envoi de la réponse HTTP
    $dao->envoyerReponse($code_reponse, $content_type, $donnees);
    
    // fin du programme (pour ne pas enchainer sur les 2 fonctions qui suivent)
    exit();
  