<?php
session_start();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Connexion</title>
    <link href="../assets/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
    <div class="container mt-5">
        <div class="row justify-content-center">
            <div class="col-md-6">
                <div class="card shadow">
                    <div class="card-header bg-primary text-white">
                        <h2 class="mb-0">Connexion</h2>
                    </div>
                    <div class="card-body">
                        <?php if (isset($_SESSION['error'])): ?>
                            <div class="alert alert-danger" role="alert">
                                <?php echo htmlspecialchars($_SESSION['error']); ?>
                            </div>
                            <?php unset($_SESSION['error']); ?>
                        <?php endif; ?>
                        <form action="../traitement/tr_login.php" method="post">
                            <div class="mb-3">
                                <label for="email" class="form-label">Email:</label>
                                <input type="email" name="email" id="email" class="form-control" required>
                            </div>
                            <div class="mb-3">
                                <label for="password" class="form-label">Mot de passe:</label>
                                <input type="password" name="password" id="password" class="form-control" required>
                            </div>
                            <button type="submit" class="btn btn-primary">Connexion</button>
                        </form>
                        <p class="mt-3">Pas de compte ? <a href="inscrit.php" class="link-primary">Inscription</a></p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</body>
</html>