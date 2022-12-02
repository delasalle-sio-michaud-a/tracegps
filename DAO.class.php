<?php
// Projet TraceGPS
// fichier : modele/DAO.class.php   (DAO : Data Access Object)
// Rôle : fournit des méthodes d'accès à la bdd tracegps (projet TraceGPS) au moyen de l'objet PDO
// modifié par dP le 12/8/2021

// liste des méthodes déjà développées (dans l'ordre d'apparition dans le fichier) :

// __construct() : le constructeur crée la connexion $cnx à la base de données
// __destruct() : le destructeur ferme la connexion $cnx à la base de données
// getNiveauConnexion($login, $mdp) : fournit le niveau (0, 1 ou 2) d'un utilisateur identifié par $login et $mdp
// existePseudoUtilisateur($pseudo) : fournit true si le pseudo $pseudo existe dans la table tracegps_utilisateurs, false sinon
// getUnUtilisateur($login) : fournit un objet Utilisateur à partir de $login (son pseudo ou son adresse mail)
// getTousLesUtilisateurs() : fournit la collection de tous les utilisateurs (de niveau 1)
// creerUnUtilisateur($unUtilisateur) : enregistre l'utilisateur $unUtilisateur dans la bdd
// modifierMdpUtilisateur($login, $nouveauMdp) : enregistre le nouveau mot de passe $nouveauMdp de l'utilisateur $login daprès l'avoir hashé en SHA1
// supprimerUnUtilisateur($login) : supprime l'utilisateur $login (son pseudo ou son adresse mail) dans la bdd, ainsi que ses traces et ses autorisations
// envoyerMdp($login, $nouveauMdp) : envoie un mail à l'utilisateur $login avec son nouveau mot de passe $nouveauMdp

// liste des méthodes restant à développer :

// existeAdrMailUtilisateur($adrmail) : fournit true si l'adresse mail $adrMail existe dans la table tracegps_utilisateurs, false sinon
// getLesUtilisateursAutorises($idUtilisateur) : fournit la collection  des utilisateurs (de niveau 1) autorisés à suivre l'utilisateur $idUtilisateur
// getLesUtilisateursAutorisant($idUtilisateur) : fournit la collection  des utilisateurs (de niveau 1) autorisant l'utilisateur $idUtilisateur à voir leurs parcours
// autoriseAConsulter($idAutorisant, $idAutorise) : vérifie que l'utilisateur $idAutorisant) autorise l'utilisateur $idAutorise à consulter ses traces
// creerUneAutorisation($idAutorisant, $idAutorise) : enregistre l'autorisation ($idAutorisant, $idAutorise) dans la bdd
// supprimerUneAutorisation($idAutorisant, $idAutorise) : supprime l'autorisation ($idAutorisant, $idAutorise) dans la bdd
// getLesPointsDeTrace($idTrace) : fournit la collection des points de la trace $idTrace
// getUneTrace($idTrace) : fournit un objet Trace à partir de identifiant $idTrace
// getToutesLesTraces() : fournit la collection de toutes les traces
// getMesTraces($idUtilisateur) : fournit la collection des traces de l'utilisateur $idUtilisateur
// getLesTracesAutorisees($idUtilisateur) : fournit la collection des traces que l'utilisateur $idUtilisateur a le droit de consulter
// creerUneTrace(Trace $uneTrace) : enregistre la trace $uneTrace dans la bdd
// terminerUneTrace($idTrace) : enregistre la fin de la trace d'identifiant $idTrace dans la bdd ainsi que la date de fin
// supprimerUneTrace($idTrace) : supprime la trace d'identifiant $idTrace dans la bdd, ainsi que tous ses points
// creerUnPointDeTrace(PointDeTrace $unPointDeTrace) : enregistre le point $unPointDeTrace dans la bdd


// certaines méthodes nécessitent les classes suivantes :
include_once ('modele/Utilisateur.class.php');
include_once ('modele/Trace.class.php');
include_once ('modele/PointDeTrace.class.php');
include_once ('modele/Point.class.php');
include_once ('modele/Outils.class.php');

// inclusion des paramètres de l'application
include_once ('parametres.php');

// début de la classe DAO (Data Access Object)
class DAO
{
    // ------------------------------------------------------------------------------------------------------
    // ---------------------------------- Membres privés de la classe ---------------------------------------
    // ------------------------------------------------------------------------------------------------------

    private $cnx;				// la connexion Ã  la base de données
    
