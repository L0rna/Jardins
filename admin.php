<?php
// Connexion à la base de données
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "jardins";

try {
    $conn = new PDO("mysql:host=$servername;dbname=$dbname", $username, $password);
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch(PDOException $e) {
    die("Connexion échouée : " . $e->getMessage());
}

// Récupération des catégories
$sql = "SELECT id, nom FROM categories";
$categories = $conn->query($sql)->fetchAll(PDO::FETCH_ASSOC);

// Vérification du formulaire d'ajout ou de modification de produit
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $nom = $_POST['nom'];
    $prix = $_POST['prix'];
    $tag = $_POST['tag'];
    $categorieId = $_POST['categorie'];

    // Vérification si une image a été envoyée
    if ($_FILES['image']['size'] > 0) {
        $imageData = file_get_contents($_FILES['image']['tmp_name']);
        $image = base64_encode($imageData);
    } else {
        $image = null;
    }

    if (isset($_POST['id'])) {
        // Modification du produit dans la base de données
        $produitId = $_POST['id'];
        $stmt = $conn->prepare("UPDATE produits SET nom = ?, prix = ?, tag = ?, image = ?, categorie_id = ? WHERE id = ?");
        $stmt->bindParam(1, $nom);
        $stmt->bindParam(2, $prix);
        $stmt->bindParam(3, $tag);
        $stmt->bindParam(4, $image, PDO::PARAM_LOB);
        $stmt->bindParam(5, $categorieId);
        $stmt->bindParam(6, $produitId);
        $stmt->execute();
    } else {
        // Insertion du produit dans la base de données
        $stmt = $conn->prepare("INSERT INTO produits (nom, prix, tag, categorie_id, image) VALUES (?, ?, ?, ?, ?)");
        $stmt->bindParam(1, $nom);
        $stmt->bindParam(2, $prix);
        $stmt->bindParam(3, $tag);
        $stmt->bindParam(4, $categorieId);
        $stmt->bindParam(5, $image, PDO::PARAM_LOB);
        $stmt->execute();
    }

    // Mise à jour de la colonne produits dans la table categories
    $updateStmt = $conn->prepare("UPDATE categories c
        SET produits = (
            SELECT GROUP_CONCAT(p.nom)
            FROM produits p
            WHERE p.categorie_id = c.id
        )");
    $updateStmt->execute();

    // Redirection vers la page d'administration après l'ajout ou la modification du produit
    header("Location: admin.php");
    exit();
}

// Vérification de l'identifiant du produit à modifier
if (isset($_GET['id'])) {
    $produitId = $_GET['id'];

    // Récupération des informations du produit à modifier
    $stmt = $conn->prepare("SELECT id, nom, prix, tag, image, categorie_id FROM produits WHERE id = ?");
    $stmt->bindParam(1, $produitId);
    $stmt->execute();
    $produit = $stmt->fetch(PDO::FETCH_ASSOC);
}

// Vérification de l'identifiant du produit à supprimer
if (isset($_GET['delete'])) {
    $produitId = $_GET['delete'];

    // Suppression du produit de la base de données
    $stmt = $conn->prepare("DELETE FROM produits WHERE id = ?");
    $stmt->bindParam(1, $produitId);
    $stmt->execute();

    // Mise à jour de la colonne produits dans la table categories
    $updateStmt = $conn->prepare("UPDATE categories c
        SET produits = (
            SELECT GROUP_CONCAT(p.nom)
            FROM produits p
            WHERE p.categorie_id = c.id
        )");
    $updateStmt->execute();

    // Redirection vers la page d'administration après la suppression du produit
    header("Location: admin.php");
    exit();
}

// Récupération de tous les produits
$sql = "SELECT p.id, p.nom, p.prix, p.tag, p.image, c.nom AS categorie 
        FROM produits p
        INNER JOIN categories c ON p.categorie_id = c.id";
$produits = $conn->query($sql)->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html>
<head>
    <title>Administration</title>
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.0/css/bootstrap.min.css">
</head>
<body>
    <div class="container">
        <h1>Administration</h1>

        <h2>Ajouter/Modifier un produit</h2>
        <form method="POST" enctype="multipart/form-data">
            <?php if (isset($produit)) : ?>
                <input type="hidden" name="id" value="<?php echo $produit['id']; ?>">
            <?php endif; ?>
            <div class="form-group">
                <label for="nom">Nom:</label>
                <input type="text" class="form-control" name="nom" value="<?php echo isset($produit) ? $produit['nom'] : ''; ?>" required>
            </div>

            <div class="form-group">
                <label for="prix">Prix:</label>
                <input type="number" class="form-control" name="prix" value="<?php echo isset($produit) ? $produit['prix'] : ''; ?>" required>
            </div>

            <div class="form-group">
                <label for="tag">Tag:</label>
                <input type="text" class="form-control" name="tag" value="<?php echo isset($produit) ? $produit['tag'] : ''; ?>">
            </div>

            <div class="form-group">
                <label for="categorie">Catégorie:</label>
                <select class="form-control" name="categorie">
                    <?php foreach ($categories as $categorie) : ?>
                        <option value="<?php echo $categorie['id']; ?>" <?php echo isset($produit) && $produit['categorie_id'] == $categorie['id'] ? 'selected' : ''; ?>><?php echo $categorie['nom']; ?></option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div class="form-group">
                <label for="image">Image:</label>
                <input type="file" name="image">
            </div>

            <input type="submit" class="btn btn-primary" value="<?php echo isset($produit) ? 'Modifier' : 'Ajouter'; ?>">
        </form>

        <h2>Liste des produits</h2>
        <table class="table">
            <thead>
                <tr>
                    <th>Nom</th>
                    <th>Prix</th>
                    <th>Tag</th>
                    <th>Catégorie</th>
                    <th>Image</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($produits as $produit) : ?>
                    <tr>
                        <td><?php echo $produit['nom']; ?></td>
                        <td><?php echo $produit['prix']; ?></td>
                        <td><?php echo $produit['tag']; ?></td>
                        <td><?php echo $produit['categorie']; ?></td>
                        <td><?php echo $produit['image'] ? 'Oui' : 'Non'; ?></td>
                        <td>
                            <a href="admin.php?id=<?php echo $produit['id']; ?>" class="btn btn-sm btn-primary">Modifier</a>
                            <a href="admin.php?delete=<?php echo $produit['id']; ?>" class="btn btn-sm btn-danger">Supprimer</a>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
    <footer class="footer fixed-bottom">
    <div class="container">
        <div class="row">
            <div class="col text-center">
                <p>&copy; <?php echo date('Y'); ?> Les Jardins Du Bien-etre. Tous droits réservés.</p>
            </div>
        </div>
    </div>
    </footer>
</body>
</html>
