# LIVRET-COBOL
 
Un programme vous permettant de suivre l'évolution des actions réalisées sur votre compte bancaire.

![Header Screenshot](Resources/Images/header_screen.png)

## Comment l'utiliser

Renseignez un montant dans l'input dédié et choisissez le type de transaction qui est réalisé (dépôt ou retrait). Une fois la validation réalisée, la transaction sera visible sur la page et le diagramme.

## Le fonctionnement

La page est créée à l'aide d'un mélange de PHP et de JAVAScript. Lorsqu'une transaction est réalisée, PHP appel un script écrit en COBOL qui va calculer le solde du compte bancaire. 

> Lors d'un prochain ajout, il sera possible de voir une simulation des intérêts gagnés en fin d'année.

## Les technologies utilisées

La page web est réalisée en PHP et JAVAScript.
Le script de calcul du solde est réalisé en COBOL.

> Le logiciel MAMP a été utilisé pour le serveur.\
> Le programme a été réalisé sous Windows sans mainframe ou WSL. J'ai donc utilisé GNUCobol.

## Installation

### En version locale

#### Le PHP

Pour exécuter ce projet en local, vous aurez besoin d'un environnement PHP. Voici quelques options :

- Utiliser le serveur web intégré de PHP :
    - Assurez-vous d'avoir PHP installé sur votre système ;
    - Ouvrez un terminal dans le dossier du projet ;
    - Exécutez la commande : `php -S localhost:8000` ;
    - Accédez à http://localhost:8000 dans votre navigateur.
- Installer un serveur web local comme Apache ou Nginx avec PHP ;
- Utiliser un outil de développement tout-en-un comme XAMPP, WAMP ou MAMP.

Choisissez l'option qui convient le mieux à votre environnement de développement.

Déplacez le fichier PHP dans l'endroit adéquoit pour qu'il puisse être exécuté.

#### Le COBOL

Pour exécuter un programme COBOL en local, vous aurez besoin d'un environnement de développement COBOL. Voici quelques options :

1. Utiliser un compilateur COBOL :
    - Installez un compilateur COBOL comme GnuCOBOL sur votre système ;
    - Ouvrez un terminal dans le dossier du projet ;
    - Compilez votre programme avec la commande : `cobc -x -o LIVRET01.exe LIVRET01.cbl` ;
    - Exécutez le programme compilé : `LIVRET01.exe`.
2. Installer un IDE COBOL comme Micro Focus Visual COBOL ou Eclipse avec le plugin COBOL ;
3. Utiliser un environnement mainframe émulé pour les applications COBOL plus complexes.

Choisissez l'option qui convient le mieux à votre environnement de développement.

> Dans l'exemple donné, LIVRET01 est le nom du script COBOL, remplassez-le par le nom que vous lui donnerez.
> L'exemple est uniquement fonctionnel pour un environnement Windows, veillez à adapter les commandes en fonction de votre environnement de développement.

Si vous souhaitez uniquement utiliser le fichier `.exe` et ne pas installer d'environnement adapté au développement COBOL, vous pouvez le faire sans problème, les scripts PHPs sont adaptés. Le script `LIVRET01.cbl` est disponible pour que vous puissiez le modifier selon vos besoins.

L'exécutable `LIVRET01.exe` doit être présent dans le dossier `/Resources/Scripts/` comme dans dépôt GitHub.

Pour modifier l'emplacement de l'exécutable, il faut modifier la variable `$SCRIPT_POSITION` dans le fichier `transactions.php` à votre convenance.

Le fichier `FICHIER.LIVRET` contient toutes les transactions du compte et est nécessaire au bon fonctionnement du programme `LIVRET01.exe`. Celui-ci est placé dans le même dossier que l'exécutable `LIVRET01.exe`.

Pour modifier le nom du fichier, il vous faut modifier la variable `FICHIER-TRANSACTIONS` dans le script COBOL, accessible à la ligne 000010.

L'intérêt de fin d'année est ici fixée à 3% (0,03). Il est possible de le modifier en changeant la valeur `WS-TAUX-INTERETS` à la ligne 000035.

La logique du calcul est ici extrêmement simplifiée, ne se composant que d'une simple augmentation du solde de 3%. Cette logique est actuellement modifiable dans le PARAGRAPHE `AFFICHER-RESULTATS` de la ligne 000092. Ke calcul est pour le moment réalisé aux lignes 000094 et 000095 dans la PHRASE suivante :
```
000094     COMPUTE WS-INTERETS-ANNUELS = WS-SOLDE * WS-TAUX-INTERETS
000095     ADD WS-INTERETS-ANNUELS TO WS-SOLDE.
```

## Les ajouts envisagés

### Affichage du solde grâce au code COBOL

Le code COBOL réalise actuellement le calcul du solde avec les intérêts inclus, mais le code PHP le réalise également. L'idée est de retirer la logique PHP pour augmenter l'impact de la logique COBOL.

### Affichage des intérêts

Les intérêts sont actuellements calculés dans le code COBOL mais ne sont pas affichés sur la page web.

### Nouveau calcul des intérêts

Les intérêts sont calculés de manière très simplifiée, il est envisagé de réalisé un calcul plus réaliste des intérêts.

### Permettre la modification de variables

Les variables telles que le taux d'intérêt ou le nom du fichier ne sont modifiable quand le script `LIVRET01.cbl` et celui-ci doit être de nouveau compilé pour pouvoir être exécuté. L'idée serait d'avoir un fichier externe (comme `FICHIER.LIVRET`) qui permette d'indiquer la valeur des variables. Ces valeurs pourraient ainsi être modifiable via la page web dans les options.