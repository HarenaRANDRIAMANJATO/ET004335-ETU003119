CREATE DATABASE ExamS2;
USE ExamS2;


CREATE TABLE membre (
    id_membre INT PRIMARY KEY AUTO_INCREMENT,
    nom VARCHAR(100) NOT NULL,
    date_de_naissance DATE,
    genre VARCHAR(25),
    email VARCHAR(255) UNIQUE NOT NULL,
    ville VARCHAR(100),
    mdp VARCHAR(255) NOT NULL,
    image_profil VARCHAR(255)
);

CREATE TABLE categorie_objet (
    id_categorie INT PRIMARY KEY AUTO_INCREMENT,
    nom_categorie VARCHAR(100) NOT NULL
);

CREATE TABLE objet (
    id_objet INT PRIMARY KEY AUTO_INCREMENT,
    nom_objet VARCHAR(100) NOT NULL,
    id_categorie INT,
    id_membre INT,
    FOREIGN KEY (id_categorie) REFERENCES categorie_objet(id_categorie),
    FOREIGN KEY (id_membre) REFERENCES membre(id_membre)
);

CREATE TABLE images_objet (
    id_image INT PRIMARY KEY AUTO_INCREMENT,
    id_objet INT,
    nom_image VARCHAR(255) NOT NULL,
    FOREIGN KEY (id_objet) REFERENCES objet(id_objet)
);

CREATE TABLE emprunt (
    id_emprunt INT PRIMARY KEY AUTO_INCREMENT,
    id_objet INT,
    id_membre INT,
    date_emprunt DATE NOT NULL,
    date_retour DATE,
    FOREIGN KEY (id_objet) REFERENCES objet(id_objet),
    FOREIGN KEY (id_membre) REFERENCES membre(id_membre)
);

INSERT INTO membre (nom, date_de_naissance, genre, email, ville, mdp, image_profil) VALUES
('Jean Dupont', '1990-05-15', 'M', 'jean.dupont@email.com', 'Paris', 'mdp123', 'profil_jean.jpg'),
('Marie Dubois', '1985-08-22', 'F', 'marie.dubois@email.com', 'Lyon', 'mdp456', 'profil_marie.jpg'),
('Pierre Martin', '1992-03-10', 'M', 'pierre.martin@email.com', 'Marseille', 'mdp789', 'profil_pierre.jpg'),
('Sophie Leroy', '1988-11-30', 'F', 'sophie.leroy@email.com', 'Toulouse', 'mdp101', 'profil_sophie.jpg');

INSERT INTO categorie_objet (nom_categorie) VALUES
('Esthétique'),
('Bricolage'),
('Mécanique'),
('Cuisine');

INSERT INTO objet (nom_objet, id_categorie, id_membre) VALUES
-- Objets de Jean (id_membre=1)
('Miroir décoratif', 1, 1),
('Parfum de luxe', 1, 1),
('Perceuse électrique', 2, 1),
('Marteau', 2, 1),
('Clé à molette', 3, 1),
('Tournevis', 3, 1),
('Mixeur', 4, 1),
('Couteau de chef', 4, 1),
('Lampe design', 1, 1),
('Scie manuelle', 2, 1),
-- Objets de Marie (id_membre=2)
('Palette de maquillage', 1, 2),
('Sèche-cheveux', 1, 2),
('Boîte à outils', 2, 2),
('Ponceuse', 2, 2),
('Pompe à vélo', 3, 2),
('Clé dynamométrique', 3, 2),
('Robot de cuisine', 4, 2),
('Planche à découper', 4, 2),
('Miroir de poche', 1, 2),
('Niveau à bulle', 2, 2),
-- Objets de Pierre (id_membre=3)
('Cadre photo', 1, 3),
('Bijou artisanal', 1, 3),
('Visseuse', 2, 3),
('Échelle pliable', 2, 3),
('Cric hydraulique', 3, 3),
('Clé allen', 3, 3),
('Blender', 4, 3),
('Moule à gâteau', 4, 3),
('Lustre', 1, 3),
('Coffret de visserie', 2, 3),
-- Objets de Sophie (id_membre=4)
('Vase décoratif', 1, 4),
('Trousse de maquillage', 1, 4),
('Scie sauteuse', 2, 4),
('Mètre ruban', 2, 4),
('Testeur électrique', 3, 4),
('Pistolet à peinture', 3, 4),
('Cocotte-minute', 4, 4),
('Mixeur plongeant', 4, 4),
('Tableau décoratif', 1, 4),
('Perforateur', 2, 4);

INSERT INTO emprunt (id_objet, id_membre, date_emprunt, date_retour) VALUES
(11, 1, '2025-01-10', '2025-01-20'), 
(21, 1, '2025-02-15', NULL),      
(31, 1, '2025-03-01', '2025-03-10'),
(1, 2, '2025-02-01', '2025-02-10'),  
(23, 2, '2025-03-15', NULL),        
(33, 2, '2025-04-01', '2025-04-15'), 
(3, 3, '2025-01-20', '2025-02-01'),  
(13, 3, '2025-03-10', NULL),         
(37, 3, '2025-05-01', '2025-05-10'), 
(7, 4, '2025-02-20', '2025-03-01'),  
(17, 4, '2025-04-10', NULL),         
(27, 4, '2025-06-01', '2025-06-15'); 