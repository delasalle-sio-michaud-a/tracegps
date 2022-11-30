

<!DOCTYPE html>
<html>
<head>
	<meta charset="utf-8">
	<title>Test de la classe DAO</title>
	<style type="text/css">body {font-family: Arial, Helvetica, sans-serif; font-size: small;}</style>
</head>
<body>

<?php
// connexion du serveur web à la base MySQL
include_once ('DAO.class.php');
$dao = new DAO();


// test de la méthode xxxxxxxxxxxxxxxxxxxxxxxxxxx ----------------------------------------------------------
// modifié par xxxxxxxxxxxxxxxxx le xxxxxxxxxx
echo "<h3>Test de Alex : </h3>";
// A CONTINUER .........




// test de la méthode getLesUtilisateursAutorises -------------------------------------------------
// modifié par dP le 13/8/2021
echo "<h3>Test de getLesUtilisateursAutorises(idUtilisateur) : </h3>";
$lesUtilisateurs = $dao->getLesUtilisateursAutorises(2);
$nbReponses = sizeof($lesUtilisateurs);
echo "<p>Nombre d'utilisateurs autorisés par l'utilisateur 2 : " . $nbReponses . "</p>";
// affichage des utilisateurs

foreach ($lesUtilisateurs as $unUtilisateur)
{ 
    echo ($unUtilisateur->toString());
    echo ('<br>');
}






// ferme la connexion à MySQL :
unset($dao);
?>