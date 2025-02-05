<?php
// Ajout de la visibilité des erreurs (c'est un travail de tests, pas de production)
error_reporting(E_ALL);
ini_set('display_errors', 1);

session_start();

// Déclaration des variables globales
$SCRIPT_POSITION = "";
$FICHIER = $SCRIPT_POSITION . 'FICHIER.LIVRET';

// Vérification de l'existance des fichiers de données
if (file_exists($FICHIER)) {
    $contenu = file_get_contents($FICHIER);
    if (empty($contenu)) {
        $solde = 0;
        $interets = 0;
        $depots = 0;
        $retraits = 0;
    } else {
        $fichier_resultats = $SCRIPT_POSITION . 'resultats.txt';
        if (file_exists($fichier_resultats)) {
            $lignes = file($fichier_resultats);
            if (count($lignes) >= 4) {
                $solde = $lignes[0];
                $interets = $lignes[1];
                $depots = $lignes[2];
                $retraits = $lignes[3];
            } /*else {  ***Logique à implémenter***  }*/
        } else {
            $solde = 0;
            $interets = 0;
            $depots = 0;
            $retraits = 0;
        }
    }
}

/*$TRANSACTIONS = file($SCRIPT_POSITION . 'FICHIER.LIVRET');
foreach ($TRANSACTIONS as $transaction) {
    $transaction = substr($chaine, 1, 8);
}*/

// Initialisation du solde si non existant
if (!isset($_SESSION['solde'])) {
    $_SESSION['solde'] = 0;
    $_SESSION['transactions'] = [];
}

// Fonction pour ajouter une transaction
function formatMontant($montant)
{
    // Multiplier par 100 pour convertir en centimes et arrondir
    $centimes = round($montant * 100);
    // Formater avec 8 chiffres (6 pour la partie entière, 2 pour les décimales)
    return sprintf('%08d', $centimes);
}

function ajouterTransaction($type, $montant, $FICHIER, $SCRIPT_POSITION)
{   
    // Déclaration des variables
    $type_transaction = ($type === 'depot') ? 'D' : 'R';
    $montantFormate = formatMontant($montant);
    $content = "$type_transaction$montantFormate" . (file_exists($FICHIER) ? "\n" : "");

    // Mise à jour de la session
    $_SESSION['solde'] += ($type === 'depot') ? $montant : -$montant;
    $_SESSION['transactions'][] = [
        'date' => date('Y-m-d H:i:s'),
        'type' => $type,
        'montant' => $montantFormate,
        'solde' => $_SESSION['solde']
    ];

    // Écriture dans le fichier
    if (file_put_contents($FICHIER, $content, FILE_APPEND) === false) {
        throw new Exception("Erreur lors de l'écriture dans le fichier.");
    }

    // Exécution du script COBOL
    if (file_exists($SCRIPT_POSITION . 'test.bat')) {
        $command = $SCRIPT_POSITION . 'test.bat > ' . $SCRIPT_POSITION . 'output.txt 2>&1';
        exec($command, $output, $return_var);
    } else {
        throw new Exception("Le fichier d'exécution n'existe pas.");
    }

    if ($return_var !== 0) {
        throw new Exception("Erreur lors de l'exécution du script COBOL.");
    }

    return true;
}


// Traitement du formulaire
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $montant = floatval($_POST['montant']);
    $type = $_POST['type'];
    if ($montant > 0) {
        ajouterTransaction($type, $montant, $FICHIER, $SCRIPT_POSITION);
    }
    // Redirection vers la même page pour éviter la soumission multiple du formulaire
    header("Location: " . $_SERVER['PHP_SELF']);
    exit();
}


// Génération des données pour le graphique
$labels = [];
$data = [];
foreach ($_SESSION['transactions'] as $transaction) {
    $labels[] = $transaction['date'];
    $data[] = $transaction['solde'];
}
?>

<!DOCTYPE html>
<html lang="fr">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gestion de Compte Bancaire</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
</head>

<body>
    <div class="container mt-5">
        <h1 class="mb-4">Gestion de Compte Bancaire</h1>
        <div class="row">
            <div class="col-md-6">
                <div class="card mb-4">
                    <div class="card-body">
                        <h5 class="card-title">Solde actuel</h5>
                        <p class="card-text fs-2"><?= $solde ?> €</p>
                    </div>
                </div>
                <div class="card mb-4">
                    <div class="card-body">
                        <h5 class="card-title">Détails du compte</h5>
                        <ul class="list-group list-group-flush">
                            <li class="list-group-item d-flex justify-content-between align-items-center">
                                Intérêts prévus
                                <span class="badge bg-primary rounded-pill"><?= trim($interets) ?> €</span>
                            </li>
                            <li class="list-group-item d-flex justify-content-between align-items-center">
                                Total Dépôts
                                <span class="badge bg-success rounded-pill"><?= trim($depots) ?> €</span>
                            </li>
                            <li class="list-group-item d-flex justify-content-between align-items-center">
                                Total Retraits
                                <span class="badge bg-danger rounded-pill"><?= trim($retraits) ?> €</span>
                            </li>
                        </ul>
                    </div>
                </div>
            </div>
            <div class="col-md-6">
                <canvas id="soldeChart"></canvas>
            </div>
        </div>
        <form method="post" action="<?php echo $_SERVER['PHP_SELF']; ?>" class="mb-4">
            <div class="mb-3">
                <label for="montant" class="form-label">Montant</label>
                <input type="number" step="0.01" class="form-control" id="montant" name="montant" required>
            </div>
            <div class="mb-3">
                <label class="form-label">Type de transaction</label>
                <div>
                    <div class="form-check form-check-inline">
                        <input class="form-check-input" type="radio" name="type" id="depot" value="depot" required>
                        <label class="form-check-label" for="depot">Dépôt</label>
                    </div>
                    <div class="form-check form-check-inline">
                        <input class="form-check-input" type="radio" name="type" id="retrait" value="retrait" required>
                        <label class="form-check-label" for="retrait">Retrait</label>
                    </div>
                </div>
            </div>
            <button type="submit" class="btn btn-primary">Valider</button>
        </form>
        <h2 class="mt-4 mb-3">Historique des transactions</h2>
        <table class="table table-striped">
            <thead>
                <tr>
                    <th>Date</th>
                    <th>Type</th>
                    <th>Montant</th>
                    <th>Solde</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach (array_reverse($_SESSION['transactions']) as $transaction): ?>
                    <tr>
                        <td><?= $transaction['date'] ?></td>
                        <td><?= ucfirst($transaction['type']) ?></td>
                        <td><?= substr($transaction['montant'], 0, -2) . ',' . substr($transaction['montant'], -2) ?> €</td>
                        <td><?= number_format($transaction['solde'], 2) ?> €</td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>

    <script>
        var ctx = document.getElementById('soldeChart').getContext('2d');
        var chart = new Chart(ctx, {
            type: 'line',
            data: {
                labels: <?= json_encode($labels) ?>,
                datasets: [{
                    label: 'Évolution du solde',
                    data: <?= json_encode($data) ?>,
                    borderColor: 'rgb(75, 192, 192)',
                    tension: 0.1
                }]
            },
            options: {
                responsive: true,
                scales: {
                    y: {
                        beginAtZero: true
                    }
                }
            }
        });
    </script>
</body>

</html>