    // ------------------------------------------------------------------------------------------------------
    // ---------------------------------- Constructeur et destructeur ---------------------------------------
    // ------------------------------------------------------------------------------------------------------
    public function __construct() {
        global $PARAM_HOTE, $PARAM_PORT, $PARAM_BDD, $PARAM_USER, $PARAM_PWD;
        try
        {	$this->cnx = new PDO ("mysql:host=" . $PARAM_HOTE . ";port=" . $PARAM_PORT . ";dbname=" . $PARAM_BDD,
            $PARAM_USER,
            $PARAM_PWD);
        return true;
        }
        catch (Exception $ex)
        {	echo ("Echec de la connexion a la base de donnees <br>");
        echo ("Erreur numero : " . $ex->getCode() . "<br />" . "Description : " . $ex->getMessage() . "<br>");
        echo ("PARAM_HOTE = " . $PARAM_HOTE);
        return false;
        }
    }
    
    public function __destruct() {
        // ferme la connexion Ã  MySQL :
        unset($this->cnx);
    }
    
    // ------------------------------------------------------------------------------------------------------
    // -------------------------------------- MÃ©thodes d'instances ------------------------------------------
    // ------------------------------------------------------------------------------------------------------
    
    // fournit le niveau (0, 1 ou 2) d'un utilisateur identifiÃ© par $pseudo et $mdpSha1
    // cette fonction renvoie un entier :
    //     0 : authentification incorrecte
    //     1 : authentification correcte d'un utilisateur (pratiquant ou personne autorisÃ©e)
    //     2 : authentification correcte d'un administrateur
    // modifiÃ© par Jim le 11/1/2018
    public function getNiveauConnexion($pseudo, $mdpSha1) {
        // prÃ©paration de la requÃªte de recherche
        $txt_req = "Select niveau from tracegps_utilisateurs";
        $txt_req .= " where pseudo = :pseudo";
        $txt_req .= " and mdpSha1 = :mdpSha1";
        $req = $this->cnx->prepare($txt_req);
        // liaison de la requÃªte et de ses paramÃ¨tres
        $req->bindValue("pseudo", $pseudo, PDO::PARAM_STR);
        $req->bindValue("mdpSha1", $mdpSha1, PDO::PARAM_STR);
        // extraction des donnÃ©es
        $req->execute();
        $uneLigne = $req->fetch(PDO::FETCH_OBJ);
        // traitement de la rÃ©ponse
        $reponse = 0;
        if ($uneLigne) {
        	$reponse = $uneLigne->niveau;
        }
        // libÃ¨re les ressources du jeu de donnÃ©es
        $req->closeCursor();
        // fourniture de la rÃ©ponse
        return $reponse;
    }
    
    
    // fournit true si le pseudo $pseudo existe dans la table tracegps_utilisateurs, false sinon
    // modifiÃ© par Jim le 27/12/2017
    public function existePseudoUtilisateur($pseudo) {
        // prÃ©paration de la requÃªte de recherche
        $txt_req = "Select count(*) from tracegps_utilisateurs where pseudo = :pseudo";
        $req = $this->cnx->prepare($txt_req);
        // liaison de la requÃªte et de ses paramÃ¨tres
        $req->bindValue("pseudo", $pseudo, PDO::PARAM_STR);
        // exÃ©cution de la requÃªte
        $req->execute();
        $nbReponses = $req->fetchColumn(0);
        // libÃ¨re les ressources du jeu de donnÃ©es
        $req->closeCursor();
        
        // fourniture de la rÃ©ponse
        if ($nbReponses == 0) {
            return false;
        }
        else {
            return true;
        }
    }
    
    
    // fournit un objet Utilisateur Ã  partir de son pseudo $pseudo
    // fournit la valeur null si le pseudo n'existe pas
    // modifiÃ© par Jim le 9/1/2018
    public function getUnUtilisateur($pseudo) {
        // prÃ©paration de la requÃªte de recherche
        $txt_req = "Select id, pseudo, mdpSha1, adrMail, numTel, niveau, dateCreation, nbTraces, dateDerniereTrace";
        $txt_req .= " from tracegps_vue_utilisateurs";
        $txt_req .= " where pseudo = :pseudo";
        $req = $this->cnx->prepare($txt_req);
        // liaison de la requÃªte et de ses paramÃ¨tres
        $req->bindValue("pseudo", $pseudo, PDO::PARAM_STR);
        // extraction des donnÃ©es
        $req->execute();
        $uneLigne = $req->fetch(PDO::FETCH_OBJ);
        // libÃ¨re les ressources du jeu de donnÃ©es
        $req->closeCursor();
        
        // traitement de la rÃ©ponse
        if ( ! $uneLigne) {
            return null;
        }
        else {
            // crÃ©ation d'un objet Utilisateur
            $unId = utf8_encode($uneLigne->id);
            $unPseudo = utf8_encode($uneLigne->pseudo);
            $unMdpSha1 = utf8_encode($uneLigne->mdpSha1);
            $uneAdrMail = utf8_encode($uneLigne->adrMail);
            $unNumTel = utf8_encode($uneLigne->numTel);
            $unNiveau = utf8_encode($uneLigne->niveau);
            $uneDateCreation = utf8_encode($uneLigne->dateCreation);
            $unNbTraces = utf8_encode($uneLigne->nbTraces);
            $uneDateDerniereTrace = utf8_encode($uneLigne->dateDerniereTrace);
            
            $unUtilisateur = new Utilisateur($unId, $unPseudo, $unMdpSha1, $uneAdrMail, $unNumTel, $unNiveau, $uneDateCreation, $unNbTraces, $uneDateDerniereTrace);
            return $unUtilisateur;
        }
    }
    
    
    // fournit la collection  de tous les utilisateurs (de niveau 1)
    // le rÃ©sultat est fourni sous forme d'une collection d'objets Utilisateur
    // modifiÃ© par Jim le 27/12/2017
    public function getTousLesUtilisateurs() {
        // prÃ©paration de la requÃªte de recherche
        $txt_req = "Select id, pseudo, mdpSha1, adrMail, numTel, niveau, dateCreation, nbTraces, dateDerniereTrace";
        $txt_req .= " from tracegps_vue_utilisateurs";
        $txt_req .= " where niveau = 1";
        $txt_req .= " order by pseudo";
        
        $req = $this->cnx->prepare($txt_req);
        // extraction des donnÃ©es
        $req->execute();
        $uneLigne = $req->fetch(PDO::FETCH_OBJ);
        
        // construction d'une collection d'objets Utilisateur
        $lesUtilisateurs = array();
        // tant qu'une ligne est trouvÃ©e :
        while ($uneLigne) {
            // crÃ©ation d'un objet Utilisateur
            $unId = utf8_encode($uneLigne->id);
            $unPseudo = utf8_encode($uneLigne->pseudo);
            $unMdpSha1 = utf8_encode($uneLigne->mdpSha1);
            $uneAdrMail = utf8_encode($uneLigne->adrMail);
            $unNumTel = utf8_encode($uneLigne->numTel);
            $unNiveau = utf8_encode($uneLigne->niveau);
            $uneDateCreation = utf8_encode($uneLigne->dateCreation);
            $unNbTraces = utf8_encode($uneLigne->nbTraces);
            $uneDateDerniereTrace = utf8_encode($uneLigne->dateDerniereTrace);
            
            $unUtilisateur = new Utilisateur($unId, $unPseudo, $unMdpSha1, $uneAdrMail, $unNumTel, $unNiveau, $uneDateCreation, $unNbTraces, $uneDateDerniereTrace);
            // ajout de l'utilisateur Ã  la collection
            $lesUtilisateurs[] = $unUtilisateur;
            // extrait la ligne suivante
            $uneLigne = $req->fetch(PDO::FETCH_OBJ);
        }
        // libÃ¨re les ressources du jeu de donnÃ©es
        $req->closeCursor();
        // fourniture de la collection
        return $lesUtilisateurs;
    }

    
    // enregistre l'utilisateur $unUtilisateur dans la bdd
    // fournit true si l'enregistrement s'est bien effectuÃ©, false sinon
    // met Ã  jour l'objet $unUtilisateur avec l'id (auto_increment) attribuÃ© par le SGBD
    // modifiÃ© par Jim le 9/1/2018
    public function creerUnUtilisateur($unUtilisateur) {
        // on teste si l'utilisateur existe dÃ©jÃ 
        if ($this->existePseudoUtilisateur($unUtilisateur->getPseudo())) return false;
        
        // prÃ©paration de la requÃªte
        $txt_req1 = "insert into tracegps_utilisateurs (pseudo, mdpSha1, adrMail, numTel, niveau, dateCreation)";
        $txt_req1 .= " values (:pseudo, :mdpSha1, :adrMail, :numTel, :niveau, :dateCreation)";
        $req1 = $this->cnx->prepare($txt_req1);
        // liaison de la requÃªte et de ses paramÃ¨tres
        $req1->bindValue("pseudo", utf8_decode($unUtilisateur->getPseudo()), PDO::PARAM_STR);
        $req1->bindValue("mdpSha1", utf8_decode(sha1($unUtilisateur->getMdpsha1())), PDO::PARAM_STR);
        $req1->bindValue("adrMail", utf8_decode($unUtilisateur->getAdrmail()), PDO::PARAM_STR);
        $req1->bindValue("numTel", utf8_decode($unUtilisateur->getNumTel()), PDO::PARAM_STR);
        $req1->bindValue("niveau", utf8_decode($unUtilisateur->getNiveau()), PDO::PARAM_INT);
        $req1->bindValue("dateCreation", utf8_decode($unUtilisateur->getDateCreation()), PDO::PARAM_STR);
        // exÃ©cution de la requÃªte
        $ok = $req1->execute();
        // sortir en cas d'Ã©chec
        if ( ! $ok) { return false; }
        
        // recherche de l'identifiant (auto_increment) qui a Ã©tÃ© attribuÃ© Ã  la trace
        $unId = $this->cnx->lastInsertId();
        $unUtilisateur->setId($unId);
        return true;
    }
    
    
    // enregistre le nouveau mot de passe $nouveauMdp de l'utilisateur $pseudo daprÃ¨s l'avoir hashÃ© en SHA1
    // fournit true si la modification s'est bien effectuÃ©e, false sinon
    // modifiÃ© par Jim le 9/1/2018
    public function modifierMdpUtilisateur($pseudo, $nouveauMdp) {
        // prÃ©paration de la requÃªte
        $txt_req = "update tracegps_utilisateurs set mdpSha1 = :nouveauMdp";
        $txt_req .= " where pseudo = :pseudo";
        $req = $this->cnx->prepare($txt_req);
        // liaison de la requÃªte et de ses paramÃ¨tres
        $req->bindValue("nouveauMdp", sha1($nouveauMdp), PDO::PARAM_STR);
        $req->bindValue("pseudo", $pseudo, PDO::PARAM_STR);
        // exÃ©cution de la requÃªte
        $ok = $req->execute();
        return $ok;
    }
    
    
    // supprime l'utilisateur $pseudo dans la bdd, ainsi que ses traces et ses autorisations
    // fournit true si l'effacement s'est bien effectuÃ©, false sinon
    // modifiÃ© par Jim le 9/1/2018
    public function supprimerUnUtilisateur($pseudo) {
        $unUtilisateur = $this->getUnUtilisateur($pseudo);
        if ($unUtilisateur == null) {
            return false;
        }
        else {
            $idUtilisateur = $unUtilisateur->getId();
            
            // suppression des traces de l'utilisateur (et des points correspondants)
            $lesTraces = $this->getLesTraces($idUtilisateur);
            foreach ($lesTraces as $uneTrace) {
                $this->supprimerUneTrace($uneTrace->getId());
            }
            
            // prÃ©paration de la requÃªte de suppression des autorisations
            $txt_req1 = "delete from tracegps_autorisations" ;
            $txt_req1 .= " where idAutorisant = :idUtilisateur or idAutorise = :idUtilisateur";
            $req1 = $this->cnx->prepare($txt_req1);
            // liaison de la requÃªte et de ses paramÃ¨tres
            $req1->bindValue("idUtilisateur", utf8_decode($idUtilisateur), PDO::PARAM_INT);
            // exÃ©cution de la requÃªte
            $ok = $req1->execute();
            
            // prÃ©paration de la requÃªte de suppression de l'utilisateur
            $txt_req2 = "delete from tracegps_utilisateurs" ;
            $txt_req2 .= " where pseudo = :pseudo";
            $req2 = $this->cnx->prepare($txt_req2);
            // liaison de la requÃªte et de ses paramÃ¨tres
            $req2->bindValue("pseudo", utf8_decode($pseudo), PDO::PARAM_STR);
            // exÃ©cution de la requÃªte
            $ok = $req2->execute();
            return $ok;
        }
    }
    
    
    // envoie un mail Ã  l'utilisateur $pseudo avec son nouveau mot de passe $nouveauMdp
    // retourne true si envoi correct, false en cas de problÃ¨me d'envoi
    // modifiÃ© par Jim le 9/1/2018
    public function envoyerMdp($pseudo, $nouveauMdp) {
        global $ADR_MAIL_EMETTEUR;
        // si le pseudo n'est pas dans la table tracegps_utilisateurs :
        if ( $this->existePseudoUtilisateur($pseudo) == false ) return false;
        
        // recherche de l'adresse mail
        $adrMail = $this->getUnUtilisateur($pseudo)->getAdrMail();
        
        // envoie un mail Ã  l'utilisateur avec son nouveau mot de passe
        $sujet = "Modification de votre mot de passe d'accÃ¨s au service TraceGPS";
        $message = "Cher(chÃ¨re) " . $pseudo . "\n\n";
        $message .= "Votre mot de passe d'accÃ¨s au service service TraceGPS a Ã©tÃ© modifiÃ©.\n\n";
        $message .= "Votre nouveau mot de passe est : " . $nouveauMdp ;
        $ok = Outils::envoyerMail ($adrMail, $sujet, $message, $ADR_MAIL_EMETTEUR);
        return $ok;
    }
    
    
    // Le code restant Ã  dÃ©velopper va Ãªtre rÃ©parti entre les membres de l'Ã©quipe de dÃ©veloppement.
    // Afin de limiter les conflits avec GitHub, il est dÃ©cidÃ© d'attribuer une zone de ce fichier Ã  chaque dÃ©veloppeur.
    // DÃ©veloppeur 1 : lignes 350 Ã  549
    // DÃ©veloppeur 2 : lignes 550 Ã  749
    // DÃ©veloppeur 3 : lignes 750 Ã  949
    // DÃ©veloppeur 4 : lignes 950 Ã  1150
    
