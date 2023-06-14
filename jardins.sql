DROP DATABASE IF EXISTS jardins;
CREATE DATABASE jardins;
USE jardins;

-- Suppression des tables si elles existent déjà
DROP TABLE IF EXISTS produits;
DROP TABLE IF EXISTS categories;

-- Création de la table categories
CREATE TABLE categories (
    id INT PRIMARY KEY AUTO_INCREMENT,
    nom VARCHAR(50) NOT NULL,
    produits VARCHAR(255)
);

-- Insertion des catégories
INSERT INTO categories (nom)
VALUES
    ('Phytothérapie'),
    ('Produit de la Ruche'),
    ('Accessoires'),
    ('Beauté Hygiène'),
    ('Minéraux'),
    ('Aromathérapie'),
    ('Encens');

-- Création de la table produits
CREATE TABLE produits (
    id INT PRIMARY KEY AUTO_INCREMENT,
    nom VARCHAR(50) NOT NULL,
    prix DECIMAL(10, 2) NOT NULL,
    tag VARCHAR(255),
    image LONGBLOB,
    categorie_id INT,
    FOREIGN KEY (categorie_id) REFERENCES categories(id)
);

-- Insertion des produits correspondant à chaque catégorie (sans image)
INSERT INTO produits (nom, tag, categorie_id)
VALUES
    ('miel', 'Miel pur et biologique', 2),
    ('savon', 'Soins doux pour la peau', 4);

-- Mise à jour de la colonne produits dans la table categories pour inclure les noms des produits
UPDATE categories c
SET produits = (
    SELECT GROUP_CONCAT(p.nom)
    FROM produits p
    WHERE p.categorie_id = c.id
);

-- Sélection des catégories avec les noms des produits
SELECT id, nom, produits
FROM categories;


-- ALTER TABLE nom_table CHANGE nom_colonne nouveau_nom_colonne type_colonne;