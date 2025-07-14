<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Inscription</title>
</head>
<body>
    <?php
    session_start();
    if (isset($_SESSION['error'])) {
        echo '<p style="color: red;">' . htmlspecialchars($_SESSION['error']) . '</p>';
        unset($_SESSION['error']);
    }
    ?>
    <form action="../traitement/tr_inscrit.php" method="post" enctype="multipart/form-data">
        <h2>Inscription</h2>
        <label for="nom">Nom:</label>
        <input type="text" name="nom" id="nom" required maxlength="100">
        <br>
        <label for="date_de_naissance">Date de naissance:</label>
        <input type="date" name="date_de_naissance" id="date_de_naissance">
        <br>
        <label for="genre">Genre:</label>
        <select name="genre" id="genre">
            <option value="">Sélectionner</option>
            <option value="M">Masculin</option>
            <option value="F">Féminin</option>
            <option value="Autre">Autre</option>
        </select>
        <br>
        <label for="email">Email:</label>
        <input type="email" name="email" id="email" required maxlength="255">
        <br>
        <label for="ville">Ville:</label>
        <input type="text" name="ville" id="ville" maxlength="100">
        <br>
        <label for="mdp">Mot de passe:</label>
        <input type="password" name="mdp" id="mdp" required maxlength="255">
        <br>
        <label for="image_profil">Image de profil:</label>
        <input type="file" name="image_profil" id="image_profil" accept="image/*">
        <br>
        <button type="submit">S'inscrire</button>
    </form>
    <p><a href="login.php">Connexion</a></p>
</body>
</html>