    // Quelques conseils pour le travail collaboratif :
    // avant d'attaquer un cycle de dÃ©veloppement (dÃ©but de sÃ©ance, nouvelle méthode, ...), faites un Pull pour rÃ©cupÃ©rer 
    // la derniÃ¨re version du fichier.
    // AprÃ¨s avoir testÃ© et validÃ© une mÃ©thode, faites un commit et un push pour transmettre cette version aux autres dÃ©veloppeurs.
    
    
    
    
    
    // --------------------------------------------------------------------------------------
    // début de la zone attribuée au développeur 1 (delasalle-sio-michaud-a) : lignes 350 à 549
    // --------------------------------------------------------------------------------------
   
    
    public function getLesUtilisateursAutorises($idUtilisateur) {
        // préparation de la requête de recherche
        $recupAutorise = "Select id, pseudo, mdpSha1, adrMail, numTel, niveau, dateCreation, nbTraces, dateDerniereTrace";
        $recupAutorise .= " from tracegps_vue_utilisateurs";
        $recupAutorise .= " where niveau = 1";
        $recupAutorise .= "  AND id IN ( SELECT idAutorise FROM tracegps_autorisations WHERE idAutorisant = :idUtilisateur)";
        $recupAutorise .= " order by pseudo";
        
        $req = $this->cnx->prepare($recupAutorise);
        $req->bindValue("idUtilisateur", $idUtilisateur, PDO::PARAM_STR);
        $req->execute();
        $uneLigne = $req->fetch(PDO::FETCH_OBJ);
        
        $lesAutorises = array();
        
        // traitement de la réponse
        while($uneLigne)
        {
            // création d'un objet Utilisateur
            $unId = utf8_encode($uneLigne->id);
            $unPseudo = utf8_encode($uneLigne->pseudo);
            $unMdpSha1 = utf8_encode($uneLigne->mdpSha1);
            $uneAdrMail = utf8_encode($uneLigne->adrMail);
            $unNumTel = utf8_encode($uneLigne->numTel);
            $unNiveau = utf8_encode($uneLigne->niveau);
            $uneDateCreation = utf8_encode($uneLigne->dateCreation);
            $unNbTraces = utf8_encode($uneLigne->nbTraces);
            $uneDateDerniereTrace = utf8_encode($uneLigne->dateDerniereTrace);
            
            $unUtilisateur = new Utilisateur($unId, $unPseudo, $unMdpSha1, $uneAdrMail, $unNumTel, $unNiveau, $uneDateCreation, $unNbTraces, $uneDateDerniereTrace);
            $lesAutorises[] = $unUtilisateur;
            $uneLigne = $req->fetch(PDO::FETCH_OBJ);
        }
        $req->closeCursor();
        
        return $lesAutorises;
    }
    
        
    
