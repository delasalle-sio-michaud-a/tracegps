<?php
// Projet TraceGPS
// fichier : modele/Trace.class.php
// Rôle : la classe Trace représente une trace ou un parcours
// Dernière mise à jour : 9/7/2021 par dPlanchet
include_once ('PointDeTrace.class.php');

class Trace
{

    // ------------------------------------------------------------------------------------------------------
    // ---------------------------------- Attributs privés de la classe -------------------------------------
    // ------------------------------------------------------------------------------------------------------
    private $id;

    // identifiant de la trace
    private $dateHeureDebut;

    // date et heure de début
    private $dateHeureFin;

    // date et heure de fin
    private $terminee;

    // true si la trace est terminée, false sinon
    private $idUtilisateur;

    // identifiant de l'utilisateur ayant créé la trace
    private $lesPointsDeTrace;

    // la collection (array) des objets PointDeTrace formant la trace

    // ------------------------------------------------------------------------------------------------------
    // ----------------------------------------- Constructeur -----------------------------------------------
    // ------------------------------------------------------------------------------------------------------
    public function __construct($unId, $uneDateHeureDebut, $uneDateHeureFin, $terminee, $unIdUtilisateur)
    {
        $this->id = $unId;
        $this->dateHeureDebut = $uneDateHeureDebut;
        $this->dateHeureFin = $uneDateHeureFin;
        $this->terminee = $terminee;
        $this->idUtilisateur = $unIdUtilisateur;
        $this->lesPointsDeTrace = array();
    }

    // ------------------------------------------------------------------------------------------------------
    // ---------------------------------------- Getters et Setters ------------------------------------------
    // ------------------------------------------------------------------------------------------------------
    public function getId()
    {
        return $this->id;
    }

    public function setId($unId)
    {
        $this->id = $unId;
    }

    public function getDateHeureDebut()
    {
        return $this->dateHeureDebut;
    }

    public function setDateHeureDebut($uneDateHeureDebut)
    {
        $this->dateHeureDebut = $uneDateHeureDebut;
    }

    public function getDateHeureFin()
    {
        return $this->dateHeureFin;
    }

    public function setDateHeureFin($uneDateHeureFin)
    {
        $this->dateHeureFin = $uneDateHeureFin;
    }

    public function getTerminee()
    {
        return $this->terminee;
    }

    public function setTerminee($terminee)
    {
        $this->terminee = $terminee;
    }

    public function getIdUtilisateur()
    {
        return $this->idUtilisateur;
    }

    public function setIdUtilisateur($unIdUtilisateur)
    {
        $this->idUtilisateur = $unIdUtilisateur;
    }

    public function getLesPointsDeTrace()
    {
        return $this->lesPointsDeTrace;
    }

    public function setLesPointsDeTrace($lesPointsDeTrace)
    {
        $this->lesPointsDeTrace = $lesPointsDeTrace;
    }

    public function getNombrePoints()
    {
        return sizeof($this->lesPointsDeTrace);
    }

    public function getCentre()
    {
        if ($this->getNombrePoints() > 0) {

            $monPoint = $this->lesPointsDeTrace[0];
            $latmini = $monPoint->getLatitude();
            $latmaxi = $monPoint->getLatitude();
            $lonmini = $monPoint->getLongitude();
            $lonmaxi = $monPoint->getLongitude();

            for ($i = 0; $i < $this->getNombrePoints() - 1; $i ++) {
                $monPoint = $this->lesPointsDeTrace[$i];
                $ptlat = $monPoint->getLatitude();
                $ptlon = $monPoint->getLongitude();

                if ($latmini > $ptlat)
                    $latmini = $ptlat;
                if ($latmaxi < $ptlat)
                    $latmaxi = $ptlat;
                if ($lonmini > $ptlon)
                    $lonmini = $ptlon;
                if ($lonmaxi < $ptlon)
                    $lonmaxi = $ptlon;
            }
            $lat = ($latmini + $latmaxi) / 2;
            $lon = ($lonmini + $lonmaxi) / 2;
            return new Point($lat, $lon, 0);
        } else
            return null;
    }

    public function getDenivele()
    {
        if ($this->getNombrePoints() > 0) {
            $monPoint = $this->lesPointsDeTrace[0];
            $altmini = $monPoint->getAltitude();
            $altmaxi = $monPoint->getAltitude();

            for ($i = 0; $i < $this->getNombrePoints() - 1; $i ++) {
                $ptalt = $this->lesPointsDeTrace[$i]->getAltitude();

                if ($altmini > $ptalt)
                    $altmini = $ptalt;
                if ($altmaxi < $ptalt)
                    $altmaxi = $ptalt;
            }
            return $altmaxi - $altmini;
        } else {
            return 0;
        }
    }

    public function getDureeEnSecondes()
    {
        if ($this->getNombrePoints() > 0)
            return $this->lesPointsDeTrace[sizeof($this->lesPointsDeTrace) - 1]->getTempsCumule();
        return 0;
    }

