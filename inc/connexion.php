<?php
$connect = mysqli_connect('localhost', 'root', '', 'ExamS2');
if (!$connect) {
    die("Erreur de connexion : " . mysqli_connect_error());
}

function dbconnect()
{
    static $connect = null;

    if ($connect === null) {
        $connect = mysqli_connect('localhost', 'root', '', 'ExamS2');

        if (!$connect) {
            // Arrête le script et affiche une erreur si la connexion échoue
            die('Erreur de connexion à la base de données : ' . mysqli_connect_error());
        }

      
    }

    return $connect;
}
?>