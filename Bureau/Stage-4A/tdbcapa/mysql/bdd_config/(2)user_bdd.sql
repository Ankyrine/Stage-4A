CREATE USER '[nom utilisateur de la bdd]'@'[IP du serveur de connexion]' IDENTIFIED BY '[votre mot de passe]';

GRANT SELECT ,
INSERT ,
UPDATE ,
DELETE ,
FILE ON * . * TO '[nom utilisateur de la bdd]'@'[IP du serveur de connexion]' IDENTIFIED BY '[votre mot de passe]' WITH MAX_QUERIES_PER_HOUR 0 MAX_CONNECTIONS_PER_HOUR 0 MAX_UPDATES_PER_HOUR 0 MAX_USER_CONNECTIONS 0 ;
