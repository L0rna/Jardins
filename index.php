<?php
// Connexion à la base de données
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "jardin";

try {
    $conn = new PDO("mysql:host=$servername;dbname=$dbname", $username, $password);
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // Récupération des catégories
    $sql = "SELECT id, nom FROM categories";
    $stmt = $conn->query($sql);
    $categories = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Récupération des produits en fonction de la catégorie sélectionnée
    $selectedCategory = isset($_GET['category']) ? $_GET['category'] : '';
    if (!empty($selectedCategory)) {
        $sql = "SELECT id, nom, image, prix FROM produits WHERE categorie_id = :category_id";
        $stmt = $conn->prepare($sql);
        $stmt->bindParam(':category_id', $selectedCategory);
        $stmt->execute();
    } else {
        $sql = "SELECT id, nom, image, prix FROM produits";
        $stmt = $conn->query($sql);
    }
    $produits = $stmt->fetchAll(PDO::FETCH_ASSOC);

    $searchTerm = isset($_GET['search']) ? $_GET['search'] : '';

    if (!empty($searchTerm)) {
        $searchTermLike = '%' . $searchTerm . '%';
        $sql = "SELECT id, nom, image, prix FROM produits WHERE nom LIKE :search_term OR tag LIKE :search_term";
        $stmt = $conn->prepare($sql);
        $stmt->bindValue(':search_term', $searchTermLike);
        $stmt->execute();
        $produits = $stmt->fetchAll(PDO::FETCH_ASSOC);
    }    

} catch (PDOException $e) {
    echo "Connexion échouée : " . $e->getMessage();
}
?>

<!DOCTYPE html>
<html lang="fr">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Les Jardins du Bien-Être</title>

    <link rel="stylesheet" href="style.css">

    <!-- Inclusion de la feuille de style Bootstrap -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet"
    integrity="sha384-9ndCyUaIbzAi2FUVXJi0CjmCapSmO7SnpJef0486qhLnuZ2cdeRhO02iuK6FUUVM" crossorigin="anonymous">
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"
    integrity="sha384-geWF76RCwLtnZ8qwWowPQNguL3RmwHVBC9FhGdlKrxdiJJigb/j/68SIy3Te4Bkz" crossorigin="anonymous"></script>


</head>

<body class="no-scroll">
    <!-- Conteneur de l'image et du titre -->
    <div class="position-relative">
        <!-- Image -->
        <img src="../mvc/religious-zen-candle-flower.jpg" alt="Zen flower" class="custom-image img-fluid">
        <!-- Titre centré sur l'image -->
        <h1 class="position-absolute top-50 start-50 translate-middle text-white"> Les Jardins du Bien-Être</h1>
        <!-- la classe display-4  pour changer la taille du titre-->
    </div>

    <!-- Barre de navigation -->
    <nav class="navbar navbar-expand-md navbar-light" style="background-color: #99CD7D;">
        <div class="container">
            <!-- Bouton pour afficher/masquer le menu de navigation sur les petits écrans -->
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav"
                aria-controls="navbarNav" aria-expanded="false" aria-label="Toggle navigation">
                <span class="navbar-toggler-icon"> </span>
            </button>

            <!-- Contenu du menu de navigation -->
            <div class="collapse navbar-collapse" id="navbarNav">
                <a href="index.php" class="home-link">
                    <!-- Utilisation de l'icône home SVG pour le premier élément de la liste -->
                    <svg xmlns="http://www.w3.org/2000/svg" width="28" height="28" fill="white"
                        class="bi bi-house-door-fill" viewBox="0 0 16 16">
                        <path
                            d="M6.5 14.5v-3.505c0-.245.25-.495.5-.495h2c.25 0 .5.25.5.5v3.5a.5.5 0 0 0 .5.5h4a.5.5 0 0 0 .5-.5v-7a.5.5 0 0 0-.146-.354L13 5.793V2.5a.5.5 0 0 0-.5-.5h-1a.5.5 0 0 0-.5.5v1.293L8.354 1.146a.5.5 0 0 0-.708 0l-6 6A.5.5 0 0 0 1.5 7.5v7a.5.5 0 0 0 .5.5h4a.5.5 0 0 0 .5-.5Z" />
                    </svg>
                </a>

                <ul class="navbar-nav d-flex flex-column flex-md-row">
                    <?php foreach ($categories as $category): ?>
                    <li class="nav-item ms-4"><a class="nav-link"
                            href="?category=<?php echo $category['id']; ?>"><?php echo $category['nom']; ?></a></li>
                    <?php endforeach; ?>
                </ul>

            </div>
            <form class="d-flex" action="" method="GET">
                <input class="form-control form-control-sm" type="text" name="search" placeholder="Rechercher par nom"
                    value="<?php echo htmlspecialchars($searchTerm); ?>">


                <button class="btn btn-outline-light btn-sm ms-3" type="submit" value="Rechercher">
                    <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" fill="white" class="bi bi-search"
                        viewBox="0 0 16 16">
                        <path
                            d="M11.742 10.344a6.5 6.5 0 1 0-1.397 1.398h-.001c.03.04.062.078.098.115l3.85 3.85a1 1 0 0 0 1.415-1.414l-3.85-3.85a1.007 1.007 0 0 0-.115-.1zM12 6.5a5.5 5.5 0 1 1-11 0 5.5 5.5 0 0 1 11 0Z" />
                    </svg>
                </button>
            </form>
        </div>
    </nav>
    <div class="container py-4">
    <div class="row">
        <?php foreach ($produits as $produit): ?>
            <div class="col-md-3 mb-3">
                <div class="card">
                    <div class="aspect-ratio">
                        <img src="data:image/jpeg;base64,<?php echo $produit['image']; ?>" class="card-img-top" alt="<?php echo $produit['nom']; ?>">
                    </div>
                    <div class="card-body">
                        <h5 class="card-title"><?php echo $produit['nom']; ?></h5>
                        <p class="card-text">Prix : <?php echo $produit['prix']; ?> €</p> <!-- Ajout du prix -->
                    </div>
                </div>
            </div>
        <?php endforeach; ?>
    </div>
    </div>

    <footer class="footer bottom">
        <nav class="navbar navbar-expand-lg">
            <div class="container">
                <ul class="navbar-nav mx-auto">
                    <li class="nav-item">
                        <a class="nav-link" href="#">Accueil</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="#">À propos</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="#">Contact</a>
                    </li>
                </ul>
            </div>
        </nav>
        <div class="container">
            <div class="row">
                <div class="col text-center">
                    <p>&copy; <?php echo date('Y'); ?> Les Jardins Du Bien-Être. Tous droits réservés.</p>
                </div>
            </div>
        </div>
    </footer>


</body>

</html>