    public function getDureeTotale()
    {
        $heures = floor($this->getDureeEnSecondes() / 3600);
        $secondes = $this->getDureeEnSecondes() - ($heures * 3600);
        $minutes = floor($secondes / 60);
        $secondes = $secondes - ($minutes * 60);

        $res = "";
        if ($heures < 10) {
            $res = $res . "0" . $heures;
        } else {
            $res = $res . $heures;
        }
        $res = $res . ":";
        if ($minutes < 10) {
            $res = $res . "0" . $minutes;
        } else {
            $res = $res . $minutes;
        }
        $res = $res . ":";
        if ($secondes < 10) {
            $res = $res . "0" . $secondes;
        } else {
            $res = $res . $secondes;
        }
        return $res;
    }

    public function getDistanceTotale()
    {
        if ($this->getNombrePoints() > 0)
            return $this->lesPointsDeTrace[$this->getNombrePoints() - 1]->getDistanceCumulee();
        return 0;
    }

    public function getDenivelePositif()
    {
        $den = 0;
        for ($i = 0; $i < $this->getNombrePoints() - 1; $i ++) {
            $pt1 = $this->lesPointsDeTrace[$i]->getAltitude();
            $pt2 = $this->lesPointsDeTrace[$i + 1]->getAltitude();
            if ($pt1 < $pt2)
                $den += $pt2 - $pt1;
        }
        return $den;
    }

    public function getDeniveleNegatif()
    {
        $den = 0;
        for ($i = 0; $i < $this->getNombrePoints() - 1; $i ++) {
            $pt1 = $this->lesPointsDeTrace[$i]->getAltitude();
            $pt2 = $this->lesPointsDeTrace[$i + 1]->getAltitude();
            if ($pt1 > $pt2)
                $den += $pt1 - $pt2;
        }
        return $den;
    }

    public function getVitesseMoyenne()
    {
        if ($this->getDistanceTotale() == 0)
            return 0;
        else {
            return $this->getDistanceTotale() / ($this->getDureeEnSecondes() / 3600);
        }
    }

    public function ajouterPoint(PointDeTrace $newPoint)
    {
        if ($this->getNombrePoints() == 0) {
            $newPoint->setTempsCumule(0);
            $newPoint->setDistanceCumulee(0);
            $newPoint->setVitesse(0);
        } else {
            $dernierPoint = $this->lesPointsDeTrace[sizeof($this->lesPointsDeTrace) - 1];

            $duree = strtotime($newPoint->getDateHeure()) - strtotime($dernierPoint->getDateHeure());
            $newPoint->setTempsCumule($dernierPoint->getTempsCumule() + $duree);

            $distance = Point::getDistance($dernierPoint, $newPoint);
            $newPoint->setDistanceCumulee($dernierPoint->getDistanceCumulee() + $distance);

            $newPoint->setVitesse($distance / $duree);

            if ($duree > 0) {
                $vitesse = $distance / ($duree * 3600);
            } else {
                $vitesse = 0;
            }
            $newPoint->setVitesse($vitesse);
        }
        $this->lesPointsDeTrace[] = $newPoint;
    }

    public function viderListePoints()
    {
        $this->lesPointsDeTrace->clear();
    }

    // Fournit une chaine contenant toutes les données de l'objet
    public function toString()
    {
        $msg = "Id : " . $this->getId() . "<br>";
        $msg .= "Utilisateur : " . $this->getIdUtilisateur() . "<br>";
        if ($this->getDateHeureDebut() != null) {
            $msg .= "Heure de début : " . $this->getDateHeureDebut() . "<br>";
        }
        if ($this->getTerminee()) {
            $msg .= "Terminée : Oui <br>";
        } else {
            $msg .= "Terminée : Non <br>";
        }
        $msg .= "Nombre de points : " . $this->getNombrePoints() . "<br>";
        if ($this->getNombrePoints() > 0) {
            if ($this->getDateHeureFin() != null) {
                $msg .= "Heure de fin : " . $this->getDateHeureFin() . "<br>";
            }
            $msg .= "Durée en secondes : " . $this->getDureeEnSecondes() . "<br>";
            $msg .= "Durée totale : " . $this->getDureeTotale() . "<br>";
            $msg .= "Distance totale en Km : " . $this->getDistanceTotale() . "<br>";
            $msg .= "Dénivelé en m : " . $this->getDenivele() . "<br>";
            $msg .= "Dénivelé positif en m : " . $this->getDenivelePositif() . "<br>";
            $msg .= "Dénivelé négatif en m : " . $this->getDeniveleNegatif() . "<br>";
            $msg .= "Vitesse moyenne en Km/h : " . $this->getVitesseMoyenne() . "<br>";
            $msg .= "Centre du parcours : " . "<br>";
            $msg .= " - Latitude : " . $this->getCentre()->getLatitude() . "<br>";
            $msg .= " - Longitude : " . $this->getCentre()->getLongitude() . "<br>";
            $msg .= " - Altitude : " . $this->getCentre()->getAltitude() . "<br>";
        }
        return $msg;
    }
} // fin de la classe Trace
// ATTENTION : on ne met pas de balise de fin de script pour ne pas prendre le risque
// d'enregistrer d'espaces après la balise de fin de script !!!!!!!!!!!!
