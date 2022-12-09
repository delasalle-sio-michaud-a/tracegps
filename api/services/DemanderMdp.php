<?php 
// fichier :  api/services/DemanderMdp.php

$dao = new DAO();



// Récupération des données transmises
$pseudo = ( empty($this->request['pseudo'])) ? "" : $this->request['pseudo'];
$lang = ( empty($this->request['lang'])) ? "" : $this->request['lang'];
$nouveauMdp = ( empty($this->request['nouveauMdp'])) ? "" : $this->request['nouveauMdp'];

if ($lang != "json") $lang = "xml";

// Les paramètres doivent être présents
if ( $pseudo == "" || $lang == "") 
{
    $msg = "Erreur : données incomplètes.";
    $code_reponse = 400;
}
else 
{
    if (!($dao->existePseudoUtilisateur($pseudo))) 
    {
        $msg = 'Erreur : pseudo inexistant.';
        $code_reponse = 400;
    }
    else 
    {
        $ok = $dao->envoyerMdp ($pseudo, $nouveauMdp);
        if ( $ok ) 
        {
            $msg = "Vous allez recevoir un courriel de confirmation avec votre nouveau mot de passe.";
            $code_reponse = 200;
        }
        else 
        {
            $msg = "Enregistrement effectué ; l'envoi du courriel de confirmation a rencontré un problème.";
            $code_reponse = 500;              
        }
    }
}

// ferme la connexion à MySQL :
unset($dao);

// création du flux en sortie
if ($lang == "xml") {
    $content_type = "application/xml; charset=utf-8";      // indique le format XML pour la réponse
    $donnees = creerFluxXML ($msg);
}
else {
    $content_type = "application/json; charset=utf-8";      // indique le format Json pour la réponse
    $donnees = creerFluxJSON ($msg);
}

// envoi de la réponse HTTP
$this->envoyerReponse($code_reponse, $content_type, $donnees);

// fin du programme (pour ne pas enchainer sur les 2 fonctions qui suivent)
exit;

// ================================================================================================

// création du flux XML en sortie
function creerFluxXML($msg)
{
    /* Exemple de code XML
     <?xml version="1.0" encoding="UTF-8"?>
     <!--Service web ChangerDeMdp - BTS SIO - Lycée De La Salle - Rennes-->
     <data>
     <reponse>Erreur : authentification incorrecte.</reponse>
     </data>
     */
    
    // crée une instance de DOMdocument (DOM : Document Object Model)
    $doc = new DOMDocument();
    
    // specifie la version et le type d'encodage
    $doc->version = '1.0';
    $doc->encoding = 'UTF-8';
    
    // crée un commentaire et l'encode en UTF-8
    $elt_commentaire = $doc->createComment('Service web ChangerDeMdp - BTS SIO - Lycée De La Salle - Rennes');
    // place ce commentaire à la racine du document XML
    $doc->appendChild($elt_commentaire);
    
    // crée l'élément 'data' à la racine du document XML
    $elt_data = $doc->createElement('data');
    $doc->appendChild($elt_data);
    
    // place l'élément 'reponse' juste après l'élément 'data'
    $elt_reponse = $doc->createElement('reponse', $msg);
    $elt_data->appendChild($elt_reponse);
    
    // Mise en forme finale
    $doc->formatOutput = true;
    
    // renvoie le contenu XML
    return $doc->saveXML();
}

// ================================================================================================

// création du flux JSON en sortie
function creerFluxJSON($msg)
{
    /* Exemple de code JSON
     {
     "data": {
     "reponse": "Erreur : authentification incorrecte."
     }
     }
     */
    
    // construction de l'élément "data"
    $elt_data = ["reponse" => $msg];
    
    // construction de la racine
    $elt_racine = ["data" => $elt_data];
    
    // retourne le contenu JSON (l'option JSON_PRETTY_PRINT gère les sauts de ligne et l'indentation)
    return json_encode($elt_racine, JSON_PRETTY_PRINT);
}

// ================================================================================================
?>