    public function creeUnPointDeTrace($unPointDeTrace) {
        // on teste si l'utilisateur existe dÃ©jÃ 
        if ($this->getUneTrace($unPointDeTrace)) return false;
        
        // prÃ©paration de la requÃªte
        $txt_req1 = "insert into tracegps_utilisateurs (id, latitude, longitude, altitude, dateHeure, ryhtmeCardio)";
        $txt_req1 .= " values (:id, :latitude, :longitude, :altitude, :dateHeure, :ryhtmeCardio)";
        $req1 = $this->cnx->prepare($txt_req1);
        // liaison de la requÃªte et de ses paramÃ¨tres
        $req1->bindValue("id", utf8_decode($unPointDeTrace->getPseudo()), PDO::PARAM_STR);
        $req1->bindValue("latitude", utf8_decode(sha1($unPointDeTrace->getMdpsha1())), PDO::PARAM_STR);
        $req1->bindValue("longitude", utf8_decode($unPointDeTrace->getAdrmail()), PDO::PARAM_STR);
        $req1->bindValue("altitude", utf8_decode($unPointDeTrace->getNumTel()), PDO::PARAM_STR);
        $req1->bindValue("dateHeure", utf8_decode($unPointDeTrace->getNiveau()), PDO::PARAM_INT);
        $req1->bindValue("ryhtmeCardio", utf8_decode($unPointDeTrace->getDateCreation()), PDO::PARAM_STR);
        // exÃ©cution de la requÃªte
        $ok = $req1->execute();
        // sortir en cas d'Ã©chec
        if ( ! $ok) { return false; }
        
        // recherche de l'identifiant (auto_increment) qui a Ã©tÃ© attribuÃ© Ã  la trace
        $unId = $this->cnx->lastInsertId();
        $unUtilisateur->setId($unId);
        return true;
    }
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    // --------------------------------------------------------------------------------------
    // début de la zone attribuée au développeur 2 (delasalle-sio-kergoat-m) : lignes 550 à 749
    // --------------------------------------------------------------------------------------
    public function getLesUtilisateursAutorisant($idUtilisateur)
    {
        $recupAutorisant = "SELECT id, pseudo, mdpSha1, adrMail, numTel, niveau, dateCreation, nbTraces, dateDerniereTrace";             // from tracegps_autorisations JOIN tracegps_utilisateurs ON idAutorisant=id WHERE idAutorise = :autorise";      
        $recupAutorisant .= " FROM tracegps_vue_utilisateurs";
        $recupAutorisant .= " WHERE tracegps_vue_utilisateurs.niveau = 1";
        $recupAutorisant .= " AND tracegps_vue_utilisateurs.id IN (SELECT idAutorisant FROM tracegps_autorisations WHERE idAutorise = :idUtilisateur)";
        $recupAutorisant .= " ORDER BY pseudo";
        
        $req = $this->cnx->prepare($recupAutorisant);
        $req->bindValue("idUtilisateur", $idUtilisateur, PDO::PARAM_STR);
        $req->execute();
        $uneLigne = $req->fetch(PDO::FETCH_OBJ);
        
        $lesAutorisants = array();
        
        while($uneLigne)
        {
            $unId = utf8_encode($uneLigne->id);
            $unPseudo = utf8_encode($uneLigne->pseudo);
            $unMdpSha1 = utf8_encode($uneLigne->mdpSha1);
            $uneAdrMail = utf8_encode($uneLigne->adrMail);
            $unNumTel = utf8_encode($uneLigne->numTel);
            $unNiveau = utf8_encode($uneLigne->niveau);
            $uneDateCreation = utf8_encode($uneLigne->dateCreation);
            $unNbTraces = utf8_encode($uneLigne->nbTraces);
            $uneDateDerniereTrace = utf8_encode($uneLigne->dateDerniereTrace);
            
            $unUtilisateur = new Utilisateur($unId, $unPseudo, $unMdpSha1, $uneAdrMail, $unNumTel, $unNiveau, $uneDateCreation, $unNbTraces, $uneDateDerniereTrace);
            $lesAutorisants[] = $unUtilisateur;
            $uneLigne = $req->fetch(PDO::FETCH_OBJ);
        }
        $req->closeCursor();
        return $lesAutorisants;
    }
    
