<?php
$htdocsDir = realpath("C:/xampp/htdocs");
$projects = array_filter(glob($htdocsDir . '/*'), 'is_dir');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = preg_replace('/[^a-zA-Z0-9_\-]/', '', $_POST['project_name']);
    $code = $_POST['project_code'] ?? '';
    $path = $htdocsDir . DIRECTORY_SEPARATOR . $name;

    if (!file_exists($path)) {
        mkdir($path);
        file_put_contents($path . '/index.php', $code);
        header("Location: " . $_SERVER['PHP_SELF']);
        exit;
    } else {
        $error = "Project '$name' already exists!";
    }
}
?>
<!DOCTYPE html>
<html lang="en" data-bs-theme="light">

<head>
    <meta charset="UTF-8">
    <title>XAMPP Project Dashboard</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        .logo {
            height: 50px;
        }

        #editor {
            height: 300px;
            border: 1px solid #ccc;
        }

        .project-card {
            margin-bottom: 15px;
        }
    </style>

    <script>
        window.openExplorer = function(path) {
            fetch(`explorer.php?path=${encodeURIComponent(path)}`);
        }

        window.openVSCode = function(path) {
            fetch(`vscode.php?path=${encodeURIComponent(path)}`);
        }
    </script>
</head>

<body>
    <div class="container py-4">

        <div class="d-flex justify-content-between align-items-center mb-4">
            <img src="https://lyncdigit.com/assets/images/logo.png" class="logo">
            <div class="d-flex gap-2">
                <button class="btn btn-outline-success" data-bs-toggle="modal" data-bs-target="#createModal">➕ New Project</button>
                <a href="http://localhost/phpmyadmin/" class="btn btn-outline-info" target="_blank">🛢️ phpMyAdmin</a>
                <a href="./phpinfo.php" class="btn btn-outline-info" target="_blank">📘 PHP Info</a>
            </div>
        </div>



        <?php if (!empty($error)): ?>
            <div class="alert alert-danger"><?= htmlspecialchars($error) ?></div>
        <?php endif; ?>

        <h4 class="mb-3">Your XAMPP Projects</h4>
        <div id="searchBoxWrapper" class="mb-3" style="display:none;">
            <input type="text" id="projectSearch" class="form-control" placeholder="🔍 Search Projects...">
        </div>
        <div class="row" id="projectList">
            <?php foreach ($projects as $projectPath):
                $projectName = basename($projectPath);
                if (in_array($projectName, ['.', '..', 'dashboard'])) continue;
            ?>
                <div class="col-md-4 project-card">
                    <div class="card">
                        <div class="card-body">
                            <h5><?= htmlspecialchars($projectName) ?></h5>
                            <p><code><?= htmlspecialchars($projectPath) ?></code></p>
                            <a href="http://localhost/<?= $projectName ?>" class="btn btn-sm btn-primary" target="_blank">🌐 Open</a>
                            <button class="btn btn-sm btn-secondary" onclick="openExplorer('<?= addslashes($projectPath) ?>')">📂 Explorer</button>
                            <button class="btn btn-sm btn-dark" onclick="openVSCode('<?= addslashes($projectPath) ?>')">💻 VS Code</button>
                            <a href="editor.php?project=<?= urlencode($projectName) ?>" class="btn btn-sm btn-warning mt-2">📝 Edit Files</a>
                            <a class="btn btn-sm btn-outline-success mt-2" href="backup.php?folder=<?= urlencode($projectName) ?>">⬇️ Backup</a>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
        <div id="noResultsCard" class="mt-4" style="display: none;"></div>
    </div>

    <!-- Modal: Create Project -->
    <div class="modal fade" id="createModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <form method="post" onsubmit="saveCode()">
                <div class="modal-content">
                    <div class="modal-header bg-primary text-white">
                        <h5 class="modal-title">Create New Project</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body">
                        <div class="mb-3">
                            <label class="form-label">Project Name</label>
                            <input type="text" name="project_name" class="form-control" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">index.php Starter Code</label>
                            <div id="editor">// Start coding...</div>
                            <textarea name="project_code" id="codeOutput" hidden></textarea>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="submit" class="btn btn-success">✅ Create Project</button>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <!-- Bootstrap -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <!-- Monaco -->
    <script src="https://cdn.jsdelivr.net/npm/monaco-editor@0.45.0/min/vs/loader.js"></script>
    <script>
        // Ctrl+F to open search
        document.addEventListener('keydown', function(e) {
            if ((e.ctrlKey || e.metaKey) && e.key === 'f') {
                e.preventDefault();
                const box = document.getElementById('searchBoxWrapper');
                box.style.display = 'block';
                document.getElementById('projectSearch').focus();
            }
        });

        const searchInput = document.getElementById('projectSearch');
        const projectCards = document.querySelectorAll('.project-card');
        const noResultsCard = document.getElementById('noResultsCard');

        searchInput.addEventListener('input', function() {
            const query = this.value.toLowerCase();
            let matches = 0;

            projectCards.forEach(card => {
                const name = card.querySelector('h5').innerText.toLowerCase();
                const visible = name.includes(query);
                card.style.display = visible ? 'block' : 'none';
                if (visible) matches++;
            });

            // Show "Create Project" suggestion
            if (query && matches === 0) {
                noResultsCard.style.display = 'block';
                noResultsCard.innerHTML = `
                <div class="card border-dashed text-center" style="cursor:pointer;" onclick="createFromSearch('${query.replace(/'/g, "\\'")}')">
                    <div class="card-body">
                        <h5 class="text-muted">➕ Create project: <code>${query}</code></h5>
                        <p class="small">No results found. Click to create new project named <strong>${query}</strong>.</p>
                    </div>
                </div>`;
            } else {
                noResultsCard.style.display = 'none';
                noResultsCard.innerHTML = '';
            }
        });

        // ESC = clear and reset
        document.addEventListener('keydown', function(e) {
            if (e.key === 'Escape') {
                searchInput.value = '';
                document.getElementById('searchBoxWrapper').style.display = 'none';
                noResultsCard.style.display = 'none';
                projectCards.forEach(card => card.style.display = 'block');
            }
        });

        function createFromSearch(name) {
            document.querySelector('input[name="project_name"]').value = name;
            bootstrap.Modal.getOrCreateInstance(document.getElementById('createModal')).show();
        }
    </script>

</body>

</html>