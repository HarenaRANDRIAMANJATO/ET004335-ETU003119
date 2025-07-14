<?php
session_start();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Inscription</title>
    <link href="../assets/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
    <div class="container mt-5">
        <div class="row justify-content-center">
            <div class="col-md-6">
                <div class="card shadow">
                    <div class="card-header bg-primary text-white">
                        <h2 class="mb-0">Inscription</h2>
                    </div>
                    <div class="card-body">
                        <?php if (isset($_SESSION['error'])): ?>
                            <div class="alert alert-danger" role="alert">
                                <?php echo htmlspecialchars($_SESSION['error']); ?>
                            </div>
                            <?php unset($_SESSION['error']); ?>
                        <?php endif; ?>
                        <form action="../traitement/tr_inscrit.php" method="post" enctype="multipart/form-data">
                            <div class="mb-3">
                                <label for="nom" class="form-label">Nom:</label>
                                <input type="text" name="nom" id="nom" class="form-control" required maxlength="100">
                            </div>
                            <div class="mb-3">
                                <label for="date_de_naissance" class="form-label">Date de naissance:</label>
                                <input type="date" name="date_de_naissance" id="date_de_naissance" class="form-control">
                            </div>
                            <div class="mb-3">
                                <label for="genre" class="form-label">Genre:</label>
                                <select name="genre" id="genre" class="form-select">
                                    <option value="">Sélectionner</option>
                                    <option value="M">Masculin</option>
                                    <option value="F">Féminin</option>
                                    <option value="Autre">Autre</option>
                                </select>
                            </div>
                            <div class="mb-3">
                                <label for="email" class="form-label">Email:</label>
                                <input type="email" name="email" id="email" class="form-control" required maxlength="255">
                            </div>
                            <div class="mb-3">
                                <label for="ville" class="form-label">Ville:</label>
                                <input type="text" name="ville" id="ville" class="form-control" maxlength="100">
                            </div>
                            <div class="mb-3">
                                <label for="mdp" class="form-label">Mot de passe:</label>
                                <input type="password" name="mdp" id="mdp" class="form-control" required maxlength="255">
                            </div>
                            <div class="mb-3">
                                <label for="image_profil" class="form-label">Image de profil:</label>
                                <input type="file" name="image_profil" id="image_profil" class="form-control" accept="image/*">
                            </div>
                            <button type="submit" class="btn btn-primary">S'inscrire</button>
                        </form>
                        <p class="mt-3">Déjà inscrit ? <a href="login.php" class="link-primary">Connexion</a></p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</body>
</html>