    public function creerUneAutorisation($idAutorisant, $idAutorise)
    {
        $txt_req1 = "insert into tracegps_autorisations (idAutorisant, idAutorise)";
        $txt_req1 .= " values (:idAutorisant, :idAutorise)";
        $req1 = $this->cnx->prepare($txt_req1);
        
        $req1->bindValue("idAutorisant", $idAutorisant, PDO::PARAM_STR);
        $req1->bindValue("idAutorise", $idAutorise, PDO::PARAM_STR);

        
        $ok = $req1->execute();
        // sortir en cas d'échec
        if ( ! $ok) 
        { 
            return false; 
        }
        return true;
    }

    public function supprimerUneAutorisation($idAutorisant, $idAutorise)
    {
        $txt_req1 = "DELETE FROM tracegps_autorisations";
        $txt_req1 .= " WHERE idAutorisant=:idAutorisant AND idAutorise=:idAutorise";
        $req1 = $this->cnx->prepare($txt_req1);
        
        $req1->bindValue("idAutorisant", $idAutorisant, PDO::PARAM_STR);
        $req1->bindValue("idAutorise", $idAutorise, PDO::PARAM_STR);
        
        
        $ok = $req1->execute();
        // sortir en cas d'échec
        if ( ! $ok)
        {
            return false;
        }
        return true;
    }

    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    

