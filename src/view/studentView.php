<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gestion des Étudiants</title>
    <link rel="stylesheet" href="src/view/styles.css">
</head>
<body>
<div class="container">
    <h1>Gestion des Étudiants</h1>

    <?php if (isset($message)): ?>
        <div class="message <?= $messageType ?? 'success' ?>">
            <?= htmlspecialchars($message) ?>
        </div>
    <?php endif; ?>

    <div class="container">
        <h2>Ajouter / Modifier un Étudiant</h2>

        <!-- Section de recherche -->
        <div class="search-section">
            <h3 style="margin-top: 0; color: #007bff;">Rechercher un étudiant pour modification</h3>
            <div class="search-input-group">
                <div class="form-group">
                    <label for="search_id">ID de l'étudiant à rechercher :</label>
                    <input type="number" id="search_id" name="search_id" min="1" placeholder="Entrez l'ID de l'étudiant">
                </div>
                <button type="button" id="searchBtn" class="btn-search">Rechercher</button>
                <button type="button" id="clearBtn" class="btn-clear">Effacer</button>
            </div>
            <div id="searchMessage" class="message" style="display: none; margin-top: 15px;"></div>
        </div>

        <form method="POST" action="">
            <div class="form-section">
                <div>
                    <div class="form-group">
                        <label for="id">ID :</label>
                        <input type="number" id="id" name="id" min="1" readonly placeholder="Auto-généré"
                        value="<?= isset($editStudent) ? htmlspecialchars($editStudent->getId()) : '' ?>">
                    </div>

                    <div class="form-group">
                        <label for="firstname">Prénom :</label>
                        <input type="text" id="firstname" name="firstname" required
                               value="<?= isset($editStudent) ? htmlspecialchars($editStudent->getFirstname()) : '' ?>">
                    </div>

                    <div class="form-group">
                        <label for="lastname">Nom :</label>
                        <input type="text" id="lastname" name="lastname" required
                               value="<?= isset($editStudent) ? htmlspecialchars($editStudent->getLastname()) : '' ?>">
                    </div>
                </div>

                <div>
                    <div class="form-group">
                        <label for="date_of_birth">Date de naissance (AAAA-MM-JJ) :</label>
                        <input type="date" id="date_of_birth" name="date_of_birth" required
                               value="<?= isset($editStudent) ? htmlspecialchars($editStudent->getDateOfBirth()) : '' ?>">
                    </div>

                    <div class="form-group">
                        <label for="email">Email :</label>
                        <input type="email" id="email" name="email" required
                               value="<?= isset($editStudent) ? htmlspecialchars($editStudent->getEmail()) : '' ?>">
                    </div>
                </div>
            </div>

            <div class="button-group">
                <button type="submit" name="action" value="add" class="btn-add">Ajouter</button>
                <button type="submit" name="action" value="update" class="btn-update">Modifier</button>
            </div>
        </form>
    </div>

    <div class="container">
        <h2>Liste des Étudiants</h2>
        <?php if (!empty($students)): ?>
            <table id="studentsTable">
                <thead>
                <tr>
                    <th>ID</th>
                    <th>Prénom</th>
                    <th>Nom</th>
                    <th>Date de naissance</th>
                    <th>Email</th>
                    <th>Action</th>
                </tr>
                </thead>
                <tbody>
                <?php foreach ($students as $student): ?>
                    <tr>
                        <td><?= htmlspecialchars($student->getId()) ?></td>
                        <td><?= htmlspecialchars($student->getFirstname()) ?></td>
                        <td><?= htmlspecialchars($student->getLastname()) ?></td>
                        <td><?= htmlspecialchars($student->getDateOfBirth()) ?></td>
                        <td><?= htmlspecialchars($student->getEmail()) ?></td>
                        <td>
                            <button type="button" class="btn-search" style="padding: 5px 10px; font-size: 12px;"
                                    onclick="loadStudentData(<?= $student->getId() ?>, '<?= htmlspecialchars($student->getFirstname()) ?>', '<?= htmlspecialchars($student->getLastname()) ?>', '<?= htmlspecialchars($student->getDateOfBirth()) ?>', '<?= htmlspecialchars($student->getEmail()) ?>')">
                                Modifier
                            </button>
                        </td>
                    </tr>
                <?php endforeach; ?>
                </tbody>
            </table>
        <?php else: ?>
            <p>Aucun étudiant trouvé dans la base de données.</p>
        <?php endif; ?>
    </div>

    <div class="container">
        <div class="delete-section">
            <h2>Supprimer un Étudiant</h2>
            <form method="POST" action="" onsubmit="return confirm('Êtes-vous sûr de vouloir supprimer cet étudiant ?');">
                <div class="form-group">
                    <label for="delete_id">ID de l'étudiant à supprimer :</label>
                    <input type="number" id="delete_id" name="delete_id" min="1" required>
                </div>
                <button type="submit" name="action" value="delete" class="btn-delete">Supprimer</button>
            </form>
        </div>
    </div>
</div>

<script>
    function loadStudentData(id, firstname, lastname, dateOfBirth, email) {
        document.getElementById('id').value = id;
        document.getElementById('firstname').value = firstname;
        document.getElementById('lastname').value = lastname;
        document.getElementById('date_of_birth').value = dateOfBirth;
        document.getElementById('email').value = email;

        document.querySelector('.container:nth-child(2)').scrollIntoView({ behavior: 'smooth' });

        showSearchMessage('Données de l\'étudiant chargées pour modification.', 'info');
    }

    document.getElementById('searchBtn').addEventListener('click', function() {
        const searchId = document.getElementById('search_id').value;

        if (!searchId) {
            showSearchMessage('Veuillez saisir un ID.', 'error');
            return;
        }

        const table = document.getElementById('studentsTable');
        if (table) {
            const rows = table.getElementsByTagName('tr');
            let found = false;

            for (let i = 1; i < rows.length; i++) {
                const cells = rows[i].getElementsByTagName('td');
                if (cells[0] && cells[0].textContent.trim() === searchId) {
                    const id = cells[0].textContent.trim();
                    const firstname = cells[1].textContent.trim();
                    const lastname = cells[2].textContent.trim();
                    const dateOfBirth = cells[3].textContent.trim();
                    const email = cells[4].textContent.trim();

                    loadStudentData(id, firstname, lastname, dateOfBirth, email);
                    found = true;
                    break;
                }
            }

            if (!found) {
                showSearchMessage('Aucun étudiant trouvé avec cet ID.', 'error');
            }
        }
    });

    document.getElementById('clearBtn').addEventListener('click', function() {
        document.getElementById('id').value = '';
        document.getElementById('firstname').value = '';
        document.getElementById('lastname').value = '';
        document.getElementById('date_of_birth').value = '';
        document.getElementById('email').value = '';
        document.getElementById('search_id').value = '';
        hideSearchMessage();
    });

    function showSearchMessage(message, type) {
        const messageDiv = document.getElementById('searchMessage');
        messageDiv.textContent = message;
        messageDiv.className = 'message ' + type;
        messageDiv.style.display = 'block';

        setTimeout(() => {
            hideSearchMessage();
        }, 3000);
    }

    function hideSearchMessage() {
        const messageDiv = document.getElementById('searchMessage');
        messageDiv.style.display = 'none';
    }

    document.getElementById('search_id').addEventListener('keypress', function(e) {
        if (e.key === 'Enter') {
            document.getElementById('searchBtn').click();
        }
    });
</script>
</body>
</html>