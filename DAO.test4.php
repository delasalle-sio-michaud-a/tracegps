<?php
// Projet TraceGPS
// fichier : modele/DAO.test1.php
// Rôle : test de la classe DAO.class.php
// Dernière mise à jour : xxxxxxxxxxxxxxxxx par xxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxx

// Le code des tests restant à développer va être réparti entre les membres de l'équipe de développement.
// Afin de limiter les conflits avec GitHub, il est décidé d'attribuer un fichier de test à chaque développeur.
// Développeur 1 : fichier DAO.test1.php
// Développeur 2 : fichier DAO.test2.php
// Développeur 3 : fichier DAO.test3.php
// Développeur 4 : fichier DAO.test4.php

// Quelques conseils pour le travail collaboratif :
// avant d'attaquer un cycle de développement (début de séance, nouvelle méthode, ...), faites un Pull pour récupérer
// la dernière version du fichier.
// Après avoir testé et validé une méthode, faites un commit et un push pour transmettre cette version aux autres développeurs.
?>
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


// test de la méthode existeAdrMailUtilisateur ----------------------------------------------------------
// modifié par Mathieu BURGOT le 23/11/2022 
echo "<h3>Test de existeAdrMailUtilisateur n�1 : </h3>";
// A CONTINUER .........

echo "<h3>Test de existeAdrMailUtilisateur : </h3>";
if ($dao->existeAdrMailUtilisateur("admin@gmail.com")) $existe = "oui"; else $existe = "non";
echo "<p>Existence de l'utilisateur 'admin@gmail.com' : <b>" . $existe . "</b><br>";
if ($dao->existeAdrMailUtilisateur("delasalle.sio.eleves@gmail.com")) $existe = "oui"; else $existe = "non";
echo "Existence de l'utilisateur 'delasalle.sio.eleves@gmail.com' : <b>" . $existe . "</b></br>";
/*
// test de la m�thode autoriseAConsulter ----------------------------------------------------------
// modifi� par dP le 13/8/2021
echo "<h3>Test de autoriseAConsulter : </h3>";
if ($dao->autoriseAConsulter(2, 3)) $autorise = "oui"; else $autorise = "non";
echo "<p>L'utilisateur 2 autorise l'utilisateur 3 : <b>" . $autorise . "</b><br>";
if ($dao->autoriseAConsulter(3, 2)) $autorise = "oui"; else $autorise = "non";
echo "<p>L'utilisateur 3 autorise l'utilisateur 2 : <b>" . $autorise . "</b><br>";
*/
// test de la m�thode supprimerUneTrace -----------------------------------------------------------
// modifi� par dP le 15/8/2021
echo "<h3>Test de supprimerUneTrace : </h3>";
$ok = $dao->supprimerUneTrace(22);
if ($ok) {
    echo "<p>Trace bien supprim�e !</p>";
}
else {
    echo "<p>Echec lors de la suppression de la trace !</p>";
}

// test des m�thodes creerUnPointDeTrace et terminerUneTrace --------------------------------------
// modifi� par dP le 15/8/2021
echo "<h3>Test de terminerUneTrace : </h3>";
// on choisit une trace non termin�e
$unIdTrace = 3;
// on l'affiche
$laTrace = $dao->getUneTrace($unIdTrace);
echo "<h4>l'objet laTrace avant l'appel de la m�thode terminerUneTrace : </h4>";
echo ($laTrace->toString());
echo ('<br>');
// on la termine
$dao->terminerUneTrace($unIdTrace);
// et on l'affiche � nouveau
$laTrace = $dao->getUneTrace($unIdTrace);
echo "<h4>l'objet laTrace apr�s l'appel de la m�thode terminerUneTrace : </h4>";
echo ($laTrace->toString());
echo ('<br>');



// ferme la connexion à MySQL :
unset($dao);
?>

</body>
</html>