    // --------------------------------------------------------------------------------------
    // début de la zone attribuée au développeur 3 (delasalle-sio-waechter-a) : lignes 750 à 949
    // --------------------------------------------------------------------------------------
    
    
    
    
    
    
    //    public function getLesPointsDeTrace()
    //    {
    //        for ($i=0; $i <= sizeof(Trace::getLesPointsDeTrace()); $i++)
        //            $lesPoints = Trace::getLesPointsDeTrace($i);
        //            $nbPoints = sizeof($lesPoints);
        //            echo "<p>Nombre de points de la trace " .$i." : " . $nbPoints . "</p>";
        //            // affichage des points
        //            foreach ($lesPoints as $unPoint)
            //            { echo ($unPoint->toString());
            //            echo ('<br>');
            //            }
        
        
        
        
        //    }
        
        
        
        
        
        public function getLesPointsDeTrace($idTrace)
        {
            //         $rtrace = "Select tracegps_traces.id,latitude,longitude,altitude, dateHeure, rythmecardio,(dateFin - dateDebut) as TempsCumule from tracegps_points inner join tracegps_traces on tracegps_points.idTrace = tracegps_traces.id ";
            
            
            
            $rtrace = "SELECT idTrace,id,latitude,longitude,altitude, dateHeure, rythmeCardio ";
            $rtrace .= "FROM tracegps_points";
            $rtrace .= " WHERE tracegps_points.idTrace = :idTrace";
            $rtrace .= " ORDER BY tracegps_points.id";
            
            $req = $this->cnx->prepare($rtrace);
            $req->bindValue("idTrace", $idTrace, PDO::PARAM_INT);
            $req->execute();
            $uneligne = $req->fetch(PDO::FETCH_OBJ);
            
            $lespointsdetrace = array();
            
            while ($uneligne) {
                
                $unID = utf8_encode($uneligne -> id);
                $uneLatitude = utf8_encode($uneligne -> latitude);
                $uneLongitude = utf8_encode($uneligne -> longitude);
                $uneAltitude = utf8_encode($uneligne -> altitude);
                $uneDateHeure = utf8_encode($uneligne -> dateHeure);
                $unRythmeCardio = utf8_encode($uneligne -> rythmeCardio);
                
                
                
                $unPointDeTrace = new PointDeTrace($idTrace, $unID, $uneLatitude, $uneLongitude, $uneAltitude, $uneDateHeure, $unRythmeCardio, 0, 0, 0);
                
                $lespointsdetrace[] = $unPointDeTrace;
                $uneligne = $req->fetch(PDO::FETCH_OBJ);
            }
            
            $req->closeCursor();
            return $lespointsdetrace;
            
        }
        
        
        
        
        
        
        
        
        
        
        
        
        
        
        
        
        
        
        
        
        
        
        
        
        
        
        /*public function getUneTrace($idTrace)
        {
            
            if(sizeof($this->getLesPointsDeTrace($idTrace)) != 0){
            
                $rtrace = "SELECT id,dateDebut,dateFin,terminee,idUtilisateur";
                $rtrace .= " FROM tracegps_traces";
                $rtrace .= " WHERE tracegps_traces.id = :idTrace";
    
                
                $req = $this->cnx->prepare($rtrace);
                $req->bindValue("idTrace", $idTrace, PDO::PARAM_INT);
                $req->execute();
                $uneligne = $req->fetch(PDO::FETCH_OBJ);
    
                
                $uneDateDebut =  utf8_encode($uneligne -> dateDebut);
                $uneDateFin =  utf8_encode($uneligne -> dateFin);
                $estTerminee = utf8_encode($uneligne -> terminee);                       
                $unIdUtilisateur = utf8_encode($uneligne -> idUtilisateur);
                
                
                $uneTrace = new Trace($idTrace, $uneDateDebut, $uneDateFin, $estTerminee, $unIdUtilisateur);
    
                
                $lespointsdetrace = $this->getLesPointsDeTrace($idTrace);            
                foreach ($lespointsdetrace as $unpoint)
                {
                    $uneTrace->ajouterPoint($unpoint);
                }
                
                return $uneTrace;
            }
            
            
            
        }*/
        public function getUneTrace($idTrace)
        {
            
            if(sizeof($this->getLesPointsDeTrace($idTrace)) != 0){
                
                $rtrace = "SELECT id,dateDebut,dateFin,terminee,idUtilisateur";
                $rtrace .= " FROM tracegps_traces";
                $rtrace .= " WHERE tracegps_traces.id = :idTrace";
                
                
                $req = $this->cnx->prepare($rtrace);
                $req->bindValue("idTrace", $idTrace, PDO::PARAM_INT);
                $req->execute();
                $uneligne = $req->fetch(PDO::FETCH_OBJ);
                
                
                $uneDateDebut =  utf8_encode($uneligne -> dateDebut);
                $uneDateFin =  utf8_encode($uneligne -> dateFin);
                $estTerminee = utf8_encode($uneligne -> terminee);
                $unIdUtilisateur = utf8_encode($uneligne -> idUtilisateur);
                
                
                $uneTrace = new Trace($idTrace, $uneDateDebut, $uneDateFin, $estTerminee, $unIdUtilisateur);
                
                
                $lespointsdetrace = $this->getLesPointsDeTrace($idTrace);
                foreach ($lespointsdetrace as $unpoint)
                {
                    $uneTrace->ajouterPoint($unpoint);
                }
                
                return $uneTrace;
            }
            
        }
 
        
        
        
        
        
        
        
        
        
        
        
        
        
        
        
        
        
        
        
        
        
        
        
        
        
        
        
        
        
        // --------------------------------------------------------------------------------------
        // début de la zone attribuée au développeur 4 (delasalle-sio-burgot-m) : lignes 950 à 1150
        // --------------------------------------------------------------------------------------
        
        public function existeAdrMailUtilisateur($adrMail){
            
            $recupAdrMail = "Select adrMail from tracegps_utilisateurs WHERE adrMail = :mail";
            $req = $this->cnx->prepare($recupAdrMail);
            $req->bindValue("mail", $adrMail, PDO::PARAM_STR);
            // extraction des donn�es
            $req->execute();
            if($req->fetch()){
                // $req->fetch() permet de lire la ligne suivante
                // si elle vaut 'true' il y a au moins une donn�e sur cette ligne
                // sinon si elle vaut 'false' la ligne est vide (et il n'y en a pas d'autre derri�re)
                
                return true;
            }
            else{
                return false;
            }
        }
        
        
        
        
        public function autoriseAConsulter($idAutorisant,$idAutorise){
            
            $userAutorisant = DAO::getLesUtilisateursAutorisant($idAutorise);
            $userAutorises = DAO::getLesUtilisateursAutorises($idAutorisant);
            if ($userAutorisant && $userAutorises){
                return true;
            }
            return false;
            
            
            
            
        }
        
        
        
        //M�thode permettant de supprimer une Trace quelconque
        public function supprimerUneTrace($idTrace){
            $supprimerTrace = "DELETE FROM tracegps_traces WHERE id LIKE :identifiantTrace";
            $supprimerPoints = "DELETE FROM tracegps_points WHERE idTrace LIKE :identifiantTrace";
            //Suppression de la trace
            $reqSupprimerTrace = $this->cnx->prepare($supprimerTrace);
            $reqSupprimerTrace->bindvalue("identifiantTrace", $idTrace, PDO::PARAM_STR);
            
            //Suppresion des points
            $reqSupprimerPoints = $this->cnx->prepare($supprimerPoints);
            $reqSupprimerPoints->bindvalue("identifiantTrace", $idTrace, PDO::PARAM_STR);
            
            //Ex�cution des requ�tes
            $reqSupprimerTrace->execute();
            $reqSupprimerPoints->execute();
            
            
            //Condition de v�rification de la bien ex�cution des requ�tes
            if ($reqSupprimerTrace && $reqSupprimerPoints){
                return true;
            }
            
            return false;
            
            
            
        }
        
        //M�thode permettant de terminer une trace qui est en cours
        public function terminerUneTrace($idTrace){
            $estTermineeTrace = "UPDATE tracegps_traces SET terminee=1 WHERE id LIKE :idTrace";
            $reqEstTermineeTrace=$this->cnx->prepare($estTermineeTrace);
            $reqEstTermineeTrace->bindvalue("idTrace", $idTrace, PDO::PARAM_STR);
            $reqEstTermineeTrace->execute();
        }
        
        
        
        
        
        
        
        
        
        
        
        
        
        
        
        
        
        
        
        
        
        
        
        
        
        
        
} // fin de la classe DAO
// ATTENTION : on ne met pas de balise de fin de script pour ne pas prendre le risque
